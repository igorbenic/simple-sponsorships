<?php
/**
 * Globally available functions for forms.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * @param $args
 */
function ss_form_render_field( $args ) {
	if ( ! class_exists( '\Simple_Sponsorships\Admin\Settings' ) ) {
		include 'admin/class-settings.php';
	}
	\Simple_Sponsorships\Admin\Settings::render_field( $args );
}