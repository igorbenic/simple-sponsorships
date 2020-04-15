'use strict';
(function($){
    $(function(){

        var SS_Stripe = {

            form: null,

            stripe: null,

            elements: null,

            cardElement: null,

            billingDetails: {
                'first_name': 'billing_first_name',
                'last_name' : 'billing_last_name',
                'email'     : 'billing_email',
                'address'   : {
                    'line1': 'billing_address',
                    'line2': 'billing_address2',
                    'city' : 'billing_city',
                    'postal_code': 'billing_postalcode',
                    'country': 'billing_country',
                    'state': 'billing_state'
                },
                'phone': 'billing_phone'
            },

            /**
             * Initialize the Stripe
             */
            init: function init(){
                if ( ! this.isStripeEnabled() ) {
                    return;
                }

                if ( ! this.isStripeKeyAvailable() ) {
                    return;
                }

                this.form = $('.ss-payment-form');
                this.stripe = Stripe( ss_stripe.key );

                this.mountElements();
                this.attachEvents();
                this.maybeHandleStripeAction();

                $( document ).triggerHandler( 'ss_stripe_init', [ this ] );
            },

            /**
             * Maybe Handle Stripe Action on initial page load.
             */
            maybeHandleStripeAction: function maybeHandleStripeAction() {
              if ( this.form.find('#ss_stripe_payment_intent_id').length && this.form.find('#ss_stripe_payment_intent_secret').length ) {
                  var secret      = this.form.find('#ss_stripe_payment_intent_secret').val(),
                      intentInput = this.form.find('#ss_stripe_payment_intent_id'),
                      redirect    = this.form.find('#ss_stripe_payment_confirm_url').val(),
                      gateways    = this.form.find('[name=payment_method]');

                  //gateways.filter('[value=stripe]').prop('checked', true);

                  this.stripe.handleCardAction( secret ).then(function(result) {
                      if ( result.error ) {
                          alert( result.error.message );
                      } else {
                          window.location = redirect;
                      }
                  });
              }
            },

            /**
             * Attach events on DOM
             */
            attachEvents: function attachEvents() {
                $( document ).on( 'submit', '.ss-payment-form', { ss_stripe: this }, this.onSubmit );
            },

            /**
             * On Submit
             */
            onSubmit: function onSubmit( event ) {
                var ss_stripe = event.data.ss_stripe;

                if ( ! ss_stripe.isStripeChosen() ) {
                    return true;
                }

                // If a source is already in place, submit the form as usual.
                if ( ss_stripe.hasPaymentMethodID() ) {
                    return true;
                }

                ss_stripe.createPaymentMethod();

                return false;
            },

            /**
             * Mount Elements
             */
            mountElements: function mountElements() {
                if ( null === this.stripe ) {
                    return;
                }

                var elements    = this.stripe.elements();

                this.cardElement = elements.create('card');

                this.cardElement.mount('#ss-stripe-card-element');
            },

            /**
             * Check if stripe key is available.
             *
             * @returns {boolean}
             */
            isStripeKeyAvailable: function isStripeKeyAvailable() {
                return typeof ss_stripe !== 'undefined' && typeof ss_stripe.key !== 'undefined' && ss_stripe.key;
            },

            /**
             * Check if stripe is enabled.
             *
             * @returns {boolean}
             */
            isStripeEnabled: function isStripeEnabled() {
                return $('#ss-stripe-card-element').length > 0;
            },

            /**
             * Create the Payment method and attach it to the form
             */
            createPaymentMethod: function createPaymentMethod() {
                this.stripe.createPaymentMethod({
                        type: 'card',
                        card: this.cardElement,
                        billing_details: this.getBillingDetails()
                    })
                    .then(function( result ) {
                        if ( result.error ) {
                            alert( result.error.message );
                        } else {
                            var paymentMethod = result.paymentMethod;

                            SS_Stripe.form.append(
                                $( '<input type="hidden" />' )
                                    .addClass( 'stripe-payment-method' )
                                    .attr( 'name', 'ss_stripe_payment_method' )
                                    .val( paymentMethod.id )
                            );

                            SS_Stripe.form.submit();
                        }
                    });
            },

            /**
             * Has Form already a Payment Method ID?
             * @returns {boolean}
             */
            hasPaymentMethodID: function hasPaymentMethodID() {
              return this.form.find('.stripe-payment-method').length !== 0;
            },

            /**
             * Is Stripe Chosen
             * @returns {boolean}
             */
            isStripeChosen: function hasStripeChosen() {
               return SS_Stripe.form.find('[name=payment_method]:checked').val() === 'stripe'
            },

            /**
             * Get the Billing Details from the Form
             * @returns {{}}
             */
            getBillingDetails: function getBillingDetails() {

                var billingDetails = {};

                for( var param in this.billingDetails ) {

                    var id = this.billingDetails[ param ];

                    if ( typeof id === 'object' ) {
                        billingDetails[ param ] = {};

                        for ( var sub_param in id ) {
                            var sub_id = id[ sub_param ];
                            var sub_field = SS_Stripe.form.find( sub_id );
                            if ( sub_field.length ) {
                                billingDetails[ param ][ sub_param ] = sub_field.val();
                            }
                        }
                    } else {
                        var field  = SS_Stripe.form.find( id );
                        if ( field.length ) {
                            billingDetails[ param ]  = field.val();
                        }
                    }
                }

                return billingDetails;
            }
        };

        SS_Stripe.init();

       /* var form             = $('.ss-payment-form'),
            stripe           = Stripe( ss_stripe.key ),
            elements         = stripe.elements(),
            cardElement      = elements.create('card'),
            stripeFormSubmit = false;

        cardElement.mount('#ss-stripe-card-element');*

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
        });*/
    });
})(jQuery);