<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11/11/18
 * Time: 19:10
 */

namespace Simple_Sponsorships\DB;


class DB_Sponsorships extends DB {

	/**
	 * Table Name.
	 *
	 * @var string
	 */
	private $table = 'ss_sponsorships';

	/**
	 * @var string
	 */
	private $meta_table = 'ss_sponsorshipmeta';

	/**
	 * Installing the DB.
	 *
	 * @return string
	 */
	public function get_schema() {
		$table_name = $this->get_table_name();
		$schema = "CREATE TABLE {$table_name} (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		type varchar(36) NOT NULL,
		amount tinytext NOT NULL,
		subtotal tinytext NOT NULL,
		currency tinytext NOT NULL,
		gateway tinytext NOT NULL,
		transaction_id varchar(64) NOT NULL,
		level bigint(20) NOT NULL,
		sponsor bingint(20) NOT NULL,
		date datetime NOT NULL,
		PRIMARY KEY ID (ID),
		KEY name (name),
		KEY status (status)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		$meta_table_name = $this->get_meta_table_name();
		$schema .= "CREATE TABLE {$meta_table_name} (
		meta_id bigint(20) NOT NULL AUTO_INCREMENT,
		sssponshorship_id bigint(20) NOT NULL DEFAULT '0',
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext,
		PRIMARY KEY meta_id (meta_id),
		KEY sssponshorship_id (level_id),
		KEY meta_key (meta_key)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		return $schema;
	}
}