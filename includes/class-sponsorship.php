<?php
/**
 * Class to handle single sponsorship.
 */
namespace Simple_Sponsorships;

use Simple_Sponsorships\DB\DB_Sponsorship_Items;
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
	 * Sponsorship Items
	 *
	 * @var array
	 */
	protected $items = null;

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
	 *
	 * @return \Simple_Sponsorships\Sponsor
	 */
	public function get_sponsor_data() {
		$sponsor_id = $this->get_data( 'sponsor' );
		$sponsor    = new Sponsor( 0 );
		// This might be just a request so we don't have a sponsor yet.
		if ( ! $sponsor_id ) {
			$db = $this->get_db_object();
			$sponsor_from_meta = apply_filters( 'ss_sponsorship_get_data_non_sponsor', array(
				'name' => $db->get_meta( $this->get_id(), '_sponsor_name', true ),
				'_email' => $db->get_meta( $this->get_id(), '_email', true ),
				'_website' => $db->get_meta( $this->get_id(), '_website', true ),
				'_company' => $db->get_meta( $this->get_id(), '_company', true ),
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
	 * Get the package for this sponsorship.
	 *
	 * @return \Simple_Sponsorships\Package Package object.
	 */
	public function get_package() {
		$package_id = $this->get_data( 'package' );
		$package    = new Package( $package_id );
		return $package;
	}

	/**
	 * Calculate Totals
	 */
	public function calculate_totals() {
		$items = $this->get_items();

		$total = 0;

		if ( $items ) {
			foreach ( $items as $item ) {
				$total += $item['item_amount'] * $item['item_qty'];
			}
		}

		$this->update_data( 'amount', $total );
	}

	/**
	 * Get the package for this sponsorship.
	 *
	 * @return array
	 */
	public function get_packages() {
		$items = $this->get_items();
		if ( ! $items ) {
			return array();
		}

		$packages = array();
		foreach ( $items as $item ) {
			if ( 'package' === $item['item_type'] ) {
				$packages[] = $item;
			}
		}

		if ( ! $packages ) {
			return array();
		}

		$objects = array();

		foreach ( $packages as $package ) {
			$objects[] = new Package( $package['item_id'] );
		}

		return $objects;
	}

	/**
	 * Add Package
	 *
	 * @param integer|Package $package_id Package ID.
	 * @param integer $qty Quantity.
	 */
	public function add_package( $package, $qty = 1 ) {
		if ( is_numeric( $package ) ) {
			$package = ss_get_package( $package );
		}

		$item = array(
			'item_id'     => $package->get_id(),
			'item_qty'    => $qty,
			'item_name'   => $package->get_data( 'title' ),
			'item_type'   => 'package',
			'item_amount' => $package->get_price(),
		);

		$this->add_item( $item );
	}

	/**
	 * Add an Item
	 *
	 * @param array $item Item array
	 *
	 * @return integer|false|\WP_Error
	 */
	public function add_item( $item ) {
		$item = wp_parse_args( $item, array(
			'item_amount' => 0,
			'item_name'   => '',
			'item_type'   => 'package',
			'item_id'     => 0,
			'item_qty'    => 1
		));

		$item['sponsorship_id'] = $this->get_id();

		$db      = new DB_Sponsorship_Items();
		$item_id = $db->create_item( $item );

		return $item_id;
	}

	/**
	 * Array of items to be added.
	 *
	 * @param array  $items
	 * @param string $item_type
	 */
	public function add_items( $items, $item_type = 'package' ) {
		if ( ! is_array( $items ) ) {
			return false;
		}

		foreach ( $items as $item ) {
			$item['item_type'] = $item_type;
			$this->add_item( $item );
		}
	}

	/**
	 * Get items.
	 */
	public function get_items() {
		if ( null === $this->items ) {
			$db    = new DB_Sponsorship_Items();
			$items = $db->get_by_column( 'sponsorship_id', $this->get_id() );
			if ( $items ) {
				$this->items = $items;
			} else {
				$this->items = array();
			}
		}

		return $this->items;
	}

	/**
	 * Get the view link
	 *
	 * @return false|string
	 */
	public function get_view_link() {
		$sponsorship_page = ss_get_option( 'sponsorship_page', 0 );

		if ( $sponsorship_page ) {
			$view_link = get_permalink( $sponsorship_page );
			$view_link = add_query_arg( 'sponsorship-key', $this->get_data( 'ss_key' ), $view_link );
			return $view_link;
		}

		return '';
	}

	/**
	 * Return if the Sponsorship has a status.
	 */
	public function is_status( $status = '' ) {
		return apply_filters( 'ss_sponsorship_is_' . $status, $status === $this->get_data( 'status' ), $this );
	}

	/**
	 * Set the Sponsorship Status.
	 *
	 * @param $status
	 */
	public function set_status( $status ) {
		if ( in_array( $status, array_keys( ss_get_sponsorship_statuses() ), true ) ) {
			if ( $this->get_data( 'status' ) !== $status ) {
				$this->update_data( 'status', $status );
			}
		}
	}

	/**
	 * Activating the Sponsorship.
	 */
	public function activate() {
		// Activate only on paid sponsorships.
		if ( ! $this->is_status( 'paid' ) ) {
			return;
		}
		ss_add_notice( sprintf( __( 'Sponsorship #%d was successfully paid', 'simple-sponsorship' ), $this->get_id() ), 'success' );

		$sponsor = $this->get_sponsor_data();
		$sponsor->add_available_quantity( $this->get_package()->get_data( 'quantity', 1 ) );
		$sponsor->maybe_activate();
		do_action( 'ss_sponsorship_activated', $this );
	}

	/**
	 * Return if the Sponsorship is pending payment.
	 */
	public function is_approved() {
		return $this->is_status( 'approved' );
	}

	/**
	 * Return if the Sponsorship is pending payment.
	 */
	public function is_request() {
		return $this->is_status( 'request' );
	}

	/**
	 * Return the formatted amount.
	 *
	 * @return string
	 */
	public function get_formatted_amount() {
		return Formatting::price( $this->get_data( 'amount' ) );
	}
}