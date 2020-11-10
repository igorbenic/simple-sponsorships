<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11/11/18
 * Time: 19:13
 */

namespace Simple_Sponsorships;

use Simple_Sponsorships\DB\DB_Packages;
use Simple_Sponsorships\DB\DB_Reports;
use Simple_Sponsorships\DB\DB_Sponsorship_Items;
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

		$db_sponsorship_items = new DB_Sponsorship_Items();
		$wpdb->sssponsorship_items = $db_sponsorship_items->get_table_name();
		$wpdb->sssponsorship_itemmeta = $db_sponsorship_items->get_meta_table_name();

		$db_reports = new DB_Reports();
		$wpdb->ssreports = $db_reports->get_table_name();
		$wpdb->ssreport_itemmeta = $db_reports->get_meta_table_name();

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

		$db_sponsorship_items = new DB_Sponsorship_Items();
		$sponsorship_items_schema = $db_sponsorship_items->get_schema();

		@dbDelta( $sponsorship_items_schema );

		$db_reports = new DB_Reports();
		$db_reports_schema = $db_reports->get_schema();

		@dbDelta( $db_reports_schema );

	}
}
