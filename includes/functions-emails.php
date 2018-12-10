<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 06/12/18
 * Time: 15:55
 */

/**
 * Send an email on new Sponsorship.
 *
 * @param integer $sponsorship_id
 */
function ss_email_on_new_sponsorhip( $sponsorship_id ) {
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
function ss_email_on_pending_sponsorship( $status, $old_status, $sponsorship_id ) {
	if ( 'pending' === $status && 'request' === $old_status ) {
		$email = new \Simple_Sponsorships\Emails\Email_Pending_Sponsorship();
		$email->trigger( $sponsorship_id );
	}
}