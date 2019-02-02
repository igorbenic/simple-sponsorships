'use strict';
import { startContentSponsorsDropdown, showSponsorSelect } from './admin/sponsors';
import { ssFieldsHideShow } from './admin/fields';
import ssFilterStart from './admin/filters';

(function($){
    $(function(){
        ssFieldsHideShow();
        startContentSponsorsDropdown();
        showSponsorSelect();
        ssFilterStart();
        $( document ).on( 'change', '#ss_sponsorships\\[sponsor\\]', showSponsorSelect );

        if ( $( '.ss-colorpicker' ).length ) {
            $( '.ss-colorpicker' ).wpColorPicker();
        }
    });
})(jQuery);