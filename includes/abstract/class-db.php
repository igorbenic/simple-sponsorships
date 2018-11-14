<?php
/**
 * Abstract class to handle Custom DB calls.
 */
namespace Simple_Sponsorships\DB;

/**
 * Class DB
 *
 * @package Simple_Sponsorships\DB
 */
abstract class DB {

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = '';

	/**
	 * Meta Table
	 *
	 * @var string
	 */
	protected $meta_table = '';

	/***** Getters *****/

	/**
	 * Get table name
	 * @return mixed
	 */
	public function get_table_name() {
		global $wpdb;
		return apply_filters( strtolower( __CLASS__ ) . '_table_name', $wpdb->prefix . $this->table );
	}

	/**
	 * Get the meta table name.
	 */
	public function get_meta_table_name() {
		global $wpdb;
		return apply_filters( strtolower( __CLASS__ ) . '_meta_table_name', $wpdb->prefix . $this->meta_table );
	}

	/**
	 * Get all results from the table.
	 *
	 * @return array|null|object
	 */
	public function get_all() {
		global $wpdb;

		$results = $wpdb->get_results( "SELECT * FROM $this->table", ARRAY_A );

		return $results ? $results : array();
	}

	/**
	 * Get one single result by ID.
	 *
	 * @param int $id
	 *
	 * @return array|null|object|void
	 */
	public function get_by_id( $id = 0 ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM $this->table WHERE ID=%d", $id );

		$results = $wpdb->get_row( $sql, ARRAY_A );

		return $results ? $results : array();
	}

	/***** DB Setters (Insert, Update) *****/


	/**
	 * Insert a record.
	 *
	 * @param      $data
	 * @param null $format
	 *
	 * @return bool|int
	 */
	public function insert( $data, $format = null ) {
		global $wpdb;

		$ret = $wpdb->insert( $this->table, $data, $format );

		return $ret ? $wpdb->insert_id : false;
	}

	/**
	 * Update a Row by ID.
	 *
	 * @param      $id
	 * @param      $data
	 * @param null $format
	 *
	 * @return bool
	 */
	public function update( $id, $data, $format = null ) {
		global $wpdb;

		$ret = $wpdb->update( $this->table, $data, array( 'ID' => $id ), $format, array( '%d') );

		return $ret ? true : false;
	}

	/***** DB Removers *****/

	/**
	 * Delete records.
	 *
	 * @param      $where
	 * @param null $format
	 *
	 * @return bool
	 */
	public function delete( $where, $format = null ) {
		global $wpdb;

		$ret = $wpdb->delete( $this->table, $where, $format );

		return $ret ? true : false;
	}

	/**
	 * Delete by ID.
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function delete_by_id( $id ) {
		return $this->delete( array( 'ID' => $id ), array( '%d' ) );
	}

	/**
	 * Get the Schema.
	 */
	public function get_schema() { return ''; }
}