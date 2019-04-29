'use strict';

var $ = window.jQuery;

/**
 * Edit the Package in a Sponsorship
 *
 * @param data   JS Object.
 * @param button jQuery object.
 */
function sponsorshipEditPackage( data, button ) {
    var editable = ss_admin.package.editable,
        row      = button.parent().parent();

    for ( var i = 0; i < editable.length; i++ ) {
        var className = editable[ i ],
            element   = row.find( '.' + className );

        element.find('.ss-item-field-text').hide();
        element.find('.ss-field-editable').attr('type', 'text');
    }

    button.attr('data-success', 'sponsorshipUneditPackage')
}

/**
 * Edit the Package in a Sponsorship
 *
 * @param data   JS Object.
 * @param button jQuery object.
 */
function sponsorshipUneditPackage( data, button ) {
    var editable = ss_admin.package.editable,
        row      = button.parent().parent();

    for ( var i = 0; i < editable.length; i++ ) {
        var className = editable[ i ],
            element   = row.find( '.' + className );

        element.find('.ss-item-field-text').show();
        element.find('.ss-field-editable').attr('type', 'hidden');
    }

    button.attr('data-success', 'sponsorshipEditPackage')
}

/**
 * Remove the Package in a Sponsorship
 *
 * @param data   JS Object.
 * @param button jQuery object.
 */
function sponsorshipRemovePackage( data, button ) {
    var row = button.parent().parent(),
        tbody = row.parent();

    row.remove();
    if ( tbody.children().length === 1 ) {
        $( '#addPackage' ).removeClass('ss-hidden');
    }
}

function sponsorshipCalculateTotals( resp, button ) {
    if ( resp.success ) {
        var amount    = resp.data['amount'],
            formatted = resp.data['formatted_amount'];

        $('#amount').val( amount );
        $('.ss-packages.ss-items tfoot .item-amount').html( formatted );
    } else {
        alert( resp.data );
    }
}

window.ssponsorships = window.ssponsorships || {};
window['ssponsorships']['sponsorshipEditPackage'] = sponsorshipEditPackage;
window['ssponsorships']['sponsorshipUneditPackage'] = sponsorshipUneditPackage;
window['ssponsorships']['sponsorshipRemovePackage'] = sponsorshipRemovePackage;
window['ssponsorships']['sponsorshipCalculateTotals'] = sponsorshipCalculateTotals;