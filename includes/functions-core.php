<?php
/**
 * Globally Available Core Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * @return \Simple_Sponsorships\Plugin
 */
function SS() {
	return \Simple_Sponsorships\get_main();
}

/**
 * Return the settings.
 *
 * @return array
 */
function ss_get_settings() {
	$plugin = SS();
	return $plugin->get_settings();
}

/**
 * Get the Option
 *
 * @param string $key     The option key (name).
 * @param mixed  $default The value that will be returned if this option does not exist.
 */
function ss_get_option( $key, $default = '' ) {
	$settings = ss_get_settings();
	return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
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