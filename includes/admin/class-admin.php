<?php
/**
 * Admin part of Simple Sponsorships.
 */

namespace Simple_Sponsorships\Admin;

/**
 * Class Admin
 *
 * @package Simple_Sponsorships\Admin
 */
class Admin {

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Including Admin files.
	 */
	public function includes() {
		include_once 'class-menus.php';
	}

	/**
	 * Admin Hooks.
	 */
	public function hooks() {
		$menus = new Menus();
		add_action( 'admin_menu', array( $menus, 'register' ) );
	}
}

new Admin();