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
		include_once 'class-packages.php';
		include_once 'class-sponsorships.php';
		include_once 'class-sponsors.php';
		include_once 'functions-settings.php';
	}

	/**
	 * Admin Hooks.
	 */
	public function hooks() {
		$menus = new Menus();
		add_action( 'admin_menu', array( $menus, 'register' ) );
		add_action( 'admin_init', array( $this, 'process_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
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

	/**
	 * Enqueue the Scripts and Styles on the Admin side.
	 *
	 * @param string $hook
	 */
	public function enqueue( $hook ) {
		global $post;

		$admin_pages = array(
			'sponsors_page_ss-sponsorships',
			'sponsors_page_ss-packages',
			'sponsors_page_ss-settings',
			'widgets.php',
		);

		$enqueue = false;

		if ( in_array( $hook, $admin_pages, true ) ) {
			$enqueue = true;
		}

		if ( ! $enqueue ) {
			if ( ( 'edit.php' === $hook || 'post-new.php' === $hook ) && isset( $_GET['post_type'] ) && 'sponsors' === $_GET['post_type'] ) {
				$enqueue = true;
			}

			if ( 'post.php' === $hook && $post && 'sponsors' === get_post_type( $post ) ) {
				$enqueue = true;
			}
		}

		if ( $enqueue ) {
			wp_enqueue_script( 'ss-admin-js', SS_PLUGIN_URL . '/assets/dist/js/admin.js', array( 'jquery' ), SS_VERSION, true );
			wp_localize_script( 'ss-admin-js', 'ss_admin', array(
				'statuses' => array(
					'ss-active'   => __( 'Active', 'simple-sponsorships' ),
					'ss-inactive' => __( 'Inactive', 'simple-sponsorships' ),
				)
			));

			wp_enqueue_style( 'ss-admin-css', SS_PLUGIN_URL . '/assets/dist/css/admin.css', array(), SS_VERSION );
		}
	}
}

new Admin();