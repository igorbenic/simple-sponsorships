'use strict';
(function($){
    $(function(){

        showSponsorSelect();
        $( document ).on( 'change', '#ss_sponsorships\\[sponsor\\]', showSponsorSelect );

    });

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
})(jQuery)