<?php
/**
 * Class for handling emails when new sponsorships are requested.
 *
 * @package Simple_Sponsorships\Emails
 */

namespace Simple_Sponsorships\Emails;

use Simple_Sponsorships\Sponsorship;
use Simple_Sponsorships\Templates;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class Email
 *
 * @package Simple_Sponsorships\Emails
 */
class Email_Pending_Sponsorship extends Email {

	/**
	 * Email constructor.
	 */
	public function __construct() {
		$this->id = 'ss_email_pending_sponsorship';
	}

	/**
	 * Send the email.
	 *
	 * @param integer $sponsorship
	 */
	public function trigger( $sponsorship_id ) {
		$this->data['email_heading'] = __( 'Sponsorship Approved', 'simple-sponsorships' );
		$sponsorship = new Sponsorship( $sponsorship_id );
		$this->data['sponsorship']   = $sponsorship_id;

		$sponsor = $sponsorship->get_sponsor_data();
		$to      = $sponsor->get_data( '_email' );
		if ( ! $to ) {
			$to = $sponsorship->get_data( '_email' );
		}
		$headers = $this->get_headers();
		$subject = __( 'Your Sponsorship has been approved', 'simple-sponsorships' );
		$this->send( $to, $subject, $this->get_content(), $headers );
	}

	/**
	 * Get the email content in HTML format.
	 *
	 * @return string
	 */
	public function get_content_html() {
		Templates::get_template_part(
			'emails/approved-sponsorship',
			'',
			$this->data
		);
	}
}