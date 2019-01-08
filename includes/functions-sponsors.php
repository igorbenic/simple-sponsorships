<?php
/**
 * Globally available functions related to Sponsors
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Return sponsors.
 *
 * @param array $args
 *
 * @return array
 */
function ss_get_sponsors( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'post_type'      => 'sponsors',
		'posts_per_page' => '-1'
	));

	return get_posts( apply_filters( 'ss_get_sponsors_args', $args ) );
}

/**
 * Return Active Sponsors
 *
 * @return array
 */
function ss_get_active_sponsors() {
	return ss_get_sponsors( array( 'post_status' => 'ss-active' ) );
}