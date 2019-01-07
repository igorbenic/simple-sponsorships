<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11/11/18
 * Time: 19:13
 */

namespace Simple_Sponsorships;

use Simple_Sponsorships\DB\DB_Packages;
use Simple_Sponsorships\DB\DB_Sponsorships;

class Databases {


	/**
	 * Register Databases.
	 */
	public function register() {
		global $wpdb;

		$db_levels = new DB_Packages();
		$wpdb->sspackagemeta = $db_levels->get_meta_table_name();
		$wpdb->sspackages = $db_levels->get_table_name();

		$db_sponsorships = new DB_Sponsorships();
		$wpdb->sssponsorshipmeta = $db_sponsorships->get_meta_table_name();
		$wpdb->sssponsorships    = $db_sponsorships->get_table_name();

	}

	/**
	 * Install databases
	 */
	public function install() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$db_levels = new DB_Packages();
		$package_schema = $db_levels->get_schema();

		@dbDelta( $package_schema );

		$db_sponsorships = new DB_Sponsorships();
		$sponsorship_schema = $db_sponsorships->get_schema();

		@dbDelta( $sponsorship_schema );

		$bla = '';
	}
}