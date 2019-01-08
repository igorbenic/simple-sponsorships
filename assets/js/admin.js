'use strict';
(function($){
    $(function(){

        showSponsorSelect();
        appendSponsorStatuses();
        $( document ).on( 'change', '#ss_sponsorships\\[sponsor\\]', showSponsorSelect );

    });

    /**
     * This will show or hide the Sponsors box on Sponsorships New/Edit screens.
     */
    function showSponsorSelect() {
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

    /**
     * This will append the Active/Inactive Sponsor Statuses on Sponsor CPT screens.
     */
    function appendSponsorStatuses() {
        if ( $('#post-status-select').length ) {
            var current_status = $('#hidden_post_status').val();
            var statuses = ss_admin.statuses;
            var custom_status = false;
            if ( statuses ) {
                var options = '';
                for ( var status in statuses ) {
                    if ( current_status === status ) {
                        custom_status = true;
                    }
                    options += '<option value="' + status + '">' + statuses[ status ] + '</option>';
                }
                $('#post-status-select #post_status').append( options );
                $('#post-status-select #post_status').val( current_status );
                if ( custom_status ) {
                    $( '#publish' ).attr( 'name', 'save' );
                    $( '#publish' ).val( 'Update' );
                    $('#post-status-display').html( statuses[ current_status ] );
                }
            }
        }
    }
})(jQuery)