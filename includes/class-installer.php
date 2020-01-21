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
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'1.3.0' => array(
			'ss_update_130_integrations',
			'ss_update_130_integrations_premium',
		),
		'1.5.0' => array(
			'flush_rewrite_rules'
		)
	);

	/**
	 * Activating the Plugin.
	 */
	public static function activate() {
		self::install();
		self::create_pages();

		// for Account query vars.
		flush_rewrite_rules();
	}

	/**
	 * Check the version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( 'ss_version', '0.5.0' ), SS()->version, '<' ) ) {
			self::update( get_option( 'ss_version', '0.5.0' ) );
			do_action( 'ss_updated' );
		}
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @since  0.6.0
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Update the Simple Sponsorships database
	 *
	 * @param string $from From which version are we updating it.
	 */
	public static function update( $from ) {
		self::install();

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $from, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					if ( function_exists( $update_callback ) ) {
						$update_callback();
					}
				}
			}
		}

		update_option( 'ss_version', SS()->version );
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