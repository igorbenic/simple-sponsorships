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

        var packageSelect = $('.ss-form-field-package_select');
        if ( packageSelect.length ) {
            packageSelect.find('.package-item input').on( 'change', function(){
               var $this   = $(this),
                   $items  = $this.parents('.packages-select-items'),
                   data    = $items.find(':input').serialize(),
                   $select = $items.parent('.packages-select');

               $select.addClass('ss-loading');
               $.ajax({
                   url: ss_wp.ajax,
                   method: 'GET',
                   data: { action: 'ss_packages_get_total', data: data, nonce: ss_wp.nonce },
                   success: function( resp ) {
                       if ( resp.success ) {
                           $select.find('.packages-total').html( resp.data.total_formatted );
                       }
                   },
                   error: function( e ) {
                       console.error( e );
                   },
                   complete: function() {
                       $select.removeClass('ss-loading');
                   }
               })

            });
        }

        /**
         * Trigger to show Account Fields.
         */
        function ssTriggerAccountFields() {
            var form   = $( '.ss-sponsor-form'),
                create = form.find('#create_account').prop('checked');

            if ( create ) {
                $('#create_account_username').parents('.ss-form-field').removeClass('ss-hidden');
                $('#create_account_password').parents('.ss-form-field').removeClass('ss-hidden');
            } else {
                $('#create_account_username').parents('.ss-form-field').addClass('ss-hidden');
                $('#create_account_password').parents('.ss-form-field').addClass('ss-hidden');
            }
        }

        if ( $('.ss-sponsor-form #create_account').length ) {
            ssTriggerAccountFields();
        }

        $( document ).on( 'change', '.ss-sponsor-form #create_account', function(e){
            ssTriggerAccountFields();
        });


    });
})(jQuery);
