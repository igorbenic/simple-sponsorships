<?php

use Simple_Sponsorships\Form_Sponsors;

ss_print_notices();

$form = new Form_Sponsors();
$args = isset( $args ) ? $args : array();

do_action( 'ss_before_sponsor_form' );

?>
<form class="simple-sponsorships ss-sponsor-form" method="POST" action="">
    <?php
    do_action( 'ss_before_sponsor_form_fields', $form, $args );

    wp_nonce_field( 'ss_sponsor_form', 'ss_nonce' );
    foreach ( $form->get_fields() as $slug => $field ) {
        $field['id']    = isset( $field['id'] ) ?  $field['id'] : $slug;
        $field['value'] = apply_filters( 'ss_sponsor_form_field_value', '', $field );

        if ( 'package' === $field['id']
             && isset( $args['packages'] )
             && $args['packages']
             && 'package_select' === $field['type'] ) {

            $package_ids = array_map( 'absint', array_map( 'trim', explode( ',', $args['packages'] ) ) );
            $options     = $field['options'];
            $field['options'] = array();
            if ( isset( $options[0] ) ) {
                $field['options'][0] = $options[0];
            }
            foreach ( $options as $package_id => $package_title ) {
                if ( ! in_array( absint( $package_id ), $package_ids, true ) ) {
                    continue;
                }

                $field['options'][ $package_id ] = $package_title;
            }
        }

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
