<?php

use Simple_Sponsorships\Form_Sponsors;

?>
<form class="ss-sponsor-form" method="POST" action="">
    <?php
        foreach ( Form_Sponsors::get_fields() as $field ) {
            ss_form_render_field( $field );
        }
    ?>
    <button type="button" class="button ss-button">
        <?php esc_html_e( 'Submit', 'simple-sponsorships' ); ?>
    </button>
</form>