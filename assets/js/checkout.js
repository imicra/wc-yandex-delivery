jQuery( function( $ ) {

    var wc_checkout_form = {
        $checkout_form: $( 'form.checkout' ),
        init: function() {
            this.$checkout_form.on( 'blur', '.address-field input.input-text', this.update_checkout_action );
        },
        update_checkout_action: function(e) {
            var $this = $( this ),
                address = $this.val();

            $.ajax({
                type: 'POST',
                url: imwcyad.ajax_url,
                data: {
                    address : address,
                    action: 'imwcyad_claims'
                },
                success: function (response) {
                    console.log(response)
                },
                error: function() {
                },
                dataType: 'json'
            });
        }
    };

    wc_checkout_form.init();
});
