jQuery( function( $ ) {

    var wc_order_shipping = {
        $order_shipping: $('#order_shipping_line_items'),
        init: function() {
            this.$order_shipping.on( 'click', '.imwcyad_btn_info', this.get_claim_info );
            this.$order_shipping.on( 'click', '.imwcyad_btn_cancel', this.cancel_claim );
        },
        get_claim_info: function() {
            var $this = $(this),
                id = $this.data('id');

            $.ajax({
                type: 'POST',
                url: imwcyad.ajax_url,
                data: {
                    claim_id : id,
                    action: 'imwcyad_order_info'
                },
                success: function (response) {
                    wc_order_shipping.show_claim_info(response);
                },
                error: function() {
                },
                dataType: 'json'
            });
        },
        cancel_claim: function() {
            var $this = $(this),
                id = $this.data('id');

            $.ajax({
                type: 'POST',
                url: imwcyad.ajax_url,
                data: {
                    claim_id : id,
                    action: 'imwcyad_order_cancel'
                },
                success: function (response) {
                    wc_order_shipping.do_cancel_claim(response);
                },
                error: function() {
                },
                dataType: 'json'
            });
        },
        show_claim_info: function(response) {
            if (imwcyad.debug) {
                console.log(response);
            }

            var container = this.$order_shipping.find('.imwcyad_order_info').show();
            var statuses = JSON.parse(imwcyad.statuses);
            var available_cancel_state = JSON.parse(imwcyad.available_cancel_state);
            var status = response.status;
            var cancel_state = response.available_cancel_state;

            if (statuses[response.status]) {
                status = statuses[response.status];
            }

            if (available_cancel_state[response.available_cancel_state]) {
                cancel_state = available_cancel_state[response.available_cancel_state];
            }

            container.find('.cancel b').text(cancel_state);
            container.find('.status b').text(status);


            if (typeof response.error_messages !== 'undefined' && response.error_messages[0]) {
                container.find('.message b').text(response.error_messages[0].code + ' : ' + response.error_messages[0].message);
            }

            // error
            if (response.code) {
                container.find('.status b').text(response.code + ' : ' + response.message);
            }
        },
        do_cancel_claim: function(response) {
            if (imwcyad.debug) {
                console.log(response);
            }

            var container = this.$order_shipping.find('.imwcyad_order_info').show();

            container.find('.status b').text(response.status);

            // error
            if (response.code) {
                container.find('.status b').text(response.code + ' : ' + response.message);
            }
        }
    };

    wc_order_shipping.init();
});
