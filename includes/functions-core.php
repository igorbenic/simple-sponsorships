<?php
/**
 * Globally Available Core Functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * @return \Simple_Sponsorships\Plugin
 */
function SS() {
	return \Simple_Sponsorships\get_main();
}

/**
 * Return the settings.
 *
 * @return array
 */
function ss_get_settings() {
	$plugin = SS();
	return $plugin->get_settings();
}

/**
 * Get the Option
 *
 * @param string $key     The option key (name).
 * @param mixed  $default The value that will be returned if this option does not exist.
 */
function ss_get_option( $key, $default = '' ) {
	$settings = ss_get_settings();
	return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

/**
 * Return content Types.
 *
 * @return mixed|string
 */
function ss_get_content_types() {
	$content_types = ss_get_option( 'content_types', array( 'post' => 'Posts', 'page' => 'Page' ) );

	if ( ! $content_types ) {
		$content_types = array();
	}

	return apply_filters( 'ss_content_types', $content_types );
}

/**
 * Get all image sizes.
 *
 * @since 1.3.0
 *
 * @return array Array where key is the image size.
 */
function ss_get_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
			$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
			$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
			$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}

	return $sizes;
}

/**
 * Get registered integrations
 *
 * @since 1.3.0
 *
 * @return array Key is the integration slug. Value is the class.
 */
function ss_get_registered_integrations() {
	return apply_filters( 'ss_registered_integrations', array(
		'gravityforms' => '\Simple_Sponsorships\Integrations\GravityForms',
		'stripe' => '\Simple_Sponsorships\Integrations\Dummy\Stripe',
		'recurring-payments' => '\Simple_Sponsorships\Integrations\Dummy\Recurring_Payments_Dummy',
		'package-slots' => '\Simple_Sponsorships\Integrations\Dummy\Package_Slots',
		'post-paid-form' => '\Simple_Sponsorships\Integrations\Dummy\Post_Paid_Form_Dummy',
		'package-features' => '\Simple_Sponsorships\Integrations\Dummy\Package_Features',
		'package-timed-availability' => '\Simple_Sponsorships\Integrations\Dummy\Package_Timed_Availability',
		'package-minimum-quantity' => '\Simple_Sponsorships\Integrations\Dummy\Package_Minimum_Quantity',
		'restrict-content' => '\Simple_Sponsorships\Integrations\Dummy\Restrict_Content_Dummy',
	));
}

/**
 * Get active integrations
 *
 * @since 1.3.0
 *
 * @return array Array of integration slugs
 */
function ss_get_active_integrations() {
	return get_option( 'ss_active_integrations', array() );
}

/**
 * Get active integrations
 *
 * @since 1.3.0
 * @param array $integrations Array of integration slugs.
 *
 * @return mixed
 */
function ss_update_active_integrations( $integrations = array() ) {
	return update_option( 'ss_active_integrations', $integrations );
}

/**
 * Return if we enabled account creation.
 *
 * @since 1.5.0
 *
 * @return bool
 */
function ss_is_account_creation_enabled() {
	return apply_filters( 'ss_is_account_creation_enabled', 1 === absint( ss_get_option( 'allow_account_creation', '0' ) ) );
}

/**
 * Get endpoint URL.
 *
 * Gets the URL for an endpoint, which varies depending on permalink settings.
 *
 * Copied from WooCommerce.
 *
 * @param  string $endpoint  Endpoint slug.
 * @param  string $value     Query param value.
 * @param  string $permalink Permalink.
 *
 * @return string
 */
function ss_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink ) {
		$permalink = get_permalink();
	}

	// Map endpoint to options.
	$query_vars = SS()->query->get_query_vars();
	$endpoint   = ! empty( $query_vars[ $endpoint ] ) ? $query_vars[ $endpoint ] : $endpoint;

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink );

		if ( $value ) {
			$url .= trailingslashit( $endpoint ) . user_trailingslashit( $value );
		} else {
			$url .= user_trailingslashit( $endpoint );
		}

		$url .= $query_string;
	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}

	return apply_filters( 'ss_get_endpoint_url', $url, $endpoint, $value, $permalink );
}

if ( ! function_exists( 'is_ss_endpoint_url' ) ) {

	/**
	 * is_ss_endpoint_url - Check if an endpoint is showing.
	 *
	 * Copied from WooCommerce
	 *
	 * @param string|false $endpoint Whether endpoint.
	 * @return bool
	 */
	function is_ss_endpoint_url( $endpoint = false ) {
		global $wp;

		$wc_endpoints = SS()->query->get_query_vars();

		if ( false !== $endpoint ) {
			if ( ! isset( $wc_endpoints[ $endpoint ] ) ) {
				return false;
			} else {
				$endpoint_var = $wc_endpoints[ $endpoint ];
			}

			return isset( $wp->query_vars[ $endpoint_var ] );
		} else {
			foreach ( $wc_endpoints as $key => $value ) {
				if ( isset( $wp->query_vars[ $key ] ) ) {
					return true;
				}
			}

			return false;
		}
	}
}

/**
 * Return allowed HTML that supports SVG.
 *
 * @param string $html
 *
 * @return string
 */
function ss_kses_with_svg( $html ) {
	$kses_defaults = wp_kses_allowed_html( 'post' );

	$svg_args = array(
		'svg'   => array(
			'class' => true,
			'aria-hidden' => true,
			'aria-labelledby' => true,
			'role' => true,
			'xmlns' => true,
			'width' => true,
			'height' => true,
			'viewbox' => true, // <= Must be lower case!
		),
		'g'     => array( 'fill' => true ),
		'title' => array( 'title' => true ),
		'path'  => array( 'd' => true, 'fill' => true,  ),
	);

	$allowed_tags = array_merge( $kses_defaults, $svg_args );
	return wp_kses( $html, $allowed_tags );
}
