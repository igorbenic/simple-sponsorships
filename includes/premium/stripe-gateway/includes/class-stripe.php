<?php
/**
 * Stripe implementation
 */
namespace Simple_Sponsorships\Gateways;

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
		$this->method_title = __( 'Stripe', 'simple-sponsorships' );
		$this->title        = __( 'Stripe', 'simple-sponsorships' );
		$this->has_fields   = true;

		$this->get_settings();

		$this->testmode = 'sandbox' === $this->settings['stripe_mode'];

		parent::__construct();
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
				'label'       => __( 'Public Key', 'simple-sponsorships' ),
				'default'     => '',
				'placeholder' => __( 'Your Stripe Public Key', 'simple-sponsorships' ),
			),
			'stripe_secret_key' => array(
				'id'          => 'stripe_secret_key',
				'type'        => 'text',
				'label'        => __( 'Secret Key', 'simple-sponsorships' ),
				'default'     => '',
				'placeholder' => __( 'Your Stripe Secret Key', 'simple-sponsorships' ),
			),
			'stripe_mode' => array(
				'id'          => 'stripe_mode',
				'type'        => 'select',
				'label'        => __( 'Mode', 'simple-sponsorships' ),
				'default'     => 'sandbox',
				'options' => array(
					'sandbox' => __( 'Sandbox', 'simple-sponsorships' ),
					'live'    => __( 'Live', 'simple-sponsorships' ),
				),
			)
		);
	}

	public function payment_fields() {
		parent::payment_fields();
		?>
		<input id="cardholder-name" type="text" />
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
        $sponsorship->set_status( 'paid' );

		return array(
			'result'   => 'success',
			'redirect' => '',
		);
	}
}