'use strict';

var $ = window.jQuery;

/**
 * Remove the Package Features
 *
 * @param resp
 * @param button
 */
function ssRemovePackageFeature( resp, button ) {
    if ( resp.success ) {
        button.parent().remove();
    }
}

export function ssMakePackageFeaturesSortable() {

    if ( $('.ss-package-features').length ) {
        $('.ss-package-features').sortable({
            handle: '.ss-sortable-handle'
        });
    }
}

function addPackageFeature( resp, button ) {
    if ( resp.success ) {
        var featureHTML = $('#ss_package_feature_template').html();
        $('.ss-package-features').append( featureHTML );
    }
}

window.ssponsorships = window.ssponsorships || {};
window['ssponsorships']['removePackageFeature'] = ssRemovePackageFeature;
window['ssponsorships']['addPackageFeature']    = addPackageFeature;
