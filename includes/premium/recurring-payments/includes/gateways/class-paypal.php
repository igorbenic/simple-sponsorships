<?php
/**
 * Adding PayPal Recurring Payment Gateway
 */

namespace Simple_Sponsorships\Recurring_Payments\Gateways;
use Simple_Sponsorships\Integrations\Dummy\Recurring_Payments_Dummy;
use Simple_Sponsorships\Recurring_Payments\Plugin;
use Simple_Sponsorships\Sponsorship;

/**
 * Class PayPal to support Recurring Payments
 *
 * @package Simple_Sponsorships\Recurring_Payments\Gateways
 */
class PayPal extends \Simple_Sponsorships\Gateways\PayPal {


	/**
	 * PayPal constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->supports = array(
			'recurring',
			'cancel_recurring',
		);

	}

	/**
	 * Cancel Sponsorship
	 *
	 * @param Sponsorship $sponsorship Sponsorship object.
	 */
	public function cancel_recurring( $sponsorship ) {

		$redirect = $this->testmode ? 'https://www.sandbox.paypal.com/cgi-bin/customerprofileweb?cmd=_manage-paylist' : 'https://www.paypal.com/cgi-bin/customerprofileweb?cmd=_manage-paylist';
		wp_redirect( $redirect );
		exit();
	}

	/**
	 * Get transaction args for paypal request, except for line item args.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship Sponsorship object.
	 * @return array
	 */
	protected function get_transaction_args( $sponsorship ) {

		if ( ! Plugin::sponsorship_contains_recurring_packages( $sponsorship ) ) {
			return parent::get_transaction_args( $sponsorship );
		}

		$paypal_args   = parent::get_transaction_args( $sponsorship );
		$packages      = $sponsorship->get_packages();
		$total_amount  = $paypal_args['amount'];
		$sign_up_fee   = 0;
		$duration      = false;
		$duration_unit = false;

		foreach ( $packages as $package ) {
			if ( 'recurring' !== $package->get_type() ) {
				continue;
			}

			$fee           = $package->get_data( 'recurring_signup_fee', 0 );
			$duration      = $package->get_data( 'recurring_duration', 1 );
			$duration_unit = $package->get_data( 'recurring_duration_unit', 'day' );

			if ( $fee ) {
				$sign_up_fee += floatval( $fee );
			}
		}

		if ( ! $duration || ! $duration_unit ) {
			return $paypal_args;
		}

		$recurring_amount = $total_amount - $sign_up_fee;

		// recurring paypal payment

		$paypal_args['cmd'] = '_xclick-subscriptions';
		$paypal_args['src'] = '1';
		$paypal_args['sra'] = '1';
		$paypal_args['a3']  = $recurring_amount;
		$paypal_args['a1']  = $total_amount;
		$paypal_args['p3']  = $duration;
		$paypal_args['p1']  = $duration;

		// Remove the total amount.
		unset( $paypal_args['amount'] );

		switch ( $duration_unit ) {

			case "day" :

				$paypal_args['t3'] = 'D';
				$paypal_args['t1'] = 'D';
				break;

			case "month" :

				$paypal_args['t3'] = 'M';
				$paypal_args['t1'] = 'M';
				break;

			case "year" :

				$paypal_args['t3'] = 'Y';
				$paypal_args['t1'] = 'Y';
				break;

		}

		return $paypal_args;
	}

