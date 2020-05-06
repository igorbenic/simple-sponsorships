<?php
/**
 * Admin part for Levels
 */

namespace Simple_Sponsorships\Admin;
use Simple_Sponsorships\DB\DB_Packages;

/**
 * Class Levels
 *
 * @package Simple_Sponsorships\Admin
 */
class Packages {

	/**
	 * Errors when processing packages.
	 *
	 * @var null|\WP_Error
	 */
	public $errors = null;

	/**
	 * Levels constructor.
	 */
	public function __construct() {
		add_action( 'ss_admin_page_ss_packages', array( $this, 'page' ) );
		add_action( 'ss_new-package', array( $this, 'new_package' ) );
		add_action( 'ss_edit-package', array( $this, 'edit_package' ) );

		$this->errors = new \WP_Error();
	}

	/**
	 * Creating a New Package.
	 */
	public function new_package() {

		if ( ! isset( $_POST['ss-action'] ) || 'add_package' !== $_POST['ss-action'] ) {
			return;
		}

		$posted_data = isset( $_POST['ss_packages'] ) ? $_POST['ss_packages'] : array();
		$title       = isset( $posted_data['title'] ) ? sanitize_text_field( $posted_data['title'] ) : '';
		$status      = isset( $posted_data['status'] ) ? sanitize_text_field( $posted_data['status'] ) : '';
		$description = isset( $posted_data['description'] ) ? $posted_data['description'] : '';
		$quantity    = isset( $posted_data['quantity'] ) ? absint( $posted_data['quantity'] ) : 1;
		$price       = isset( $posted_data['price'] ) ? floatval( $posted_data['price'] ) : 0;
		$type        = isset( $posted_data['type'] ) && $posted_data['type'] ? sanitize_text_field( $posted_data['type'] ) : 'onetime';

		if ( ! $title ) {
			$this->errors->add( 'no-name', __( 'Package Name is required.', 'simple-sponsorships' ) );
		}

		if ( $this->errors->get_error_messages() ) {
			return;
		}

		$db = new DB_Packages();
		$db_data = array(
			'title'       => $title,
			'description' => $description,
			'quantity'    => $quantity,
			'price'       => $price,
			'status'      => $status,
			'type'        => $type,
		);

		$package_id = $db->insert( $db_data, array( '%s', '%s', '%s', '%s', '%s' ) );

		do_action( 'ss_package_added', $package_id, $posted_data );

		if ( isset( $_POST['ss-redirect'] ) ) {
			wp_safe_redirect( $_POST['ss-redirect'] );
			exit;
		}
	}

	/**
	 * Creating a New Package.
	 */
	public function edit_package() {

		if ( ! isset( $_POST['ss-action'] ) || 'edit_package' !== $_POST['ss-action'] ) {
			return;
		}

		$posted_data = isset( $_POST['ss_packages'] ) ? $_POST['ss_packages'] : array();
		$title       = isset( $posted_data['title'] ) ? sanitize_text_field( $posted_data['title'] ) : '';
		$status      = isset( $posted_data['status'] ) ? sanitize_text_field( $posted_data['status'] ) : '';
		$description = isset( $posted_data['description'] ) ?  $posted_data['description'] : '';
		$quantity    = isset( $posted_data['quantity'] ) ? absint( $posted_data['quantity'] ) : 1;
		$price       = isset( $posted_data['price'] ) ? floatval( $posted_data['price'] ) : 0;
		$type        = isset( $posted_data['type'] ) && $posted_data['type'] ? sanitize_text_field( $posted_data['type'] ) : 'onetime';
		$id          = isset( $posted_data['id'] ) ? absint( $posted_data['id'] ) : 0;

		if ( ! $title ) {
			$this->errors->add( 'no-name', __( 'Package Name is required.', 'simple-sponsorships' ) );
		}

		if ( ! $id ) {
			$this->errors->add( 'no-id', __( 'This package does not exist.', 'simple-sponsorships' ) );
		}

		if ( $this->errors->get_error_messages() ) {
			return;
		}

		$db = new DB_Packages();
		$db_data = array(
			'title'       => $title,
			'description' => $description,
			'quantity'    => $quantity,
			'price'       => $price,
			'status'      => $status,
			'type'        => $type,
		);

		$ret = $db->update( $id, $db_data, array( '%s', '%s', '%s', '%s', '%s' ) );

		if ( $ret ) {
			// We are sure that there were changes to the main package fields.
			do_action( 'ss_package_updated', $id, $posted_data );
		}
	}

	/**
	 * Admin Page
	 */
	public function page() {
		$action = isset( $_GET['ss-action'] ) ? sanitize_text_field( $_GET['ss-action'] ) : 'list';

		switch( $action ) {
			case 'edit-package':
				$errors = $this->errors->get_error_messages();
				$fields = $this->get_package_fields();
				$package = isset( $_GET['id'] ) ? ss_get_package( absint( $_GET['id'] ) ) : ss_get_package(0 );
				include_once 'views/packages/edit.php';
				break;
			case 'new-package':
				$errors = $this->errors->get_error_messages();
				$fields = $this->get_package_fields();
				include_once 'views/packages/new.php';
				break;
			default:
				include_once 'packages/class-packages-table-list.php';

				$list = new Packages_Table_List();

				include_once 'views/packages/list.php';
				break;
		}

	}

	/**
	 * Return the level fields.
	 *
	 * @return array
	 */
	public function get_package_fields() {

		$types = ss_get_package_types();

		$fields = array(
			'title' => array(
				'id' => 'title',
				'type' => 'text',
				'placeholder' => __( 'Enter the Package Name', 'simple-sponsorships' ),
				'title' => __( 'Title', 'simple-sponsorships' ),
			),
			'status' => array(
				'id' => 'status',
				'type' => 'select',
				'placeholder' => __( 'Choose a Status', 'simple-sponsorships' ),
				'title' => __( 'Status', 'simple-sponsorships' ),
				'field_class' => 'widefat',
				'options' => ss_get_package_statuses(),
				'default' => 'active',
			),
			'description' => array(
				'id' => 'description',
				'type' => 'editor',
				'placeholder' => __( 'Enter the Package Description', 'simple-sponsorships' ),
				'title' => __( 'Description', 'simple-sponsorships' ),
				'field_class' => 'widefat',
			),
			'type' => array(
				'id' => 'type',
				'type' => $types && count( $types ) > 1 ? 'select' : 'hidden',
				'placeholder' => __( 'Payment Type', 'simple-sponsorships' ),
				'title'   => __( 'Payment Type', 'simple-sponsorships' ),
				'options' => $types,
				'default' => 'onetime',
			),
			'price' => array(
				'id' => 'price',
				'type' => 'number',
				'title' => __( 'Price', 'simple-sponsorships' ),
				'field_class' => 'widefat',
				'step' => '0.01',
			),
			'quantity' => array(
				'id' => 'quantity',
				'type' => 'number',
				'title' => __( 'Quantity', 'simple-sponsorships' ),
				'field_class' => 'widefat',
				'default' => '1',
				'desc' => __( 'How many content will this package allow to subscribe to. For example, a 12 episode package, would have 12 here.', 'simple-sponsorships' ),
			)
		);

		return apply_filters( 'ss_get_package_fields', $fields );
	}
}

new Packages();