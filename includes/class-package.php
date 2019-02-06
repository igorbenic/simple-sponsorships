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
		'price'       => 'price'
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
	 * @return mixed
	 */
	public function is_available() {
		return apply_filters( 'ss_package_is_available', true, $this );
	}

	/**
	 * Get the Price
	 */
	public function get_price() {
		return $this->get_data( 'price' );
	}

	/**
	 * Get the Price HTML.
	 */
	public function get_price_html() {
		return ss_currency_symbol() . $this->get_price();
	}
}