	/**
	 * There was a valid response.
	 *
	 * @param  array $posted Post data after wp_unslash.
	 */
	public function valid_response( $posted ) {
		$sponsorship = ! empty( $posted['custom'] ) ? $this->get_paypal_sponsorship( $posted['custom'] ) : false;

		if ( $sponsorship ) {

			// Subscriptions
			switch ( $posted['txn_type'] ) :

				case "subscr_signup" :

					$sponsorship = ss_get_recurring_sponsorship( $sponsorship );
					// when a new user signs up.
					$user_id = $sponsorship->get_data('_user_id', 0 );

					if ( $user_id ) {
						// store the recurring payment ID
						update_user_meta( $user_id, 'ss_paypal_subscriber', $posted['payer_id'] );
						if ( isset( $posted['subscr_id'] ) ) {
							update_user_meta( $user_id, 'ss_payment_profile_id', $posted['subscr_id'] );
						}
					}

					$sponsorship->update_data( 'ss_paypal_subscriber', $posted['payer_id'] );
					if ( isset( $posted['subscr_id'] ) ) {
						$sponsorship->update_data( 'ss_payment_profile_id', $posted['subscr_id'] );
					}

					$this->validate_amount( $sponsorship, $posted['mc_gross'] );
					$this->save_paypal_meta_data( $sponsorship, $posted );

					if ( ! empty( $posted['mc_fee'] ) ) {
						// Log paypal transaction fee.
						$sponsorship->update_data( '_paypal_transaction_fee', ss_clean( $posted['mc_fee'] ) );
					}

					$sponsorship->update_data( 'type', 'recurring' );
					$sponsorship->calculate_expiry_date();

					$this->complete( $sponsorship );

					do_action( 'ss_ipn_subscr_signup', $user_id );
					do_action( 'ss_webhook_recurring_payment_profile_created', $user_id, $sponsorship, $this );


					die( 'successful subscr_signup' );

					break;

				case "subscr_payment" :

					// when a user makes a recurring payment

					$args = array();
					$args['amount'] = $posted['mc_gross'];

					if ( ! ss_sponsorship_can_have_recurring( $sponsorship ) ) {
						return;
					}

					$recurring_sponsorship = ss_create_recurring_sponsorship( $sponsorship, $args );

					if ( ! $recurring_sponsorship ) {
						// Sponsorship could not be created.
						return;
					}

					$user_id = $recurring_sponsorship->get_data('_user_id', 0 );

					if ( $user_id ) {
						// store the recurring payment ID
						update_user_meta( $user_id, 'ss_paypal_subscriber', $posted['payer_id'] );
						if ( isset( $posted['subscr_id'] ) ) {
							update_user_meta( $user_id, 'ss_payment_profile_id', $posted['subscr_id'] );
						}
					}

					$recurring_sponsorship->update_data( 'ss_paypal_subscriber', $posted['payer_id'] );
					if ( isset( $posted['subscr_id'] ) ) {
						$recurring_sponsorship->update_data( 'ss_payment_profile_id', $posted['subscr_id'] );
					}

					$this->save_paypal_meta_data( $recurring_sponsorship, $posted );

					if ( ! empty( $posted['mc_fee'] ) ) {
						// Log paypal transaction fee.
						$sponsorship->update_data( '_paypal_transaction_fee', ss_clean( $posted['mc_fee'] ) );
					}

					$this->complete( $recurring_sponsorship );

					do_action( 'ss_ipn_subscr_payment', $user_id );
					do_action( 'ss_webhook_recurring_payment_processed', $recurring_sponsorship, $posted, $this );
					do_action( 'ss_gateway_payment_processed', $recurring_sponsorship, $this );

					die( 'successful subscr_payment' );

					break;

				case "subscr_cancel" :

					rcp_log( 'Processing PayPal Standard subscr_cancel IPN.' );

					$user_id = $sponsorship->get_data( '_user_id', 0 );
					$subscr_id = 0;

					if ( $user_id ) {
						$subscr_id = get_user_meta( $user_id,'ss_payment_profile_id', true );
					}

					if( isset( $posted['subscr_id'] ) && $posted['subscr_id'] == $subscr_id ) {

						// set the use to no longer be recurring
						delete_user_meta( $user_id, 'ss_paypal_subscriber' );

						$recurring_sponsorship = ss_get_recurring_sponsorship( $sponsorship );
						$recurring_sponsorship->cancel();

						do_action( 'ss_ipn_subscr_cancel', $user_id );
						do_action( 'ss_webhook_cancel', $user_id, $posted, $this );

						die( 'successful subscr_cancel' );
					}

					break;

				case "subscr_failed" :

					$user_id = $sponsorship->get_data( '_user_id', 0 );

					if ( ! empty( $posted['txn_id'] ) ) {

						$this->webhook_event_id = sanitize_text_field( $posted['txn_id'] );

					} elseif ( ! empty( $posted['ipn_track_id'] ) ) {

						$this->webhook_event_id = sanitize_text_field( $posted['ipn_track_id'] );
					}

					do_action( 'ss_recurring_payment_failed', $user_id, $sponsorship, $this );
					do_action( 'ss_ipn_subscr_failed' );

					die( 'successful subscr_failed' );

					break;

				case "subscr_eot" :

					// user's subscription has reached the end of its term
					$user_id = $sponsorship->get_data( '_user_id', 0 );

					$recurring_sponsorship = ss_get_recurring_sponsorship( $sponsorship );
					$recurring_sponsorship->expire();

					do_action('rcp_ipn_subscr_eot', $user_id, $sponsorship );


					die( 'successful subscr_eot' );

					break;

				case "web_accept" :

					// Lowercase returned variables.
					$posted['payment_status'] = strtolower( $posted['payment_status'] );

					if ( method_exists( $this, 'payment_status_' . $posted['payment_status'] ) ) {
						call_user_func( array( $this, 'payment_status_' . $posted['payment_status'] ), $sponsorship, $posted );
					}

					die( 'successful web_accept' );

					break;

				case "cart" :
				case "express_checkout" :
				default :

					break;

			endswitch;


		}
	}

	/**
	 * Process PDT.
	 */
	public function process_pdt() {
		$token   = $this->settings['paypal_identity_token'];

		if ( ! $token ) {
			return;
		}

		$sponsorship = $this->get_paypal_sponsorship( ss_clean( wp_unslash( $_REQUEST['cm'] ) ) );

		if ( ! $sponsorship ) {
			return;
		}

		if ( $sponsorship->is_status( 'paid' ) ) {
			return;
		}

		$status      = ss_clean( strtolower( wp_unslash( $_REQUEST['st'] ) ) ); // WPCS: input var ok, CSRF ok, sanitization ok.
		$amount      = ss_clean( wp_unslash( $_REQUEST['amt'] ) ); // WPCS: input var ok, CSRF ok, sanitization ok.
		$transaction = ss_clean( wp_unslash( $_REQUEST['tx'] ) ); // WPCS: input var ok, CSRF ok, sanitization ok.

		$transaction_result = $this->validate_transaction( $transaction );

		if ( $transaction_result ) {
			$sponsorship->update_data( '_paypal_status', $status );
			$sponsorship->update_data( 'transaction_id', $transaction );

			if ( 'completed' === $status ) {
				if ( number_format( $sponsorship->get_data( 'amount' ), 2, '.', '' ) !== number_format( $amount, 2, '.', '' ) ) {
					$sponsorship->set_status( 'approved' );
				} else {
					$this->complete( $sponsorship );

					// Log paypal transaction fee and payment type.
					if ( ! empty( $transaction_result['mc_fee'] ) ) {
						$sponsorship->update_data( '_paypal_transaction_fee', $transaction_result['mc_fee'] );
					}
					if ( ! empty( $transaction_result['payment_type'] ) ) {
						$sponsorship->update_data( '_payment_type', $transaction_result['payment_type'] );
					}
				}
			}
		}
	}
}
