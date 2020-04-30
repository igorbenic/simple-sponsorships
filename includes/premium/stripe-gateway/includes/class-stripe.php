<?php
/**
 * Stripe implementation
 */
namespace Simple_Sponsorships\Gateways;
use Braintree\Exception;
use Simple_Sponsorships\DB\DB_Sponsorships;
use Simple_Sponsorships\Package;
use Simple_Sponsorships\Sponsorship;
use Simple_Sponsorships\Stripe\Stripe_API;

/**
 * Class Stripe
 *
 * @package Simple_Sponsorships\Gateways
 */
class Stripe extends Payment_Gateway {
	/**
	 * PayPal constructor.
	 */
	public function __construct() {
		$this->id = 'stripe';
		$this->method_title = __( 'Stripe', 'simple-sponsorships-premium' );
		$this->title        = __( 'Stripe', 'simple-sponsorships-premium' );
		$this->has_fields   = true;

		$this->get_settings();

		$this->testmode = 'sandbox' === $this->settings['stripe_mode'];

		parent::__construct();

		$this->supports = array(
			'recurring',
			'cancel_recurring',
		);
	}

	/**
	 * Cancel Sponsorship
	 *
	 * @param Sponsorship $sponsorship Sponsorship object.
	 */
	public function cancel_recurring( $sponsorship ) {

	    $stripe_sub_id = $sponsorship->get_data( 'ss_stripe_subscription_id', '' );

        if ( ! $stripe_sub_id ) {
            return new \WP_Error( 'no-stripe-sub', __( 'No Subscription found. Please contact the administrator', 'simple-sponsorships-premium' ));
        }

		$subscription = Stripe_API::request(
			[],
			'subscriptions/' . $stripe_sub_id,
            'DELETE'
		);

		if ( is_wp_error( $subscription ) ) {
			return $subscription;
		}

		if ( isset( $subscription->status ) && ( 'canceled' !== $subscription->status && 'cancelled' !== $subscription->status ) ) {
			return new \WP_Error( 'no-stripe-status', __( 'Subscription could not be cancelled. Please contact the administrator.', 'simple-sponsorships-premium' ));
		}

		$sponsorship = ss_get_recurring_sponsorship( $sponsorship );
		$sponsorship->update_recurring_status('cancelled' );
	}

	/**
     * Return if the Stripe is available or not.
     *
	 * @return bool
	 */
	public function is_available() {
        if ( ! is_ssl() ) {
            return false;
        }

		return parent::is_available();
	}

	/**
	 * Fields for PayPal.
	 *
	 * @return array
	 */
	public function get_fields() {
		return array(
			'stripe_public_key' => array(
				'id'          => 'stripe_public_key',
				'type'        => 'text',
				'label'       => __( 'Public Key', 'simple-sponsorships-premium' ),
				'default'     => '',
				'placeholder' => __( 'Your Stripe Public Key', 'simple-sponsorships-premium' ),
			),
			'stripe_secret_key' => array(
				'id'          => 'stripe_secret_key',
				'type'        => 'text',
				'label'        => __( 'Secret Key', 'simple-sponsorships-premium' ),
				'default'     => '',
				'placeholder' => __( 'Your Stripe Secret Key', 'simple-sponsorships-premium' ),
			),
			'stripe_mode' => array(
				'id'          => 'stripe_mode',
				'type'        => 'select',
				'label'        => __( 'Mode', 'simple-sponsorships-premium' ),
				'default'     => 'sandbox',
				'options' => array(
					'sandbox' => __( 'Sandbox', 'simple-sponsorships-premium' ),
					'live'    => __( 'Live', 'simple-sponsorships-premium' ),
				),
			),
			'stripe_webhook_secret' => array(
				'id'          => 'stripe_webhook_secret',
				'type'        => 'text',
				'label'        => __( 'Webhook/Event Signing Secret', 'simple-sponsorships-premium' ),
				'default'     => '',
				'desc'        => sprintf( __( 'When a webhook is created, you will also get a signing secret. If using webhooks, use the url %s for your webhook', 'simple-sponsorship-premium' ), '<code>' . add_query_arg( 'ss-listener', 'stripe', get_site_url() ) . '</code>' )
			),
		);
	}

