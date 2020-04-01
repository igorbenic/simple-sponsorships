<?php
/**
 * Content for the New Sponsorship Email
 */

use \Simple_Sponsorships\Sponsorship;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

do_action( 'ss_email_header', __( 'New Sponsorship', 'simple-sponsorships' ) );

if ( $args['sponsorship'] ) {
	$sponsorship_object = new Sponsorship( $args['sponsorship'] );
	do_action( 'ss_sponsorship_details', $sponsorship_object, 'email');

	do_action( 'ss_sponsorship_sponsor', $sponsorship_object );
}

do_action( 'ss_email_footer' );