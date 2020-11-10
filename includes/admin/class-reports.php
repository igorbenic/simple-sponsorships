<?php
/**
 * Simple Sponsorship Reports
 */

namespace Simple_Sponsorships\Admin;

/**
 * Class Integrations
 *
 * @package Simple_Sponsorships\Admin
 */
class Reports {

	/**
	 * Integrations constructor.
	 */
	public function __construct() {
		add_action( 'ss_admin_page_ss_reports', array( $this, 'page' ) );
	}

	/**
	 * Integrations Page.
	 */
	public function page() {
		include_once 'views/reports.php';
	}
}

new Reports();
