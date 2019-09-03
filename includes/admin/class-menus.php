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
			array( $this, 'view' ) );

		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Sponsorships', 'simple-sponsorships' ),
			__( 'Sponsorships', 'simple-sponsorships' ),
			'manage_options',
			'ss-sponsorships',
			array( $this, 'view' ) );

		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Simple Sponsorships Settings', 'simple-sponsorships' ),
			__( 'Settings', 'simple-sponsorships' ),
			'manage_options',
			'ss-settings',
			array( $this, 'view' ) );

		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Simple Sponsorships Integrations', 'simple-sponsorships' ),
			__( 'Integrations', 'simple-sponsorships' ),
			'manage_options',
			'ss-integrations',
			array( $this, 'view' ) );
	}

	public function view() {
		?>
		<div class="wrap">
			<?php do_action( 'ss_admin_page_' . str_replace( '-', '_', $_REQUEST['page'] ) );?>
		</div>
		<?php
	}
}