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
 * Return the package types.
 *
 * @since 1.6.0
 *
 * @return array
 */
function ss_get_package_types() {
	return apply_filters( 'ss_package_types', array(
		'onetime' => __( 'One-time', 'simple-sponsorships' ),
	));
}


/**
 * Get the package.
 *
 * @param integer $id Package ID.
 * @param boolean $load Should load initial data.
 *
 * @return \Simple_Sponsorships\Package
 */
function ss_get_package( $id, $load = true ) {
	return new \Simple_Sponsorships\Package( $id, $load );
}

/**
 * Get all Packages
 *
 * @param string $type If we want the raw array from DB, leave it as it is.
 *                     If we want an array of objects, pass 'object'.
 *
 * @return array
 */
function ss_get_packages( $type = 'raw' ) {
	$db       = new \Simple_Sponsorships\DB\DB_Packages();
	$packages = $db->get_all();

	if ( 'object' === $type && $packages ) {
		$_packages = $packages;
		$packages = array();
		foreach ( $_packages as $index => $package_array ) {
			$package = new \Simple_Sponsorships\Package( 0 );
			$package->populate_from_package( $package_array );
			$packages[] = $package;
		}
	}
	return $packages;
}

/**
 * Get all available packages.
 *
 * @since 0.6.0
 *
 * @return array Array of Package objects.
 */
function ss_get_available_packages() {
	$db       = new \Simple_Sponsorships\DB\DB_Packages();
	$results  = $db->get_available();
	$packages = array();
	if ( $results ) {
		foreach ( $results as $index => $package_array ) {
			$package = new \Simple_Sponsorships\Package( 0 );
			$package->populate_from_package( $package_array );
			if ( $package->is_available() ) {
				$packages[] = $package;
			}
		}
	}
	return $packages;
}

/**
 * Return true if we allow multiple packages.
 *
 * @since 1.3.0
 *
 * @return bool
 */
function ss_multiple_packages_enabled() {
	return 1 === absint( ss_get_option( 'allow_multiple_packages', '0' ) );
}