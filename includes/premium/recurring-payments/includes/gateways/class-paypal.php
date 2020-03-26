<?php
/**
 * Adding PayPal Recurring Payment Gateway
 */

namespace Simple_Sponsorships\Recurring_Payments\Gateways;
use Simple_Sponsorships\Recurring_Payments\Plugin;

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
			'recurring'
		);
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

}
