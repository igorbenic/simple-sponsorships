<?php
/**
 * Sponsor Information Metabox
 */

$sponsor = new \Simple_Sponsorships\Sponsor( 0 );
$sponsor->populate_from_post( $post );

wp_nonce_field( 'ss_sponsor_nonce', 'ss_sponsor_nonce' );

foreach ( $fields as $field_name => $field ) {
	$field['value'] = $sponsor->get_data( $field_name );
	ss_form_render_field( $field );
}