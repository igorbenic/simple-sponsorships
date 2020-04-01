<?php
/**
 * Content for the Rejected Sponsorship Email
 */


if ( ! defined( 'ABSPATH' ) ) {
	return;
}

do_action( 'ss_email_header', $args['email_heading'] );

if ( $args['sponsorship'] ) {
	$sponsorship_object = $args['sponsorship'];

	$reason = $sponsorship_object->get_data( 'reject_reason', ''  );

	echo '<p>' . __( 'We\'re sorry, but your Sponsorship request was rejected.', 'simple-sponsorships' ) . '</p>';

	if ( $reason ) {
		echo '<p><strong>' . __( 'Reject Reason', 'simple-sponsorships' ) . '</strong></p>';
		echo $reason;
	}

	do_action( 'ss_sponsorship_details', $sponsorship_object, 'email' );
}

do_action( 'ss_email_footer' );