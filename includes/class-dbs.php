<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11/11/18
 * Time: 19:13
 */

namespace Simple_Sponsorships;


use Simple_Sponsorships\DB\DB_Levels;
use Simple_Sponsorships\DB\DB_Sponsorships;

class Databases {


	/**
	 * Register Databases.
	 */
	public function register() {
		global $wpdb;

		$db_levels = new DB_Levels();
		$wpdb->sslevelmeta = $db_levels->get_meta_table_name();

		$db_sponsorships = new DB_Sponsorships();
		$wpdb->sssponsorshipmeta = $db_sponsorships->get_meta_table_name();
	}

	/**
	 * Install databases
	 */
	public function install() {

	}
}