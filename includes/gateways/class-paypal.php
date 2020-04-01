<?php
/**
 * PayPal Payment Gateway Class
 */

namespace Simple_Sponsorships\Gateways;
use Simple_Sponsorships\DB\DB_Sponsorships;

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

		add_action( 'ss_settings_field_paypal_documentation', array( $this, 'paypal_documentation_field' ) );

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
				'label'        => __( 'Email', 'simple-sponsorships' ),
				'default'     => '',
				'desc'        => __( 'Email that will receive payments from Sponsors', 'simple-sponsorships' ),
				'placeholder' => __( 'Email', 'simple-sponsorships' ),
			),
			'paypal_mode' => array(
				'id'          => 'paypal_mode',
				'type'        => 'select',
				'label'        => __( 'Mode', 'simple-sponsorships' ),
				'default'     => 'sandbox',
				'options' => array(
					'sandbox' => __( 'Sandbox', 'simple-sponsorships' ),
					'live'    => __( 'Live', 'simple-sponsorships' ),
				),
			),
			'paypal_invoice_prefix' => array(
				'id'        => 'paypal_invoice_prefix',
				'label'      => __( 'Invoice prefix', 'simple-sponsorships' ),
				'type'      => 'text',
				'desc'      => __( 'Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'simple-sponsorships' ),
				'default'   => 'SS-',
				'desc_tip'  => true,
			),
			'paypal_identity_token' => array(
				'id'   => 'paypal_identity_token',
				'label' => __( 'Identity Token', 'simple-sponsorships' ),
				'type' => 'text',
				'desc' => __( 'Enter your PayPal Identity Token to enable Payment Data Transfer (PDT).', 'simple-sponsorships' )
			),
			'paypal_documentation_field' => array(
				'id' => 'paypal_documentation_field',
				'type' => 'paypal_documentation',
				'label' => __( 'Documentation', 'simple-sponsorships' ),
			)
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
		$item_name = sprintf( __( 'Sponsorship %s', 'simple-sponsorships' ), $sponsorship->get_id() );

		return array(
				'cmd'           => '_xclick',
				'business'      => $this->settings[ 'paypal_email' ],
				'no_note'       => 1,
				'currency_code' => ss_get_currency(),
				'charset'       => 'utf-8',
				'rm'            => 2,
				'no_shipping'   => '1',
				'shipping'      => '0',
				'upload'        => 1,
				'return'        => esc_url_raw( add_query_arg( 'payment_confirmation', 'paypal', $this->get_return_url( $sponsorship ) ) ),
				'cancel_return' => esc_url_raw( add_query_arg( 'canceled', '1', $this->get_return_url( $sponsorship ) ) ),
				//'image_url'     => esc_url_raw( $this->gateway->get_option( 'image_url' ) ),
				'bn'            => 'SimpleSponsorships_BuyNow',
				'cbt'           => get_bloginfo( 'name' ),
				'invoice'       => $this->limit_length( $this->settings[ 'paypal_invoice_prefix' ] . $sponsorship->get_id(), 127 ),
				'custom'        => wp_json_encode(
					array(
						'sponsorship_id'  => $sponsorship->get_id(),
						'sponsorship_key' => $sponsorship->get_data( 'ss_key' ),
					)
				),
				'notify_url'    => $this->limit_length( $this->get_notify_url(), 255 ),
				'ipn_notification_url' => $this->limit_length( $this->get_notify_url(), 255 ),
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

		$notify_url = add_query_arg( array(
				'ss-listener' => 'paypal',
			),
			trailingslashit( home_url( '', $scheme ) ));

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
			'ss_paypal_args_with_items', array_merge(
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

		return $paypal_args; // This is to be used when we maybe add multiple items for each package $this->fix_request_length( $sponsorship, $paypal_args );
	}

	/**
	 * Processing the IPN
	 */
	public function process_webhooks() {
		if ( ! empty( $_POST ) && $this->validate_ipn() ) { // WPCS: CSRF ok.
			$posted = wp_unslash( $_POST ); // WPCS: CSRF ok, input var ok.
			$this->valid_response( $posted );
			exit;
		}
	}

	/**
	 * Process PDT.
	 */
	public function process_pdt() {
		$token   = $this->settings['paypal_identity_token'];

		if ( ! $token ) {
			return;
		}

		$sponsorship = $this->get_paypal_sponsorship( ss_clean( wp_unslash( $_REQUEST['cm'] ) ) );

		if ( ! $sponsorship ) {
			return;
		}

		if ( $sponsorship->is_status( 'paid' ) ) {
			return;
		}

		$status      = ss_clean( strtolower( wp_unslash( $_REQUEST['st'] ) ) ); // WPCS: input var ok, CSRF ok, sanitization ok.
		$amount      = ss_clean( wp_unslash( $_REQUEST['amt'] ) ); // WPCS: input var ok, CSRF ok, sanitization ok.
		$transaction = ss_clean( wp_unslash( $_REQUEST['tx'] ) ); // WPCS: input var ok, CSRF ok, sanitization ok.

		$transaction_result = $this->validate_transaction( $transaction );

		if ( $transaction_result ) {
			$sponsorship->update_data( '_paypal_status', $status );
			$sponsorship->update_data( 'transaction_id', $transaction );

			if ( 'completed' === $status ) {
				if ( number_format( $sponsorship->get_data( 'amount' ), 2, '.', '' ) !== number_format( $amount, 2, '.', '' ) ) {
					$sponsorship->set_status( 'approved' );
				} else {
					$this->complete( $sponsorship );

					// Log paypal transaction fee and payment type.
					if ( ! empty( $transaction_result['mc_fee'] ) ) {
						$sponsorship->update_data( '_paypal_transaction_fee', $transaction_result['mc_fee'] );
					}
					if ( ! empty( $transaction_result['payment_type'] ) ) {
						$sponsorship->update_data( '_payment_type', $transaction_result['payment_type'] );
					}
				}
			}
		}
	}

	/**
	 * Validate a PDT transaction to ensure its authentic.
	 *
	 * @param  string $transaction TX ID.
	 * @return bool|array False or result array if successful and valid.
	 */
	protected function validate_transaction( $transaction ) {
		$pdt = array(
			'body'        => array(
				'cmd' => '_notify-synch',
				'tx'  => $transaction,
				'at'  => $this->settings['paypal_identity_token'],
			),
			'timeout'     => 60,
			'httpversion' => '1.1',
			'user-agent'  => 'SimpleSponsorships/' . SS()->version,
		);

		// Post back to get a response.
		$response = wp_safe_remote_post( $this->testmode ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr', $pdt );

		if ( is_wp_error( $response ) || strpos( $response['body'], 'SUCCESS' ) !== 0 ) {
			return false;
		}

		// Parse transaction result data.
		$transaction_result  = array_map( 'ss_clean', array_map( 'urldecode', explode( "\n", $response['body'] ) ) );
		$transaction_results = array();

		foreach ( $transaction_result as $line ) {
			$line                            = explode( '=', $line );
			$transaction_results[ $line[0] ] = isset( $line[1] ) ? $line[1] : '';
		}

		if ( ! empty( $transaction_results['charset'] ) && function_exists( 'iconv' ) ) {
			foreach ( $transaction_results as $key => $value ) {
				$transaction_results[ $key ] = iconv( $transaction_results['charset'], 'utf-8', $value );
			}
		}

		return $transaction_results;
	}

	/**
	 * Check PayPal IPN validity.
	 *
	 * @return boolean
	 */
	public function validate_ipn() {

	    if ( $this->testmode && isset( $_POST['test_ipn'] ) ) {
	        return true;
        }

		// Get received values from post data.
		$validate_ipn        = wp_unslash( $_POST ); // WPCS: CSRF ok, input var ok.
		$validate_ipn['cmd'] = '_notify-validate';

		// Send back post vars to paypal.
		$params = array(
			'body'        => $validate_ipn,
			'timeout'     => 60,
			'httpversion' => '1.1',
			'compress'    => false,
			'decompress'  => false,
			'user-agent'  => 'SimpleSponsorships/' . SS()->version,
		);

		// Post back to get a response.
		$response = wp_safe_remote_post( $this->testmode ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr', $params );

		// Check to see if the request was valid.
		if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr( $response['body'], 'VERIFIED' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * There was a valid response.
	 *
	 * @param  array $posted Post data after wp_unslash.
	 */
	public function valid_response( $posted ) {
		$sponsorship = ! empty( $posted['custom'] ) ? $this->get_paypal_sponsorship( $posted['custom'] ) : false;

		if ( $sponsorship ) {

			// Lowercase returned variables.
			$posted['payment_status'] = strtolower( $posted['payment_status'] );

			if ( method_exists( $this, 'payment_status_' . $posted['payment_status'] ) ) {
				call_user_func( array( $this, 'payment_status_' . $posted['payment_status'] ), $sponsorship, $posted );
			}
		}
	}

	/**
	 * Check payment amount from IPN matches the order.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship  Order object.
	 * @param int      $amount Amount to validate.
	 */
	protected function validate_amount( $sponsorship, $amount ) {
		if ( number_format( $sponsorship->get_data( 'amount' ), 2, '.', '' ) !== number_format( $amount, 2, '.', '' ) ) {
			exit;
		}
	}

	/**
	 * Save important data from the IPN to the order.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship  Order object.
	 * @param array    $posted Posted data.
	 */
	protected function save_paypal_meta_data( $sponsorship, $posted ) {
		if ( ! empty( $posted['payment_type'] ) ) {
			$sponsorship->update_data( '_payment_type', ss_clean( $posted['payment_type'] ) );
		}
		if ( ! empty( $posted['txn_id'] ) ) {
			$sponsorship->update_data( '_transaction_id', ss_clean( $posted['txn_id'] ) );
			$sponsorship->update_data( 'transaction_id', ss_clean( $posted['txn_id'] ) );
		}
		if ( ! empty( $posted['payment_status'] ) ) {
			$sponsorship->update_data( '_paypal_status', ss_clean( $posted['payment_status'] ) );
		}
	}

	/**
	 * Handle a completed payment.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship  Order object.
	 * @param array    $posted Posted data.
	 */
	protected function payment_status_completed( $sponsorship, $posted ) {
		// Already Paid.
		if ( $sponsorship->is_status( 'paid' ) ) {
			exit;
		}

		//$this->validate_transaction_type( $posted['txn_type'] );
		//$this->validate_currency( $order, $posted['mc_currency'] );
		$this->validate_amount( $sponsorship, $posted['mc_gross'] );
		//$this->validate_receiver_email( $order, $posted['receiver_email'] );
		$this->save_paypal_meta_data( $sponsorship, $posted );

		if ( 'completed' === $posted['payment_status'] ) {

			$sponsorship->set_status( 'paid' );

			if ( ! empty( $posted['mc_fee'] ) ) {
				// Log paypal transaction fee.
				$sponsorship->update_data( '_paypal_transaction_fee', ss_clean( $posted['mc_fee'] ) );
			}
		}
	}

	/**
	 * Handle a pending payment.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship  Order object.
	 * @param array    $posted Posted data.
	 */
	protected function payment_status_pending( $sponsorship, $posted ) {
		$this->payment_status_completed( $sponsorship, $posted );
	}

	/**
	 * Handle a failed payment.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship  Order object.
	 * @param array    $posted Posted data.
	 */
	protected function payment_status_failed( $sponsorship, $posted ) {
		/* translators: %s: payment status. */
		// For now, we do nothing on fail.
	}

	/**
	 * Handle a denied payment.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship  Order object.
	 * @param array    $posted Posted data.
	 */
	protected function payment_status_denied( $sponsorship, $posted ) {
		$this->payment_status_failed( $sponsorship, $posted );
	}

	/**
	 * Handle an expired payment.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship  Order object.
	 * @param array    $posted Posted data.
	 */
	protected function payment_status_expired( $sponsorship, $posted ) {
		$this->payment_status_failed( $sponsorship, $posted );
	}

	/**
	 * Handle a voided payment.
	 *
	 * @param \Simple_Sponsorships\Sponsorship $sponsorship  Order object.
	 * @param array    $posted Posted data.
	 */
	protected function payment_status_voided( $sponsorship, $posted ) {
		$this->payment_status_failed( $sponsorship, $posted );
	}

	/**
	 * Get the order from the PayPal 'Custom' variable.
	 *
	 * @param  string $raw_custom JSON Data passed back by PayPal.
	 * @return bool|\Simple_Sponsorships\Sponsorship object
	 */
	protected function get_paypal_sponsorship( $raw_custom ) {
		// We have the data in the correct format, so get the order.
		$custom = json_decode( $raw_custom );
		if ( $custom && is_object( $custom ) ) {
			$sponsorship_id  = $custom->sponsorship_id;
			$sponsorship_key = $custom->sponsorship_key;
		} else {
			return false;
		}

		$sponsorship = ss_get_sponsorship( $sponsorship_id );

		if ( ! $sponsorship ) {
			$db  = new DB_Sponsorships();
			$rows = $db->get_by_column( 'ss_key', $sponsorship_key );
			$sponsorship    = ss_get_sponsorship( $rows[0]['ID'] );
		}

		if ( ! $sponsorship || $sponsorship->get_data( 'ss_key' ) !== $sponsorship_key ) {
			return false;
		}

		return $sponsorship;
	}

	/**
	 * PayPal Documentation Field.
	 *
	 * @param $field
	 */
	public function paypal_documentation_field( $field ) {
		?>
		<p>To configure your account for PDT:</p>

		<ol>
			<li>Log in to your PayPal account.</li>
			<li id="step2">In your <strong>Profile</strong>, choose <strong>My Selling Tools</strong> on the left.</li>
			<li id="step3">Click <strong>Update</strong> for <strong>Website Preferences</strong>.</li>
			<li>In Auto Return for Website Payments, click the <strong>On</strong> option.</li>
			<li>For the return URL, enter the URL on your site that will receive the transaction ID posted by PayPal after a customer payment.</li>
			<li>In Payment Data Transfer, click the <strong>On</strong> option.</li>
			<li>Click <strong>Save</strong>.</li>
			<li>Repeat steps <a href="#step2" pa-marked="1">2</a> and <a href="#step3" pa-marked="1">3</a>.</li>
			<li>To view your PDT identity token, scroll down to the <strong>Payment Data Transfer</strong> section on the page.</li>
		</ol>

		<p>To Configure the IPN PayPal settings:</p>
		<ol>
			<li>Log in to your PayPal account.</li>
			<li id="step2">In your <strong>Profile</strong>, choose <strong>My Selling Tools</strong> on the left.</li>
			<li id="step3">Click <strong>Update</strong> for <strong>Website Preferences</strong>.</li>
			<li>Click on the Update link for Instant Payment Notifications (IPN.)</li>
			<li>Set the notification URL to <code>http://yoursite.com/?ss-listener=paypal</code> where <em>yoursite.com</em>> needs to be changed into your site.</li>
			<li>Select "Receive IPN messages (Enabled)".</li>
			<li>Click <strong>Save</strong>.</li>
		</ol>
		<?php
	}
}