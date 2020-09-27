<?php

/**
 * Plugin Name: Simple Sponsorships - Restrict Content
 * Description: This is an add-on for Simple Sponsorships to restrict content for sponsors.
 * Version: 1.0.0
 */

namespace Simple_Sponsorships\Restrict_Content;

use Simple_Sponsorships\DB\DB_Sponsorship_Items;
use Simple_Sponsorships\DB\DB_Sponsorships;
use Simple_Sponsorships\Integrations\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}


class Plugin extends Integration {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		$this->title = __( 'Restrict Content', 'simple-sponsorships-premium' );
		$this->id    = 'restrict-content';
		$this->desc  = __( 'This will restrict content to only sponsors that sponsored it or a package.', 'simple-sponsorships-premium' );
		$this->image = trailingslashit( SS_PLUGIN_URL ) . 'assets/images/svg/integrations/lock.svg';

		$this->desc  .= '<br/><br/>' . __( 'Use [ss_restrict_content]CONTENT[/ss_restrict_content] to restrict CONTENT.', 'simple-sponsorships-premium' );
		$this->desc  .= '<br/><br/>' . __( 'Use type=package to restrict package or type=post to restrict content.', 'simple-sponsorships-premium' );
		$this->desc  .= '<br/><br/>' . __( 'Use id=ID to be specific which package or content. Example: id=3 for Package ID 3', 'simple-sponsorships-premium' );

		include_once 'includes/class-shortcodes.php';

		add_action( 'init', array( $this, 'register_shortcode' ) );
	}

	/**
	 * Register shortcode
	 */
	public function register_shortcode() {
		$shortcode = new Shortcodes();
		$shortcode->register();
	}

	/**
	 * Return if User has Access to content
	 * @param        $object_id
	 * @param string $object_type
	 */
	public static function has_access_to( $object_id, $object_type = 'post' ) {
		global $wpdb;
		$has_access = false;

		if ( is_user_logged_in() ) {
			switch ( $object_type ) {
				case 'post':
					$sponsors = ss_get_sponsors(array(
						'meta_key' => '_user_id',
						'meta_value_num' => get_current_user_id()
					));

					if ( $sponsors ) {
						$sponsors_of_object = get_post_meta( $object_id, '_ss_sponsor' );
						if ( $sponsors_of_object ) {
							$sponsors_ids = wp_list_pluck( $sponsors, 'ID' );
							$sponsors_of_object = array_map( 'absint', $sponsors_of_object );
							$sponsors_ids = array_map( 'absint', $sponsors_ids );
							// If we get an array that is not empty,
							// it means that some of the content sponsors are also sponsors of the user
							if ( array_intersect( $sponsors_of_object, $sponsors_ids ) ) {
								$has_access = true;
							}
						}
					}
					break;
				case 'package':
					// Get sponsorships of the user and check for package.
					// Custom DB call might be faster here.
					$db_sponsorships = new DB_Sponsorships();
					$db_sponsorship_items = new DB_Sponsorship_Items();
					$sql = $wpdb->prepare( "SELECT DISTINCT sponsorships.ID FROM {$db_sponsorships->get_table_name()} sponsorships
					 INNER JOIN {$db_sponsorship_items->get_table_name()} ss_items ON ss_items.sponsorship_id=sponsorships.ID
					 INNER JOIN {$db_sponsorships->get_meta_table_name()} ss_meta ON ss_meta.sssponsorship_id=sponsorships.ID
					 WHERE ss_meta.meta_key='_user_id' AND ss_meta.meta_value=%d AND ss_items.item_type='package' AND ss_items.item_id=%d AND sponsorships.status='paid'", get_current_user_id(), $object_id );
					$results = $wpdb->get_results( $sql );
					if( $results ) {
						$has_access = true;
					}
					break;
			}

		}

		return apply_filters( 'ss_restrict_content_access_to_' . $object_type, $has_access, $object_id );
	}
}
