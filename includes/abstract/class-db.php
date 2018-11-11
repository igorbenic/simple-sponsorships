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
	private $table = '';

	/**
	 * Meta Table
	 *
	 * @var string
	 */
	private $meta_table = '';

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

	/**
	 * Get the Schema.
	 */
	public function get_schema() { return ''; }
}