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
		$data = $this->process_data();

		if ( $data ) {
			die( 'Do Something' );
			//$this->process_payment( $data );
		}
	}

	/**
	 * Return the fields for Form Sponsors.
	 */
	public function get_fields() {
		$packages = ss_get_packages();
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
				'type'     => 'text',
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