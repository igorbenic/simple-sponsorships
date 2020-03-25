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

		add_action( 'ss_package_updated', array( $this, 'save_package_recurring' ), 20, 2 );
		add_action( 'ss_package_added', array( $this, 'save_package_recurring' ), 20, 2 );

		$this->includes();
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


		$html = '';

		if ( $signup_fee ) {
			$html = Formatting::price( $price + $signup_fee, array( 'exclude_html' => $exclude_html ) ) . ' ' . __( 'then ', 'simple-sponsorships' ) . ' ';
		}

		$price_html    = Formatting::price( $price, array( 'exclude_html' => $exclude_html ) );
		$duration_html = isset( $units[ $duration_unit ] ) ? $units[ $duration_unit ] : $duration_unit;
		$html .= $price_html . ' each ' . $duration . ' ' . $duration_html;


		return $html;
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