<?php
/**
 * Content for the Customer Invoice email.
 */


if ( ! defined( 'ABSPATH' ) ) {
	return;
}

do_action( 'ss_email_header', $args['email_heading'] );

if ( $args['sponsorship'] ) {
	$sponsorship_object = $args['sponsorship'];
	do_action( 'ss_sponsorship_details', $sponsorship_object, 'invoice' );

	do_action( 'ss_sponsorship_customer_details', $sponsorship_object );
}

do_action( 'ss_email_footer' );