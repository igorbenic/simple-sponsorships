<?php
/**
 * Installer Class to hold everything for installing and upgrading.
 */

namespace Simple_Sponsorships;

/**
 * Class Installer
 *
 * @package Simple_Sponsorships
 */
class Installer {

	/**
	 * Activating the Plugin.
	 */
	public static function activate() {
		self::install();
		self::create_pages();
	}

	/**
	 * Create the Pages.
	 */
	public static function create_pages() {
		$settings     = ss_get_settings();
		$sponsor_page = array_key_exists( 'sponsor_page', $settings ) ? get_post( $settings[ 'sponsor_page' ] ) : false;
		if ( empty( $sponsor_page ) ) {
			// Sponsor Page.
			$sponsor = wp_insert_post(
				array(
					'post_title'     => __( 'Sponsor', 'easy-digital-downloads' ),
					'post_content'   => '[ss_sponsor_form]',
					'post_status'    => 'publish',
					'post_author'    => 1,
					'post_type'      => 'page',
					'comment_status' => 'closed'
				)
			);

			$settings['sponsor_page'] = $sponsor;
		}

		$sponsorship_page = array_key_exists( 'sponsorship_page', $settings ) ? get_post( $settings[ 'sponsorship_page' ] ) : false;
		if ( empty( $sponsorship_page ) ) {
			// Sponsor Page.
			$sponsorship = wp_insert_post(
				array(
					'post_title'     => __( 'Sponsorship', 'easy-digital-downloads' ),
					'post_content'   => '[ss_sponsorship_details]',
					'post_status'    => 'publish',
					'post_author'    => 1,
					'post_type'      => 'page',
					'comment_status' => 'closed'
				)
			);

			$settings['sponsorship_page'] = $sponsorship;
		}

		update_option( 'ss_settings', $settings );
	}

	/**
	 * Install the
	 */
	public static function install() {
		$dbs = new Databases();
		$dbs->install();
	}
}