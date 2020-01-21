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
		add_shortcode( 'ss_sponsor_form', array( __CLASS__, 'sponsor_form' ) );
		add_shortcode( 'ss_sponsorship_details', array( $this, 'sponsorship_details' ) );
		add_shortcode( 'ss_sponsors', array( __CLASS__, 'sponsors' ) );
		add_shortcode( 'ss_packages', array( __CLASS__, 'packages' ) );
		add_shortcode( 'ss_account', array( __CLASS__, 'account' ) );
	}

	/**
	 * Sponsor Form.
	 */
	public static function sponsor_form( $args = array() ) {
		ob_start();
		$atts = shortcode_atts( array(
			'packages' => '',
		), $args, 'ss_sponsor_form' );
		Templates::get_template_part( 'sponsor-form', null, $atts );
		return ob_get_clean();
	}

	/**
	 * Sponsorship Details.
	 */
	public function sponsorship_details( $args = array() ) {
		ob_start();
		Templates::get_template_part( 'sponsorship', 'details' );
		return ob_get_clean();
	}

	/**
	 * Method to show sponsors.
	 *
	 * @param array $args Shortcode array.
	 * @return string
	 */
	public static function sponsors( $args = array() ) {
		/**
		 * Possible values:
		 *
		 * content: current or ID of a CPT,
		 * all: 0 or 1 (If 1, it will ignore content and it will show all active sponsors.
		 * text: 0 or 1
		 * logo: 0 or 1
		 */
		$atts = shortcode_atts( array(
			'content'      => 'current',
			'all'          => '0',
			'logo'         => '1',
			'text'         => '1',
			'package'      => '0',
			'size'         => 'medium',
			'col'          => 2,
			'link_sponsor' => '1',
			'hide_title'   => '0',
		), $args, 'ss_sponsors' );

		ob_start();
		Templates::get_template_part( 'shortcode/sponsors', null, $atts );
		return ob_get_clean();
	}

	/**
	 * Method to show packages.
	 *
	 * @param array $args Shortcode array.
	 * @return string
	 */
	public static function packages( $args = array() ) {
		/**
		 * Possible values:
		 *
		 * id: 0 or X. If 0, it will show all.
		 */
		$atts = shortcode_atts( array(
			'id'      => '0',
			'button'  => '0',
			'heading' => 'h2',
			'col'     => 1,
		), $args, 'ss_packages' );

		ob_start();
		Templates::get_template_part( 'shortcode/packages', null, $atts );
		return ob_get_clean();
	}

	/**
	 * Method to show packages.
	 *
	 * @param array $args Shortcode array.
	 * @return string
	 */
	public static function account( $args = array() ) {
		/**
		 * Possible values:
		 *
		 * id: 0 or X. If 0, it will show all.
		 */
		$atts = shortcode_atts( array(), $args, 'ss_account' );

		ob_start();
		Templates::get_template_part( 'shortcode/account', null, $atts );
		return ob_get_clean();
	}
}