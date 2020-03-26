<?php

/**
 * Plugin Name: Simple Sponsorships - Recurring Payments
 * Description: This is an add-on for Simple Sponsorships to enable recurring payments.
 * Version: 1.0.0
 */

namespace Simple_Sponsorships\Recurring_Payments;

use Simple_Sponsorships\DB\DB_Packages;
use Simple_Sponsorships\Formatting;
use Simple_Sponsorships\Integrations\Integration;
use Simple_Sponsorships\Package;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class Plugin
 *
 * @package Simple_Sponsorships\Recurring_Payments
 */
class Plugin extends Integration {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->title = __( 'Recurring Payments', 'simple-sponsorships' );
		$this->id    = 'recurring-payments';
		$this->desc  = __( 'Allows you to accept recurring payments (subscriptions).', 'simple-sponsorships' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/repeat.svg';

		add_filter( 'ss_package_types', array( $this, 'add_recurring_type' ) );
		add_filter( 'ss_get_package_fields', array( $this, 'add_recurring_fields' ), 11 );
		add_filter( 'ss_packages_column_price', array( $this, 'show_recurring_on_price_column' ), 20, 2 );
		add_filter( 'ss_package_get_price_formatted', array( $this, 'ss_package_show_formatted_price' ), 20, 4 );
		add_filter( 'ss_payment_gateways', array( $this, 'add_gateways' ) );
		add_filter( 'ss_create_sponsorships_package_availability_check', array( $this, 'restrict_only_one_recurring_package' ), 20, 2 );
		add_filter( 'ss_ajax_packages_get_total_fragments', array( $this, 'get_total_fragments' ), 20, 4 );
		add_filter( 'ss_create_sponsorships_account_required', array( $this, 'set_required_account_if_recurring_package' ), 20, 2 );
		add_filter( 'ss_available_payment_gateways', array( $this, 'filter_available_gateways' ), 20, 2 );
		add_filter( 'ss_sponsorship_formatted_amount', array( $this, 'format_sponsorship_amount' ), 20, 3 );
		add_filter( 'ss_package_get_price', array( $this, 'package_get_price' ), 20, 2 );

		add_action( 'ss_package_updated', array( $this, 'save_package_recurring' ), 20, 2 );
		add_action( 'ss_package_added', array( $this, 'save_package_recurring' ), 20, 2 );

		$this->includes();
	}

	/**
	 * Package Price with signup fee.
	 *
	 * @param number $price
	 * @param Package $package
	 */
	public function package_get_price( $price, $package ) {
		if ( 'recurring' !== $package->get_type() ) {
			return $price;
		}

		$signup_fee = $package->get_data( 'recurring_signup_fee', 0 );

		if ( ! $signup_fee ) {
			return $price;
		}

		$price = $price + $signup_fee;
		return $price;
	}

