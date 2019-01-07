<?php
/**
 * This class is used to send invoice.
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
class Email_Customer_Invoice extends Email {

	/**
	 * Email constructor.
	 */
	public function __construct() {
		$this->id = 'ss_email_customer_invoice';
	}

	/**
	 * Send the email.
	 *
	 * @param Sponsorship $sponsorship
	 */
	public function trigger( $sponsorship ) {
		$this->data['email_heading'] = sprintf( __( 'Invoice for Sponsorship #%d', 'simple-sponsorships' ), $sponsorship->get_id() );
		$this->data['sponsorship']   = $sponsorship;

		$to      = $sponsorship->get_data( 'billing_email' );
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
			'emails/customer-invoice',
			'',
			$this->data
		);
	}
}