<?php
/**
 * A class to handle the Submissions for Sponsors.
 */

namespace Simple_Sponsorships;

/**
 * Class Form_Sponsors
 *
 * @package Simple_Sponsorships
 */
class Form_Sponsors {

	/**
	 * Return the fields for Form Sponsors.
	 */
	public static function get_fields() {
		$packages = ss_get_packages();
		$package_options = array();
		if ( $packages ) {
			$package_options[0] = __( 'Select a Package', 'simple-sponsorships' );
			foreach( $packages as $package ) {
				$package_options[ $package['ID'] ] = $package['title'];
			}
		}
		$fields = array(
			'name' => array(
				'title'    => __( 'Your Name', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => true,
			),
			'website' => array(
				'title'    => __( 'Website', 'simple-sponsorships' ),
				'type'     => 'url',
				'required' => true,
			),
			'company' => array(
				'title'    => __( 'Company Name', 'simple-sponsorships' ),
				'type'     => 'text',
				'required' => false,
			),
			'packages' => array(
				'title'    => __( 'Sponsorship', 'simple-sponsorships' ),
				'required' => true,
				'type'     => 'select',
				'options'  => $package_options
			),
		);

		return apply_filters( 'ss_form_sponsors_fields', $fields );
	}
}