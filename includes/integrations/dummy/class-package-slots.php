<?php
/**
 * Dummy Integration to show it in the Integrations Screen.
 */

namespace Simple_Sponsorships\Integrations\Dummy;

use Simple_Sponsorships\Integrations\Premium_Integration_Dummy;

class Package_Slots extends Premium_Integration_Dummy {

	/**
	 * Stripe constructor.
	 */
	public function __construct() {
		$this->title = __( 'Package Slots', 'simple-sponsorships' );
		$this->id    = 'package-slots';
		$this->desc  = __( 'Allow packages to have maximum slots and thus provide availability.', 'simple-sponsorships' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/package.svg';
	}
}