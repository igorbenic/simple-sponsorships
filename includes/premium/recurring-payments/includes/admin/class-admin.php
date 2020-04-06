<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 06/04/2020
 * Time: 16:58
 */

namespace Simple_Sponsorships\Recurring_Payments\Admin;

/**
 * Class Admin
 *
 * @package Simple_Sponsorships\Recurring_Payments\Admin
 */
class Admin {

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_action( 'ss_admin_menus_after_sponsorships', array( $this, 'register_menus' ) );

		include_once 'class-sponsorships.php';

		new Sponsorships();
	}

	/**
	 * Register Menus
	 */
	public function register_menus( $admin ) {
		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Subscriptions', 'simple-sponsorships' ),
			__( 'Subscriptions', 'simple-sponsorships' ),
			'manage_options',
			'ss-subscriptions',
			array( $admin, 'view' ),
			21 );
	}
}

new Admin();