	public function payment_fields() {
		parent::payment_fields();

		if ( $this->testmode ) {
		    ?>
            <p><?php echo wp_kses_post( __( 'Testing mode enabled. Use Card <code>4242 4242 4242 4242</code> to test the payment', 'simple-sponsorships-premium' ) ); ?></p>
            <?php
        }
		?>
        <div class="ss-form-field ss-form-field-text">
            <label for="stripe-cardholder-name"><?php esc_html_e( 'Cardholder Name', 'simple-sponsorships-premium' ); ?></label>
            <input id="stripe-cardholder-name" type="text" placeholder="<?php esc_attr_e( 'Cardholder Name', 'simple-sponsorships-premium' ); ?>" />
        </div>
		<!-- placeholder for Elements -->
		<div id="ss-stripe-card-element"></div>
		<?php
    }

	/**
     * Verify Intent
     *
	 * @param Sponsorship $sponsorship
	 */
    public function verify_intent( $sponsorship ) {
        if ( $sponsorship->is_paid() ) {
            return;
        }

	    $intent_id = $sponsorship->get_data( '_stripe_payment_intent_id', false );

        if ( ! $intent_id ) {
            return;
        }

	    $intent = Stripe_API::request(
		    [],
		    'payment_intents/' . $intent_id . '/confirm'
	    );

	    if ( is_wp_error( $intent ) ) {
	        throw new \Exception( $intent->get_error_code(), $intent->get_error_message() );
	    }

	    if ( $intent->status == 'succeeded' ) {
		    if ( ! empty( $intent->charges->data[0]['id'] ) && 'succeeded' === $intent->charges->data[0]['status'] ) {
			    // Set the transaction ID from the charge.
			    $sponsorship->update_data( 'transaction_id', sanitize_text_field( $intent->charges->data[0]['id'] ) );
		    }

		    $sponsorship->delete_data( '_stripe_payment_intent_secret' );
		    $this->complete( $sponsorship );
	    }
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
	 * @return array|\WP_Error
	 */
	public function process_payment( $sponsorship ) {

	    if ( ! isset( $_POST['ss_stripe_payment_method'] ) ) {
		    return new \WP_Error( 'no-stripe-payment-method', __( 'No Payment Method information', 'simple-sponsorships-premium' ) );
	    }

	    try {

		    // Started Payment
		    $sponsorship->set_status( 'on-hold' );

		    $user_id     = $sponsorship->get_data( '_user_id', 0 );
		    $intent_id   = $sponsorship->get_data( '_stripe_payment_intent_id', false );
		    $intent      = false;
		    $customer_id = $user_id ? $this->get_or_create_customer( $user_id, $sponsorship ) : false;

		    if ( isset( $_POST['ss_stripe_payment_method'] ) ) {
			    $payment_method = sanitize_text_field( $_POST['ss_stripe_payment_method'] );
			    $intent = Stripe_API::request(
				    [
					    'payment_method'      => $payment_method,
					    'amount'              => $sponsorship->get_data( 'amount' ) * 100,
					    'currency'            => strtolower( $sponsorship->get_data( 'currency' ) ),
					    'confirmation_method' => 'manual',
					    'customer'            => $customer_id,
					    'confirm'             => 'true',
					    'metadata'            => array( 'sponsorship_id' => $sponsorship->get_id() ),
					    'receipt_email'       => isset( $_POST['billing_email'] ) ? sanitize_text_field( $_POST['billing_email'] ) : '',
                        'setup_future_usage'  => function_exists( 'ss_is_recurring_sponsorship' ) && ss_is_recurring_sponsorship( $sponsorship ) ? 'off_session' : 'on_session'
				    ],
				    'payment_intents'
			    );

			    if ( is_wp_error( $intent ) ) {
				    return $intent;
			    }

			    if ( $customer_id ) {
				    $payment_method = sanitize_text_field( $_POST['ss_stripe_payment_method'] );
				    $this->attach_payment_method( $payment_method, $customer_id );
				    $this->set_payment_method_as_detault( $payment_method, $customer_id );
			    }
		    }

		    if ( ! $intent && $intent_id ) {
			    $intent = Stripe_API::request(
				    [],
				    'payment_intents/' . $intent_id . '/confirm'
			    );

			    if ( is_wp_error( $intent ) ) {
				    return $intent;
			    }
		    }

		    if ( ! $intent ) {
			    return new \WP_Error( 'no-intent', __( 'No Payment Intent information from Stripe', 'simple-sponsorships-prmeium' ) );
		    }

		    $sponsorship->update_data( '_stripe_payment_intent_id', $intent->id );

		    if ( $intent->status == 'requires_action' &&
		         $intent->next_action->type == 'use_stripe_sdk'
		    ) {
			    $sponsorship->update_data( 'gateway', $this->id );
			    $sponsorship->update_data( '_stripe_payment_intent_secret', $intent->client_secret );

			    return new \WP_Error( 'requires-action', __( 'Payment requires additional information', 'simple-sponsorships-premium' ) );
		    } else if ( $intent->status == 'succeeded' ) {

			    if ( ! empty( $intent->charges->data[0]['id'] ) && 'succeeded' === $intent->charges->data[0]['status'] ) {
				    // Set the transaction ID from the charge.
				    $sponsorship->update_data( 'transaction_id', sanitize_text_field( $intent->charges->data[0]['id'] ) );
			    }

			    $sponsorship->delete_data( '_stripe_payment_intent_secret' );
			    $this->create_subscriptions( $sponsorship, $customer_id, $intent );
			    $this->complete( $sponsorship );

			    return array(
				    'result'   => 'success',
				    'redirect' => '',
			    );
		    } else {
			    return new \WP_Error( 'requires-action', __( 'Invalid Payment', 'simple-sponsorships-premium' ) );
		    }
	    } catch ( \Exception $e ) {
		    return new \WP_Error( $e->getCode(), $e->getMessage() );
        }
	}

	/**
     * Create Subscriptions
     * This will create usually one subscription but making it possible to accept multiple recurring packages.
     *
     * This part is supporting Recurring Payments Add-on
     *
	 * @param Sponsorship $sponsorship
	 * @param string      $customer_id
	 * @param object      $intent
	 */
	public function create_subscriptions( $sponsorship, $customer_id, $intent ) {
        if ( ! function_exists( 'ss_is_recurring_sponsorship' ) ) {
            return;
        }

        if ( ! ss_is_recurring_sponsorship( $sponsorship ) ) {
            return;
        }

        $items = $sponsorship->get_items( 'package' );

        foreach ( $items as $item ) {
	        $package = ss_get_package( $item['item_id'] );

	        if ( 'recurring' !== $package->get_type() ) {
	            continue;
            }

            $subscription = $this->create_subscription_from_package( $package, $sponsorship, $customer_id, $intent );

            $sponsorship->update_data( 'ss_stripe_subscription_id', $subscription->id );
            $recurring_sponsorship = ss_get_recurring_sponsorship( $sponsorship );
            $recurring_sponsorship->calculate_expiry_date();
            // Only 1 package for now
            break;
        }
    }

	/**
     * Create a Subscription from Package
     *
	 * @param Package     $package     Sponsorship Package
     * @param Sponsorship $sponsorship Sponsorship
     * @param string      $customer_id Stripe Customer ID
     * @param object      $intent      Stripe Payment Intent
	 */
    public function create_subscription_from_package( $package, $sponsorship, $customer_id, $intent ) {

	    $duration      = $package->get_data( 'recurring_duration', 1 );
	    $duration_unit = $package->get_data( 'recurring_duration_unit', 'day' );
	    $amount        = $package->get_price( true );

	    $plan_args = array(
            'name'           => $package->get_data('title'),
            'price'          => $amount,
            'interval'       => $duration_unit,
            'interval_count' => $duration
        );

	    $plan_id = $this->get_or_create_plan( $plan_args );

        if ( is_wp_error( $plan_id ) ) {
            throw new Exception( $plan_id->get_error_code(), $plan_id->get_error_message() );
        }

	    $base_date    = current_time( 'mysql' );
	    $timezone     = get_option( 'timezone_string' );
	    $timezone     = ! empty( $timezone ) ? $timezone : 'UTC';
	    $datetime     = new \DateTime( $base_date, new \DateTimeZone( $timezone ) );
	    $current_time = getdate();
	    $datetime->setTime( $current_time['hours'], $current_time['minutes'], $current_time['seconds'] );
	    $start_date = $datetime->getTimestamp() - HOUR_IN_SECONDS; // Reduce by 60 seconds to account for inaccurate server times.

        $future_timestamp = 0;
        switch ( $duration_unit ) {
            case 'day':
                $future_timestamp = DAY_IN_SECONDS;
                break;
            case 'month':
                $future_timestamp = MONTH_IN_SECONDS;
                break;
            case 'year':
                $future_timestamp = YEAR_IN_SECONDS;
                break;
        }

        $future_timestamp = $future_timestamp * $duration;

        $future_date = $start_date + $future_timestamp;

	    $sub_args = array(
		    'default_payment_method' => $intent->payment_method,
		    'customer'               => $customer_id,
		    'plan'                   => $plan_id,
		    'proration_behavior'     => 'none',
		    'metadata'               => array(
			    'package'     => $package->get_id(),
			    'user_id'     => $sponsorship->get_data( '_user_id', 0 ),
			    'sponsorship' => $sponsorship->get_id(),
		    )
	    );

	    if ( $future_date > $start_date ) {
	        $sub_args['billing_cycle_anchor'] = $future_date;
        }

	    /**
	     * Filters the Stripe subscription arguments.
	     *
	     * @param array  $sub_args
	     * @param Stripe   $this
	     */
	    $sub_args = apply_filters( 'ss_stripe_create_subscription_args', $sub_args, $this, $sponsorship, $customer_id, $package );


	    return $this->create_subscription( $sub_args );
    }

	/**
	 * @param Package $package
	 */
    public function get_or_create_plan( $args ) {
	    $args = wp_parse_args( $args, array(
		    'name'           => '',
		    'price'          => 0.00,
		    'interval'       => 'month',
		    'interval_count' => 1,
		    'currency'       => strtolower( ss_get_currency() ),
		    'id'             => ''
	    ) );

	    // Name and price are required.
	    if ( empty( $args['name'] ) || empty( $args['price'] ) ) {
		    return new \WP_Error( 'missing_name_price', __( 'Missing plan name or price.', 'simple-sponsorships-premium' ) );
	    }

	    /*
		 * Create a new object that looks like a membership level object.
		 * We do this because generate_plan_id() expects a membership level object but we
		 * don't actually have one.
		 */
	    if ( empty( $args['id'] ) ) {
		    $plan_level                = new \stdClass();
		    $plan_level->name          = $args['name'];
		    $plan_level->price         = $args['price'];
		    $plan_level->duration      = $args['interval_count'];
		    $plan_level->duration_unit = $args['interval'];
		    $plan_id                   = $this->generate_plan_id( $plan_level );
	    } else {
		    $plan_id = $args['id'];
	    }

	    if ( empty( $plan_id ) ) {
		    return new \WP_Error( 'empty_plan_id', __( 'Empty plan ID.', 'simple-sponsorships' ) );
	    }

	    // Convert price to Stripe format.
	    $price = round( $args['price'] * 100, 0 );

	    try {

		    $package_plan = isset( $plan_level ) ? $plan_level : new \stdClass();

		    /**
		     * Filters the ID of the plan to check for. If this exists, the new subscription will
		     * use this plan.
		     *
		     * @param string $plan_id      ID of the Stripe plan to check for.
		     * @param object $package_plan Packcage object.
		     */
		    $existing_plan_id = apply_filters( 'ss_stripe_existing_plan_id', $plan_id, $package_plan );

		    $plan = $this->get_plan( $existing_plan_id );

		    if ( isset( $plan->id ) ) {
			    return $plan->id;
		    }

	    } catch ( \Exception $e ) {
	    }

	    try {

		    $product = $this->create_product( $args );
		    $plan    = $this->create_plan( $plan_id, $product->id, $price, $args );

		    // plan successfully created
		    return $plan->id;

	    } catch ( \Exception $e ) {

		    return new \WP_Error( 'stripe_exception', sprintf( 'Error creating Stripe plan. Code: %s; Message: %s', $e->getCode(), $e->getMessage() ) );
	    }
    }

	/**
	 * Generate a Stripe plan ID string based on a membership level
	 *
	 * The plan name is set to {levelname}-{price}-{duration}{duration unit}
	 * Strip out invalid characters such as '@', '.', and '()'.
	 * Similar to WP core's sanitize_html_class() & sanitize_key() functions.
     *
     * Copied from Restrict Content Pro
	 *
	 * @param Package $package
	 *
	 * @since 1.6.0
	 * @return string
	 */
	private function generate_plan_id( $plan ) {

		$level_name = strtolower( str_replace( ' ', '', sanitize_title_with_dashes( $plan->name ) ) );
		$plan_id    = sprintf( '%s-%s-%s', $level_name, $plan->price, $plan->duration . $plan->duration_unit );
		$plan_id    = preg_replace( '/[^a-z0-9_\-]/', '-', $plan_id );

		return $plan_id;

	}

	/**
     * Create a Plan
     *
	 * @param $plan_id
	 * @param $product_id
	 * @param $price
	 * @param $args
	 */
	public function create_plan( $plan_id, $product_id, $price, $args ) {
		$result = Stripe_API::request(
			[
				'id'        => $plan_id,
				'product'        => $product_id,
				"amount"         => $price,
				"interval"       => $args['interval'],
				"interval_count" => $args['interval_count'],
				"currency"       => $args['currency'],
			],
			'plans'
		);

		return $result;
    }

	/**
	 * Create a Subscription
	 *
	 * @param array $args
     *
     * @return object
	 */
	public function create_subscription( $args ) {
		$result = Stripe_API::request(
			$args,
			'subscriptions'
		);

		return $result;
	}


	/**
     * Attach a Payment Method to a Customer
     *
	 * @param string $payment_method
	 * @param string $customer_id
	 */
    public function attach_payment_method( $payment_method, $customer_id ) {
	    $result = Stripe_API::request(
		    [
			    'customer' => $customer_id,
		    ],
		    'payment_methods/' . $payment_method . '/attach'
	    );

	    return $result;
    }

	/**
     * Set the Payment Method as Default
     *
	 * @param string $payment_method
	 * @param string $customer_id
	 */
    public function set_payment_method_as_detault( $payment_method, $customer_id ) {
	    $result = Stripe_API::request(
		    [
			    'invoice_settings' => array(
				    'default_payment_method' => $payment_method,
			    ),
		    ],
		    'customers/' . $customer_id
	    );

	    return $result;
    }

	/**
	 * Create a Product
	 *
	 * @param string $payment_method
	 * @param string $customer_id
	 */
	public function create_product( $args ) {
		$result = Stripe_API::request(
			[
				'name' => $args['name'],
                'type' => 'service'
			],
			'products'
		);

		return $result;
	}

	/**
     * Get a Stripe Plan object
     *
	 * @param $plan_id
	 *
	 * @return array|mixed|\WP_Error
	 */
    public function get_plan( $plan_id ) {
	    $result = Stripe_API::request(
		    [],
		    'plans/' . $plan_id
	    );

	    return $result;
    }

	/**
     * Get or Create a Customer
     *
	 * @param integer $user_id
     * @param Sponsorship $sponsorship
	 */
	public function get_or_create_customer( $user_id, $sponsorship ) {
        $customer_id = get_user_meta( $user_id, '_ss_stripe_customer', true );

        if ( ! $customer_id ) {
            $user          = new \WP_User( $user_id );
            $customer_data = apply_filters( 'ss_stripe_new_customer_args', array(
                'email'    => $user->user_email,
                'metadata' => array(
                    '_user_id' => $user_id
                )
            ), $user_id, $sponsorship );

	        $customer = Stripe_API::request(
		        $customer_data,
		        'customers'
	        );

	        if ( is_wp_error( $customer ) ) {
	            throw new \Exception( $customer->get_error_code(), $customer->get_error_message() );
	        }

	        $customer_id = $customer->id;
        }

        return $customer_id;
    }
	/**
	 * Process webhooks
	 *
	 * @access public
	 * @return void
	 */
	public function process_webhooks() {

		if( ! isset( $_GET['ss-listener'] ) || strtolower( $_GET['ss-listener'] ) != 'stripe' ) {
			return;
		}

		// Ensure listener URL is not cached by W3TC
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}

		try {
			// retrieve the request's body and parse it as JSON
			$body          = @file_get_contents( 'php://input' );
			$event_json_id = json_decode( $body );
			$sig_header    = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : false;
			$expiration    = '';

			// for extra security, retrieve from the Stripe API
			if ( ! isset( $event_json_id->id ) ) {
				die( 'no event ID found' );
			}

			if ( false !== $sig_header ) {
                $this->verify_signature( $sig_header, $body );
			}

			$event_id = $event_json_id->id;

			try {

			    if ( defined( 'SS_TEST_STRIPE_WEBHOOK' ) && SS_TEST_STRIPE_WEBHOOK ) {
                    $event = $event_json_id;
                } else {
				    $event = Stripe_API::request( [], 'events/' . $event_id, 'GET' );
			    }

				if ( is_wp_error( $event ) ) {
				    throw new \Exception( $event->get_error_message() );
                }

				$payment_event = $event->data->object;
				$sponsorship   = false;

				if ( empty( $payment_event->customer ) ) {
					die( 'no customer attached' );
				}

				$invoice = $customer = $subscription = false;

				// Try to get an invoice object from the payment event.
				if ( ! empty( $payment_event->object ) && 'invoice' === $payment_event->object ) {
					$invoice = $payment_event;
				} elseif ( ! empty( $payment_event->invoice ) ) {
					$invoice = Stripe_API::request([], 'invoices/' .  $payment_event->invoice );
				}

				// Now try to get a subscription from the invoice.
				if ( ! empty( $invoice->subscription ) ) {
					$subscription = Stripe_API::request([], 'subscriptions/' .  $invoice->subscription );
				}

				// We can also get the subscription by the object ID in some circumstances.
				if ( empty( $subscription ) && false !== strpos( $payment_event->id, 'sub_' ) ) {
					$subscription = Stripe_API::request([], 'subscriptions/' .  $payment_event->id );
				}

				// Retrieve the membership by subscription ID.
				if ( ! empty( $subscription ) && ! is_wp_error( $subscription ) ) {
				    $db = new DB_Sponsorships();
				    $sponsorships = $db->get_by_meta( 'ss_stripe_subscription_id', $subscription->id );
				    $sponsorship  = $sponsorships ? ss_get_sponsorship( $sponsorships[0]['ID'] ) : false;
				}

				// Retrieve the membership by payment meta (one-time charges only).
				if ( ! empty( $payment_event->metadata->sponsorship ) ) {
					$sponsorship = ss_get_sponsorship( $payment_event->metadata->sponsorship );
				}

				if ( ! $sponsorship instanceof Sponsorship ) {

					die( 'no sponsorship ID found' );
				}

				if ( $event->type == 'charge.succeeded' || $event->type == 'invoice.payment_succeeded' ) {

				    $transaction_id = '';
				    $amount         = '';

					if ( $event->type == 'charge.succeeded' ) {

						// Successful one-time payment
						if ( empty( $payment_event->invoice ) ) {

							$amount         = $payment_event->amount / 100;
							$transaction_id = $payment_event->id;

							// Successful subscription payment
						} else {

							$amount         = $invoice->amount_due / 100;
							$transaction_id = $payment_event->id;
						}

					}

					if ( $transaction_id ) {
						$db           = new DB_Sponsorships();
						$sponsorships = $db->get_by_column( 'transaction_id', $transaction_id );
                        if ( $sponsorships ) {
                            $the_sponsorship = ss_get_sponsorship( $sponsorships[0]['ID'] );
                            if ( ! $the_sponsorship->is_paid() ) {
                                $this->complete( $the_sponsorship );
                            }
                            die( 'duplicate payment' );
                        }

                        // this is a subscription
                        if ( ! empty( $subscription ) && function_exists( 'ss_get_recurring_sponsorship' ) ) {

	                        if ( ! ss_sponsorship_can_have_recurring( $sponsorship ) ) {
		                        return;
	                        }

	                        $sponsorship->update_data( 'type', 'recurring' );
                            $parent_sponsorship = ss_get_recurring_sponsorship( $sponsorship );
	                        $parent_sponsorship->calculate_expiry_date( current_time( 'mysql' ) );

                            $args = array(
                                'amount'         => $amount,
                                'transaction_id' => $transaction_id
                            );

	                        $recurring_sponsorship = ss_create_recurring_sponsorship( $sponsorship, $args );

	                        if ( ! $recurring_sponsorship ) {
		                        // Sponsorship could not be created.
		                        return;
	                        }

	                        $this->complete( $recurring_sponsorship );
	                        $parent_sponsorship->update_recurring_status( 'active' );
                        }

                        do_action( 'ss_gateway_payment_processed', $recurring_sponsorship, $this );
                        do_action( 'ss_stripe_charge_succeeded', $customer->get_user_id(), $recurring_sponsorship, $event );

                        die( 'ss_stripe_charge_succeeded action fired successfully' );

					}
				}

				// failed payment
				if ( $event->type == 'invoice.payment_failed' ) {
					$db = new DB_Sponsorships();
					// Make sure this invoice is tied to a subscription and is the user's current subscription.
					if ( ! empty( $event->data->object->subscription ) && function_exists( 'ss_get_recurring_sponsorship' ) ) {

						$sponsorships = $db->get_by_meta( 'ss_stripe_subscription_id', $event->data->object->subscription );
						$sponsorship  = $sponsorships ? ss_get_sponsorship( $sponsorships[0]['ID'] ) : false;

						if ( $sponsorship ) {
							$recurring_sponsorship = ss_get_recurring_sponsorship( $sponsorship );
							$recurring_sponsorship->update_recurring_status( 'pending' );
						}

						do_action( 'ss_recurring_payment_failed', $sponsorship, $this );
					} elseif ( ! empty( $event->data->object->charge ) ) {
						$sponsorships = $db->get_by_column( 'transaction_id', $event->data->object->charge );
						$sponsorship  = $sponsorships ? ss_get_sponsorship( $sponsorships[0]['ID'] ) : false;
						if ( $sponsorship ) {
							$sponsorship->set_status( 'on-hold' ); // Not a subscription
						}
                    }

					do_action( 'ss_stripe_charge_failed', $payment_event, $event, $sponsorship );

					die( 'ss_stripe_charge_failed action fired successfully' );

				}

				// Cancelled / failed subscription
				if ( $event->type == 'customer.subscription.deleted' && function_exists( 'ss_get_recurring_sponsorship' ) ) {
					$db           = new DB_Sponsorships();

					if ( $payment_event->id ) {
						$sponsorships = $db->get_by_meta( 'ss_stripe_subscription_id', $payment_event->id );
						$sponsorship  = $sponsorships ? ss_get_sponsorship( $sponsorships[0]['ID'] ) : false;

						if ( $sponsorship ) {
						    $recurring_sponsorship = ss_get_recurring_sponsorship( $sponsorship );
						    $recurring_sponsorship->cancel();
                        }


						do_action( 'ss_webhook_cancel', $sponsorship, $this );

						die( 'recurring payment cancelled successfully' );

					}

				}

				do_action( 'ss_stripe_' . $event->type, $payment_event, $event );


			} catch ( \Exception $e ) {
				die( 'PHP exception: ' . $e->getMessage() );
			}
		} catch ( \Exception $e ) {
			die( 'PHP exception: ' . $e->getMessage() );
        }

		die( '1' );

	}

