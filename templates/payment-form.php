<?php

use Simple_Sponsorships\Form_Payment;
use Simple_Sponsorships\Templates;

$sponsorship = isset( $args['sponsorship'] ) ? $args['sponsorship'] : false;

if ( ! $sponsorship ) {
	return;
}

echo '<h3>' . esc_html__( 'Payment Information', 'simple-sponsorships' ) . '</h3>';

if ( ! ss_payments_enabled() ) {
    $instructions = ss_get_option( 'payment_instructions', __( 'We will contact you with specific information through email.', 'simple-sponsorships' ) );
    if ( $instructions ) {
        echo '<p><strong>' . __( 'Payment Instructions', 'simple-sponsorships' ) . '</strong></p>';
	    echo $instructions;
    }

    return;
}

$gateways = SS()->payment_gateways();
$gateways->set_sponsorship( $sponsorship );
$available_gateways = $gateways->get_available_payment_gateways();

// We don't need a payment form if we don't have gateways.
if ( ! $available_gateways ) {
    return;
}

ss_print_notices();

$form = new Form_Payment();

do_action( 'ss_before_payment_form' );

?>
    <form class="ss-payment-form" method="POST" action="">
		<?php
		do_action( 'ss_before_payment_form_fields', $sponsorship );

		wp_nonce_field( 'ss_sponsor_form', 'ss_nonce' );
		foreach ( $form->get_fields() as $slug => $field ) {
			$field['id'] = isset( $field['id'] ) ? $field['id'] : $slug;
            $field['value'] = $sponsorship->get_data( $field['id'] );
			ss_form_render_field( $field );
		}

		do_action( 'ss_after_payment_form_fields', $sponsorship );

		if ( $available_gateways ) {
		    $chosen_gateway = $sponsorship->get_data('gateway');

			if ( ! $chosen_gateway && count( $available_gateways ) ) {
				current( $available_gateways )->set_current();
			}

		    ?>
            <h3><?php esc_html_e( 'Payment Methods', 'simple-sponsorships' ); ?></h3>
            <ul class="ss-payment-gateways">
            <?php
		    foreach ( $available_gateways as $gateway_id => $gateway ) {
		        if ( $chosen_gateway === $gateway_id ) {
		            $gateway->set_current();
                }
		        Templates::get_template_part(
		                'payment-method',
                        null,
                        array( 'method' => $gateway )
                );
            }
            ?>
            </ul>
            <?php
        }
		?>
        <input type="hidden" name="ss-action" value="payment_form"/>
        <input type="hidden" name="ss_sponsorship_id" value="<?php echo esc_attr( $sponsorship->get_id() ); ?>" />

        <button type="submit" name="ss_sponsor_form_submit" class="button ss-button">
			<?php esc_html_e( 'Complete', 'simple-sponsorships' ); ?>
        </button>
    </form>
<?php

do_action( 'ss_after_payment_form' );
