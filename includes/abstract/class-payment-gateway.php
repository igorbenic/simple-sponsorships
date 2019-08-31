<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 10/12/18
 * Time: 03:07
 */

namespace Simple_Sponsorships\Gateways;

use Simple_Sponsorships\Sponsorship;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Payment_Gateway
 *
 * Copied from WooCommerce and modified.
 *
 * @package Simple_Sponsorships\Gateways
 */
abstract class Payment_Gateway {

	/**
	 * Gateway ID.
	 * @var string
	 */
	public $id = '';

	/**
	 * Set if the place order button should be renamed on selection.
	 *
	 * @var string
	 */
	public $order_button_text;

	/**
	 * Yes or no based on whether the method is enabled.
	 *
	 * @var string
	 */
	public $enabled = '1';

	/**
	 * Payment method title for the frontend.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Payment method description for the frontend.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Chosen payment method id.
	 *
	 * @var bool
	 */
	public $chosen;

	/**
	 * What is supported.
	 *
	 * @var array
	 */
	public $supports = array();

	/**
	 * Gateway title.
	 *
	 * @var string
	 */
	public $method_title = '';

	/**
	 * Gateway description.
	 *
	 * @var string
	 */
	public $method_description = '';

	/**
	 * True if the gateway shows fields on the checkout.
	 *
	 * @var bool
	 */
	public $has_fields;

	/**
	 * Countries this gateway is allowed for.
	 *
	 * @var array
	 */
	public $countries;

	/**
	 * Available for all counties or specific.
	 *
	 * @var string
	 */
	public $availability;

	/**
	 * Icon for the gateway.
	 *
	 * @var string
	 */
	public $icon;

	/**
	 * Maximum transaction amount, zero does not define a maximum.
	 *
	 * @var int
	 */
	public $max_amount = 0;

	/**
	 * Optional label to show for "new payment method" in the payment
	 * method/token selection radio selection.
	 *
	 * @var string
	 */
	public $new_method_label = '';

	/**
	 * @var array
	 */
	public $settings = null;

	/**
	 * Payment_Gateway constructor.
	 */
	public function __construct() {
		add_filter( 'ss_settings_gateways', array( $this, 'register_settings_fields' ) );
		add_filter( 'ss_get_settings_sections', array( $this, 'register_gateway_section' ) );
	}

	/**
	 * Register the settings section.
	 *
	 * @param $settings
	 */
	public function register_gateway_section( $sections ) {

		$sections['gateways'][ $this->id ] = $this->get_method_title();

		return $sections;
	}

	/**
	 * Register the settings fields.
	 *
	 * @param $settings
	 */
	public function register_settings_fields( $settings ) {
		$settings[ $this->id ] = array_merge( array(
			$this->id . '_enabled' => array(
				'id'   => $this->id . '_enabled',
				'label' => __( 'Enabled', 'simple-sponsorships' ),
				'desc' => __( 'Enable this payment gateway', 'simple-sponsorships' ),
				'type' => 'checkbox',
			)
		), $this->get_fields() );

		return $settings;
	}

	/**
	 * Field configuration for Payment Gateway.
	 *
	 * @return array
	 */
	public function get_fields() {
		$fields = array();
		return $fields;
	}

	/**
	 * Return the title for admin screens.
	 *
	 * @return string
	 */
	public function get_method_title() {
		return apply_filters( 'ss_gateway_method_title', $this->method_title, $this );
	}

	/**
	 * Return the description for admin screens.
	 *
	 * @return string
	 */
	public function get_method_description() {
		return apply_filters( 'ss_gateway_method_description', $this->method_description, $this );
	}

	/**
	 * Init settings for gateways.
	 *
	 */
	public function get_settings() {
		//parent::init_settings();
		if ( null === $this->settings ) {
			$fields = $this->get_fields();
			$this->settings['enabled'] = ss_get_option( $this->id . '_enabled', '0' );
			foreach ( $fields as $id => $field ) {
				$default = isset( $field['default'] ) ? $field['default'] : '';
				$field_id = isset( $field['id'] ) ? $field['id'] : $id;
				$this->settings[ $field_id ] = ss_get_option( $field_id, $default );
			}
		}
		$this->enabled  = ! empty( $this->settings['enabled'] ) && '1' === $this->settings['enabled'] ? true : false;
	}

