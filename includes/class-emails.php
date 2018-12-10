<?php
/**
 * Data and functions related to all emails.
 */

namespace Simple_Sponsorships;


class Emails {

	public function __construct() {
		add_action( 'ss_email_header', array( $this, 'email_header' ) );
		add_action( 'ss_email_footer', array( $this, 'email_footer' ) );
	}

	/**
	 * Get the email header.
	 *
	 * @param mixed $email_heading Heading for the email.
	 */
	public function email_header( $email_heading ) {
		Templates::get_template_part( 'emails/email-header', null, array( 'email_heading' => $email_heading ) );
	}

	/**
	 * Get the email footer.
	 */
	public function email_footer() {
		Templates::get_template_part( 'emails/email-footer.php' );
	}
}