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

/**
 * Get all image sizes.
 *
 * @since 1.3.0
 *
 * @return array Array where key is the image size.
 */
function ss_get_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
			$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
			$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
			$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}

	return $sizes;
}