<?php
/**
 * Abstract class for handling emails.
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
abstract class Email {

	/**
	 * Email ID. Used for filtering and hooking mostly.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Used to allow dynamic content.
	 *
	 * @var array
	 */
	protected $placeholders = array();

	/**
	 * Template
	 *
	 * @var string
	 */
	protected $template = '';

	/**
	 * Data that can be used in templates.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Email constructor.
	 */
	public function __construct() {}

	/**
	 * Get email content.
	 *
	 * @return string
	 */
	public function get_content() {
		$this->sending = true;
		ob_start();
		$this->get_content_html();
		return ob_get_clean();
	}

	/**
	 * Get the email content in HTML format.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return '';
	}

	/**
	 * Apply inline styles to dynamic content.
	 *
	 * We only inline CSS for html emails, and to do so we use Emogrifier library (if supported).
	 *
	 * @param string|null $content Content that will receive inline styles.
	 * @return string
	 */
	public function style_inline( $content ) {
		if ( in_array( $this->get_content_type(), array( 'text/html', 'multipart/alternative' ), true ) ) {
			ob_start();
			Templates::get_template_part( 'emails/email-styles', '', '', true );
			$css = apply_filters( 'ss_email_styles', ob_get_clean() );
			$content = '<style type="text/css">' . $css . '</style>' . $content;
		}
		return $content;
	}

	/**
	 * Send the Email.
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @param string $headers
	 * @param array  $attachments
	 */
	public function send( $to, $subject, $message, $headers = '', $attachments = array() ) {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		$message = apply_filters( 'ss_mail_content', $this->style_inline( $message ) );
		$return  = \wp_mail( $to, $subject, $message, $headers, $attachments );

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		return $return;
	}

	/**
	 * Format email string.
	 *
	 * @param mixed $string Text to replace placeholders in.
	 * @return string
	 */
	public function format_string( $string ) {
		$find    = array_keys( $this->placeholders );
		$replace = array_values( $this->placeholders );

		return apply_filters( 'ss_email_format_string', str_replace( $find, $replace, $string ), $this );
	}

	/**
	 * Get email headers.
	 *
	 * @return string
	 */
	public function get_headers() {
		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";

		return apply_filters( 'ss_email_headers', $header, $this->id );
	}

	/**
	 * Return the content type of the email.
	 *
	 * @param string $type Type.
	 *
	 * @return string
	 */
	public function get_content_type( $type = '' ) {
		return 'text/html';
	}

	/**
	 * Get the from name for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_name() {
		$from_name = apply_filters( 'ss_email_from_name', get_option( 'site_title' ), $this );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	/**
	 * Get the from address for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_address() {
		$from_address = apply_filters( 'ss_email_from_address', get_option( 'admin_email' ), $this );
		return sanitize_email( $from_address );
	}
}