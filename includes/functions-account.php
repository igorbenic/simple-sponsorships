<?php
/**
 * Functions related to Account
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! function_exists( 'ss_account_content' ) ) {

	/**
	 * My Account content output.
	 */
	function ss_account_content() {
		global $wp;

		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key ) {
					continue;
				}

				if ( has_action( 'ss_account_' . $key . '_endpoint' ) ) {
					do_action( 'ss_account_' . $key . '_endpoint', $value );
					return;
				}
			}
		}

		\Simple_Sponsorships\Templates::get_template_part( 'account/dashboard', null, array(
			'current_user' => get_user_by( 'id', get_current_user_id() ),
		) );
		return;
	}
}


if ( ! function_exists( 'ss_account_sponsorships_content' ) ) {

	/**
	 * My Account Sponsorships content output.
	 */
	function ss_account_sponsorships_content() {
		$db              = new \Simple_Sponsorships\DB\DB_Sponsorships();
		$db_sponsorships = $db->get_by_meta( '_user_id', get_current_user_id() );
		$sponsorships    = array();

		if ( $db_sponsorships ) {
			foreach ( $db_sponsorships as $single_sponsorship ) {
				$sponsorship = new \Simple_Sponsorships\Sponsorship( absint( $single_sponsorship['ID'] ), false );
				$sponsorship->populate_from_data( $single_sponsorship );
				$sponsorships[] = $sponsorship;
			}
		}
		\Simple_Sponsorships\Templates::get_template_part( 'account/sponsorships', null, array(
			'current_user' => get_user_by( 'id', get_current_user_id() ),
			'sponsorships' => $sponsorships,
		) );
		return;
	}
}

if ( ! function_exists( 'ss_account_view_sponsorship_content' ) ) {

	/**
	 * My Account Sponsorships content output.
	 */
	function ss_account_view_sponsorship_content( $sponsorship_id ) {

		\Simple_Sponsorships\Templates::get_template_part( 'account/view-sponsorship', null, array(
			'current_user' => get_user_by( 'id', get_current_user_id() ),
			'sponsorship' => ss_get_sponsorship( $sponsorship_id ),
		) );
		return;
	}
}