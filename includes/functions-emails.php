<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Send an email on new Sponsorship.
 *
 * @param integer $sponsorship_id
 */
function ss_email_on_new_sponsorship( $sponsorship_id ) {
	$email = new \Simple_Sponsorships\Emails\Email_New_Sponsorship();
	$email->trigger( $sponsorship_id );
}

/**
 * Send an email when the Sponsorship is approved.
 *
 * @param $status
 * @param $old_status
 * @param $sponsorship_id
 */
function ss_email_on_approved_sponsorship( $status, $old_status, $sponsorship_id ) {
	if ( 'approved' === $status && 'request' === $old_status ) {
		$email = new \Simple_Sponsorships\Emails\Email_Pending_Sponsorship();
		$email->trigger( $sponsorship_id );
	}
}

/**
 * Send an email when the Sponsorship is paid.
 *
 * @param $status
 * @param $old_status
 * @param $sponsorship_id
 */
function ss_email_on_activated_sponsorship( $sponsorship ) {
	$email = new \Simple_Sponsorships\Emails\Email_Activated_Sponsorship();
	$email->trigger( $sponsorship );

}

/**
 * Send an email when the Sponsorship is paid.
 *
 * @param $status
 * @param $old_status
 * @param $sponsorship_id
 */
function ss_email_invoice_on_activated_sponsorship( $sponsorship ) {

	$email = new \Simple_Sponsorships\Emails\Email_Customer_Invoice();
	$email->trigger( $sponsorship );

}

/**
 * @param \Simple_Sponsorships\Sponsorship $sponsorship
 */
function ss_email_on_rejected_sponsorship( $sponsorship ){
	$email = new \Simple_Sponsorships\Emails\Email_Rejected_Sponsorship();
	$email->trigger( $sponsorship );
}
