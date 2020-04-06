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
		'type'           => 'type',
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
	 * Get the Type of this Sponsorship
	 */
	public function get_type() {
		$type =  $this->get_data( 'type', 'onetime' );

		if ( ! $type ) {
			return 'onetime';
		}

		return $type;
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
		if ( ! $package ) { return false; }
		if ( is_numeric( $package ) ) {
			$package = ss_get_package( $package );
		}

		if ( ! $package ) { return false; }

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
	 * Get the data for the level
	 *
	 * @param string|array $key Key or array of keys for data.
	 */
	public function get_data( $key, $default = '' ) {

		if ( 'packages' === $key ) {
			$value = $this->get_packages();
			$this->set_data( $key, $value );
		} else {
			parent::get_data( $key, $default );
		}

		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : $default;
	}

	/**
	 * Reset items data.
	 */
	public function reset_items_data() {
		$this->items = null;
		$this->get_items();
	}

	/**
	 * Get items.
	 */
	public function get_items( $item_type = '' ) {
		if ( null === $this->items ) {
			$db    = new DB_Sponsorship_Items();
			$items = $db->get_by_column( 'sponsorship_id', $this->get_id() );
			if ( $items ) {
				$this->items = $items;
			} else {
				$this->items = array();
			}
		}

		$items = $this->items;

		if ( $item_type && $items ) {
			$items = array();
			foreach ( $this->items as $item ) {
				if ( $item_type !== $item['item_type'] ) {
					continue;
				}

				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Update sponsorship items.
	 *
	 * @param array $items
	 */
	public function update_items( $items ) {
		$db_items      = $this->get_items();
		$db            = new DB_Sponsorship_Items();
		$new_items     = array();
		$update_items  = array();

		if ( $db_items ) {
			if ( ! $items ) {
				// No Items anymore? Delete DB items.
				foreach ( $db_items as $db_item ) {
					$db->delete_item( $db_item['ID'] );
				}

				return;
			}

			$item_ids      = wp_list_pluck( $items, 'ID' );
			$removed_items = array();
			foreach ( $db_items as $index => $db_item ) {
				if ( ! in_array( $db_item['ID'], $item_ids ) ) {
					$removed_items[] = $db_item['ID'];
					unset( $db_items[ $index ] );
				}
			}

			if ( $removed_items ) {
				foreach ( $removed_items as $removed_item_id ) {
					$db->delete_item( $removed_item_id );
				}
			}
		}

		foreach ( $items as $item ) {
			if ( ! isset( $item['ID'] ) || ! $item['ID'] ) {
				$new_items[] = $item;
			} else {
				$update_items[] = $item;
			}
		}

		foreach ( $new_items as $item ) {
			$item['sponsorship_id'] = $this->get_id();
			$db->create_item( $item );
		}

		foreach ( $update_items as $update_item ) {
			$update_item['sponsorship_id'] = $this->get_id();
			$db->update_item( $update_item );
		}

		$this->reset_items_data();
	}

	/**
	 * Remove items.
	 *
	 * @param array $delete_ids
	 */
	public function remove_items( $delete_ids ) {
		$items = $this->get_items();
		$db    = new DB_Sponsorship_Items();

		if ( ! is_array( $delete_ids ) ) {
			$delete_ids = array( $delete_ids );
		}

		$items_ids = wp_list_pluck( $items, 'ID' );
		foreach ( $delete_ids as $id ) {
			if ( ! in_array( $id, $items_ids ) ) {
				unset( $delete_ids[ $id ] );
			}
		}

		if ( $delete_ids ) {
			foreach ( $delete_ids as $delete_id ) {
				$db->delete_item( $delete_id );
			}
			$this->reset_items_data();
		}
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
	 * Generates a URL to view an order from the my account page.
	 *
	 * @return string
	 */
	public function get_view_account_url() {
		$account_page = ss_get_option( 'account_page', 0 );
		if ( ! $account_page ) {
			return $this->get_view_link();
		}

		return apply_filters( 'ss_get_view_account_url', ss_get_endpoint_url( 'view-sponsorship', $this->get_id(), get_permalink( $account_page ) ), $this );
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

		$sponsor              = $this->get_sponsor_data();
		$purchased_quantities = 0;
		$packages             = $this->get_packages();
		foreach ( $packages as $package ) {
			$purchased_quantities += $package->get_data( 'quantity', 1 );
		}
		$sponsor->add_available_quantity( $purchased_quantities );
		$sponsor->maybe_activate();
		$content_id = $this->get_data( '_content_id' );
		if ( $content_id ) {
			$sponsor_id = $sponsor->get_id();
			$add        = ss_add_sponsor_for_content( $content_id, $sponsor_id );
			if ( $add ) {
				$sponsor->remove_available_quantity( 1 );
			}
		}
		do_action( 'ss_sponsorship_activated', $this );
	}

	/**
	 * Return if the Sponsorship is pending payment.
	 */
	public function is_approved() {
		return $this->is_status( 'approved' );
	}

	/**
	 * Return if the Sponsorship is paid.
	 */
	public function is_paid() {
		return $this->is_status( 'paid' );
	}

	/**
	 * Return if the Sponsorship is pending payment.
	 */
	public function is_request() {
		return $this->is_status( 'request' );
	}

	/**
	 * Return if the Sponsorship is pending payment.
	 */
	public function is_on_hold() {
		return $this->is_status( 'on-hold' );
	}

	/**
	 * Return the formatted amount.
	 *
	 * @return string
	 */
	public function get_formatted_amount() {
		return apply_filters( 'ss_sponsorship_formatted_amount', Formatting::price( $this->get_data( 'amount' ) ), $this->get_data( 'amount' ), $this );
	}
}