jQuery( function( $ ) {

    var wc_checkout_form = {
        $checkout_form: $( 'form.checkout' ),
        address_field: $('.address-field input.input-text'),
        init: function() {
            this.$checkout_form.on( 'change blur', '.address-field input.input-text', this.update_checkout_action );
            this.$checkout_form.on( 'change', 'input.shipping_method', this.change_shipping_method );
            this.init_shipping();
        },
        update_checkout_action: function() {
            $this = $(this);
                var address = $this.val();

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
        update_shipping_method(response) {
            console.log(response);
            // console.log(response.pricing.offer.price);
            var subtotal = $('.woocommerce-checkout-review-order-table').find('.cart-subtotal .sum').text();
            var cost = response[1];
            var total = parseFloat(subtotal) + cost;

            $('input.shipping_method').each(function() {
                var method = $(this).val().split(':');

                if ("imicra-yandex-delivery" === method[0]) {
                    $(this).next().find('.sum').text(cost);
                }
            });

            $('.woocommerce-checkout-review-order-table').find('#imwcyad_cost').val(cost);
            $('.woocommerce-checkout-review-order-table').find('#imwcyad_data').val(response[0]); // claim id
            $('.woocommerce-checkout-review-order-table').find('.order-total .sum').text(total);

            // $.each( response.fragments, function( key, value ) {
            //     $( key ).replaceWith( value );
            // });

            // $( '.woocommerce-checkout-review-order-table, .woocommerce-checkout-payment' ).unblock();
            $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table' ).removeClass('overlay');
        },
        change_shipping_method() {
            var method = $(this).val().split(':');

            if ("imicra-yandex-delivery" === method[0]) {
                wc_checkout_form.address_field.trigger('change');
            }
        },
        init_shipping() {
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
