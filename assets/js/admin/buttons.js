'use strict';

var $ = window.jQuery;

function getFunction( name ) {
    if ( name ) {
        var ss = window['ssponsorships'];
        if ( ss && typeof ss[ name ] === 'function' ) {
            return ss[ name ];
        }

        if ( typeof window[ name ] === 'function' ) {
            return window[ name ];
        }
    }

    return false;
}

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
                    var fc = getFunction( success );
                    if ( false !== fc ) {
                        fc( resp, $button );
                    }

                }

            });
        } else {
            var fc = getFunction( success );
            if ( false !== fc ) {
                fc( data, $button );
            }
        }
    });
}
