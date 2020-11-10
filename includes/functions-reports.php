<?php

/**
 * Globally available functions for reports
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Get the report link
 * @param        $redirect_to
 * @param        $sponsor_id
 * @param        $content_id
 * @param        $content_url
 * @param string $content_type
 *
 * @return string
 */
function ss_get_report_link( $redirect_to, $sponsor_id, $content_id, $content_url, $content_type = 'post' ) {
	return add_query_arg([
		'redirect_to'  => $redirect_to,
		'sponsor'      => $sponsor_id,
		'ss-action'    => 'insert_report',
		'content_id'   => $content_id,
		'content_url'  => $content_url,
		'content_type' => $content_type,
		'_wpnonce'     => wp_create_nonce( 'report-' . $sponsor_id . $content_id ),
	]);
}

/**
 * Enter the report
 * @param        $content_id
 * @param        $sponsor_id
 * @param        $content_url
 * @param string $content_type
 * @param null   $user_id
 *
 * @return bool|int
 */
function ss_insert_report( $content_id, $sponsor_id, $content_url, $content_type = 'post', $user_id = null ) {
	$db = new \Simple_Sponsorships\DB\DB_Reports();

	return $db->insert([
		'sponsor'      => $sponsor_id,
		'user'         => $user_id ? $user_id : get_current_user_id(),
		'content_id'   => $content_id,
		'content_type' => $content_type,
		'content_url'  => $content_url,
		'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
	]);
}

/**
 * Get reports for a sponsor
 *
 * @param $sponsor_id
 *
 * @return array|object|null
 */
function ss_get_reports_for_sponsor( $sponsor_id ) {
	$db = new \Simple_Sponsorships\DB\DB_Reports();

	return $db->get_by_column( 'sponsor', $sponsor_id );
}

/**
 * Inserting the report from request and redirecting the user
 */
function ss_insert_report_from_request() {
	if ( ! isset( $_GET['_wpnonce'] ) ) {
		return;
	}

	$sponsor_id = isset( $_GET['sponsor'] ) ? $_GET['sponsor'] : 0;
	$content_id = isset( $_GET['content_id'] ) ? $_GET['content_id'] : 0;

	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'report-' . $sponsor_id . $content_id ) ) {
		wp_die( 'No hacking please' );
	}

	$content_url  = isset( $_GET['content_url'] ) ? $_GET['content_url'] : get_permalink( $content_id );
	$content_type = isset( $_GET['content_type'] ) ? $_GET['content_type'] : get_post_type( $content_id );
	ss_insert_report( $content_id, $sponsor_id, $content_url, $content_type, get_current_user_id() );

	$redirect_to = isset( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : '';
	wp_redirect( $redirect_to );
	exit();
}
