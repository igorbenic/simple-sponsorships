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
	 * DB Type.
	 *
	 * @var string
	 */
	protected $type = '';

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

	/**
	 * Meta Type
	 * @var string
	 */
	protected $meta_type = '';

	/***** Getters *****/

	/**
	 * Return the type of this DB.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

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

		$results = $wpdb->get_results( "SELECT * FROM " . $this->get_table_name(), ARRAY_A );

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

		$sql = $wpdb->prepare( "SELECT * FROM " . $this->get_table_name() . " WHERE ID=%d", $id );

		$results = $wpdb->get_row( $sql, ARRAY_A );

		return $results ? $results : array();
	}

	/**
	 * Get results by a column.
	 *
	 * @param $column
	 * @param $value
	 *
	 * @return array|null|object
	 */
	public function get_by_column( $column, $value ) {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT * FROM " . $this->get_table_name() . " WHERE $column=%s", $value );

		$results = $wpdb->get_results( $sql, ARRAY_A );

		return $results ? $results : array();
	}

	/**
	 * Get Results by meta key
	 * @param string $key
	 * @param mixed  $value
	 */
	public function get_by_meta( $key, $value ) {
		global $wpdb;

		$sql = "SELECT main_table.* FROM " . $this->get_table_name() . " main_table ";
		$sql .= "LEFT JOIN " . $this->get_meta_table_name() . " meta_table ON meta_table.{$this->meta_type}_id=main_table.ID ";
		$sql .= $wpdb->prepare( "WHERE meta_table.meta_key=%s AND meta_table.meta_value=%s", $key, $value );
		$results = $wpdb->get_results( $sql, ARRAY_A );

		return $results ? $results : array();
	}

	/**
	 * Get all meta from the Object ID
	 *
	 * @param string $id ID of the object (example: Sponsorship ID).
	 *
	 * @return array
	 */
	public function get_all_meta( $id ) {
		global $wpdb;

		$sql = "SELECT meta_table.* FROM " . $this->get_meta_table_name() . " meta_table ";
		$sql .= $wpdb->prepare( "WHERE meta_table.{$this->meta_type}_id=%s", $id );
		$results = $wpdb->get_results( $sql, ARRAY_A );

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

		$ret = $wpdb->insert( $this->get_table_name(), $data, $format );

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

		$old = $this->get_by_id( $id );
		$ret = $wpdb->update( $this->get_table_name(), $data, array( 'ID' => $id ), $format, array( '%d') );

		if ( $ret ) {
			foreach ( $data as $column => $value ) {
				if ( $value !== $old[ $column ] ) {
					do_action( 'ss_' . $this->get_type() . '_' . $column . '_updated', $value, $old[ $column ], $id );
					do_action( 'ss_' . $this->get_type() . '_' . $column . '_' . $value, $id );
				}
			}
		}

		return false !== $ret ? true : false;
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

		$ret = $wpdb->delete( $this->get_table_name(), $where, $format );

		return $ret ? true : false;
	}

	/**
	 * Delete records.
	 *
	 * @param      $where
	 * @param null $format
	 *
	 * @return bool
	 */
	public function delete_all_meta( $where, $format = null ) {
		global $wpdb;

		$ret = $wpdb->delete( $this->get_meta_table_name(), $where, $format );

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