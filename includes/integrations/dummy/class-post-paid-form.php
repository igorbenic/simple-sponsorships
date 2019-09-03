<?php
/**
 * Dummy Integration to show it in the Integrations Screen.
 */

namespace Simple_Sponsorships\Integrations\Dummy;

use Simple_Sponsorships\Integrations\Premium_Integration_Dummy;

class Post_Paid_Form_Dummy extends Premium_Integration_Dummy {

	/**
	 * Stripe constructor.
	 */
	public function __construct() {
		$this->title = __( 'Post Paid Form', 'simple-sponsorships' );
		$this->id    = 'post-paid-form';
		$this->desc  = __( 'Allow sponsors that have purchased a sponsorship to edit their details on the sponsorhip page.', 'simple-sponsorships' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/user-edit.svg';
	}
}