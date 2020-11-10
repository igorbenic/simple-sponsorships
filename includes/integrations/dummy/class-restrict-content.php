<?php
/**
 * Dummy Integration to show it in the Integrations Screen.
 */

namespace Simple_Sponsorships\Integrations\Dummy;

use Simple_Sponsorships\Integrations\Premium_Integration_Dummy;

class Restrict_Content_Dummy extends Premium_Integration_Dummy {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->title = __( 'Restrict Content', 'simple-sponsorships-premium' );
		$this->id    = 'restrict-content';
		$this->desc  = __( 'This will restrict content to only sponsors that sponsored it or a package', 'simple-sponsorships-premium' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/lock.svg';
	}

}
