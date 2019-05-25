<?php

/**
 * Plugin Name: Package Slots
 * Description: This is an add-on for Simple Sponsorships to add package slots.
 * Version: 1.0.0
 */

namespace Simple_Sponsorships\Package_Slots;

use Simple_Sponsorships\DB\DB_Packages;
use Simple_Sponsorships\DB\DB_Sponsorship_Items;
use Simple_Sponsorships\Sponsorship;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

class Plugin {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		add_action( 'ss_get_package_fields', array( $this, 'add_slots_field' ) );
		add_action( 'ss_package_updated', array( $this, 'save_package_slot' ), 20, 2 );
		add_filter( 'ss_package_is_available', array( $this, 'is_package_available' ), 20, 2 );
		add_filter( 'ss_package_get_title', array( $this, 'package_title' ), 20, 2 );
		add_action( 'ss_sponsorship_status_approved', array( $this, 'add_slot_on_active_sponsorship' ) );
		add_action( 'ss_sponsorship_status_paid', array( $this, 'add_slot_on_active_sponsorship' ) );

		add_action( 'ss_sponsorship_item_delete', array( $this, 'remove_slots_on_item_delete') );
		add_action( 'ss_update_package_data_used_slots', array( $this, 'update_package_status' ), 20, 2 );
	}

	/**
	 * @param $fields
	 */
	public function add_slots_field( $fields ) {

		$fields['slots'] = array(
			'id' => 'slots',
			'type' => 'number',
			'title' => __( 'Slots', 'simple-sponsorships' ),
			'field_class' => 'widefat',
			'default' => 0,
			'desc' => __( 'How many slots does this package have? If 0 or empty, unlimited.', 'simple-sponsorships' ),
		);

		$fields['used_slots'] = array(
			'id' => 'used_slots',
			'type' => 'number',
			'title' => __( 'Used Slots', 'simple-sponsorships' ),
			'field_class' => 'widefat',
			'default' => 0,
			'desc' => __( 'How many slots have already been used? This will be also updated automatically.', 'simple-sponsorships' ),
		);
		return $fields;
	}

	/**
	 * @param $id
	 * @param $data
	 */
	public function save_package_slot( $id, $data ) {
		$db = new DB_Packages();
		if ( isset( $data['slots'] ) ) {
			$db->update_meta( $id, 'slots', absint( $data['slots'] ) );
		} else {
			$db->delete_meta( $id, 'slots' );
		}

		if ( isset( $data['used_slots'] ) ) {
			$db->update_meta( $id, 'used_slots', absint( $data['used_slots'] ) );
		} else {
			$db->delete_meta( $id, 'used_slots' );
		}
	}

	/**
	 * Return if the package is available.
	 *
	 * @param $bool
	 * @param \Simple_Sponsorships\Package $package
	 */
	public function is_package_available( $bool, $package ) {
		if ( ! $bool ) {
			return $bool;
		}

		$slots      = $package->get_data('slots' );
		$used_slots = $package->get_data( 'used_slots' );

		if ( ! $slots || 0 === absint( $slots ) ) {
			return $bool;
		}

		if ( absint( $slots ) <= absint( $used_slots ) ) {
			return false;
		}

		return $bool;
	}

	/**
	 * @param Sponsorship $sponsorship
	 */
	public function add_slot_on_active_sponsorship( $sponsorship_id ) {
		$sponsorship = ss_get_sponsorship( $sponsorship_id, false );
		$items = $sponsorship->get_items( 'package' );
		if ( $items ) {
			$db = new DB_Sponsorship_Items();
			foreach ( $items as $item ) {
				$slot = $db->get_meta( $item['ID'], '_package_slots', true );
				if ( ! $slot ) {
					$slots = 1 * $item['item_qty'];
					$db->update_meta( $item['ID'], '_package_slots', $slots );
					$package = ss_get_package( $item['item_id'], false );
					if ( $package ) {
						$used_slots = $package->get_data('used_slots');
						$used_slots = $used_slots + $slots;
						$package->update_data( 'used_slots', $used_slots );
					}
				}
			}
		}
	}

	/**
	 * @param $item_id
	 */
	public function remove_slots_on_item_delete( $item_id ) {
		$db = new DB_Sponsorship_Items();
		$item  = $db->get_by_id( $item_id );
		$slots = $db->get_meta( $item_id, '_package_slots', true );
		if ( ! $slots ) {
			return;
		}
		$package = ss_get_package( $item['item_id'], false );
		if ( $package ) {
			$used_slots = $package->get_data('used_slots');
			$used_slots = $used_slots - $slots;
			if ( $used_slots < 0 ) {
				$used_slots = 0;
			}
			$package->update_data( 'used_slots', $used_slots );
		}
	}

	/**
	 * @param $used_slots
	 * @param $package
	 */
	public function update_package_status( $used_slots, $package ) {
		$slots = $package->get_data( 'slots' );
		if ( absint( $slots ) <= absint( $used_slots ) ) {
			$package->update_data( 'status', 'unavailable' );
		} else {
			$package->update_data( 'status', 'available' );
		}
	}

	/**
	 * @param $title
	 * @param $package
	 *
	 * @return string
	 */
	public function package_title( $title, $package ) {
		$slots = $package->get_data( 'slots' );
		if ( $slots ) {
			$used_slots = $package->get_data( 'used_slots', 0 );

			$title .= ' (' . $used_slots . '/' . $slots . ')';
		}
		return $title;
	}
}

new Plugin();