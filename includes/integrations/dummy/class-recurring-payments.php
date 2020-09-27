<?php
/**
 * Dummy Integration to show it in the Integrations Screen.
 */

namespace Simple_Sponsorships\Integrations\Dummy;

use Simple_Sponsorships\Integrations\Platinum_Integration_Dummy;

class Recurring_Payments_Dummy extends Platinum_Integration_Dummy {

	/**
	 * Stripe constructor.
	 */
	public function __construct() {
		$this->title = __( 'Recurring Payments', 'simple-sponsorships' );
		$this->id    = 'recurring-payments';
		$this->desc  = __( 'Allows you to accept recurring payments (subscriptions).', 'simple-sponsorships' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/repeat.svg';
	}
}
