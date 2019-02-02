'use strict';
import { startContentSponsorsDropdown, showSponsorSelect } from './admin/sponsors';
import { ssFieldsHideShow } from './admin/fields';
(function($){
    $(function(){
        ssFieldsHideShow();
        startContentSponsorsDropdown();
        showSponsorSelect();
        $( document ).on( 'change', '#ss_sponsorships\\[sponsor\\]', showSponsorSelect );

        if ( $( '.ss-colorpicker' ).length ) {
            $( '.ss-colorpicker' ).wpColorPicker();
        }
    });
})(jQuery);