	/**
     * Verify signagure from Stripe
	 * @param string $signature
     * @param string $payload Received body of the webhook
	 */
	protected function verify_signature( $signature, $payload ) {

	    $signing_secret = isset( $this->settings['stripe_webhook_secret'] ) ? $this->settings['stripe_webhook_secret'] : '';

	    // Use did not create a webhook or forgot to enter the secret.
	    if ( ! $signing_secret ) {
	        return;
        }

        $signature_array = explode( ',', $signature );

        if ( count( $signature_array ) < 2 ) {
            throw new \Exception( 'incorrect-signature', __( 'Signature is not correct', 'simple-sponsorships' ) );
        }

        $timestamp = '';
        $scheme    = '';

        foreach ( $signature_array as $sig_value ) {
            $sig_value_arr = explode( '=', $sig_value );
            foreach ( $sig_value_arr as $sig_key => $sig_val ) {
                if ( 't' === $sig_key ) {
                    $timestamp = $sig_val;
                }

                if ( 'v1' === $sig_key ) {
                    $scheme = $sig_val;
                }
            }
        }

        if ( ! $timestamp || ! $scheme ) {
	        throw new \Exception( 'incorrect-signature', __( 'Signature is not correct', 'simple-sponsorships' ) );
        }

        $signed_payload = $timestamp . '.' . $payload;
        $new_signature  = hash_hmac( 'sha256', $signed_payload, $signing_secret );

        if ( $signature !== $new_signature ) {
	        throw new \Exception( 'incorrect-signature', __( 'Signature is not correct', 'simple-sponsorships' ) );
        }
    }
}