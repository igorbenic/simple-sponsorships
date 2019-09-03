'use strict';
import { startContentSponsorsDropdown, showSponsorSelect, updateSponsorQuantityColumnOnAjax } from './admin/sponsors';
import { ssFieldsHideShow } from './admin/fields';
import ssFilterStart from './admin/filters';
import { attachButtonEvents } from './admin/buttons';
import './admin/sponsorships';
import { ssMakePackageFeaturesSortable } from './admin/packages';

window.ssponsorships = window.ssponsorships || {};
(function($){
    $(function(){
        attachButtonEvents();
        ssFieldsHideShow();
        startContentSponsorsDropdown();
        showSponsorSelect();
        ssMakePackageFeaturesSortable();
        ssFilterStart();
        $( document ).on( 'change', '.ss-view-sponsorship #sponsor', showSponsorSelect );

        if ( $( '.ss-colorpicker' ).length ) {
            $( '.ss-colorpicker' ).wpColorPicker();
        }

        if ( $( '.ss-datepicker' ).length ) {
            $( '.ss-datepicker' ).datepicker({ dateFormat: 'yy-mm-dd' });
        }

        $( document ).on( 'click', '.ss-button-integration-deactivate', function( e ){
            e.preventDefault();

            var integration = $(this).attr('data-integration'),
                $this = $(this);
            if( integration ) {
                $.ajax({
                    url: ss_admin.ajax,
                    dataType: 'json',
                    type: 'POST',
                    data: { action: 'ss_deactivate_integration', integration: integration, nonce: ss_admin.nonce },
                    success: function(resp) {
                        if( resp.success ) {
                            $this.removeClass('ss-button-integration-deactivate')
                                .removeClass('button-default')
                                .addClass('ss-button-integration-activate')
                                .addClass('button-primary')
                                .html( ss_admin.text.activate );
                        }
                    }
                });
            }

        });

        $( document ).on( 'click', '.ss-button-integration-activate', function( e ){
            e.preventDefault();

            var integration = $(this).attr('data-integration'),
                $this = $(this);

            if( integration ) {
                $.ajax({
                    url: ss_admin.ajax,
                    dataType: 'json',
                    type: 'POST',
                    data: { action: 'ss_activate_integration', integration: integration, nonce: ss_admin.nonce },
                    success: function(resp) {
                        if( resp.success ) {
                            $this.addClass('ss-button-integration-deactivate')
                                .addClass('button-default')
                                .removeClass('ss-button-integration-activate')
                                .removeClass('button-primary')
                                .html( ss_admin.text.deactivate );
                        }
                    }
                });
            }

        });

    });
})(jQuery);