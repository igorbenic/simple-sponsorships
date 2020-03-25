<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 14/11/18
 * Time: 01:55
 */

namespace Simple_Sponsorships\Admin;

use Simple_Sponsorships\DB\DB_Packages;
use Simple_Sponsorships\Formatting;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Packages_Table_List extends \WP_List_Table {

	/**
	 * Settings
	 *
	 * @var null|array
	 */
	public $settings = null;

	/** Class constructor */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Package', 'simple-sponsorships' ), //singular name of the listed records
			'plural'   => __( 'Packages', 'simple-sponsorships' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?
		));

		$this->_column_headers = $this->get_columns();
	}

	/**
	 * Get the settings
	 */
	public function get_settings() {
		if ( null === $this->settings ) {
			$this->settings = ss_get_settings();
		}

		return $this->settings;
	}

	/**
	 * Retrieve level's data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_packages( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = 'SELECT * FROM ' . $wpdb->sspackages . ' WHERE 1=1';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$sql .= ' LIMIT ' . $per_page;

		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * Delete a level
	 *
	 * @param int $id Level ID
	 */
	public static function delete_package( $id ) {
		$db = new DB_Packages();

		$db->delete_by_id( $id );
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = 'SELECT COUNT(*) FROM ' . $wpdb->sspackages;

		return $wpdb->get_var( $sql );
	}

	/**
	 * Text to display when there is no level found.
	 */
	public function no_items() {
		_e( 'No packages.', 'simple-sponsorships' );
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_title( $item ) {
		$html = $item['title'];

		$actions = apply_filters( 'ss_packages_column_title_actions', array(
			'edit' => '<a href="' . admin_url( 'edit.php?post_type=sponsors&page=ss-packages&ss-action=edit-package&id=' . $item['ID'] ) . '">' . __( 'Edit', 'simple-sponsorships' ) . '</a>',
		));

		if ( $actions ) {
			$html .= '<div class="ss-table-actions">' . implode( ' | ', $actions ) . '</div>';
		}
		return $html;
	}

	/**
	 * Column amount
	 * @param $item
	 *
	 * @return string
	 */
	public function column_price( $item ) {
		$price = Formatting::price( $item['price'] );
		return apply_filters( 'ss_packages_column_price', $price, $item );
	}

	/**
	 * Column type
	 * @param $item
	 *
	 * @return string
	 */
	public function column_type( $item ) {
		$types = ss_get_package_types();

		if ( ! isset( $item['type'] ) || ! $item['type'] ) {
			$type = 'onetime';
		} else {
			$type = isset( $types[ $item['type'] ] ) ? $types[ $item['type'] ] : $item['type'];
		}

		return $type;
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);
	}

	public function column_status( $item ) {
		$statuses = ss_get_package_statuses();
		return isset( $statuses[ $item['status'] ] ) ? $statuses[ $item['status'] ] : __( 'Available', 'simple-sponsorships' );
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'title'    => __( 'Title', 'simple-sponsorships' ),
			'quantity' => __( 'Quantity', 'simple-sponsorships' ),
			'price'    => __( 'Price', 'simple-sponsorships' ),
			'status'   => __( 'Status', 'simple-sponsorships' ),
		);

		$types = ss_get_package_types();

		if ( count( $types ) > 1 ) {
			$columns['type'] = __( 'Type', 'simple-sponsorships' );
		}

		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'price' => array( 'price', true )
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => 'Delete'
		);

		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'levels_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );


		$this->items = self::get_packages( $per_page, $current_page );

	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'giveasap_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_package( absint( $_GET['user'] ) );


			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_package( $id );

			}
		}
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @since 2.10.0
	 *
	 * @param string $which
	 */
	/*protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			$email = isset( $_REQUEST['ga_search_email'] ) ? $_REQUEST['ga_search_email'] : '';
			?>
			<div class="alignleft actions">
				<input type="email" name="ga_search_email" value="<?php echo esc_attr( $email ); ?>"
				       placeholder="<?php esc_attr_e( 'Search for an Email', 'giveasap' ); ?>"/>
				<button class="button ga-search-email"><?php _e( 'Search', 'giveasap' ); ?></button>
			</div>
			<?php
		}
	}*/
}