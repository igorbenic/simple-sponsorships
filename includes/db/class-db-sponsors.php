<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11/11/18
 * Time: 19:10
 */

namespace Simple_Sponsorships\DB;


class DB_Sponsors extends DB {

	/**
	 * DB Type.
	 *
	 * @var string
	 */
	protected $type = 'sponsor';

	/**
	 * Table Name.
	 *
	 * @var string
	 */
	protected $table = 'posts';

	/**
	 * @var string
	 */
	protected $meta_table = 'postmeta';

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
		return \get_post_meta( $id, $key, $single );
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
		return \add_post_meta( $id, $key, $value );
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
		return \update_post_meta( $id, $key, $value, $prev_value );
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
		return \delete_post_meta( $id, $key, $value );
	}
}