'use strict';

var $ = window.jQuery;

/**
 * Attaching the Button Events for SS Button Action.
 */
export function attachButtonEvents() {

    $( document ).on( 'click', '.ss-button-action', function(e){
        e.preventDefault();
        var button = this,
            $button = $(this),
            action  = $button.attr('data-action') || false,
            success = $button.attr('data-success') || false,
            data    = {};

        // Adding all attributes, removing 'data-' from data-attributes and passing them to AJAX also in data.
        if ( button.attributes.length ) {
            for ( var i = 0; i < button.attributes.length; i++ ) {
                data[ button.attributes[ i ].name.replace('data-', '' ) ] = button.attributes[ i ].value;
            }
        }

        if ( action ) {
            $.ajax({
                url: ss_admin.ajax,
                data: { nonce: ss_admin.nonce, action: action, data: data },
                success: function( resp ) {
                    if ( success && typeof window[ success ] === 'function' ) {
                        var fc = window[success];
                        fc( resp, $button );
                    }
                }

            });
        }
    });
}