	/**
	 * Check if a Sponsorship contains a recurring package
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship
	 *
	 * @return boolean
	 */
	public static function sponsorship_contains_recurring_packages( $sponsorship ) {
		if ( ! $sponsorship ) {
			return false;
		}

		$packages = $sponsorship->get_packages();

		if ( ! $packages ) {
			return false;
		}

		foreach ( $packages as $package ) {
			if ( 'recurring' === $package->get_type() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Filter the Available Gateways
	 *
	 * @param array $gateways
	 * @param null|\Simple_Sponsorships\Sponsorship $sponsorship
	 */
	public function filter_available_gateways( $gateways, $sponsorship ) {
		if ( null === $sponsorship ) {
			return $gateways;
		}

		if ( ! self::sponsorship_contains_recurring_packages( $sponsorship ) ) {
			return $gateways;
		}

		$supported_gateways = array();
		foreach ( $gateways as $gateway_id => $gateway_object ) {
			if ( $gateway_object->supports( 'recurring' ) ) {
				$supported_gateways[ $gateway_id ] = $gateway_object;
			}
		}

		return $supported_gateways;
	}

	/**
	 * If there is a package set that is recurring, we need the account.
	 *
	 * @param boolean $is_required
	 * @param array   $posted_data
	 */
	public function set_required_account_if_recurring_package( $is_required, $posted_data ) {
		if ( $is_required ) {
			return $is_required;
		}

		$packages = array();
		if ( isset( $posted_data['package'] ) ) {
			if ( ss_multiple_packages_enabled() ) {
				$packages = $posted_data['package'];
			} else {
				$packages = array( $posted_data['package'] => 1 );
			}
		}

		if ( ! $packages ) {
			return $is_required;
		}

		foreach ( $packages as $package_id => $qty ) {
			$package = ss_get_package( $package_id );
			if ( 'recurring' === $package->get_type() ) {
				return true;
			}
		}

		return $is_required;
	}

	/**
	 * Restrict only to one recurring package
	 *
	 * @param $null
	 * @param $packages
	 *
	 * @return \WP_Error
	 */
	public function restrict_only_one_recurring_package( $null, $packages ) {
		if ( null !== $null ) {
			return $null;
		}

		if ( $packages ) {
			$qty_total = array_sum( $packages );
			if ( $qty_total > 1 ) {
				foreach ( $packages as $package_id => $qty ) {
					$package = ss_get_package( $package_id );

					if ( 'recurring' === $package->get_type() ) {
						return new \WP_Error( 'recurring-one', __( 'When selecting recurring packages, only 1 package can be selected.', 'simple-sponsorships-premium' ) );
					}
				}
			}
		}

		return $null;
	}

	/**
	 * Includes
	 */
	public function includes() {
		require_once 'includes/gateways/class-paypal.php';
	}

	/**
	 * @param $gateways
	 */
	public function add_gateways( $gateways ) {
		$index = array_search( '\Simple_Sponsorships\Gateways\PayPal', $gateways );
		if ( $index >= 0 ) {
			$gateways[ $index ] = '\Simple_Sponsorships\Recurring_Payments\Gateways\PayPal';
		} else {
			$gateways[] = '\Simple_Sponsorships\Recurring_Payments\Gateways\PayPal';
		}

		return $gateways;
	}

	/**
	 * Show the formatted price
	 *
	 * @param string  $html Currently formatted HTML.
	 * @param float   $price Price of the package (initial).
	 * @param boolean $exclude_html Should we exclude or not.
	 * @param $package \Simple_Sponsorships\Package Package object.
	 */
	public function ss_package_show_formatted_price( $html, $price, $exclude_html, $package ) {
		if ( 'recurring' !== $package->get_type() ) {
			return $html;
		}

		$duration      = $package->get_data( 'recurring_duration', 1 );
		$duration_unit = $package->get_data( 'recurring_duration_unit', 'day' );
		$signup_fee    = $package->get_data( 'recurring_signup_fee', 0 );
		$units         = self::get_duration_units();


		$price_html    = Formatting::price( $package->get_price( true ), array( 'exclude_html' => $exclude_html ) );
		$duration_html = isset( $units[ $duration_unit ] ) ? $units[ $duration_unit ] : $duration_unit;
		$html          = $price_html . ' ' . __( 'each', 'simple-sponsorships' ) . ' ' . $duration . ' ' . $duration_html;
		$html         .= '<div><small>+' . Formatting::price( $signup_fee,  array( 'exclude_html' => $exclude_html ) )  . ' ' . __( 'Signup Fee', 'simple-sponsorships' ) .  '</small></div>';

		return $html;
	}

	/**
	 * Sponsorship Amount
	 *
	 * @param $formatted
	 * @param $amount
	 * @param $sponsorship
	 */
	public function format_sponsorship_amount( $formatted, $amount, $sponsorship ) {
		if ( ! self::sponsorship_contains_recurring_packages( $sponsorship ) ) {
			return $formatted;
		}

		$packages       = $sponsorship->get_packages();
		$recurring_html = array();
		$units          = self::get_duration_units();

		foreach ( $packages as $package_id => $object ) {
			if ( 'recurring' === $object->get_type() ) {
				$duration      = $object->get_data( 'recurring_duration', 1 );
				$duration_unit = $object->get_data( 'recurring_duration_unit', 'day' );
				$price         = $object->get_price( true );
				$duration_html = isset( $units[ $duration_unit ] ) ? $units[ $duration_unit ] : $duration_unit;
				$recurring_html[]   = Formatting::price( $price ). ' ' . __( 'each', 'simple-sponsorships' ) . ' ' . $duration . ' ' . $duration_html;
			}
		}

		if ( $recurring_html ) {
			return $formatted . ' ' . __( 'then', 'simple-sponsorships' ) . ' ' . implode( ' and ', $recurring_html );
		}

		return $formatted;
	}

	/**
	 * @param $fragments
	 * @param $total
	 * @param $packages
	 * @param $package_objects
	 */
	public function get_total_fragments( $fragments, $total, $packages, $package_objects ) {

		$packages_qty = $packages['package'];
		$formatted    = array();
		$units        = self::get_duration_units();
		foreach ( $package_objects as $package_id => $object ) {
			if ( $packages_qty[ $package_id ] < 1 ) {
				continue;
			}

			if ( 'recurring' === $object->get_type() ) {
				$duration      = $object->get_data( 'recurring_duration', 1 );
				$duration_unit = $object->get_data( 'recurring_duration_unit', 'day' );
				$price         = $object->get_price( true ) * $packages_qty[ $package_id ];
				$duration_html = isset( $units[ $duration_unit ] ) ? $units[ $duration_unit ] : $duration_unit;
				$formatted[]   = Formatting::price( $price ). ' ' . __( 'each', 'simple-sponsorships' ) . ' ' . $duration . ' ' . $duration_html;
			}
		}

		if ( $formatted ) {
			$total_formatted = Formatting::price( $total ) . ' ' . __( 'then', 'simple-sponsorships' ) . ' ' . implode( ' and ', $formatted );
			$fragments = array(
				'total' => $total,
				'total_formatted' => $total_formatted,
			);
		}

		return $fragments;
	}

	/**
	 * Return the duration units
	 *
	 * @return array
	 */
	public static function get_duration_units() {
		return array(
			'day'   => __( 'Day(s)', 'simple-sponsorships' ),
			'month' => __( 'Month(s)', 'simple-sponsorships' ),
			'year'  => __( 'Year(s)', 'simple-sponsorships' ),
		);
	}

	/**
	 * Showing recurring info if available with price
	 *
	 * @param string $price
	 * @param array $item
	 *
	 * @return string
	 */
	public function show_recurring_on_price_column( $price, $item ) {

		if ( ! isset( $item['type'] ) || ! $item['type'] ) {
			return $price;
		}

		if ( 'recurring' !== $item['type'] ) {
			return $price;
		}

		$db            = new DB_Packages();
		$duration      = $db->get_meta( $item['ID'], 'recurring_duration', true );
		$duration_unit = $db->get_meta( $item['ID'], 'recurring_duration_unit', true );
		$signup_fee    = $db->get_meta( $item['ID'], 'recurring_signup_fee', true );

		if ( ! $duration ) {
			$duration = 1;
		}

		if ( ! $duration_unit ) {
			$duration_unit = 'day';
		}

		$duration_unit_name = __( 'Day(s)', 'simple-sponsorships' );
		switch ( $duration_unit ) {
			case 'month':
				$duration_unit_name = __( 'Month(s)', 'simple-sponsorships' );
				break;
			case 'year':
				$duration_unit_name = __( 'Year(s)', 'simple-sponsorships' );
				break;
		}

		$price .= ' / ' . $duration . ' ' . $duration_unit_name;

		if ( $signup_fee ) {
			$price .= '<br/><small> + ' . Formatting::price( $signup_fee ) . ' ' . __( 'Signup Fee', 'simple-sponsorships' ) . '</small>';
		}

		return $price;
	}

	/**
	 * Add Recurring Fields
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function add_recurring_fields( $fields ) {
		$new_fields = array();

		$recurring_fields = array(
			'recurring_duration' => array(
				'id' => 'recurring_duration',
				'type' => 'number',
				'title' => __( 'Recurring Duration', 'simple-sponsorships' ),
				'row_class'  => array( 'ss-hidden',  'show_if_type_recurring' ),
				'step' => '1',
			),
			'recurring_duration_unit' => array(
				'id' => 'recurring_duration_unit',
				'type' => 'select',
				'title' => __( 'Recurring Duration Unit', 'simple-sponsorships' ),
				'row_class'  => array( 'ss-hidden',  'show_if_type_recurring' ),
				'options' => array(
					'day'   => __( 'Day(s)', 'simple-sponsorships' ),
					'month' => __( 'Month(s)', 'simple-sponsorships' ),
					'year'  => __( 'Year', 'simple-sponsorships' ),
				),
				'default' => 'day'
			),
			'recurring_signup_fee' => array(
				'id' => 'recurring_signup_fee',
				'type' => 'number',
				'title' => __( 'Recurring Signup Fee', 'simple-sponsorships' ),
				'row_class'  => array( 'ss-hidden',  'show_if_type_recurring' ),
				'step' => '0.01',
				'default' => 0
			),
		);

		foreach ( $fields as $field_key => $field_config ) {
			$new_fields[ $field_key ] = $field_config;

			if ( 'price' === $field_key ) {
				$new_fields = array_merge( $new_fields, $recurring_fields );
			}
		}
		return $new_fields;
	}

	/**
	 * @param $id
	 * @param $data
	 */
	public function save_package_recurring( $id, $data ) {
		$db = new DB_Packages();
		if ( isset( $data['recurring_duration'] ) ) {
			$db->update_meta( $id, 'recurring_duration', absint( $data['recurring_duration'] ) );
		}

		if ( isset( $data['recurring_duration_unit'] ) ) {
			$db->update_meta( $id, 'recurring_duration_unit', sanitize_text_field( $data['recurring_duration_unit'] ) );
		}

		if ( isset( $data['recurring_signup_fee'] ) ) {
			$db->update_meta( $id, 'recurring_signup_fee', floatval( $data['recurring_signup_fee'] ) );
		}
	}

	/**
	 * Add the recurring type to package
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	public function add_recurring_type( $types ) {
		$types['recurring'] = __( 'Recurring', 'simple-sponsorships' );

		return $types;
	}
}