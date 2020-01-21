<?php
/**
 * Globally available functions for handling sessions.
 * Most code copied from WooCommerce.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Get the session
 */
function ss_get_session( $key, $default = false ) {
	$ss = \Simple_Sponsorships\get_main();
	return $ss->session->get( $key, $default );
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

/**
 * Check if a notice has already been added.
 *
 * @since  1.5.0
 * @param  string $message The text to display in the notice.
 * @param  string $notice_type Optional. The name of the notice type - either error, success or notice.
 * @return bool
 */
function ss_has_notice( $message, $notice_type = 'success' ) {

	$notices = ss_get_session( 'ss_notices', array() );
	$notices = isset( $notices[ $notice_type ] ) ? $notices[ $notice_type ] : array();
	return array_search( $message, $notices, true ) !== false;
}


/**
 * Get Notices.
 *
 * @param string $type
 *
 * @return array
 */
function ss_get_notices( $type = '' ) {
	$notices = ss_get_session( 'ss_notices', array() );
	if ( ! $type ) {
		return $notices;
	} elseif ( isset( $notices[ $type ] ) ) {
		return $notices[ $type ];
	}
	return array();
}

/**
 * Add a single Notice the session
 *
 * @param        $notice
 * @param string $type
 */
function ss_add_notice( $notice, $type = 'success' ) {
	$notices = ss_get_session( 'ss_notices', array() );
	if ( ! isset( $notices[ $type ] ) ) {
		$notices[ $type ] = array();
	}

	$notices[ $type ][] = $notice;
	ss_set_session( 'ss_notices', $notices );
}

/**
 * Add a Notice the session
 *
 * @param array  $notices_array
 * @param string $type
 */
function ss_add_notices( $notices_array, $type = 'success' ) {
	$notices = ss_get_session( 'ss_notices', array() );

	if ( ! is_array( $notices_array ) ) {
		$notices_array = array( $notices_array );
	}

	if ( ! isset( $notices[ $type ] ) ) {
		$notices[ $type ] = array();
	}

	$notices[ $type ] = array_merge( $notices[ $type ], $notices_array );

	ss_set_session( 'ss_notices', $notices );
}

/**
 * Clearing all notices from the session.
 */
function ss_clear_notices( $type = '' ) {
	if ( ! $type ) {
		ss_set_session( 'ss_notices', array() );
	} else {
		$notices = ss_get_notices();
		$notices[ $type ] = array();
		ss_set_session( 'ss_notices', $notices );
	}
}

/**
 * We will print notices and also reset them.
 *
 * @param string $type
 */
function ss_print_notices( $type = '' ) {
	$notices  = ss_get_notices( $type );

	if ( ! $notices ) {
		return;
	}

	$_notices = array();

	if ( ! $type ) {
		foreach( $notices as $notice_type => $notices_of_type ) {
			foreach ( $notices_of_type as $notice ) {
				$_notices[] = array( 'notice' => $notice, 'type' => $notice_type );
			}
		}
	} else {
		foreach ( $notices as $notice ) {
			$_notices[] = array( 'notice' => $notice, 'type' => $type );
		}
	}

	if ( ! $_notices ) {
		return;
	}

	ss_clear_notices( $type );
	\Simple_Sponsorships\Templates::get_template_part( 'notices', null, array( 'notices' => $_notices ) );
}