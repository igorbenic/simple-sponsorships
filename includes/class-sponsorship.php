<?php
/**
 * Class to handle single sponsorship.
 */
namespace Simple_Sponsorships;

use Simple_Sponsorships\DB\DB_Sponsorships;

/**
 * Class Sponsorship
 *
 * @package Simple_Sponsorships
 */
class Sponsorship extends Custom_Data {

	/**
	 * Table Fields from DB Schema.
	 * The keys are field ids and the values are db column names.
	 *
	 * @var array
	 */
	protected $table_columns = array(
		'id'             => 'ID',
		'status'         => 'status',
		'amount'         => 'amount',
		'subtotal'       => 'subtotal',
		'currency'       => 'currency',
		'gateway'        => 'gateway',
		'transaction_id' => 'transaction_id',
		'package'        => 'package',
		'sponsor'        => 'sponsor',
		'date'           => 'date',
		'key'            => 'ss_key',
		'ss_key'         => 'ss_key',
	);


	/**
	 * Get the DB Object.
	 */
	public function get_db_object() {
		if ( null === $this->db ) {
			$this->db = new DB_Sponsorships();
		}

		return $this->db;
	}

	/**
	 * Get the Sponsor Data
	 */
	public function get_sponsor_data() {
		$sponsor_id = $this->get_data( 'sponsor' );
		$sponsor    = new Sponsor( 0 );
		// This might be just a request so we don't have a sponsor yet.
		if ( ! $sponsor_id ) {
			$db = $this->get_db_object();
			$sponsor_from_meta = apply_filters( 'ss_sponsorship_get_data_non_sponsor', array(
				'title' => $db->get_meta( $this->get_id(), '_sponsor_name', true ),
			), $this );
			// Let's use the data stored in meta.
			foreach ( $sponsor_from_meta as $key => $value ) {
				$sponsor->set_data( $key, $value );
			}

		} else {
			$sponsor->set_id( $sponsor_id );
		}

		return $sponsor;
	}

	/**
	 * Return if the Sponsorship has a status.
	 */
	public function is_status( $status = '' ) {
		return apply_filters( 'ss_sponsorship_is_' . $status, $status === $this->get_data( 'status' ), $this );
	}

	/**
	 * Return if the Sponsorship is pending payment.
	 */
	public function is_pending() {
		return $this->is_status( 'pending' );
	}

	/**
	 * Return if the Sponsorship is pending payment.
	 */
	public function is_request() {
		return $this->is_status( 'request' );
	}
}