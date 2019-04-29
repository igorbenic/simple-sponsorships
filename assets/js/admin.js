'use strict';
import { startContentSponsorsDropdown, showSponsorSelect, updateSponsorQuantityColumnOnAjax } from './admin/sponsors';
import { ssFieldsHideShow } from './admin/fields';
import ssFilterStart from './admin/filters';
import { attachButtonEvents } from './admin/buttons';
import './admin/sponsorships';

window.ssponsorships = window.ssponsorships || {};
(function($){
    $(function(){
        attachButtonEvents();
        ssFieldsHideShow();
        startContentSponsorsDropdown();
        showSponsorSelect();
        ssFilterStart();
        $( document ).on( 'change', '.ss-view-sponsorship #sponsor', showSponsorSelect );

        if ( $( '.ss-colorpicker' ).length ) {
            $( '.ss-colorpicker' ).wpColorPicker();
        }
    });
})(jQuery);