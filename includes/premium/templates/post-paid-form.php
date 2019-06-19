<?php

use Simple_Sponsorships\Post_Paid_Form\Form_Post_Paid;

$sponsorship = isset( $args['sponsorship'] ) ? $args['sponsorship'] : false;

if ( ! $sponsorship ) {
	return;
}

$sponsor = $sponsorship->get_sponsor_data();

if ( ! $sponsor->get_id() ) {
    return;
}

ss_print_notices();

$form = new Form_Post_Paid();
$form->set_sponsor( $sponsor );

do_action( 'ss_before_post_paid_form' );

?>
	<form class="ss-post-paid-form" method="POST" action="" enctype="multipart/form-data">
		<?php
		do_action( 'ss_before_post_paid_form_fields' );

		wp_nonce_field( 'ss_post_paid_form', 'ss_post_paid_form_nonce' );
		foreach ( $form->get_fields() as $slug => $field ) {
			$field['id'] = isset( $field['id'] ) ? $field['id'] : $slug;
			ss_form_render_field( $field );
		}

		do_action( 'ss_after_post_paidt_form_fields' );

		?>
		<input type="hidden" name="ss-action" value="post_paid_form"/>
		<input type="hidden" name="ss_sponsorship_id" value="<?php echo esc_attr( $sponsorship->get_id() ); ?>" />
        <input type="hidden" name="ss_sponsor_id" value="<?php echo esc_attr( $sponsor->get_id() ); ?>" />

        <button type="submit" name="ss_sponsor_form_submit" class="button ss-button">
			<?php esc_html_e( 'Update', 'simple-sponsorships-premium' ); ?>
		</button>
	</form>
<?php

do_action( 'ss_after_post_paid_form' );
