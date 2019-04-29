<?php
/**
 * AJAX for Simple Sponsorships
 */

namespace Simple_Sponsorships;

if ( ! \defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class AJAX
 *
 * @package Simple_Sponsorships
 */
class AJAX {

	/**
	 * AJAX constructor.
	 */
	public function __construct() {

		$actions = array(
			'get_available_sponsors' => false,
			'get_packages' => false,
			'add_quantity_sponsor' => false,
			'remove_quantity_sponsor' => false,
			'remove_sponsor_from_content' => false,
			'sponsorship_calculate_totals' => false,
		);

		foreach ( $actions as $action => $nopriv ) {
			add_action( 'wp_ajax_ss_' . $action, array( $this, $action ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_ss_' . $action, array( $this, $action ) );
			}
		}
	}

	/**
	 * Removing Sponsor from a Content.
	 */
	public function remove_sponsor_from_content() {
		check_ajax_referer( 'ss-admin-nonce', 'nonce', true );

		$data = isset( $_REQUEST['data'] ) ? $_REQUEST['data'] : array();

		if ( ! $data ) {
			wp_send_json_error( __( 'No data found', 'simple-sponsorships' ) );
			wp_die();
		}

		$sponsor_id = isset( $data['sponsor'] ) ? absint( $data['sponsor'] ) : 0;

		if ( ! $sponsor_id ) {
			wp_send_json_error( __( 'No Sponsor provided', 'simple-sponsorships' ) );
			wp_die();
		}

		$content_id = isset( $data['content'] ) ? absint( $data['content'] ) : 0;

		if ( ! $content_id ) {
			wp_send_json_error( __( 'No Content provided', 'simple-sponsorships' ) );
			wp_die();
		}

		ss_delete_sponsors_for_content( $content_id, $sponsor_id );
		wp_send_json_success();
		wp_die();
	}

	/**
	 * Add a quantity on the Sponsor
	 */
	public function add_quantity_sponsor() {
		check_ajax_referer( 'ss-admin-nonce', 'nonce', true );

		$data = isset( $_REQUEST['data'] ) ? $_REQUEST['data'] : array();

		if ( ! $data ) {
			wp_send_json_error( __( 'No data found', 'simple-sponsorships' ) );
			wp_die();
		}

		$sponsor_id = isset( $data['sponsor'] ) ? absint( $data['sponsor'] ) : 0;

		if ( ! $sponsor_id ) {
			wp_send_json_error( __( 'No Sponsor provided', 'simple-sponsorships' ) );
			wp_die();
		}

		$sponsor = new Sponsor( $sponsor_id, false );
		$sponsor->add_available_quantity( 1 );
		wp_send_json_success( $sponsor->get_available_quantity() );
		wp_die();
	}

	/**
	 * Add a quantity on the Sponsor
	 */
	public function remove_quantity_sponsor() {
		check_ajax_referer( 'ss-admin-nonce', 'nonce', true );

		$data = isset( $_REQUEST['data'] ) ? $_REQUEST['data'] : array();

		if ( ! $data ) {
			wp_send_json_error( __( 'No data found', 'simple-sponsorships' ) );
			wp_die();
		}

		$sponsor_id = isset( $data['sponsor'] ) ? absint( $data['sponsor'] ) : 0;

		if ( ! $sponsor_id ) {
			wp_send_json_error( __( 'No Sponsor provided', 'simple-sponsorships' ) );
			wp_die();
		}

		$sponsor = new Sponsor( $sponsor_id, false );
		$sponsor->remove_available_quantity( 1 );
		wp_send_json_success( $sponsor->get_available_quantity() );
		wp_die();
	}

	/**
	 * Get only available sponsors.
	 */
	public function get_available_sponsors() {
		check_ajax_referer( 'ss-admin-nonce', 'nonce', true );

		$sponsors = ss_get_available_sponsors( array( 's' => $_GET['search'] ) );
		$exclude  = isset( $_GET['exclude'] ) ? $_GET['exclude'] : array();

		if ( $exclude ) {
			foreach ( $sponsors as $i => $sponsor ) {
				if ( in_array( $sponsor->ID, $exclude ) ) {
					unset( $sponsors[ $i ] );
				}
			}
		}

		wp_send_json_success( $sponsors );
		wp_die();
	}

	/**
	 * Get only available sponsors.
	 */
	public function get_packages() {
		check_ajax_referer( 'ss-admin-nonce', 'nonce', true );

		$packages = ss_get_packages();

		wp_send_json_success( $packages );
		wp_die();
	}

	/**
	 * Calculate the Totals
	 */
	public function sponsorship_calculate_totals() {
		check_ajax_referer( 'ss-admin-nonce', 'nonce', true );

		$data = isset( $_REQUEST['data'] ) ? $_REQUEST['data'] : array();

		if ( ! $data ) {
			wp_send_json_error( __( 'No data found', 'simple-sponsorships' ) );
			wp_die();
		}

		if ( ! isset( $data['id'] ) || ! absint( $data['id'] ) ) {
			wp_send_json_error( __( 'No ID provided for the Sponsorship.', 'simple-sponsorships' ) );
			wp_die();
		}

		$sponsorship = ss_get_sponsorship( absint( $data['id'] ), false );

		if ( ! $sponsorship ) {
			wp_send_json_error( __( 'No Sponsorship found.', 'simple-sponsorships' ) );
			wp_die();
		}

		$sponsorship->calculate_totals();

		wp_send_json_success( array(
			'amount' => $sponsorship->get_data('amount'),
			'formatted_amount' => $sponsorship->get_formatted_amount()
		) );
		wp_die();
	}
}

new AJAX();