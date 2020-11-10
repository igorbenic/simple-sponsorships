<?php
/**
 * Class to handle each Package.
 */

namespace Simple_Sponsorships;

use Simple_Sponsorships\DB\DB_Packages;

/**
 * Class Package
 *
 * @package Simple_Sponsorships
 */
class Package extends Custom_Data {

	/**
	 * Table Fields from DB Schema.
	 * The keys are field ids and the values are db column names.
	 *
	 * @var array
	 */
	protected $table_columns = array(
		'id'          => 'ID',
		'title'       => 'title',
		'description' => 'description',
		'quantity'    => 'quantity',
		'price'       => 'price',
		'status'      => 'status',
		'type'        => 'type',
	);

	/**
	 * Get the DB Object.
	 */
	public function get_db_object() {
		if ( null === $this->db ) {
			$this->db = new DB_Packages();
		}

		return $this->db;
	}

	/**
	 * Get the status.
	 *
	 * @since 0.6.0
	 *
	 * @return mixed|string
	 */
	public function get_status() {
		return $this->get_data( 'status' ) ? $this->get_data( 'status' ) : 'active';
	}

	/**
	 * @return mixed
	 */
	public function is_available() {
		return apply_filters( 'ss_package_is_available', 'unavailable' !== $this->get_status(), $this );
	}

	/**
	 * Get the Price
	 */
	public function get_price( $unfiltered = false ) {
		return $unfiltered ? $this->get_data( 'price' ) : apply_filters( 'ss_package_get_price', $this->get_data( 'price' ), $this );
	}

	/**
	 * Get the title
	 *
	 * @return string
	 */
	public function get_type() {
		return apply_filters( 'ss_package_get_type', $this->get_data('type' ), $this );
	}

	/**
	 * Get the title
	 *
	 * @param boolean $unfiltered
	 *
	 * @return string
	 */
	public function get_title( $unfiltered = false ) {
		return $unfiltered ? $this->get_data('title' ) : apply_filters( 'ss_package_get_title', $this->get_data('title' ), $this );
	}

	/**
	 * Get the Price HTML.
	 */
	public function get_price_html() {
		return Formatting::price( $this->get_price() );
	}

	/**
	 * Get the Price HTML.
	 */
	public function get_price_formatted( $exclude_html = true ) {
		return apply_filters( 'ss_package_get_price_formatted', Formatting::price( $this->get_price(), array( 'exclude_html' => $exclude_html ) ), $this->get_price(), $exclude_html, $this );
	}

	/**
	 * Get the Description.
	 *
	 * @return string
	 */
	public function get_description() {
		$description = $this->get_data( 'description' );
		if ( ! $description ) {
			return '';
		}

		return wpautop( wp_unslash( $description ) );
	}

	/**
	 * Populate data from a package array.
	 *
	 * @param array $package
	 */
	public function populate_from_package( $package ) {
		$this->set_id( $package['ID'] );
		foreach ( $package as $column => $value ) {
			$this->set_data( $column, $value );
			$additional_keys = array();
			foreach ( $this->table_columns as $key => $table_column ) {
				if ( $column === $table_column && $key !== $column ) {
					$additional_keys[] = $key;
				}
			}
			foreach ( $additional_keys as $key ) {
				$this->set_data( $key, $value );
			}
		}
	}
}
