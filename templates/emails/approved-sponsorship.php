<?php
/**
 * Content for the New Sponsorship Email
 */

use \Simple_Sponsorships\Sponsorship;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

do_action( 'ss_email_header', $args['email_heading'] );

if ( $args['sponsorship'] ) {
	$sponsorship_object = new Sponsorship( $args['sponsorship'] );

	$view_link = $sponsorship_object->get_view_link();

	if ( $view_link ) {
		echo '<p>' . sprintf( __( 'Your Sponsorship was approved. You can view the Sponsorship details <a href="%s">here</a>.', 'simple-sponsorships' ), $view_link ) . '</p>';
	}

	do_action( 'ss_sponsorship_details', $sponsorship_object, 'email' );
}

do_action( 'ss_email_footer' );