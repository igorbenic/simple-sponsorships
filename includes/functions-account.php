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

if ( ! function_exists( 'ss_account_navigation' ) ) {

	/**
	 * My Account navigation template.
	 */
	function ss_account_navigation() {
		\Simple_Sponsorships\Templates::get_template_part( 'account/navigation', null );
	}
}

/**
 * Get My Account menu items.
 *
 * Copied from WooCommerce
 *
 * @since 1.5.0
 * @return array
 */
function ss_get_account_menu_items() {
	$endpoints = array(
		'sponsorships'    => get_option( 'ss_myaccount_orders_endpoint', 'sponsorships' ),
		//'downloads'       => get_option( 'ss_myaccount_downloads_endpoint', 'downloads' ),
		'edit-sponsor'    => get_option( 'ss_myaccount_edit_address_endpoint', 'edit-sponsor' ),
		//'payment-methods' => get_option( 'ss_myaccount_payment_methods_endpoint', 'payment-methods' ),
		//'edit-account'    => get_option( 'ss_myaccount_edit_account_endpoint', 'edit-account' ),
		//'customer-logout' => get_option( 'ss_logout_endpoint', 'customer-logout' ),
	);

	$items = array(
		'dashboard'       => __( 'Dashboard', 'simple-sponsorships' ),
		'sponsorships'    => __( 'Sponsorships', 'simple-sponsorships' ),
		//'downloads'       => __( 'Downloads', 'woocommerce' ),
		'edit-sponsor'    => __( 'Sponsor', 'woocommerce' ),
		//'payment-methods' => __( 'Payment methods', 'woocommerce' ),
		//'edit-account'    => __( 'Account details', 'woocommerce' ),
		//'customer-logout' => __( 'Logout', 'woocommerce' ),
	);

	// Remove missing endpoints.
	foreach ( $endpoints as $endpoint_id => $endpoint ) {
		if ( empty( $endpoint ) ) {
			unset( $items[ $endpoint_id ] );
		}
	}

	// Check if payment gateways support add new payment methods.
	if ( isset( $items['payment-methods'] ) ) {
		$support_payment_methods = false;
		foreach ( SS()->payment_gateways()->get_available_payment_gateways() as $gateway ) {
			if ( $gateway->supports( 'add_payment_method' ) || $gateway->supports( 'tokenization' ) ) {
				$support_payment_methods = true;
				break;
			}
		}

		if ( ! $support_payment_methods ) {
			unset( $items['payment-methods'] );
		}
	}

	return apply_filters( 'ss_account_menu_items', $items, $endpoints );
}

/**
 * Get account menu item classes.
 *
 * Copied from WooCommerce
 *
 * @since 1.5.0
 * @param string $endpoint Endpoint.
 * @return string
 */
function ss_get_account_menu_item_classes( $endpoint ) {
	global $wp;

	$classes = array(
		'ss-account-navigation-link',
		'ss-account-navigation-link--' . $endpoint,
	);

	// Set current item class.
	$current = isset( $wp->query_vars[ $endpoint ] );
	if ( 'dashboard' === $endpoint && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) ) {
		$current = true; // Dashboard is not an endpoint, so needs a custom check.
	} elseif ( 'sponsorships' === $endpoint && isset( $wp->query_vars['view-sponsorship'] ) ) {
		$current = true; // When looking at individual sponsorship, highlight Orders list item (to signify where in the menu the user currently is).
	}

	if ( $current ) {
		$classes[] = 'is-active';
	}

	$classes = apply_filters( 'ss_account_menu_item_classes', $classes, $endpoint );

	return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
}

/**
 * Get account endpoint URL.
 *
 * Copied from WooCommerce
 *
 * @since 1.5.0
 * @param string $endpoint Endpoint.
 * @return string
 */
function ss_get_account_endpoint_url( $endpoint ) {
	if ( 'dashboard' === $endpoint ) {
		return get_permalink( ss_get_option( 'account_page', 0 ) );
	}

	if ( 'customer-logout' === $endpoint ) {
		return wp_logout_url( home_url() );
	}

	return ss_get_endpoint_url( $endpoint, '', get_permalink( ss_get_option( 'account_page', 0 ) ) );
}