<?php
/**
 * Content for the showing a Sponsorship
 */

use \Simple_Sponsorships\DB\DB_Sponsorships;
use \Simple_Sponsorships\Sponsorship;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! isset( $_GET['sponsorship-key'] ) || '' == $_GET['sponsorship-key'] ) {
	return;
}

$db           = new DB_Sponsorships();
$sponsorships = $db->get_by_column( 'ss_key', sanitize_text_field( $_GET['sponsorship-key'] ) );

if ( ! $sponsorships ) {
	return;
}

$sponsorship = $sponsorships[0];
if ( ! isset( $sponsorship['ID'] ) ) {
	return;
}

$sponsorship_object = new Sponsorship( $sponsorship['ID'] );

ss_print_notices();

do_action( 'ss_sponsorship_details', $sponsorship_object, 'sponsorship-view');

do_action( 'ss_sponsorship_sponsor', $sponsorship_object );

do_action( 'ss_sponsorship_form', $sponsorship_object );