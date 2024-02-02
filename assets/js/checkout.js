jQuery( function( $ ) {

    var wc_checkout_form = {
        $checkout_form: $( 'form.checkout' ),
        address_field: $('.address-field input.input-text'),
        init: function() {
            this.$checkout_form.on( 'change', '.address-field input.input-text', this.update_checkout_action );
            this.$checkout_form.on( 'change', 'input.shipping_method', this.change_shipping_method );
            this.init_shipping();
        },
        update_checkout_action: function() {
            var $this = $(this),
                address = $this.val();

            // only if imicra-yandex-delivery method is checked
            var currentMethod;
            $('input.shipping_method').each(function() {
                var method = $(this).val().split(':');

                if ($(this).is(':checked')) {
                    currentMethod = method[0];
                }
            });
            if (currentMethod !== "imicra-yandex-delivery") {
                return;
            }

            // $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table' ).block({
            //     message: null,
            //     overlayCSS: {
            //         background: '#fff',
            //         opacity: 0.6
            //     }
            // });

            $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table' ).addClass('overlay');

            $.ajax({
                type: 'POST',
                url: imwcyad.ajax_url,
                data: {
                    address : address,
                    action: 'imwcyad_claims'
                },
                success: function (response) {
                    wc_checkout_form.update_shipping_method(response);
                },
                error: function() {
                },
                dataType: 'json'
            });
        },
        update_shipping_method: function(response) {
            if (imwcyad.debug) {
                console.log(response);
            }

            var subtotal = $('.woocommerce-checkout-review-order-table').find('.cart-subtotal .sum').text();
            var cost = typeof response.pricing !== 'undefined' && response.pricing.length !== 0 ? response.pricing.offer.price : 0;
            cost = parseFloat(cost);
            var total = parseFloat(subtotal) + cost;
            var claimId = response.id;

            $('input.shipping_method').each(function() {
                var method = $(this).val().split(':');

                if ("imicra-yandex-delivery" === method[0]) {
                    $(this).next().find('.sum').text(cost);
                }
            });

            $('.woocommerce-checkout-review-order-table').find('#imwcyad_cost').val(cost);
            $('.woocommerce-checkout-review-order-table').find('#imwcyad_data').val(claimId);
            $('.woocommerce-checkout-review-order-table').find('.order-total .sum').text(total);

            // $.each( response.fragments, function( key, value ) {
            //     $( key ).replaceWith( value );
            // });

            // $( '.woocommerce-checkout-review-order-table, .woocommerce-checkout-payment' ).unblock();
            $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table' ).removeClass('overlay');
        },
        change_shipping_method: function() {
            var method = $(this).val().split(':');

            if ("imicra-yandex-delivery" === method[0]) {
                wc_checkout_form.address_field.trigger('change');
            }
        },
        init_shipping: function() {
            $('input.shipping_method').each(function() {
                var method = $(this).val().split(':');

                if ("imicra-yandex-delivery" === method[0] && $(this).is(':checked')) {
                    wc_checkout_form.address_field.trigger('change');
                }
            })
        }
    };

    wc_checkout_form.init();
});
