<?php
/**
 * Metabox for other Content to add Sponsors to it.
 */

wp_nonce_field( 'ss_sponsor_nonce', 'ss_sponsor_nonce' );

?>
<input type="text" class="widefat ss-autocompleter-input" name="ss-search-sponsor" placeholder="<?php esc_attr_e( 'Search for a sponsor', 'simple-sponsorships' ); ?>"/>
<input type="hidden" name="ss_sponsors" value="" />
<ul class="ss-connected-sponsors ss-autocompleter-selection"></ul>
<!--<select class="ss-enhanced-select widefat" id="ssContentSponsors"></select>-->
