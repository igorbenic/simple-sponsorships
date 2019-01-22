'use strict';
import { startContentSponsorsDropdown, showSponsorSelect } from './admin/sponsors';
(function($){
    $(function(){
        startContentSponsorsDropdown();
        showSponsorSelect();
        $( document ).on( 'change', '#ss_sponsorships\\[sponsor\\]', showSponsorSelect );

        if ( $( '.ss-colorpicker' ).length ) {
            $( '.ss-colorpicker' ).wpColorPicker();
        }
    });
})(jQuery)