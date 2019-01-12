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
	return ss_get_sponsors( array( 'post_status' => 'publish' ) );
}

/**
 * Get Sponsors for a Post Type or different content.
 *
 * @param int $post_id Post ID.
 */
function ss_get_sponsors_for_content( $post_id ) {
	$ret = get_post_meta( $post_id, '_ss_sponsor', false );
	if ( ! $ret ) {
		$ret = array();
	}

	return $ret;
}

/**
 * Get Sponsors for a Post Type or different content.
 *
 * @param int $post_id Post ID.
 * @param array|int $sponsor_ids Array of Ids or a single Id.
 */
function ss_update_sponsors_for_content( $post_id, $sponsor_ids ) {
	if ( ! is_array( $sponsor_ids ) ) {
		$sponsor_ids = array( $sponsor_ids );
	}

	// First, let's delete all previous.
	ss_delete_sponsors_for_content( $post_id );

	foreach ( $sponsor_ids as $sponsor_id ) {
		ss_add_sponsor_for_content( $post_id, $sponsor_id );
	}
}

/**
 * Adding a sponsor of a content.
 *
 * @param int $post_id Content ID.
 * @param int $sponsor_id Sponsor ID.
 * @return mixed
 */
function ss_add_sponsor_for_content( $post_id, $sponsor_id ) {
	return add_post_meta( $post_id, '_ss_sponsor', absint( $sponsor_id ) );
}

/**
 * Deleting Sponsors for a content.
 *
 * @param int  $post_id Post ID.
 * @param bool|int|array $sponsors_ids If false, it will delete all. If array or integer, it will delete only specific sponsors.
 */
function ss_delete_sponsors_for_content( $post_id, $sponsors_ids = false ) {
	if ( ! $sponsors_ids ) {
		delete_post_meta( $post_id, '_ss_sponsor' );
	} else {
		if ( ! is_array( $sponsors_ids ) ) {
			$sponsor_ids = array( $sponsor_ids );
		}
		foreach ( $sponsor_ids as $sponsor_id ) {
			delete_post_meta( $post_id, '_ss_sponsor', $sponsor_id );
		}
	}
}