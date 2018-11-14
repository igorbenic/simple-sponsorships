<?php
/**
 * Admin part for Levels
 */

namespace Simple_Sponsorships\Admin;

/**
 * Class Levels
 *
 * @package Simple_Sponsorships\Admin
 */
class Levels {

	/**
	 * Levels constructor.
	 */
	public function __construct() {
		add_action( 'ss_admin_page_ss_levels', array( $this, 'page' ) );
	}

	/**
	 * Levels Admin Page
	 */
	public function page() {
		$action = isset( $_GET['ss-action'] ) ? $_GET['ss-action'] : 'list';

		switch( $action ) {
			case 'new-level':
				include_once 'views/levels/new.php';
				break;
			default:
				include_once 'levels/class-levels-table-list.php';

				$list = new Levels_Table_List();

				include_once 'views/levels/list.php';
				break;
		}

	}
}

new Levels();