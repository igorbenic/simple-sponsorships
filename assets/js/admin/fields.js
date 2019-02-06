'use strict';
import Escape from '../vendors/sizze-escape.js';
var $ = window.jQuery;

function ssFieldShowHide( field ) {
    if ( ! field.hasClass('ss-field-row') ) {
        field = field.parents('.ss-field-row');
    }

    var row_id    = field.attr('id'),
        field_id  = row_id.substring( 0, row_id.lastIndexOf("_row") ).replace( '[', '\\[').replace( ']', '\\]'),
        field_val = $( '#' + field_id ).val();

    var hide_class = 'hide_if_' + field_id + '_' + field_val,
        show_class = 'show_if_' + field_id + '_' + field_val;

    $('[class*="show_if_' + field_id + '"]').addClass('ss-hidden');
    $('[class*="hide_if_' + field_id + '"]').removeClass('ss-hidden');

    $( '.' + Escape( show_class ) ).removeClass( 'ss-hidden' );
    $( '.' + Escape( hide_class ) ).addClass( 'ss-hidden' );
}

export function ssFieldsHideShow() {
    if ( $( '.ss-field-row' ).length ) {
        $( '.ss-field-row' ).each( function() {
            ssFieldShowHide( $(this) );
        });
    }

    $( document ).on( 'change', '.ss-field-row :input', function(){
        ssFieldShowHide( $(this) );
    });
}

