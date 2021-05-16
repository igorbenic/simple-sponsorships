<?php
/**
 * Simple Sponsorship Reports
 */

namespace Simple_Sponsorships\Admin;

use Simple_Sponsorships\DB\DB_Sponsorships;

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
		$sponsorships_db   = new DB_Sponsorships();
		$sponsorships = $sponsorships_db->get_all();

		include_once 'views/reports.php';
	}
}

new Reports();
