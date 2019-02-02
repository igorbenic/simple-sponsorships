<?php
/**
 * Class for handling emails when a sponsorship is rejected.
 * This email is sent to the Customer.
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
class Email_Rejected_Sponsorship extends Email {

	/**
	 * Email constructor.
	 */
	public function __construct() {
		$this->id = 'ss_email_rejected_sponsorship';
	}

	/**
	 * Send the email.
	 *
	 * @param integer $sponsorship_id
	 */
	public function trigger( $sponsorship_id ) {
		$this->data['email_heading'] = sprintf( __( 'Sponsorship #%d Rejected', 'simple-sponsorships' ), $sponsorship_id );
		$sponsorship                 = new Sponsorship( $sponsorship_id );
		$this->data['sponsorship']   = $sponsorship;
		$sponsor = $sponsorship->get_sponsor_data();

		$to = $sponsor->get_data( '_email', '' );
		if ( ! $to ) {
			return;
		}
		$headers = $this->get_headers();
		$subject = $this->data['email_heading'];
		$this->send( $to, $subject, $this->get_content(), $headers );
	}

	/**
	 * Get the email content in HTML format.
	 *
	 * @return string
	 */
	public function get_content_html() {
		Templates::get_template_part(
			'emails/rejected-sponsorship',
			'',
			$this->data
		);
	}
}