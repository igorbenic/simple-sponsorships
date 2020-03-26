<?php

/**
 * Plugin Name: Package Minimum Quantity
 * Description: This is an add-on for Simple Sponsorships to add enable minimum quantity.
 * Version: 1.0.0
 */

namespace Simple_Sponsorships\Package_Minimum_Quantity;

use Simple_Sponsorships\DB\DB_Packages;
use Simple_Sponsorships\DB\DB_Sponsorship_Items;
use Simple_Sponsorships\Integrations\Integration;
use Simple_Sponsorships\Sponsorship;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

class Plugin extends Integration {


	protected $validation_message = '';

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->title = __( 'Package Minimum Quantity', 'simple-sponsorships-premium' );
		$this->id    = 'package-minimum-quantity';
		$this->desc  = __( 'Allow packages to have minimum quantity for purchase.', 'simple-sponsorships-premium' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/package.svg';

		add_action( 'ss_get_package_fields', array( $this, 'add_quantity_field' ) );
		add_action( 'ss_package_updated', array( $this, 'save_package_minimum_qty' ), 20, 2 );

		add_filter( 'ss_package_check_required', array( $this, 'check_package_quantity' ), 20, 2 );
		add_filter( 'ss_required_field_notice', array( $this, 'field_notice' ), 20, 4 );
	}

	/**
	 * Add Validation Message
	 *
	 * @param $message
	 *
	 * @return string
	 */
	public function field_notice( $notice, $field, $key, $form ) {
		if ( $this->validation_message && 'form_sponsors' === $form && 'package' === $key ) {
			return $this->validation_message;
		}

		return $notice;
	}

	/**
	 * @param $fields
	 */
	public function add_quantity_field( $fields ) {

		$fields['minimum_quantity'] = array(
			'id' => 'minimum_quantity',
			'type' => 'number',
			'title' => __( 'Minimum Quantity', 'simple-sponsorships-premium' ),
			'field_class' => 'widefat',
			'default' => 0,
			'desc' => __( 'If 0 or empty, disabled. Works if multiple packages are enabled.', 'simple-sponsorships-premium' ),
		);

		return $fields;
	}

	/**
	 * @param $id
	 * @param $data
	 */
	public function save_package_minimum_qty( $id, $data ) {
		$db = new DB_Packages();
		if ( isset( $data['minimum_quantity'] ) ) {
			$db->update_meta( $id, 'minimum_quantity', absint( $data['minimum_quantity'] ) );
		} else {
			$db->delete_meta( $id, 'minimum_quantity' );
		}
	}

	/**
	 * Check Package Quantites
	 * @param boolean $ret
	 * @param array $packages
	 */
	public function check_package_quantity( $ret, $packages ) {
		if ( ! $ret ) {
			return $ret;
		}

		if ( is_array( $packages ) && $packages ) {
			$db = new DB_Packages();
			foreach ( $packages as $package_id => $package_qty ) {
				if ( $package_qty < 1 ) {
					continue; // We don't need to check that.
				}
				$package  = ss_get_package( $package_id, false );
				$quantity = $db->get_meta( $package_id, 'minimum_quantity', true );
				if ( $quantity && $quantity > $package_qty ) {
					$this->validation_message = sprintf( __( 'Package %s requires at least %d quantities', 'simple-sponsorships-premium' ), $package->get_title(), $quantity );
					return false;
				}
			}
		}

		return $ret;
	}

}

//new Plugin();