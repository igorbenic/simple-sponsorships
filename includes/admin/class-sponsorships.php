<?php
/**
 * Admin part for Levels
 */

namespace Simple_Sponsorships\Admin;
use Simple_Sponsorships\DB\DB_Sponsorships;

/**
 * Class Levels
 *
 * @package Simple_Sponsorships\Admin
 */
class Sponsorships {

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
		add_action( 'ss_admin_page_ss_sponsorships', array( $this, 'page' ) );
		add_action( 'ss_new-sponsorship', array( $this, 'new_sponsorship' ) );
		add_action( 'ss_edit-sponsorship', array( $this, 'edit_sponsorship' ) );

		$this->errors = new \WP_Error();
	}

	/**
	 * Creating a New Sponsorship.
	 */
	public function new_sponsorship() {

		if ( ! isset( $_POST['ss-action'] ) || 'add_sponsorship' !== $_POST['ss-action'] ) {
			return;
		}

		$posted_data    = isset( $_POST['ss_sponsorships'] ) ? $_POST['ss_sponsorships'] : array();
		$status         = isset( $posted_data['status'] ) ? sanitize_text_field( $posted_data['status'] ) : '';
		$amount         = isset( $posted_data['amount'] ) ? floatval( $posted_data['amount'] ) : 0;
		$package        = isset( $posted_data['package'] ) ? absint( $posted_data['package'] ) : 0;
		$sponsor        = isset( $posted_data['sponsor'] ) ? $posted_data['sponsor'] : false;
		$transaction_id = isset( $posted_data['transaction_id'] ) ? sanitize_text_field( $posted_data['transaction_id'] ) : '';

		if ( ! $status ) {
			$this->errors->add( 'no-status', __( 'No Status is required.', 'simple-sponsorships' ) );
		}

		if ( 'new' === $sponsor ) {
			$sponsor_name = isset( $posted_data['sponsor_name'] ) ? sanitize_text_field( $posted_data['sponsor_name'] ) : '';

			if ( ! $sponsor_name ) {
				$this->errors->add( 'no-sponsor', __( 'Select a Sponsor or insert the name to add a new one.', 'simple-sponsorships' ) );
				return;
			}

			$sponsor_id = wp_insert_post( array(
				'post_status' => 'publish',
				'post_type'   => 'sponsors',
				'post_title'  => $sponsor_name
			) );

			if ( is_wp_error( $sponsor_id ) ) {
				$this->errors->add( $sponsor_id->get_error_code(), $sponsor_id->get_error_message() );
			}

			$sponsor = $sponsor_id;
		}

		if ( $this->errors->get_error_messages() ) {
			return;
		}


		$sponsorship_id = ss_create_sponsorship(array(
			'status'         => $status,
			'amount'         => $amount,
			'gateway'        => 'manual',
			'transaction_id' => $transaction_id,
			'package'        => $package,
			'sponsor'        => $sponsor,
		));

		if ( ! $sponsorship_id ) {
			$this->errors->add( 'cant-create', __( 'Sponsorship could not be created.', 'simple-sponsorships' ) );
			return;
		}

		do_action( 'ss_sponsorship_added', $sponsorship_id );

		if ( isset( $_POST['ss-redirect'] ) ) {
			wp_safe_redirect( $_POST['ss-redirect'] );
			exit;
		}
	}

	/**
	 * Creating a New Sponsorship.
	 */
	public function edit_sponsorship() {

		if ( ! isset( $_POST['ss-action'] ) || 'edit_sponsorship' !== $_POST['ss-action'] ) {
			return;
		}

		$posted_data    = isset( $_POST['ss_sponsorships'] ) ? $_POST['ss_sponsorships'] : array();
		$status         = isset( $posted_data['status'] ) ? sanitize_text_field( $posted_data['status'] ) : '';
		$amount         = isset( $posted_data['amount'] ) ? floatval( $posted_data['amount'] ) : 0;
		$package        = isset( $posted_data['package'] ) ? absint( $posted_data['package'] ) : 0;
		$sponsor        = isset( $posted_data['sponsor'] ) ? $posted_data['sponsor'] : false;
		$gateway        = isset( $posted_data['gateway'] ) ? $posted_data['gateway'] : 'manual';
		$transaction_id = isset( $posted_data['transaction_id'] ) ? sanitize_text_field( $posted_data['transaction_id'] ) : '';
		$id             = isset( $posted_data['id'] ) ? absint( $posted_data['id'] ) : 0;

		if ( ! $status ) {
			$this->errors->add( 'no-status', __( 'No Status is required.', 'simple-sponsorships' ) );
		}

		if ( ! $id ) {
			$this->errors->add( 'no-id', __( 'This Sponsorship does not exist.', 'simple-sponsorships' ) );
		}

		if ( $this->errors->get_error_messages() ) {
			return;
		}

		if ( 'new' === $sponsor ) {
			$sponsor_name = isset( $posted_data['sponsor_name'] ) ? sanitize_text_field( $posted_data['sponsor_name'] ) : '';

			if ( ! $sponsor_name ) {
				$this->errors->add( 'no-sponsor', __( 'Select a Sponsor or insert the name to add a new one.', 'simple-sponsorships' ) );
				return;
			}

			$sponsor_id = wp_insert_post( array(
				'post_status' => 'publish',
				'post_type'   => 'sponsors',
				'post_title'  => $sponsor_name
			) );

			if ( is_wp_error( $sponsor_id ) ) {
				$this->errors->add( $sponsor_id->get_error_code(), $sponsor_id->get_error_message() );
			}

			$sponsor = $sponsor_id;
		}

		if ( $this->errors->get_error_messages() ) {
			return;
		}

		$db = new DB_Sponsorships();
		$db_data = array(
			'status'         => $status,
			'amount'         => $amount,
			'gateway'        => $gateway,
			'transaction_id' => $transaction_id,
			'package'        => $package,
			'sponsor'        => $sponsor,
		);

		$ret = $db->update( $id, $db_data, array( '%s', '%s', '%s', '%d' ) );

		if ( $ret ) {
			do_action( 'ss_sponsorship_updated', $id, $posted_data );
		}
	}

	/**
	 * Admin Page
	 */
	public function page() {
		$action = isset( $_GET['ss-action'] ) ? $_GET['ss-action'] : 'list';

		switch( $action ) {
			case 'edit-sponsorship':
				$errors      = $this->errors->get_error_messages();
				$fields      = $this->get_fields();
				$sponsorship = isset( $_GET['id'] ) ? ss_get_sponsorship( absint( $_GET['id'] ) ) : ss_get_sponsorship(0 );
				include_once 'views/sponsorships/edit.php';
				break;
			case 'new-sponsorship':
				$errors = $this->errors->get_error_messages();
				$fields = $this->get_fields();
				include_once 'views/sponsorships/new.php';
				break;
			default:
				include_once 'sponsorships/class-sponsorships-table-list.php';

				$list = new Sponsorships_Table_List();

				include_once 'views/sponsorships/list.php';
				break;
		}

	}

	/**
	 * Return the level fields.
	 *
	 * @return array
	 */
	public function get_fields() {
		$all_packages = ss_get_packages();
		$packages     = array();

		if ( $all_packages ) {
			foreach ( $all_packages as $_package ) {
				$packages[ $_package['ID'] ] = $_package['title'];
			}
		}

		$all_sponsors = ss_get_sponsors();
		$sponsors     = array(
			'new' => __( 'Create a new sponsor', 'simple-sponsorships' ),
		);

		if ( $all_sponsors ) {
			foreach ( $all_sponsors as $_sponsor ) {
				$sponsors[ $_sponsor->ID ] = $_sponsor->post_title;
			}
		}
		$fields = array(
			'status' => array(
				'id'      => 'status',
				'type'    => 'select',
				'title'   => __( 'Status', 'simple-sponsorships' ),
				'options' => ss_get_sponsorship_statuses()
			),
			'amount' => array(
				'id'          => 'amount',
				'type'        => 'number',
				'placeholder' => __( 'The Sponsorship Amount', 'simple-sponsorships' ),
				'title'       => sprintf( __( 'Amount (%s)', 'simple-sponsorships' ), ss_get_currency() ),
			),
			'package' => array(
				'id'      => 'package',
				'type'    => 'select',
				'title'   => __( 'Package', 'simple-sponsorships' ),
				'options' => $packages,
			),
			'sponsor_heading' => array(
				'id'      => 'sponsor_heading',
				'type'    => 'heading',
				'title'   => __( 'Sponsor', 'simple-sponsorships' ),
			),
			'sponsor' => array(
				'id'      => 'sponsor',
				'type'    => 'select',
				'title'   => __( 'Sponsor', 'simple-sponsorships' ),
				'options' => $sponsors
			),
			'sponsor_name' => array(
				'id'      => 'sponsor_name',
				'type'    => 'text',
				'title'   => __( 'First Name', 'simple-sponsorships' ),
			),
			'payment_heading' => array(
				'id'      => 'payment_heading',
				'type'    => 'heading',
				'title'   => __( 'Payment', 'simple-sponsorships' ),
			),
			'transaction_id' => array(
				'id'          => 'transaction_id',
				'type'        => 'text',
				'placeholder' => __( 'Transaction ID', 'simple-sponsorships' ),
				'title'       => __( 'Transaction ID', 'simple-sponsorships' ),
				'desc'        => __( 'The transaction ID, if any', 'simple-sponsorships' ),
			),
		);

		return apply_filters( 'ss_get_package_fields', $fields );
	}

	/**
	 * Return Level Types.
	 *
	 * @return array
	 */
	public static function get_types() {
		return apply_filters( 'ss_package_types', array(
			'normal' => __( 'Normal', 'simple-sponsorships' ),
		));
	}
}

new Sponsorships();