<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! $args['current_user'] ) {
    return;
}
?>

	<p><?php
		printf(
		/* translators: 1: user display name 2: logout url */
			__( 'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'woocommerce' ),
			'<strong>' . esc_html( $args['current_user']->display_name ) . '</strong>',
			esc_url( wp_logout_url( home_url() ) )
		);
		?></p>

	<p><?php
		printf(
			__( 'From your account dashboard you can view your <a href="%1$s">recent sponsorships</a>, and <a href="%2$s">edit your sponsor details</a>.', 'simple-sponsorships' ),
			esc_url( ss_get_endpoint_url( 'sponsorships' ) ),
			esc_url( ss_get_endpoint_url( 'sponsor-info' ) )
		);
		?></p>

<?php
/**
 * Account dashboard.
 *
 * @since 1.5.0
 */
do_action( 'ss_account_dashboard' );

