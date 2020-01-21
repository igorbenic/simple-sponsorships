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

$user_id = $sponsor->get_data( '_user_id', 0 );

if ( $user_id ) {
	?>
	<p><?php esc_html_e( 'User:', 'simple-sponsorships' ); ?> <a href="<?php esc_url( admin_url( 'user-edit.php?user_id=' . absint( $user_id ) ) ); ?>"><?php echo absint( $user_id ); ?></a></p>
	<?php
}