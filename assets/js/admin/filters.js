'use strict';

var $ = window.jQuery;

export default function filterStart() {
    $( document.body ).on( 'click', '.ss-button-table-list-filter', function(e){
        e.preventDefault();

        var parent = $(this).parent(),
            url   = window.location.href,
            parts = url.split('?'),
            query = parts[1],
            qparams = query.split('&'),
            params = {};

        for( var i = 0; i < qparams.length; i++ ){
            var strings = qparams[i].split('=');
            params[ strings[0] ] = strings[1];
        }

        parent.find('.ss-filter').each(function(){
            var name = $(this).attr('name'),
                value = $(this).val();

            if ( value ) {
                params[ name ] = value;
            } else {
                delete params[ name ];
            }
        });

        delete params[ 'paged' ];

        var params_array = [];
        for( var key in params ) {
            params_array.push(key + '=' + params[key] );
        }
        var location = parts[0] + '?' + params_array.join('&');

        window.location.href = location;
    });
}