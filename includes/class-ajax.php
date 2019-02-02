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
		);

		foreach ( $actions as $action => $nopriv ) {
			add_action( 'wp_ajax_ss_' . $action, array( $this, $action ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_ss_' . $action, array( $this, $action ) );
			}
		}
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
}

new AJAX();