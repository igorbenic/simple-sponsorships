<?php
/**
 * Dummy Integration to show it in the Integrations Screen.
 */

namespace Simple_Sponsorships\Integrations\Dummy;

use Simple_Sponsorships\Integrations\Premium_Integration_Dummy;

class Stripe extends Premium_Integration_Dummy {

	/**
	 * Stripe constructor.
	 */
	public function __construct() {
		$this->title = __( 'Stripe', 'simple-sponsorships' );
		$this->id    = 'stripe';
		$this->desc  = __( 'This will add Stripe as a Payment Gateway.', 'simple-sponsorships' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/stripe.svg';
	}
}