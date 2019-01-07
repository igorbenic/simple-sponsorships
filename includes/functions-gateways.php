<?php
/**
 * Functions for gateways. Globally available.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

add_action( 'init', 'ss_process_gateways' );

/**
 * Processing the Gateway Listeners.
 */
function ss_process_gateways() {

	if ( ! isset( $_REQUEST['ss-listener'] ) ) {
		return;
	}

	$gateway_id = $_REQUEST['ss-listener'];

	$gateways = SS()->payment_gateways();

	foreach ( $gateways->get_available_payment_gateways() as $gateway_key => $gateway ) {
		if ( $gateway ) {
			if ( $gateway_key === $gateway_id ) {
				$gateway->process_webhooks();
			}
		} else {
			// Process all since we don't have one selected.
			$gateway->process_webhooks();
		}
	}

}

add_action( 'init', 'ss_paypal_pdt' );

/**
 * Processing PayPal PDT on return.
 */
function ss_paypal_pdt() {
	if ( ! isset( $_GET['payment_confirmation'] )
		|| 'paypal' !== $_GET['payment_confirmation']
		|| ! isset( $_GET['tx'] ) ) {
		return;
	}

	$gateway = new \Simple_Sponsorships\Gateways\PayPal();
	$gateway->process_pdt();
}