<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 14/06/2019
 * Time: 01:07
 */

namespace Simple_Sponsorships\Stripe;


class Stripe_API {

	const STRIPE_API_VERSION = '2019-05-16';

	/**
	 * API URL
	 * @var string
	 */
	public static $url = 'https://api.stripe.com/v1/';

	/**
	 * Secret API Key.
	 * @var string
	 */
	private static $secret_key = '';

	/**
	 * Set secret API Key.
	 * @param string $key
	 */
	public static function set_secret_key( $secret_key ) {
		self::$secret_key = $secret_key;
	}

	/**
	 * Get secret key.
	 * @return string
	 */
	public static function get_secret_key() {
		if ( ! self::$secret_key ) {
			$options = ss_get_option( 'stripe_secret_key', '' );
			self::set_secret_key( $options );
		}
		return self::$secret_key;
	}

	/**
	 * Generates the user agent we use to pass to API request so
	 * Stripe can identify our application.
	 *
	 * @since 1.2.0
	 * @version 1.2.0
	 */
	public static function get_user_agent() {
		$app_info = array(
			'name'    => 'WordPress Simple Sponsorships',
			'version' => '1.0.0',
		);

		return array(
			'lang'         => 'php',
			'lang_version' => phpversion(),
			'publisher'    => 'simple-sponsorships',
			'uname'        => php_uname(),
			'application'  => $app_info,
		);
	}

	/**
	 * Generates the headers to pass to API request.
	 *
	 * @since 1.2.0
	 * @version 1.2.0
	 */
	public static function get_headers() {
		$user_agent = self::get_user_agent();
		$app_info   = $user_agent['application'];

		return apply_filters(
			'ss_stripe_request_headers',
			array(
				'Authorization'              => 'Basic ' . base64_encode( self::get_secret_key() . ':' ),
				'Stripe-Version'             => self::STRIPE_API_VERSION,
				'User-Agent'                 => $app_info['name'] . '/' . $app_info['version'],
				'X-Stripe-Client-User-Agent' => json_encode( $user_agent ),
			)
		);
	}

	/**
	 * @param        $request
	 * @param        $data
	 * @param string $method
	 */
	public static function request( $data, $api, $method = 'POST' ) {
		$headers = self::get_headers();

		$response = wp_safe_remote_post(
			self::$url . $api,
			array(
				'method'  => $method,
				'headers' => $headers,
				'body'    => apply_filters( 'ss_stripe_request_body', $data, $api ),
				'timeout' => 70,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if( empty( $response['body'] ) ) {
			return new \WP_Error( 'no-body', __( 'There was an error. Stripe did not return any information.', 'simple-sponsorships-premium' ) );
		}

		return json_decode( $response['body'] );
	}
}