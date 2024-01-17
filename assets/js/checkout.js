jQuery( function( $ ) {

    var wc_checkout_form = {
        $checkout_form: $( 'form.checkout' ),
        init: function() {
            this.$checkout_form.on( 'change', '.address-field input.input-text', this.update_checkout_action );
        },
        update_checkout_action: function(e) {
            var $this = $( this ),
                address = $this.val();

            $( '.woocommerce-checkout-review-order-table' ).block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

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
            // var price = response[1];
            // $('.woocommerce-checkout-review-order-table').find('#imwcyad_cost').val(price);
            $.each( response.fragments, function( key, value ) {
                $( key ).replaceWith( value );
            });
        }
    };

    wc_checkout_form.init();
});
