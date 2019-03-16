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
 * Return content Types.
 *
 * @return mixed|string
 */
function ss_get_content_types() {
	$content_types = ss_get_option( 'content_types', array( 'post' => 'Posts', 'page' => 'Page' ) );

	if ( ! $content_types ) {
		$content_types = array();
	}

	return apply_filters( 'ss_content_types', $content_types );
}