<?php
/**
 * Dummy Integration to show it in the Integrations Screen.
 */

namespace Simple_Sponsorships\Integrations\Dummy;

use Simple_Sponsorships\Integrations\Platinum_Integration_Dummy;

class Package_Timed_Availability extends Platinum_Integration_Dummy {

	/**
	 * Stripe constructor.
	 */
	public function __construct() {
		$this->title = __( 'Package Timed Availability', 'simple-sponsorships' );
		$this->id    = 'package-timed-availability';
		$this->desc  = __( 'Define the availability of each package through date and time.', 'simple-sponsorships' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/clock.svg';
	}
}