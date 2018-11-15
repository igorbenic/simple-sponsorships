<?php
/**
 * Globally Available Settings Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

use \Simple_Sponsorships\Admin\Settings;

/**
 * Render the settings fields
 *
 * @param $args
 */
function ss_render_settings_field( $args ) {
	Settings::render_field( $args );
}