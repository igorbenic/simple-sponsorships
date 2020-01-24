<?php
/**
 * Dummy Integration to show it in the Integrations Screen.
 */

namespace Simple_Sponsorships\Integrations\Dummy;

use Simple_Sponsorships\Integrations\Premium_Integration_Dummy;

class Package_Minimum_Quantity extends Premium_Integration_Dummy {

	/**
	 * Stripe constructor.
	 */
	public function __construct() {
		$this->title = __( 'Package Minimum Quantity', 'simple-sponsorships' );
		$this->id    = 'package-minimum-quantity';
		$this->desc  = __( 'Allow packages to have minimum quantity for purchase.', 'simple-sponsorships' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/package.svg';
	}
}