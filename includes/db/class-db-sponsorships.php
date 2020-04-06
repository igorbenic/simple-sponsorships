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
	 * DB Type.
	 *
	 * @var string
	 */
	protected $type = 'sponsorship';

	/**
	 * Table Name.
	 *
	 * @var string
	 */
	protected $table = 'ss_sponsorships';

	/**
	 * @var string
	 */
	protected $meta_table = 'ss_sponsorshipmeta';

	/**
	 * @var string
	 */
	protected $meta_type = 'sssponsorship';

	/**
	 * Get the level meta data.
	 *
	 * @param      $id
	 * @param      $key
	 * @param bool $single
	 *
	 * @return mixed
	 */
	public function get_meta( $id, $key, $single = true ) {
		return \get_metadata( 'sssponsorship', $id, $key, $single );
	}

	/**
	 * Add Meta
	 *
	 * @param $id
	 * @param $key
	 * @param $value
	 *
	 * @return false|int
	 */
	public function add_meta( $id, $key, $value ) {
		return \add_metadata( 'sssponsorship', $id, $key, $value );
	}

	/**
	 * Update Meta.
	 *
	 * @param        $id
	 * @param        $key
	 * @param string $value
	 * @param string $prev_value
	 *
	 * @return bool|int
	 */
	public function update_meta( $id, $key, $value = '', $prev_value = '' ) {
		return \update_metadata( 'sssponsorship', $id, $key, $value, $prev_value );
	}

	/**
	 * Delete Meta.
	 *
	 * @param        $id
	 * @param        $key
	 * @param string $value
	 * @param bool   $delete_all
	 *
	 * @return bool
	 */
	public function delete_meta( $id, $key, $value = '', $delete_all = false ) {
		return \delete_metadata( 'sssponsorship', $id, $key, $value, $delete_all );
	}

	/**
	 * Installing the DB.
	 *
	 * @return string
	 */
	public function get_schema() {
		$table_name = $this->get_table_name();
		$schema = "CREATE TABLE {$table_name} (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		status varchar(24) NOT NULL,
		amount tinytext NOT NULL,
		currency tinytext NOT NULL,
		gateway tinytext NOT NULL,
		transaction_id varchar(64) NOT NULL,
		type varchar(20) NOT NULL default 'onetime',
		package bigint(20) NOT NULL,
		sponsor bigint(20) NOT NULL,
		parent_id  bigint(20) NOT NULL DEFAULT 0,
		date datetime NOT NULL,
		ss_key  varchar(36) NOT NULL,
		PRIMARY KEY ID (ID)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		$meta_table_name = $this->get_meta_table_name();
		$schema .= "CREATE TABLE {$meta_table_name} (
		meta_id bigint(20) NOT NULL AUTO_INCREMENT,
		sssponsorship_id bigint(20) NOT NULL DEFAULT '0',
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext,
		PRIMARY KEY meta_id (meta_id),
		KEY sssponsorship_id (sssponsorship_id),
		KEY meta_key (meta_key)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		return $schema;
	}
}