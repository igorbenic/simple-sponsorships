<?php
/**
 * Installer Class to hold everything for installing and upgrading.
 */

namespace Simple_Sponsorships;

/**
 * Class Installer
 *
 * @package Simple_Sponsorships
 */
class Installer {

	/**
	 * Activating the Plugin.
	 */
	public static function activate() {
		self::install();
	}

	/**
	 * Install the
	 */
	public static function install() {
		$dbs = new Databases();
		$dbs->install();
	}
}