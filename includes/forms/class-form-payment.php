<?php
/**
 * A class to handle the Payments for Sponsors.
 */

namespace Simple_Sponsorships;
use Simple_Sponsorships\DB\DB_Sponsorships;

/**
 * Class Form_Sponsors
 *
 * @package Simple_Sponsorships
 */
class Form_Payment extends Form {

	/**
	 * Slug used for filtering and such.
	 *
	 * @var string
	 */
	protected $slug = 'form_payment';

	/**
	 * Process the Form.
	 */
	public function process() {
		// Validate Form Field Data.
		$data = $this->process_data();

		$sponsorship_id = isset( $_POST['ss_sponsorship_id'] ) ? absint( $_POST['ss_sponsorship_id'] ) : 0;

		if ( ! $sponsorship_id ) {
			ss_add_notice( __( 'No Sponsorship', 'simple-sponsorships' ), 'error' );
		}

		$gateway = $this->validate_gateway();

		if ( $data && empty( ss_get_notices( 'error' ) ) ) {
			$sponsorship = ss_get_sponsorship( $sponsorship_id );
			foreach ( $data as $meta_key => $meta_value ) {
				$sponsorship->update_data( $meta_key, $meta_value );
			}

			$response = $gateway->process_payment( $sponsorship );

			if ( is_wp_error( $response ) ) {
				ss_add_notice( $response->get_error_message(), 'error' );
				return;
			}

			if ( false === $response ) {
				return;
			}

			if ( 'success' === $response['result'] ) {
				if ( $response['redirect'] ) {
					wp_redirect( $response['redirect'] );
					exit;
				}
			} elseif ( isset( $response['message'] ) ) {
				ss_add_notice( $response['message'], 'error' );
			}
		}
	}

	/**
	 * We will validate the Gateway.
	 *
	 * @return \Simple_Sponsorships\Gateways\Payment_Gateway|false Boolean if no gateway selected. Otherwise the Gateway object.
	 */
	public function validate_gateway() {
		$gateways  = SS()->payment_gateways();
		$available = $gateways->get_available_payment_gateways();
		$method    = isset( $_POST['payment_method'] ) ? $_POST['payment_method'] : false;
		if ( ! $method || ! isset( $available[ $method ] ) ) {
			ss_add_notice(  __( 'Select an available Payment Method', 'simple-sponsorships' ), 'error' );
			return false;
		}

		return $available[ $method ];
	}

	/**
	 * Return the fields for Form Sponsors.
	 */
	public function get_fields() {
		$countries = new Countries();
		$packages  = ss_get_packages();
		$package_options = array();
		if ( $packages ) {
			$package_options[0] = __( 'Select a Package', 'simple-sponsorships' );
			foreach( $packages as $package ) {
				$package_options[ $package['ID'] ] = $package['title'];
			}
		}
		$fields = array(
			'billing_first_name' => array(
				'title'    => __( 'First Name', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => true,
			),
			'billing_last_name' => array(
				'title'    => __( 'Last Name', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => true,
			),
			'billing_email' => array(
				'title'    => __( 'Email', 'simple-sponsorships' ),
				'type'     => 'email',
				'required' => true,
			),
			'billing_address' => array(
				'title'    => __( 'Address', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => true,
			),
			'billing_address2' => array(
				'title'    => __( 'Address Line 2', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => false,
			),
			'billing_city' => array(
				'title'    => __( 'City', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => true,
			),
			'billing_postalcode' => array(
				'title'    => __( 'ZIP / Postal Code', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => true,
			),
			'billing_country' => array(
				'title'    => __( 'Country', 'simple-sponsorships' ),
				'type'     => 'select',
				'options'  => $countries->get_countries(),
				'required' => true,
			),
			'billing_state' => array(
				'title'    => __( 'State / Province', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => false,
			),
		);

		return apply_filters( 'ss_form_sponsors_fields', $fields );
	}


}