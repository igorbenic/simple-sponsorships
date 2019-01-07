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
	 * @return mixed|void
	 */
	public function is_available() {
		return apply_filters( 'ss_package_is_available', true, $this );
	}

}