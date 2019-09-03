<?php
/**
 * Simple Sponsorship Integrations
 */

namespace Simple_Sponsorships\Admin;

/**
 * Class Integrations
 *
 * @package Simple_Sponsorships\Admin
 */
class Integrations {

	/**
	 * Integrations constructor.
	 */
	public function __construct() {
		add_action( 'ss_admin_page_ss_integrations', array( $this, 'page' ) );
	}

	/**
	 * Integrations Page.
	 */
	public function page() {
		include_once 'views/integrations.php';
	}
}

new Integrations();