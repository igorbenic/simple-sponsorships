<?php
/**
 * Class for handling emails when a sponsorship is activated.
 * This email is sent to the Site Owner.
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
class Email_Activated_Sponsorship extends Email {

	/**
	 * Email constructor.
	 */
	public function __construct() {
		$this->id = 'ss_email_activated_sponsorship';
	}

	/**
	 * Send the email.
	 *
	 * @param Sponsorship $sponsorship
	 */
	public function trigger( $sponsorship ) {
		$this->data['email_heading'] = sprintf( __( 'Sponsorship #%d Activated', 'simple-sponsorships' ), $sponsorship->get_id() );
		$this->data['sponsorship']   = $sponsorship;

		$to = ss_get_option( 'ss_admin_email', false );
		if ( false === $to ) {
			$to = get_option( 'admin_email' );
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
			'emails/activated-sponsorship',
			'',
			$this->data
		);
	}
}