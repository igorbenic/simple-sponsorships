<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 14/11/18
 * Time: 01:55
 */

namespace Simple_Sponsorships\Admin;

use Simple_Sponsorships\DB\DB_Sponsorships;
use Simple_Sponsorships\Formatting;
use Simple_Sponsorships\Package;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Sponsorships_Table_List extends \WP_List_Table {

	/**
	 * Date Format
	 * @var null|string
	 */
	protected $date_format = null;

	/**
	 * Sponsorship Page-
	 *
	 * @var string|string
	 */
	protected $sponsorship_page = null;

	/**
	 * Settings
	 *
	 * @var null|array
	 */
	public $settings = null;

	/** Class constructor */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Sponsorship', 'simple-sponsorships' ), //singular name of the listed records
			'plural'   => __( 'Sponsorships', 'simple-sponsorships' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?
		));

		$this->_column_headers = $this->get_columns();
	}

	/**
	 * Get the Date Format.
	 *
	 * @return null|string
	 */
	public function get_date_format() {
		if ( null === $this->date_format ) {
			$this->date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		return $this->date_format;
	}

	/**
	 * Get the Sponsorship Page
	 *
	 * @return null|string
	 */
	public function get_sponsorship_page() {
		if ( null === $this->sponsorship_page ) {
			$this->sponsorship_page = get_permalink( ss_get_option( 'sponsorship_page', 0 ) );
		}

		return $this->sponsorship_page;
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
     * Get Where Array for creating SQL
	 * @return mixed
	 */
	public static function get_where() {
		$sql_where = array();

		if ( isset( $_REQUEST['ss_filter_sponsors'] ) && $_REQUEST['ss_filter_sponsors'] ) {
			$sql_where['sponsor'] = absint( $_REQUEST['ss_filter_sponsors'] );
		}

		if ( isset( $_REQUEST['ss_filter_statuses'] ) && $_REQUEST['ss_filter_statuses'] ) {
			$sql_where['status'] = sanitize_text_field( $_REQUEST['ss_filter_statuses'] );
		}

        if ( isset( $_REQUEST['ss_filter_packages'] ) && $_REQUEST['ss_filter_packages'] ) {
	        $sql_where['package'] = sanitize_text_field( $_REQUEST['ss_filter_packages'] );
        }

		return apply_filters( 'ss_sponsorships_table_list_sql_where', $sql_where );
    }

	/**
	 * Retrieve level's data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_sponsorships( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = 'SELECT * FROM ' . $wpdb->sssponsorships;

        $where = self::get_where();

        if ( $where ) {
            $sql .= ' WHERE 1=1 ';
            foreach ( $where as $column => $column_value ) {
                $sql .= $wpdb->prepare( 'AND ' . $column . '=%s ', $column_value );
            }
        }

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
	public static function delete_sponsorship( $id ) {
		$db = new DB_Sponsorships();

		$db->delete_by_id( $id );
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = 'SELECT COUNT(*) FROM ' . $wpdb->sssponsorships;

		$where = self::get_where();

		if ( $where ) {
			$sql .= ' WHERE 1=1 ';
			foreach ( $where as $column => $column_value ) {
				$sql .= $wpdb->prepare( ' AND ' . $column . '=%s ', $column_value );
			}
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * Text to display when there is no level found.
	 */
	public function no_items() {
		_e( 'No Sponsorships.', 'simple-sponsorships' );
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
	 * Column amount
	 * @param $item
	 *
	 * @return string
	 */
	public function column_amount( $item ) {
		return Formatting::price( $item['amount'] );
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
		$html = sprintf( __( 'Sponsorship #%d', 'simple-sponsorships' ), $item['ID'] );

		$actions = apply_filters( 'ss_sponsorships_column_title_actions', array(
			'edit' => '<a href="' . admin_url( 'edit.php?post_type=sponsors&page=ss-sponsorships&ss-action=edit-sponsorship&id=' . $item['ID'] ) . '">' . __( 'Edit', 'simple-sponsorships' ) . '</a>',
		));

		$sponsorship_page = $this->get_sponsorship_page();

		if ( $sponsorship_page ) {
			$actions['view'] = '<a href="' . add_query_arg( 'sponsorship-key', $item['ss_key'], $sponsorship_page ) . '">' . __( 'View', 'simple-sponsorships' ) . '</a>';

		}

		if ( $actions ) {
			$html .= '<div class="ss-table-actions">' . implode( ' | ', $actions ) . '</div>';
		}
		return $html;
	}

	/**
	 * Get the package title
	 * @param $item
	 *
	 * @return mixed|string
	 */
	public function column_package( $item ) {
	    $sponsorship = ss_get_sponsorship( $item['ID'], false );
	    $packages    = $sponsorship->get_packages();

	    if ( ! $packages ) {
	        $html = __( 'N/A', 'simple-sponsorships' );
        } elseif ( count( $packages ) > 4 ) {
	        $html = sprintf( _n( '%s Package', '%s Packages', count( $packages ), 'simple-sponsorships' ), count( $packages ) );
        } else {
	        $titles = array();
	        foreach ( $packages as $package ) {
	            $titles[] = $package->get_data( 'title' );
            }
            $html = implode( ', ', $titles );
        }


		/*$package = new Package( absint( $item['package' ] ) );
		$actions = apply_filters( 'ss_sponsorships_column_package_actions', array(
			'view' => '<a href="' . admin_url( 'edit.php?post_type=sponsors&page=ss-packages&ss-action=edit-package&id=' . $item['package'] ) . '">' . __( 'View', 'simple-sponsorships' ) . '</a>',
		));

		if ( ! $item['package'] ) {
			$actions = array();
		}

		if ( $actions ) {
			//$html .= '<div class="ss-table-actions">' . implode( ' | ', $actions ) . '</div>';
		}*/

		return $html;
	}

	/**
	 * Get the Sponsor
	 * @param $item
	 *
	 * @return mixed|string
	 */
	public function column_sponsor( $item ) {
		$html    = $item['sponsor'] ? get_the_title( $item['sponsor'] ) : __( 'N/A', 'simple-sponsorships' );
		$actions = apply_filters( 'ss_sponsorships_column_sponsor_actions', array(
			'view' => '<a href="' . admin_url( 'post.php?post=' . $item['sponsor'] . '&action=edit' ) . '">' . __( 'View', 'simple-sponsorships' ) . '</a>',
		));

		if ( ! $item['sponsor'] ) {
			$actions = array();
		}

		if ( $actions ) {
			$html .= '<div class="ss-table-actions">' . implode( ' | ', $actions ) . '</div>';
		}
		return $html;
	}

	/**
	 * Get the Date
	 * @param $item
	 *
	 * @return mixed|string
	 */
	public function column_date( $item ) {
		$html = $item['date'] ? date_i18n( $this->get_date_format(), strtotime( $item['date'] ) ) : __( 'N/A', 'simple-sponsorships' );

		return $html;
	}

	/**
	 * Status of the Sponsorship.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_status( $item ) {
		$statuses = ss_get_sponsorship_statuses();
		$html     = isset( $statuses[ $item['status'] ] ) ? $statuses[ $item['status'] ] : __( 'Unknown Status', 'simple-sponsorships' );
		$class    = isset( $statuses[ $item['status'] ] ) ? $item['status'] : 'unknown';
		return sprintf( '<div class="ss-sponsorship-status status-%1$s">%2$s</div>', $class, $html );
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

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'title'   => __( 'Title', 'simple-sponsorships' ),
			'status'  => __( 'Status', 'simple-sponsorships' ),
			'package' => __( 'Package', 'simple-sponsorships' ),
			'sponsor' => __( 'Sponsor', 'simple-sponsorships' ),
			'amount'  => __( 'Amount', 'simple-sponsorships' ),
			'date'    => __( 'Date', 'simple-sponsorships' ),
		);

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


		$this->items = self::get_sponsorships( $per_page, $current_page );

	}

	/**
	 * Processing Bulk Action
	 */
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'giveasap_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_sponsorship( absint( $_GET['user'] ) );


			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_sponsorship( $id );

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
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			$sponsors = ss_get_sponsors();
			$statuses = ss_get_sponsorship_statuses();
			$packages = ss_get_packages();
			?>
			<div class="alignleft actions">
				<?php
				if ( $sponsors ) {
				    $selected_sponsor = isset( $_REQUEST['ss_filter_sponsors'] ) ? absint( $_REQUEST['ss_filter_sponsors'] ) : '';
					echo '<select name="ss_filter_sponsors" class="ss-filter">';
					echo '<option value="">' . __( 'Filter by Sponsor', 'simple-sponsorships' ) . '</option>';
					foreach ( $sponsors as $sponsor ) {
					    echo '<option ' . selected( $selected_sponsor, $sponsor->ID, false ) . ' value="' . esc_attr( $sponsor->ID ) . '">' . esc_html( $sponsor->post_title ) . '</option>';
                    }
					echo '</select>';
                }

				if ( $statuses ) {
					$selected_status = isset( $_REQUEST['ss_filter_statuses'] ) ? sanitize_text_field( $_REQUEST['ss_filter_statuses'] ) : '';
					echo '<select name="ss_filter_statuses" class="ss-filter">';
					echo '<option value="">' . __( 'Filter by Status', 'simple-sponsorships' ) . '</option>';
					foreach ( $statuses as $status => $status_text ) {
						echo '<option ' . selected( $selected_status, $status, false ) . ' value="' . esc_attr( $status ) . '">' . esc_html( $status_text ) . '</option>';
					}
					echo '</select>';
				}

				if ( $packages ) {
					$selected_package = isset( $_REQUEST['ss_filter_packages'] ) ? sanitize_text_field( $_REQUEST['ss_filter_packages'] ) : '';
					echo '<select name="ss_filter_packages" class="ss-filter">';
					echo '<option value="">' . __( 'Filter by Package', 'simple-sponsorships' ) . '</option>';
					foreach ( $packages as $package ) {
						echo '<option ' . selected( $selected_package, $package['ID'], false ) . ' value="' . esc_attr( $package['ID'] ) . '">' . esc_html( $package['title'] ) . '</option>';
					}
					echo '</select>';
				}
				?>

                <?php do_action( 'ss_sponsorships_table_list_filters' ); ?>
				<button class="button ss-button-table-list-filter"><?php _e( 'Filter', 'simple-sponsorships' ); ?></button>
			</div>
			<?php
		}
	}
}