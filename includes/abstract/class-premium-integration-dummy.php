<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 02/09/2019
 * Time: 17:47
 */

namespace Simple_Sponsorships\Integrations;


/**
 * Class Premium_Integration_Dummy
 *
 * @package Simple_Sponsorships\Integrations
 */
abstract class Premium_Integration_Dummy extends Integration {

	/**
	 * Buttons
	 */
	public function buttons() {
		echo '<a href="' . \Simple_Sponsorships\ss_fs()->get_upgrade_url() . '" class="button button-primary">' . __( 'Upgrade to Premium', 'simple-sponsorships' ) . '</a>';
	}
}

/**
 * Class Premium_Integration_Dummy
 *
 * @package Simple_Sponsorships\Integrations
 */
abstract class Platinum_Integration_Dummy extends Integration {

	/**
	 * Buttons
	 */
	public function buttons() {
		echo '<a href="' . \Simple_Sponsorships\ss_fs()->get_upgrade_url() . '" class="button button-primary">' . __( 'Upgrade to Platinum', 'simple-sponsorships' ) . '</a>';
	}
}