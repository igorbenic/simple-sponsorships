<?php
/**
 * Class used for Payment Gateways.
 */

namespace Simple_Sponsorships;


/**
 * Class Payment_Gateways
 *
 * Copied from WooCommerce
 *
 * @package Simple_Sponsorships
 */
class Payment_Gateways {

	/**
	 * Payment gateway classes.
	 *
	 * @var array
	 */
	public $payment_gateways = array();

	/**
	 * This can be used to check availability of gateways for the current sponsorship
	 *
	 * @var null|\Simple_Sponsorships\Sponsorship
	 */
	public $sponsorship = null;

	/**
	 * The single instance of the class.
	 *
	 * @var Payment_Gateways
	 * @since 2.1.0
	 */
	protected static $_instance = null;

	/**
	 * Main Payment_Gateways Instance.
	 *
	 * Ensures only one instance of WC_Payment_Gateways is loaded or can be loaded.
	 *
	 * @return Payment_Gateways Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.1
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'simple-sponsorships' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.1
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'simple-sponsorships' ), '1.0.0' );
	}

	/**
	 * Initialize payment gateways.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Load gateways and hook in functions.
	 */
	public function init() {
		$load_gateways = array(
			'\Simple_Sponsorships\Gateways\PayPal',
			'\Simple_Sponsorships\Gateways\Bank_Transfer',
		);

		// Filter.
		$load_gateways = apply_filters( 'ss_payment_gateways', $load_gateways );

		// Get sort order option.
		$ordering  = (array) get_option( 'ss_gateway_order' );
		$order_end = 999;

		// Load gateways in order.
		foreach ( $load_gateways as $gateway ) {
			$load_gateway = is_string( $gateway ) ? new $gateway() : $gateway;

			if ( isset( $ordering[ $load_gateway->id ] ) && is_numeric( $ordering[ $load_gateway->id ] ) ) {
				// Add in position.
				$this->payment_gateways[ $ordering[ $load_gateway->id ] ] = $load_gateway;
			} else {
				// Add to end of the array.
				$this->payment_gateways[ $order_end ] = $load_gateway;
				$order_end++;
			}
		}

		ksort( $this->payment_gateways );
	}

	/**
	 * Get gateways.
	 *
	 * @return array
	 */
	public function payment_gateways() {
		$_available_gateways = array();

		if ( count( $this->payment_gateways ) > 0 ) {
			foreach ( $this->payment_gateways as $gateway ) {
				$_available_gateways[ $gateway->id ] = $gateway;
			}
		}

		return $_available_gateways;
	}

	/**
	 * Get array of registered gateway ids
	 *
	 * @return array of strings
	 */
	public function get_payment_gateway_ids() {
		return wp_list_pluck( $this->payment_gateways, 'id' );
	}

	/**
	 * Get available gateways.
	 *
	 * @return array
	 */
	public function get_available_payment_gateways() {
		$_available_gateways = array();

		foreach ( $this->payment_gateways as $gateway ) {
			if ( $gateway->is_available() ) {
				$_available_gateways[ $gateway->id ] = $gateway;
			}
		}

		return apply_filters( 'ss_available_payment_gateways', $_available_gateways, $this->sponsorship );
	}

	/**
	 * Set the current, active gateway.
	 *
	 * @param array $gateways Available payment gateways.
	 */
	public function set_current_gateway( $gateways ) {
		// Be on the defensive.
		if ( ! is_array( $gateways ) || empty( $gateways ) ) {
			return;
		}

		$current_gateway = false;
		$current         = ss_get_session( 'chosen_payment_method' );

		if ( $current && isset( $gateways[ $current ] ) ) {
			$current_gateway = $gateways[ $current ];
		}

		if ( ! $current_gateway ) {
			$current_gateway = current( $gateways );
		}

		// Ensure we can make a call to set_current() without triggering an error.
		if ( $current_gateway && is_callable( array( $current_gateway, 'set_current' ) ) ) {
			$current_gateway->set_current();
		}
	}

	/**
	 * Sponsorship
	 *
	 * @param Sponsorship $sponsorship
	 */
	public function set_sponsorship( $sponsorship ) {
		if ( $sponsorship && is_a( $sponsorship, '\Simple_Sponsorships\Sponsorship' ) ) {
			$this->sponsorship = $sponsorship;
		}
	}

	/**
	 * Save options in admin.
	 */
	public function process_admin_options() {
		$gateway_order = isset( $_POST['gateway_order'] ) ? wc_clean( wp_unslash( $_POST['gateway_order'] ) ) : ''; // WPCS: input var ok, CSRF ok.
		$order         = array();

		if ( is_array( $gateway_order ) && count( $gateway_order ) > 0 ) {
			$loop = 0;
			foreach ( $gateway_order as $gateway_id ) {
				$order[ esc_attr( $gateway_id ) ] = $loop;
				$loop++;
			}
		}

		update_option( 'woocommerce_gateway_order', $order );
	}
}