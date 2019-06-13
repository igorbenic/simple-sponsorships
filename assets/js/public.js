'use strict';

window.ssponsorships = window.ssponsorships || {};
(function($){
    $(function(){

        $( document ).on( 'change', '.ss-payment-gateways [name=payment_method]', function(){
           var method = $(this).val(),
               box    = $( '.ss-payment-gateways .payment_box.payment_method_' + method );
           $( '.ss-payment-gateways .payment_box').hide();
           if ( box.length ) {
               box.show();
           }
        });
    });
})(jQuery);