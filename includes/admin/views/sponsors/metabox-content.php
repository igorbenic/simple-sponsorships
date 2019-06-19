<?php
/**
 * Metabox for other Content to add Sponsors to it.
 */

wp_nonce_field( 'ss_sponsor_nonce', 'ss_sponsor_nonce' );

$hide_placeholder = get_post_meta( $post->ID, '_ss_hide_placeholder', true );
$sponsors         = get_post_meta( $post->ID, '_ss_sponsor', false );
$availability     = get_post_meta( $post->ID, '_ss_availability', true );

if ( ! $availability ) {
    $availability = 0;
}

if ( ! $sponsors ) {
    $sponsors = array();
}
if ( ! $hide_placeholder ) {
    $hide_placeholder = false;
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


<div class="widefat ss-admin-field">
    <input id="ss_hide_placeholder" <?php checked( $hide_placeholder, '1', true ); ?> type="checkbox" name="ss_hide_placeholder" value="1" style="margin-top:0;" />
    <label for="ss_hide_placeholder"><?php esc_html_e( 'Hide Sponsor Placeholder', 'simple-sponsorships' ); ?></label>
</div>

<div class="widefat ss-admin-field">
    <br/>
    <label for="ss_content_availability"><?php esc_html_e( 'How many Sponsors can this content have?', 'simple-sponsorships' ); ?></label>

    <input class="widefat" id="ss_content_availability" type="number" name="ss_content_availability" value="<?php echo esc_attr( $availability ); ?>" />
    <p class="description"><?php esc_html_e( 'If 0, it will allow unlimited number of sponsors', 'simple-sponsorships' ); ?></p>
</div>