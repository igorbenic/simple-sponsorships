<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11/11/18
 * Time: 19:10
 */

namespace Simple_Sponsorships\DB;


class DB_Levels extends DB {

	/**
	 * Table Name.
	 *
	 * @var string
	 */
	protected $table = 'ss_levels';

	/**
	 * @var string
	 */
	protected $meta_table = 'ss_levelmeta';

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
		return \get_metadata( 'sslevel', $id, $key, $single );
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
		return \add_metadata( 'sslevel', $id, $key, $value );
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
		return \update_metadata( 'sslevel', $id, $key, $value, $prev_value );
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
		return \delete_metadata( 'sslevel', $id, $key, $value, $delete_all );
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
		title varchar(200) NOT NULL,
		description longtext NOT NULL,
		type varchar(36) NOT NULL,
		price tinytext NOT NULL,
		PRIMARY KEY ID (ID)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		$meta_table_name = $this->get_meta_table_name();
		$schema .= "CREATE TABLE {$meta_table_name} (
		meta_id bigint(20) NOT NULL AUTO_INCREMENT,
		sslevel_id bigint(20) NOT NULL DEFAULT '0',
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext,
		PRIMARY KEY meta_id (meta_id),
		KEY sslevel_id (sslevel_id),
		KEY meta_key (meta_key)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		return $schema;
	}
}