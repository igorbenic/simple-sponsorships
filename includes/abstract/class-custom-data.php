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
			$additional_keys = array();
			foreach ( $this->table_columns as $key => $table_column ) {
				if ( $column === $table_column ) {
					$additional_keys[] = $key;
					break;
				}
			}
			$this->set_data( strtolower( $column ), $value );
			if ( $additional_keys ) {
				foreach ( $additional_keys as $new_column ) {
					$this->set_data( strtolower( $new_column ), $value );
				}
			}
		}
	}

	/**
	 * Populate the object from data.
	 * This can be used when we retrieved data from DB already and want to set the data there.
	 *
	 * @since 1.5.0
	 *
	 * @param array $data
	 */
	public function populate_from_data( $data ) {
		if ( ! $data ) {
			return;
		}

		if ( ! is_array( $data ) ) {
			return;
		}

		foreach ( $data as $data_key => $data_value ) {
			$this->set_data( $data_key, $data_value );
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
	 * @return mixed
	 */
	public function get_current_class() {
		return str_replace( 'simple_sponsorships_', '', strtolower( str_replace( '\\', '_', get_class( $this ) ) ) );
	}

	/**
	 * Get the data for the level
	 *
	 * @param string|array $key Key or array of keys for data.
	 */
	public function get_data( $key, $default = '' ) {
		if ( ! isset( $this->data[ $key ] ) ) {
			// If we have no ID, we can't get DB data.
			if ( $this->id ) {
				if ( $this->is_table_column( $key ) ) {
					$this->populate_table_data();
				} else {
					$db    = $this->get_db_object();
					$value = $db->get_meta( $this->id, $key, true );
					$value = $value === '' ? $default : $value;
					$this->set_data( $key, $value );
				}
			}
		}
		$return = isset( $this->data[ $key ] ) ? $this->data[ $key ] : $default;
		return $return;
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
	 * Set the data.
	 *
	 * @param string $key
	 */
	public function remove_data( $key ) {
		if ( isset( $this->data[ $key ] ) ) {
			unset( $this->data[ $key ] );
		}
	}

	/**
	 * Adding Data to the Custom Data object.
	 *
	 * @param $key
	 * @param $value
	 */
	public function add_data( $key, $value ) {
		$db = $this->get_db_object();
		if ( $this->is_table_column( $key ) ) {
			$data = array();
			$data[ $key ] = $value;
			$db->update( $this->get_id(), $data );
		} else {
			$db->add_meta( $this->get_id(), $key, $value );
		}
		$this->set_data( $key, $value );
	}

	/**
	 * This will update the data for the Custom Data object.
	 *
	 * @param $key
	 * @param $value
	 */
	public function update_data( $key, $value ) {
		$db = $this->get_db_object();
		if ( $this->is_table_column( $key ) ) {
			$data = array();
			$data[ $key ] = $value;
			$db->update( $this->get_id(), $data );
		} else {
			$db->update_meta( $this->get_id(), $key, $value );
		}
		do_action( 'ss_update_' . $this->get_current_class() . '_data_' . $key, $value, $this );
		$this->set_data( $key, $value );
	}

	/**
	 * This will update the data for the Custom Data object.
	 *
	 * @param $key
	 * @param $value
	 */
	public function delete_data( $key, $value = '' ) {
		$db = $this->get_db_object();
		if ( $this->is_table_column( $key ) ) {
			$data = array();
			$data[ $key ] = $value;
			$db->update( $this->get_id(),'' );
		} else {
			$db->delete_meta( $this->get_id(), $key, $value );
		}
		$this->remove_data( $key, $value );
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