<?php

use Simple_Sponsorships\Form_Sponsors;

ss_print_notices();

$form = new Form_Sponsors();

do_action( 'ss_before_sponsor_form' );

?>
<form class="ss-sponsor-form" method="POST" action="">
    <?php
    do_action( 'ss_before_sponsor_form_fields' );

    wp_nonce_field( 'ss_sponsor_form', 'ss_nonce' );
    foreach ( $form->get_fields() as $slug => $field ) {
        $field['id'] = isset( $field['id'] ) ?  $field['id'] : $slug;

        ss_form_render_field( $field );
    }

    do_action( 'ss_after_sponsor_form_fields' );
    ?>
    <input type="hidden" name="ss-action" value="sponsor_form" />

    <button type="submit" name="ss_sponsor_form_submit" class="button ss-button">
        <?php esc_html_e( 'Submit', 'simple-sponsorships' ); ?>
    </button>
</form>
<?php

do_action( 'ss_after_sponsor_form' );
