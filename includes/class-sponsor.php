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
		'post_status'  => 'post_status',
		'status'       => 'post_status'
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

	/**
	 * Is the Sponsor active?
	 * It should check for any active sponsorships if we don't have any status.
	 */
	public function is_active() {
		$status = $this->get_data( 'post_status' );
		return $status === 'ss-active';
	}

	/**
	 * Add Sponsored Quantity.
	 *
	 * @param int $qty Quantity
	 */
	public function add_sponsored_quantity( $qty ) {
		$sponsored = $this->get_data( '_sponsored_quantity', 1 );
		$sponsored = $sponsored + $qty;
		$this->update_data( '_sponsored_quantity', $sponsored );
	}

	/**
	 * Maybe Activate the Sponsor
	 */
	public function maybe_activate() {
		$status = $this->get_data( 'post_status' );
		if ( 'ss-active' !== $status ) {
			$this->update_data( 'post_status', 'ss-active' );
		}
	}
}