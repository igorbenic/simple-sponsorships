<?php

use Simple_Sponsorships\Form_Payment;
use Simple_Sponsorships\Templates;

$sponsorship = isset( $args['sponsorship'] ) ? $args['sponsorship'] : false;

if ( ! $sponsorship ) {
	return;
}

if ( ! ss_payments_enabled() ) {
    return;
}

$gateways           = SS()->payment_gateways();
$available_gateways = $gateways->get_available_payment_gateways();

// We don't need a payment form if we don't have gateways.
if ( ! $available_gateways ) {
    return;
}

ss_print_notices();

$form = new Form_Payment();

do_action( 'ss_before_sponsor_form' );

?>
    <form class="ss-sponsor-form" method="POST" action="">
		<?php
		do_action( 'ss_before_sponsor_form_fields' );

		wp_nonce_field( 'ss_sponsor_form', 'ss_nonce' );
		foreach ( $form->get_fields() as $slug => $field ) {
			$field['id'] = isset( $field['id'] ) ? $field['id'] : $slug;
            $field['value'] = $sponsorship->get_data( $field['id'] );
			ss_form_render_field( $field );
		}

		do_action( 'ss_after_sponsor_form_fields' );

		if ( $available_gateways ) {
			if ( count( $available_gateways ) ) {
				current( $available_gateways )->set_current();
			}
		    ?>
            <h3><?php esc_html_e( 'Payment Methods', 'simple-sponsorships' ); ?></h3>
            <ul class="ss-payment-gateways">
            <?php
		    foreach ( $available_gateways as $gateway ) {
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

do_action( 'ss_after_sponsor_form' );
