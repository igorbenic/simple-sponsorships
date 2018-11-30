<?php
/**
 * Globally available functions for handling sessions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Get the session
 */
function ss_get_session( $key ) {
	$ss = \Simple_Sponsorships\get_main();
	return $ss->session->get( $key );
}

/**
 * @param $key
 * @param $value
 *
 * @return mixed
 */
function ss_set_session( $key, $value ) {
	$ss = \Simple_Sponsorships\get_main();
	return $ss->session->set( $key, $value );
}