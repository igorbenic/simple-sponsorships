<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 12/11/18
 * Time: 00:56
 */

namespace Simple_Sponsorships\Admin;


class Menus {

	/**
	 * Registering Menus.
	 */
	public function register() {

		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Packages', 'simple-sponsorships' ),
			__( 'Packages', 'simple-sponsorships' ),
			'manage_options',
			'ss-packages',
			array( $this, 'view' ),
            10 );

		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Sponsorships', 'simple-sponsorships' ),
			__( 'Sponsorships', 'simple-sponsorships' ),
			'manage_options',
			'ss-sponsorships',
			array( $this, 'view' ),
            20 );


		do_action( 'ss_admin_menus_after_sponsorships', $this );

		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Simple Sponsorships Settings', 'simple-sponsorships' ),
			__( 'Settings', 'simple-sponsorships' ),
			'manage_options',
			'ss-settings',
			array( $this, 'view' ),
            30 );

		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Simple Sponsorships Reports', 'simple-sponsorships' ),
			__( 'Reports', 'simple-sponsorships' ),
			'manage_options',
			'ss-reports',
			array( $this, 'view' ),
			31 );

		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Simple Sponsorships Integrations', 'simple-sponsorships' ),
			__( 'Integrations', 'simple-sponsorships' ),
			'manage_options',
			'ss-integrations',
			array( $this, 'view' ),
            40 );

		do_action( 'ss_admin_menus', $this );
	}

	/**
	 * View for the admin page
	 */
	public function view() {
		?>
		<div class="wrap">
			<?php do_action( 'ss_admin_page_' . str_replace( '-', '_', $_REQUEST['page'] ) );?>
		</div>
		<?php
	}
}
