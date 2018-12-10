<?php
/**
 * Class for handling custom data.
 */

namespace Simple_Sponsorships;
use Simple_Sponsorships\DB\DB;

/**
 * Class Custom_Data
 *
 * @package Simple_Sponsorships
 */
abstract class Custom_Data {

	/**
	 * ID
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Table Fields from DB Schema.
	 * The keys are field ids and the values are db column names.
	 *
	 * @var array
	 */
	protected $table_columns = array();

	/**
	 * Database interface for using Levels.
	 *
	 * @var null
	 */
	protected $db = null;

	/**
	 * Data retrieved from DB.
	 * @var array
	 */
	protected $data = array();

	/**
	 * Package constructor.
	 *
	 * @param      $package_id
	 * @param bool $get_data
	 */
	public function __construct( $id, $get_data = true ) {
		$this->id = $id;

		if ( $this->id && $get_data ) {
			$this->populate_table_data();
		}
	}

	/**
	 * Get all columns and populate the data.
	 */
	public function populate_table_data() {
		$db   = $this->get_db_object();
		$data = $db->get_by_id( $this->id );
		foreach ( $data as $column => $value ) {
			foreach ( $this->table_columns as $key => $table_column ) {
				if ( $column === $table_column ) {
					$column = $key;
					break;
				}
			}
			$this->set_data( strtolower( $column ), $value );
		}
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the ID and return it.
	 *
	 * @param $id
	 *
	 * @return int
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
		return $this->id;
	}

	/**
	 * Get the DB Object.
	 *
	 * @return DB
	 */
	public function get_db_object() {
		throw new \Exception( __( 'This must be defined in each class.', 'simple-sponsorships' ) );
	}

	/**
	 * Get the data for the level
	 *
	 * @param string|array $key Key or array of keys for data.
	 */
	public function get_data( $key ) {
		if ( ! isset( $this->data[ $key ] ) ) {
			// If we have no ID, we can't get DB data.
			if ( $this->id ) {
				if ( $this->is_table_column( $key ) ) {
					$this->populate_table_data();
				} else {
					$db    = $this->get_db_object();
					$value = $db->get_meta( $this->id, $key, true );
					$this->set_data( $key, $value );
				}
			} else {
				return '';
			}
		}

		return $this->data[ $key ];
	}

	/**
	 * Set the data.
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set_data( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Check if the provided key is a table column or meta data.
	 *
	 * @param $key
	 * @return boolean
	 */
	public function is_table_column( $key ) {
		if ( isset( $this->table_columns[ strtolower( $key ) ] ) ) {
			return true;
		}

		return false;
	}
}