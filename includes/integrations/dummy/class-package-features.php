<?php
/**
 * Dummy Integration to show it in the Integrations Screen.
 */

namespace Simple_Sponsorships\Integrations\Dummy;

use Simple_Sponsorships\Integrations\Platinum_Integration_Dummy;

class Package_Features extends Platinum_Integration_Dummy {

	/**
	 * Stripe constructor.
	 */
	public function __construct() {
		$this->title = __( 'Package Features', 'simple-sponsorships' );
		$this->id    = 'package-features';
		$this->desc  = __( 'You will be able to add features and display them in tables similar to pricing tables.', 'simple-sponsorships' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/list.svg';
	}
}