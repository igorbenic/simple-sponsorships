<?php

/**
 * Plugin Name: Simple Sponsorships - Stripe Gateway
 * Description: This is an add-on for Simple Sponsorships to add Stripe Gateway.
 * Version: 1.0.0
 */

namespace Simple_Sponsorships\Stripe;

use Simple_Sponsorships\Gateways\Stripe;
use Simple_Sponsorships\Integrations\Integration;
use Simple_Sponsorships\Sponsorship;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

class Plugin extends Integration {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->title = __( 'Stripe', 'simple-sponsorships-premium' );
		$this->id    = 'stripe';
		$this->desc  = __( 'This will add Stripe as a Payment Gateway.', 'simple-sponsorships-premium' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/stripe.svg';

		$this->includes();
		add_filter( 'ss_payment_gateways', array( $this, 'add_gateways' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 10 );
		//add_action( 'wp_ajax_ss_stripe_confirm_payment', array( $this, 'confirm_payment' ) );
		//add_action( 'wp_ajax_nopriv_ss_stripe_confirm_payment', array( $this, 'confirm_payment' ) );

		add_action( 'ss_after_payment_form_fields', array( $this, 'add_intent_fields' ) );
		add_action( 'ss_stripe_confirm_pi', array( $this, 'confirm_intent' ) );
	}

	/**
	 * Confirm Intent
	 */
	public function confirm_intent() {
	    try {
		    if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'ss_stripe_confirm_pi' ) ) {
			    throw new \Exception( 'missing-nonce', __( 'CSRF verification failed.', 'simple-sponsorships-premium' ) );
		    }

		    if ( ! isset( $_GET['sponsorship_id'] ) ) {
			    throw new \Exception( 'missing-sponsorship', __( 'No Sponsorship.', 'simple-sponsorships-premium' ) );
            }

		    $sponsorship = ss_get_sponsorship( $_GET['sponsorship_id'] );

		    $stripe = new Stripe();
		    $stripe->verify_intent( $sponsorship );

	    } catch( \Exception $e ) {
	        ss_add_notice( $e->getMessage(), 'error' );
        }
    }

	/**
	 * Add Intent Fields
	 *
	 * @param Sponsorship $sponsorship
	 */
	public function add_intent_fields( $sponsorship ) {
		$payment_intent_id = $sponsorship->get_data( '_stripe_payment_intent_id', false );
		$payment_intent_secret = $sponsorship->get_data( '_stripe_payment_intent_secret', false );

		if ( $payment_intent_id && $payment_intent_secret ) {
		    $verification_url = add_query_arg( array(
                'sponsorship_id' => $sponsorship->get_id(),
                'nonce'          => wp_create_nonce( 'ss_stripe_confirm_pi' ),
                'ss-action'      => 'stripe_confirm_pi'
            ), $sponsorship->get_view_link() );
			?>
			<input type="hidden" id="ss_stripe_payment_intent_id" name="ss_stripe_payment_intent_id" value="<?php echo esc_attr( $payment_intent_id ); ?>" />
			<input type="hidden" id="ss_stripe_payment_intent_secret" name="ss_stripe_payment_intent_secret" value="<?php echo esc_attr( $payment_intent_secret ); ?>" />
            <input type="hidden" id="ss_stripe_payment_confirm_url" name="ss_stripe_payment_confirm_url" value="<?php echo esc_url( $verification_url ); ?>" />
            <?php
		}
	}

	/**
	 * Confirm Payment.
	 */
	public function confirm_payment() {
		$intent = false;
		$sponsorship_id = isset( $_REQUEST['sponsorship_id'] ) ? absint( $_REQUEST['sponsorship_id'] ) : false;

		if ( ! $sponsorship_id ) {
			wp_send_json_error( __( 'No Sponsorship Provided for payment', 'simple-sponsorships-premium' ) );
			wp_die();
		}

		$sponsorship = ss_get_sponsorship( $sponsorship_id );

		if ( ! $sponsorship ) {
			wp_send_json_error( __( 'Sponsorship does not exist.', 'simple-sponsorships-premium' ) );
			wp_die();
		}

		if ( $sponsorship->is_status( 'paid' ) ) {
			wp_send_json_error( __( 'Sponsorship was already paid.', 'simple-sponsorships-premium' ) );
			wp_die();
		}

		if ( isset( $_REQUEST['payment_method_id'] ) ) {
			$intent = Stripe_API::request(
				[
				'payment_method' => $_REQUEST['payment_method_id'],
				'amount' => $sponsorship->get_data('amount') * 100,
				'currency' => strtolower( $sponsorship->get_data('currency') ),
				'confirmation_method' => 'manual',
				'confirm' => 'true',
				],
				'payment_intents'
				);
			if ( is_wp_error( $intent ) ) {
				wp_send_json_error( $intent->get_error_message() );
				wp_die();
			}
		}

		if ( isset( $_REQUEST['payment_intent_id'] ) ) {
			$intent = Stripe_API::request(
				[],
				'payment_intents/' . $_REQUEST['payment_intent_id'] . '/confirm'
			);
			if ( is_wp_error( $intent ) ) {
				wp_send_json_error( $intent->get_error_message() );
				wp_die();
			}
		}

		if ( false !== $intent ) {
			$sponsorship->update_data( '_stripe_payment_intent', $intent->id );
			if ( $intent->status == 'requires_action' &&
			     $intent->next_action->type == 'use_stripe_sdk'
			) {
				wp_send_json_success([
					'requires_action'              => true,
					'payment_intent_client_secret' => $intent->client_secret
				]);
				wp_die();
			} else if ( $intent->status == 'succeeded' ) {

				wp_send_json_success();
				wp_die();
			} else {
				wp_send_json_error( 'Invalid PaymentIntent status' );
				wp_die();
			}
		}
	}

	/**
	 * Includes
	 */
	private function includes() {
		include_once 'includes/class-stripe.php';
		include_once 'includes/class-stripe-api.php';
	}

	/**
	 * Enqueue
	 */
	public function enqueue() {
		if ( ! is_ssl() ) {
			return;
		}

		if ( ss_get_option( 'stripe_enabled', '0' ) !== '1' ) {
			return;
		}

		if ( ss_get_option( 'sponsorship_page', '0' ) === '0' ) {
			return;
		}

		wp_enqueue_script( 'ss-stripe-js', 'https://js.stripe.com/v3/' );
		if ( is_page( ss_get_option( 'sponsorship_page', '0' ) ) ) {
			wp_enqueue_script( 'ss-stripe-checkout', plugin_dir_url( __FILE__ ) . '/assets/stripe.js', array( 'jquery', 'ss-stripe-js' ), '', true );
			wp_localize_script( 'ss-stripe-checkout', 'ss_stripe', array(
				'ajax' => admin_url( 'admin-ajax.php' ),
				'key'  => ss_get_option( 'stripe_public_key', '' )
			));
		}
	}

	/**
	 * Add Premium/Platinum gateways.
	 *
	 * @param array $gateways Array of gateway classes.
	 *
	 * @return mixed
	 */
	public function add_gateways( $gateways ) {
		$gateways[] = '\Simple_Sponsorships\Gateways\Stripe';
		return $gateways;
	}

}
