<?php
/**
 * Content for the New Sponsorship Email
 */

use \Simple_Sponsorships\Sponsorship;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( $args['sponsorship'] ) {
	$sponsorship_object = new Sponsorship( $args['sponsorship'] );
	do_action( 'ss_sponsorship_details', $args['sponsorship'] );
}