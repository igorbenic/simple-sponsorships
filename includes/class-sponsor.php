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
		'ID'           => 'ID',
		'id'           => 'ID',
		'name'         => 'post_title',
		'post_title'   => 'post_title',
		'post_content' => 'post_content',
		'post_type'    => 'post_type',
		'post_status'  => 'post_status',
		'status'       => 'post_status'
	);

	/**
	 * Populate data from a Post object.
	 *
	 * @param \WP_Post $post Post object.
	 */
	public function populate_from_post( $post ) {
		$object = (array) $post;
		$this->set_id( $object['ID'] );
		foreach ( $object as $column => $value ) {
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

	/**
	 * Return the link to the Sponsor.
	 *
	 * @return false|string
	 */
	public function get_link() {
		if ( ! $this->get_id() ) {
			return '';
		}

		$link = $this->get_data( '_website', '' );

		if ( ! $link ) {
			$link = get_permalink( $this->get_id() );
		}

		return $link;
	}

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
	 * Get how much quantity the sponsor has available to sponsor.
	 *
	 * @return mixed|string
	 */
	public function get_available_quantity() {
		return $this->get_data( '_available_qty', 0 );
	}

	/**
	 * Add Sponsored Quantity.
	 *
	 * @param int $qty Quantity
	 */
	public function add_available_quantity( $qty ) {
		$sponsored = $this->get_data( '_available_qty', 0 );
		$sponsored = absint( $sponsored ) + absint( $qty );
		$this->update_data( '_available_qty', $sponsored );
	}

	/**
	 * Remove Sponsored Quantity.
	 *
	 * @param int $qty Quantity
	 */
	public function remove_available_quantity( $qty ) {
		$sponsored = $this->get_data( '_available_qty', 0 );
		$sponsored = absint( $sponsored ) - absint( $qty );
		if ( $sponsored < 0 ) {
			$sponsored = 0;
		}
		$this->update_data( '_available_qty', $sponsored );
	}

	/**
	 * Maybe Activate the Sponsor
	 */
	public function maybe_activate() {
		$status = $this->get_data( 'post_status' );
		if ( 'publish' !== $status ) {
			$this->update_data( 'post_status', 'publish' );
		}
	}

	/**
	 * Get Sponsored Content
	 *
	 * @return \int[]|\WP_Post[]
	 */
	public function get_sponsored_content() {
		$db = $this->get_db_object();
		return $db->get_sponsored_content( $this->get_id() );
	}

	/**
	 * Has the sponsor a logo
	 */
	public function has_logo() {
		return has_post_thumbnail( $this->get_id() );
	}

	/**
	 * Return the logo of the sponsor
	 */
	public function get_logo( $size = 'medium' ) {
		return get_the_post_thumbnail( $this->get_id(), $size );
	}
}
