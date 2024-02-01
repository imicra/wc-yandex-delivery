'use strict';

jQuery(($) => {
    var calcBtn = {
        btnHtml: '<button class="calcBtn">Определить</button>',
        cantainer: $('.wc-shipping-zone-method-fields'),
        address: $(`input#woocommerce_${imwcyad.plugin_id}_seller_address`).val(),
        inputLon: $(`input#woocommerce_${imwcyad.plugin_id}_warehouse_lon`),
        inputLat: $(`input#woocommerce_${imwcyad.plugin_id}_warehouse_lat`),

        init: function() {
            this.cantainer.on('click', '.calcBtn', this.calculate);

            const coordHtml = calcBtn.init_coordinates();

            $(`input#woocommerce_${imwcyad.plugin_id}_address_btn`)
            .after(`<div id="${imwcyad.plugin_id}-wrapper">${this.btnHtml}${coordHtml}</div>`);
        },

        init_coordinates: function() {
            const lon = calcBtn.inputLon.val() ? calcBtn.inputLon.val() : '';
            const lat = calcBtn.inputLat.val() ? calcBtn.inputLat.val() : '';

            const lonHtml = `<div class="item"><span>Долгота: </span><span class="item-lon">${lon}</span></div>`;
            const latHtml = `<div class="item"><span>Широта: </span><span class="item-lat">${lat}</span></div>`;
            const coordHtml = `<div class="coord-wrapper"><div>Координаты:</div><div class="coord-wrapper__inner">${lonHtml}${latHtml}</div></div>`;

            return coordHtml;
        },

        calculate: function(event) {
            event.preventDefault();

            $.ajax({
                type: 'POST',
                url: imwcyad.ajax_url,
                data: {
                    address : calcBtn.address,
                    action: 'imwcyad_warehouse'
                },
                success: function (response) {
                    calcBtn.populate_coordinates(response);
                },
                error: function() {
                },
                dataType: 'json'
            });
        },

        populate_coordinates: function(response) {
            const lon = response[0];
            const lat = response[1];

            // save coordinates
            calcBtn.inputLon.val(lon);
            calcBtn.inputLat.val(lat);

            calcBtn.cantainer.find('.item-lon').text(lon);
            calcBtn.cantainer.find('.item-lat').text(lat);
        }
    };

    if ($(`input#woocommerce_${imwcyad.plugin_id}_address_btn`).length) {
        calcBtn.init();
    }
});
