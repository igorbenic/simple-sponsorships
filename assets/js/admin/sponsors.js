'use strict';
import autoComplete from '../vendors/autocomplete';
//import { FormTokenField, Button } from '@wordpress/components';
var $ = window.jQuery;

let currentFoundSponsors = [];

/**
 * This function will add the sponsor_id to the rest of the IDs.
 * @param sponsor_id
 */
function addContentSponsor( sponsor_id ) {
    var ret = false;
    $( '[name=ss_sponsors]' ).each(function(){
       var ids = $(this).val();
       if ( ! ids ) {
           ids = []
       } else {
           ids = ids.split(',');
       }
       if ( ! ids.includes( sponsor_id ) ) {
           ids.push( sponsor_id );
           $(this).val(ids.join(','));
           ret = true;
       }
    });
    return ret;
}

/**
 * This function will remove the sponsor_id to the rest of the IDs.
 * @param sponsor_id
 */
function removeContentSponsor( sponsor_id ) {
    var ret = false;
    $( '[name=ss_sponsors]' ).each(function(){
        var ids = $(this).val();
        if ( ! ids ) {
            ids = []
        } else {
            ids = ids.split(',');
        }
        if ( ids.includes( sponsor_id ) ) {
            ids.splice( ids.indexOf( sponsor_id ), 1 );
            $(this).val(ids.join(','));
            ret = true;
        }
    });
    return ret;
}

/**
 * Display the Content Sponsor
 * @param sponsor
 */
function displayContentSponsor( sponsor, container ) {
    var html = '<li>' + sponsor.title.rendered + '<button type="button" data-id="' + sponsor.id + '" class="ss-remove-sponsor-content">x</button></li>';
    container.append( html );
}


/**
 * Todo: remove sponsor from input.
 * Selected item show.
 */

/**
 * Start the Content Sponsors Dropdown.
 */
export function startContentSponsorsDropdown() {
    if ( $('[name=ss-search-sponsor]').length ) {

        new autoComplete({
            selector: 'input[name="ss-search-sponsor"]',
            minChars: 2,
            menuClass: 'ss-autocompleter',
            source: function( term, response ) {
                console.log(term);
                $.ajax({
                    url: '/wp-json/wp/v2/sponsors',
                    data: { search: term },
                    success: function( resp ) {
                        currentFoundSponsors = resp;

                        response( resp.map( item => item.title.rendered ));
                    }
                });
            },
            onSelect: function( event, term, item ) {
                if ( currentFoundSponsors.length ) {
                    for ( var i = 0; i < currentFoundSponsors.length; i++ ) {
                        var sponsor = currentFoundSponsors[ i ];
                        if ( term === sponsor.title.rendered ) {
                            var added = addContentSponsor( sponsor.id );
                            if ( added ) {
                                displayContentSponsor( sponsor, $( item ).parents('.inside').find('.ss-connected-sponsors') );
                            }
                            break;
                        }
                    }
                }
                $( item ).parents('.inside').find('[name=ss-search-sponsor]').val('');
            }
        });
        $( document ).on( 'click', '.ss-remove-sponsor-content', function(){
            var id = $(this).attr( 'data-id' );
            if ( removeContentSponsor( id ) ) {
                $(this).parent().remove();
            }
        });
    }
}

/**
 * This will show or hide the Sponsors box on Sponsorships New/Edit screens.
 */
export function showSponsorSelect() {
    var sponsorSelect = $('#ss_sponsorships\\[sponsor\\]');
    if ( sponsorSelect.length ) {
        if ( 'new' !== sponsorSelect.val() ) {
            $( '.hide-if-sponsor').each(function(){
                $(this).parent().parent().hide();
            });
        } else {
            $( '.hide-if-sponsor').each(function(){
                $(this).parent().parent().show();
            });
        }
    }
}

