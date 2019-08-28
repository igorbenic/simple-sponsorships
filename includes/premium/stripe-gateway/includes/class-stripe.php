<?php
/**
 * Stripe implementation
 */
namespace Simple_Sponsorships\Gateways;
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
			)
		);
	}

	public function payment_fields() {
		parent::payment_fields();
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

	    $payment_intent_id = $sponsorship->get_data( '_stripe_payment_intent', false );

	    if ( false === $payment_intent_id ) {
		    return new \WP_Error( 'no-stripe-intent', __( 'No Payment Intent information from Stripe', 'simple-sponsorships-premium' ) );
        }

        $payment_intent = Stripe_API::request( [], 'payment_intents/' .$payment_intent_id, 'GET' );

	    if ( ! $payment_intent ) {
		    return new \WP_Error( 'no-stripe-intent', __( 'No Payment Intent information from Stripe', 'simple-sponsorships-premium' ) );
	    }

	    if ( is_wp_error( $payment_intent ) ) {
	        return $payment_intent;
        }

        if ( 'succeeded' !== $payment_intent->status ) {
	        return new \WP_Error( 'stripe-status', __( 'Waiting on Payment to succeed', 'simple-sponsorships-premium' ) );
        }

        $this->complete( $sponsorship );

		return array(
			'result'   => 'success',
			'redirect' => '',
		);
	}
}