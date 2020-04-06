<?php
/**
 * Admin part for Levels
 */

namespace Simple_Sponsorships\Recurring_Payments\Admin;

/**
 * Class Levels
 *
 * @package Simple_Sponsorships\Admin
 */
class Sponsorships {


	/**
	 * Sponsorship Item
	 *
	 * @var null|Sponsorship
	 */
	public $sponsorship = null;

	/**
	 * Levels constructor.
	 */
	public function __construct() {
		add_action( 'ss_admin_page_ss_subscriptions', array( $this, 'page' ) );
	}

	/**
	 * Admin Page
	 */
	public function page() {
		include_once 'class-sponsorships-table-list.php';
		$list = new Sponsorships_Table_List();
		include_once 'views/subscriptions.php';
	}
}

new Sponsorships();