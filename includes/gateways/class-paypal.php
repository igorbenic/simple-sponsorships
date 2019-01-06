<?php
/**
 * PayPal Payment Gateway Class
 */

namespace Simple_Sponsorships\Gateways;

/**
 * Class PayPal
 *
 * @package Simple_Sponsorships\Gateways
 */
class PayPal extends Payment_Gateway {

	/**
	 * PayPal constructor.
	 */
	public function __construct() {
		$this->id = 'paypal';
		$this->method_title = __( 'PayPal', 'simple-sponsorships' );
		$this->title        = __( 'PayPal', 'simple-sponsorships' );

		$this->get_settings();

		$this->testmode = 'sandbox' === $this->settings['paypal_mode'];

		parent::__construct();
	}

	/**
	 * Fields for PayPal.
	 *
	 * @return array
	 */
	public function get_fields() {
		return array(
			'paypal_email' => array(
				'id'          => 'paypal_email',
				'type'        => 'email',
				'name'        => __( 'Email', 'simple-sponsorships' ),
				'default'     => '',
				'desc'        => __( 'Email that will receive payments from Sponsors', 'simple-sponsorships' ),
				'placeholder' => __( 'Email', 'simple-sponsorships' ),
			),
			'paypal_mode' => array(
				'id'          => 'paypal_mode',
				'type'        => 'select',
				'name'        => __( 'Mode', 'simple-sponsorships' ),
				'default'     => 'sandbox',
				'options' => array(
					'sandbox' => __( 'Sandbox', 'simple-sponsorships' ),
					'live'    => __( 'Live', 'simple-sponsorships' ),
				),
			),
			'paypal_invoice_prefix' => array(
				'id'          => 'paypal_invoice_prefix',
				'name'       => __( 'Invoice prefix', 'simple-sponsorships' ),
				'type'        => 'text',
				'description' => __( 'Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'simple-sponsorships' ),
				'default'     => 'SS-',
				'desc_tip'    => true,
			),
		);
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

		return array(
			'result'   => 'success',
			'redirect' => $this->get_request_url( $sponsorship, $this->testmode ),
		);
	}

	/**
	 * Get the PayPal request URL for an order.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship Sponsorship object.
	 * @param  bool     $sandbox Whether to use sandbox mode or not.
	 * @return string
	 */
	public function get_request_url( $sponsorship, $sandbox = false ) {
		$this->endpoint = $sandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr?test_ipn=1&' : 'https://www.paypal.com/cgi-bin/webscr?';
		$paypal_args    = $this->get_paypal_args( $sponsorship );

		return $this->endpoint . http_build_query( $paypal_args, '', '&' );
	}

	/**
	 * Limit length of an arg.
	 *
	 * @param  string  $string Argument to limit.
	 * @param  integer $limit Limit size in characters.
	 * @return string
	 */
	protected function limit_length( $string, $limit = 127 ) {
		// As the output is to be used in http_build_query which applies URL encoding, the string needs to be
		// cut as if it was URL-encoded, but returned non-encoded (it will be encoded by http_build_query later).
		$url_encoded_str = rawurlencode( $string );

		if ( strlen( $url_encoded_str ) > $limit ) {
			$string = rawurldecode( substr( $url_encoded_str, 0, $limit - 3 ) . '...' );
		}
		return $string;
	}

	/**
	 * Get transaction args for paypal request, except for line item args.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship Sponsorship object.
	 * @return array
	 */
	protected function get_transaction_args( $sponsorship ) {
		$package   = $sponsorship->get_data( 'package' );
		$item_name = sprintf( __( 'Sponsorship %s', 'simple-sponsorships' ), $sponsorship->get_id() );
		if ( $package ) {
			$package   = ss_get_package( $package );
			$item_name = $package->get_data( 'title' );
		}

		return array(
				'cmd'           => '_xclick',
				'business'      => $this->settings[ 'paypal_email' ],
				'no_note'       => 1,
				'currency_code' => ss_get_currency(),
				'charset'       => 'utf-8',
				'rm'            => 2,
				'upload'        => 1,
				'return'        => esc_url_raw( add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $sponsorship ) ) ),
				'cancel_return' => esc_url_raw( $this->get_return_url( $sponsorship ) ),
				//'image_url'     => esc_url_raw( $this->gateway->get_option( 'image_url' ) ),
				'bn'            => 'SimpleSponsorships_BuyNow',
				'invoice'       => $this->limit_length( $this->settings[ 'paypal_invoice_prefix' ] . $sponsorship->get_id(), 127 ),
				'custom'        => wp_json_encode(
					array(
						'sponsorship_id'  => $sponsorship->get_id(),
						'sponsorship_key' => $sponsorship->get_data( 'ss_key' ),
					)
				),
				'notify_url'    => $this->limit_length( $this->get_notify_url(), 255 ),
				'first_name'    => $this->limit_length( $sponsorship->get_data( 'billing_first_name' ), 32 ),
				'last_name'     => $this->limit_length( $sponsorship->get_data( 'billing_last_name' ), 64 ),
				'address1'      => $this->limit_length( $sponsorship->get_data( 'billing_address_1' ), 100 ),
				'address2'      => $this->limit_length( $sponsorship->get_data( 'billing_address_2' ), 100 ),
				'city'          => $this->limit_length( $sponsorship->get_data( 'billing_city' ), 40 ),
				'state'         => $this->get_paypal_state( $sponsorship->get_data( 'billing_country' ), $sponsorship->get_data( 'billing_state' ) ),
				'zip'           => $this->limit_length( ss_format_postcode( $sponsorship->get_data( 'billing_postcode' ), $sponsorship->get_data( 'billing_country' ) ), 32 ),
				'country'       => $this->limit_length( $sponsorship->get_data( 'billing_country' ), 2 ),
				'email'         => $this->limit_length( $sponsorship->get_data( 'billing_email' ) ),
			    'item_name'     => $this->limit_length( $item_name, 127 ),
				'quantity'      => 1,
				'amount'        => $sponsorship->get_data( 'amount' ),
		        'item_number'   => $this->limit_length( $sponsorship->get_id(), 127 ),
		);
	}

	/**
	 * Return a Notification URL.
	 *
	 * @return string
	 */
	protected function get_notify_url() {
		if ( is_ssl() ) {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}

		$notify_url = add_query_arg( 'ss-action', 'SS_PayPal', trailingslashit( home_url( '', $scheme ) ) );

		return esc_url_raw( $notify_url );
	}

	/**
	 * Get the state to send to paypal.
	 *
	 * @param  string $cc Country two letter code.
	 * @param  string $state State code.
	 * @return string
	 */
	protected function get_paypal_state( $cc, $state ) {
		if ( 'US' === $cc ) {
			return $state;
		}

		/**
		 * @todo States
		 */
		$states = array(); //WC()->countries->get_states( $cc );

		if ( isset( $states[ $state ] ) ) {
			return $states[ $state ];
		}

		return $state;
	}

	/**
	 * If the default request with line items is too long, generate a new one with only one line item.
	 *
	 * If URL is longer than 2,083 chars, ignore line items and send cart to Paypal as a single item.
	 * One item's name can only be 127 characters long, so the URL should not be longer than limit.
	 * URL character limit via:
	 * https://support.microsoft.com/en-us/help/208427/maximum-url-length-is-2-083-characters-in-internet-explorer.
	 *
	 * @param WC_Order $order Order to be sent to Paypal.
	 * @param array    $paypal_args Arguments sent to Paypal in the request.
	 * @return array
	 */
	protected function fix_request_length( $order, $paypal_args ) {
		$max_paypal_length = 2083;
		$query_candidate   = http_build_query( $paypal_args, '', '&' );

		if ( strlen( $this->endpoint . $query_candidate ) <= $max_paypal_length ) {
			return $paypal_args;
		}

		return apply_filters(
			'woocommerce_paypal_args', array_merge(
			$this->get_transaction_args( $order ),
			$this->get_line_item_args( $order, true )
		), $order
		);

	}

	/**
	 * Get PayPal Args for passing to PP.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship Sponsorship object.
	 * @return array
	 */
	protected function get_paypal_args( $sponsorship ) {

		$paypal_args = apply_filters(
			'ss_paypal_args',
			$this->get_transaction_args( $sponsorship ),
			$sponsorship
		);

		return $this->fix_request_length( $sponsorship, $paypal_args );
	}
}