<?php
/**
 * Class to define shortcodes.
 *
 * @package Simple_Sponsorships
 */

namespace Simple_Sponsorships;

/**
 * Class Shortcodes
 *
 * @package Simple_Sponsorships
 */
class Shortcodes {

	/**
	 * Shortcodes constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register all shortcodes.
	 */
	public function register() {
		add_shortcode( 'ss_sponsor_form', array( $this, 'sponsor_form' ) );
		add_shortcode( 'ss_sponsorship_details', array( $this, 'sponsorship_details' ) );
	}

	/**
	 * Sponsor Form.
	 */
	public function sponsor_form( $args = array() ) {
		Templates::get_template_part( 'sponsor-form' );
	}

	/**
	 * Sponsor Form.
	 */
	public function sponsorship_details( $args = array() ) {
		Templates::get_template_part( 'sponsorship', 'details' );
	}
}