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
		include_once 'class-settings.php';
		include_once 'class-levels.php';
		include_once 'functions-settings.php';
	}

	/**
	 * Admin Hooks.
	 */
	public function hooks() {
		$menus = new Menus();
		add_action( 'admin_menu', array( $menus, 'register' ) );
		add_action( 'admin_init', array( $this, 'process_actions' ) );
	}

	/**
	 * Processing Actions on POST or GET requests in Admin
	 */
	public function process_actions() {
		if ( isset( $_POST['ss-action'] ) ) {
			do_action( 'ss_' . $_POST['ss-action'], $_POST );
		}

		if ( isset( $_GET['ss-action'] ) ) {
			do_action( 'ss_' . $_GET['ss-action'], $_GET );
		}
	}
}

new Admin();