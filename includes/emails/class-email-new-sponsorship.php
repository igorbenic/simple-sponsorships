<?php
/**
 * Class for handling emails when new sponsorships are requested.
 *
 * @package Simple_Sponsorships\Emails
 */

namespace Simple_Sponsorships\Emails;

use Simple_Sponsorships\Templates;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class Email
 *
 * @package Simple_Sponsorships\Emails
 */
class Email_New_Sponsorship extends Email {

	/**
	 * Email constructor.
	 */
	public function __construct() {
		$this->id = 'ss_email_new_sponsorship';
	}

	/**
	 * Send the email.
	 *
	 * @param integer $sponsorship
	 */
	public function trigger( $sponsorship ) {
		$this->data['email_heading'] = __( 'New Sponsorship', 'simple-sponsorships' );
		$this->data['sponsorship']   = $sponsorship;

		$to = ss_get_option( 'ss_admin_email', false );
		if ( false === $to ) {
			$to = get_option( 'admin_email' );
		}

		$headers = $this->get_headers();
		$subject = __( 'New Sponsorship Request', 'simple-sponsorships' );
		$this->send( $to, $subject, $this->get_content(), $headers );
	}

	/**
	 * Get the email content in HTML format.
	 *
	 * @return string
	 */
	public function get_content_html() {
		 Templates::get_template_part(
			'emails/new-sponsorship',
			'',
			$this->data
		);
	}
}