	/**
	 * Return whether or not this gateway still requires setup to function.
	 *
	 * When this gateway is toggled on via AJAX, if this returns true a
	 * redirect will occur to the settings page instead.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	public function needs_setup() {
		return false;
	}

	/**
	 * Get the return url (thank you page).
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship Sponsorship object.
	 * @return string
	 */
	public function get_return_url( $sponsorship = null ) {
		if ( $sponsorship ) {
			$return_url = $sponsorship->get_view_link();
		} else {
			$return_url = get_permalink( ss_get_option( 'sponsorship_page', 0 ) );
		}

		if ( is_ssl() ) {
			$return_url = str_replace( 'http:', 'https:', $return_url );
		}

		return apply_filters( 'ss_get_return_url', $return_url, $sponsorship );
	}

	/**
	 * Check if the gateway has fields on the checkout.
	 *
	 * @return bool
	 */
	public function has_fields() {
		return (bool) $this->has_fields;
	}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		$this->get_settings();
		$is_available = $this->enabled;

		return $is_available;
	}

	/**
	 * Return the gateway's title.
	 *
	 * @return string
	 */
	public function get_title() {
		return apply_filters( 'ss_gateway_title', $this->title, $this->id );
	}

	/**
	 * Return the gateway's description.
	 *
	 * @return string
	 */
	public function get_description() {
		return apply_filters( 'ss_gateway_description', $this->description, $this->id );
	}

	/**
	 * Return the gateway's icon.
	 *
	 * @return string
	 */
	public function get_icon() {

		$icon = $this->icon ? '<img src="' . $this->icon . '" alt="' . esc_attr( $this->get_title() ) . '" />' : '';

		return apply_filters( 'ss_gateway_icon', $icon, $this->id );
	}

	/**
	 * Set as current gateway.
	 *
	 * Set this as the current gateway.
	 */
	public function set_current() {
		$this->chosen = true;
	}

	/**
	 * Process Payment.
	 *
	 * Process the payment. Override this in your gateway. When implemented, this should.
	 * return the success and redirect in an array. e.g:
	 *
	 *        return array(
	 *            'result'   => 'success',
	 *            'redirect' => $this->get_return_url( $sponsorship )
	 *        );
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship Sponsorship Object.
	 * @return array
	 */
	public function process_payment( $sponsorship ) {
		return array();
	}

	/**
	 * Process refund.
	 *
	 * If the gateway declares 'refunds' support, this will allow it to refund.
	 * a passed in amount.
	 *
	 * @param  int    $sponsorship_id Sponsorship ID.
	 * @param  float  $amount Refund amount.
	 * @param  string $reason Refund reason.
	 * @return boolean True or false based on success, or a WP_Error object.
	 */
	public function process_refund( $sponsorship_id, $amount = null, $reason = '' ) {
		return false;
	}

	/**
	 * Validate frontend fields.
	 *
	 * Validate payment fields on the frontend.
	 *
	 * @return bool
	 */
	public function validate_fields() {
		return true;
	}

	/**
	 * If There are no payment fields show the description if set.
	 * Override this in your gateway if you have some.
	 */
	public function payment_fields() {
		$description = $this->get_description();
		if ( $description ) {
			echo wpautop( wptexturize( $description ) ); // @codingStandardsIgnoreLine.
		}
	}

	/**
	 * Check if a gateway supports a given feature.
	 *
	 * Gateways should override this to declare support (or lack of support) for a feature.
	 * For backward compatibility, gateways support 'products' by default, but nothing else.
	 *
	 * @param string $feature string The name of a feature to test support for.
	 * @return bool True if the gateway supports the feature, false otherwise.
	 */
	public function supports( $feature ) {
		return apply_filters( 'ss_payment_gateway_supports', in_array( $feature, $this->supports ), $feature, $this );
	}

	/**
	 * Can the order be refunded via this gateway?
	 *
	 * Should be extended by gateways to do their own checks.
	 *
	 * @param  Sponsorship $sponsorship Order object.
	 * @return bool If false, the automatic refund button is hidden in the UI.
	 */
	public function can_refund_sponsorship( $sponsorship ) {
		return $sponsorship && $this->supports( 'refunds' );
	}

	/**
	 * Each gateway has it's own way to process webhooks.
	 */
	public function process_webhooks() {}

	/**
	 * Complete the payment.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship Object.
	 */
	public function complete( $sponsorship ) {
		// Setting it to Paid.
		$sponsorship->set_status( 'paid' );
		$sponsorship->update_data( 'gateway', $this->id );
	}
}
