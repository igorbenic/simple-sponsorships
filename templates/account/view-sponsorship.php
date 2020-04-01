<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 22/01/2020
 * Time: 01:45
 */


if ( ! isset( $args['sponsorship'] ) ) {
	return;
}

if ( ! $args['sponsorship'] ) {
	return;
}

$sponsorship_object = $args['sponsorship'];

ss_print_notices();

do_action( 'ss_sponsorship_details', $sponsorship_object, 'account' );

do_action( 'ss_sponsorship_sponsor', $sponsorship_object );

do_action( 'ss_sponsorship_form', $sponsorship_object );