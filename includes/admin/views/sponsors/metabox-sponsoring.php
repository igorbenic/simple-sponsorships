<?php
/**
 * Showing the Sponsoring Content
 */

$sponsor = ss_get_sponsor( $post->ID, false );
$sponsor->populate_from_post( $post );

foreach ( $fields as $field_name => $field ) {
	$field['value'] = $sponsor->get_data( $field_name );
	ss_form_render_field( $field );
}

$sponsored_content = $sponsor->get_sponsored_content();
?>
<h3><?php esc_html_e( 'Sponsored Content', 'simple-sponsorhips' ); ?></h3>
<?php

if ( ! $sponsored_content ) {
	echo '<p>' . esc_html__( 'This Sponsor is not yet added to any content', 'simple-sponsorships' ) . '</p>';
} else {
	echo '<table class="ss-sponsor-content">';
	foreach ( $sponsored_content as $content ) {
		echo '<tr>';
		echo '<td><a href="' . admin_url( 'post.php?post=' . $content->ID . '&action=edit' ) . '">' . $content->post_title . '</a></td>';
		echo '<td><button type="button" class="button button-secondary button-small ss-button-action" data-success="ssRemoveSponsorFromContent" data-action="ss_remove_sponsor_from_content" data-content="' . esc_attr( $content->ID ) . '" data-sponsor="'. esc_attr( $sponsor->get_id() ) .'">' . esc_html__( 'Remove', 'simple-sponsorships' ) . '</button></td>';
		echo '</tr>';


	}
	echo '</table>';
}

