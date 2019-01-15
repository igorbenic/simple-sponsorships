<?php
/**
 * Metabox for other Content to add Sponsors to it.
 */

wp_nonce_field( 'ss_sponsor_nonce', 'ss_sponsor_nonce' );
$sponsors = get_post_meta( $post->ID, '_ss_sponsor', false );
if ( ! $sponsors ) {
    $sponsors = array();
}
?>
<input type="text" class="widefat ss-autocompleter-input" name="ss-search-sponsor" placeholder="<?php esc_attr_e( 'Search for a sponsor', 'simple-sponsorships' ); ?>"/>
<input type="hidden" name="ss_sponsors" value="<?php echo esc_attr( implode( ',', $sponsors ) ); ?>" />
<ul class="ss-connected-sponsors ss-autocompleter-selection">
    <?php
    if ( $sponsors ) {
        foreach ( $sponsors as $sponsor_id ) {
	        echo '<li>'
                 . get_the_title( $sponsor_id )
                 . '<button type="button" data-id="' . $sponsor_id . '" class="ss-remove-sponsor-content">x</button></li>';
        }
    }
    ?>
</ul>
