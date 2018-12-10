<?php
/**
 * Class to handle single sponsorship.
 */
namespace Simple_Sponsorships;

use Simple_Sponsorships\DB\DB_Sponsors;

/**
 * Class Sponsorship
 *
 * @package Simple_Sponsorships
 */
class Sponsor extends Custom_Data {

	/**
	 * Table Fields from DB Schema.
	 * The keys are field ids and the values are db column names.
	 *
	 * @var array
	 */
	protected $table_columns = array(
		'id'           => 'ID',
		'name'         => 'post_title',
		'post_content' => 'post_content',
		'post_type'    => 'post_type',
	);


	/**
	 * Get the DB Object.
	 */
	public function get_db_object() {
		if ( null === $this->db ) {
			$this->db = new DB_Sponsors();
		}

		return $this->db;
	}
}