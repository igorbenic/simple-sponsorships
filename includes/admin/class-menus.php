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
			__( 'Sponsorships', 'simple-sponsorships' ),
			__( 'Sponsorships', 'simple-sponsorships' ),
			'manage_options',
			'ss-sponsorships',
			array( $this, 'view' ) );

		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Levels', 'simple-sponsorships' ),
			__( 'Levels', 'simple-sponsorships' ),
			'manage_options',
			'ss-levels',
			array( $this, 'view' ) );

		add_submenu_page(
			'edit.php?post_type=sponsors',
			__( 'Settings', 'simple-sponsorships' ),
			__( 'Settings', 'simple-sponsorships' ),
			'manage_options',
			'ss-settings',
			array( $this, 'view' ) );
	}

	public function view() {
		?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>
			<?php do_action( 'ss_admin_page_' . str_replace( '-', '_', $_REQUEST['page'] ) );?>
		</div>
		<?php
	}
}