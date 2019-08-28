'use strict';
(function($){
    $(function(){

        if ( ! $('#ss-stripe-card-element').length ) {
            return;
        }

        var stripe = Stripe( ss_stripe.key );

        var elements = stripe.elements();
        var cardElement = elements.create('card');
        var stripeFormSubmit = false;

        cardElement.mount('#ss-stripe-card-element');

        function handleServerResponse(response, $form ) {
            $form = $form || false;
            if (! response.success ) {
                // Show error from server on payment form
                alert( response.data );
                $form.removeClass('ss-loading');
            } else if ( typeof response.data !== 'undefined' && response.data.requires_action ) {
                // Use Stripe.js to handle required card action
                stripe.handleCardAction(
                    response.data.payment_intent_client_secret
                ).then(function(result) {
                    if (result.error) {
                        alert( result.error );
                        // Show error in payment form
                    } else {

                        $.ajax({
                            method: 'POST',
                            url: ss_stripe.ajax,
                            data: { action: 'ss_stripe_confirm_payment', payment_intent_id: result.paymentIntent.id, sponsorship_id: $form.find('input[name=ss_sponsorship_id]').val() },
                            success: function( resp ) {
                                if ( resp.success ) {
                                    handleServerResponse( resp, $form );
                                }
                            }
                        });
                    }
                });
            } else {
                // Show success message
                if ( $form ) {
                    stripeFormSubmit = true;
                    $form.removeClass('ss-loading');
                    $form.submit();
                }
            }
        }

        $( '.ss-payment-form' ).on( 'submit', function(e){
            var $this = $(this);

            if ( $this.find('[name=payment_method]:checked').val() === 'stripe' && false === stripeFormSubmit ) {
                var cardholderName = document.getElementById('stripe-cardholder-name');
                $this.addClass('ss-loading');
                stripe.createPaymentMethod( 'card', cardElement, {
                    billing_details: { name: cardholderName.value }
                }).then( function( result ) {
                    if (result.error) {
                        // Show error in payment form
                        alert( result.error );
                        $this.removeClass('ss-loading');
                    } else {
                        $.ajax({
                            method: 'POST',
                            url: ss_stripe.ajax,
                            data: { action: 'ss_stripe_confirm_payment', payment_method_id: result.paymentMethod.id, sponsorship_id: $this.find('input[name=ss_sponsorship_id]').val() },
                            success: function( resp ) {
                                if ( resp.success ) {
                                    handleServerResponse( resp, $this );
                                }
                            }
                        });
                    }
                });
                return false;
            }
        });
    });
})(jQuery);