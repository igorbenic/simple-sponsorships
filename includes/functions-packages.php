<?php

/**
 * Globally available Package functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Return the package statuses.
 *
 * @return array
 */
function ss_get_package_statuses() {
	return apply_filters( 'ss_package_statuses', array(
		'available' => __( 'Available', 'simple-sponsorships' ),
		'unavailable' => __( 'Unavailable', 'simple-sponsorships' ),
	));
}


/**
 * Get the package.
 *
 * @param integer $id
 */
function ss_get_package( $id ) {
	return new \Simple_Sponsorships\Package( $id, true );
}

/**
 * Get all Packages
 *
 * @return array
 */
function ss_get_packages() {
	$db = new \Simple_Sponsorships\DB\DB_Packages();
	return $db->get_all();
}

/**
 * Get all active packages.
 *
 * @return array|null|object
 */
function ss_get_available_packages() {
	$db       = new \Simple_Sponsorships\DB\DB_Packages();
	$packages = $db->get_available();
	if ( $packages ) {
		foreach ( $packages as $index => $package_array ) {
			$package = new \Simple_Sponsorships\Package( 0 );
			$package->populate_from_package( $package_array );
			if ( ! $package->is_available() ) {
				unset( $packages[ $index ] );
			}
		}
	}
	return $packages;
}