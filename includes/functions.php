<?php
/**
 * Functions
 */

use Imicra\WcYandexDelivery\Helper;

/**
 * Input value for hold cost from delivery api.
 */
function imicra_shipping_rate_cost( $method ) {
  if ( strstr( $method->id, IMYAD_PLUGIN_ID ) ) {
    // $cost = WC()->session->get( 'imwcyad_cost' ) ? WC()->session->get( 'imwcyad_cost' ) : 0;

    echo '<input type="hidden" name="imwcyad_cost" id="imwcyad_cost" />';
    echo '<input type="hidden" name="imwcyad_data" id="imwcyad_data" />';
  }
}
add_action( 'woocommerce_after_shipping_rate', 'imicra_shipping_rate_cost' );

/**
 * Add price html to shipping method label in a list of shipping methods.
 */
function imicra_shipping_method_label( $label, $method ) {
    if ( strstr( $method->id, IMYAD_PLUGIN_ID ) ) {
        $label = $method->get_label();
        $label .= ': ' . wc_price( 0 );
    }

    return $label;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'imicra_shipping_method_label', 10, 2 );

/**
 * Set total and shipping_total to a new order when a new order is create.
 */
function imicra_checkout_create_order( $order ) {
    if ( isset( $_POST['imwcyad_cost'] ) && ! empty( $_POST['imwcyad_cost'] ) ) {
        $cost = (int)$_POST["imwcyad_cost"];
        $total = $order->get_total();
        $total = $total + $cost;

        $order->set_shipping_total( $cost );
        $order->set_total( $total );


    }

    // create request to api for accept claim
    if ( isset( $_POST['imwcyad_data'] ) && ! empty( $_POST['imwcyad_data'] ) ) {
        $claim_id = wp_unslash( $_POST['imwcyad_data'] );
        $path =  'claims/accept';
        $query = "claim_id={$claim_id}";
        $url = "https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/$path?$query";
        $token = Helper::getActualShippingMethod()->get_option( 'client_secret' );
        $headers = [
            'headers' => [
                'Authorization'   => "Bearer {$token}",
                'Accept-Language' => 'ru',
                'Content-Type'    => 'application/json'
            ]
        ];
        $body = [
            'version' => 1
        ];
        $args['body'] = json_encode( $body );
        $args = array_merge( $headers, $args );
        $response = wp_remote_post( $url, $args );
        $result = wp_remote_retrieve_body( $response );
        $result = json_decode( $result, true );

        // create order meta for keep claim data in order
        $order->update_meta_data( 'imwcyad_data', $result );
    }

}
add_action( 'woocommerce_checkout_create_order', 'imicra_checkout_create_order' );

function imicra_order_shipping_data( $order_id ) {
    $order = wc_get_order( $order_id );
    $claim_data = $order->get_meta( 'imwcyad_data' );

    if ( $claim_data ) :
    ?>
        <tr>
            <td></td>
            <td>
                <?php
                // if accept claim success
                if ( array_key_exists( 'id', $claim_data ) ) :
                ?>
                    <button type="button" class="button imwcyad_btn_info" data-id="<?php echo $claim_data['id']; ?>">Информация по заявке</button>
                    <button type="button" class="button imwcyad_btn_cancel" data-claim-id="<?php echo $claim_data['id']; ?>" data-order-id="<?php echo $order_id; ?>">Отмена заявки</button>
                    <div class="imwcyad_order_info" style="display: none;">
                        <div class="cancel">Возможность отмены: <b>-</b></div>
                        <div class="status">Статус заявки: <b>-</b></div>
                        <div class="message">Ошибка: <b>Нет ошибок</b></div>
                    </div>
                <?php
                // if accept claim error
                else :
                ?>
                    <?php echo $claim_data['code'] . ' : ' . $claim_data['message']; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php
    endif;
}
add_action( 'woocommerce_admin_order_items_after_shipping', 'imicra_order_shipping_data' );

/**
 * Add span to the price format for js interactions.
 */
function imicra_price_format( $format, $currency_pos ) {
	$format = '%1$s<span class="sum">%2$s</span>';

	switch ( $currency_pos ) {
		case 'left':
			$format = '%1$s<span class="sum">%2$s</span>';
			break;
		case 'right':
			$format = '<span class="sum">%2$s</span>%1$s';
			break;
		case 'left_space':
			$format = '%1$s&nbsp;<span class="sum">%2$s</span>';
			break;
		case 'right_space':
			$format = '<span class="sum">%2$s</span>&nbsp;%1$s';
			break;
	}

	return $format;
}
add_filter( 'woocommerce_price_format', 'imicra_price_format', 10, 2 );

/**
 * Get items needing shipping data for api.
 */
function imicra_get_shipping_items_data() {
    $items = [];
        // defaults
        $defaults = [
            "cost_currency" => "RUB",
            "droppof_point" => 2,
            "pickup_point" => 1,
        ];

        $shipping_packages = WC()->cart->get_shipping_packages();

        if ( ! empty( $shipping_packages[0]["contents"] ) ) {
            $shipping_items = array();

            foreach ( $shipping_packages[0]["contents"] as $cart_item ) {
                $shipping_items[] = [
                    'length' => (int)$cart_item["data"]->get_length(),
                    'width' => (int)$cart_item["data"]->get_width(),
                    'height' => (int)$cart_item["data"]->get_height(),
                    'weight' => (int)$cart_item["data"]->get_weight(),
                    'title' => $cart_item["data"]->get_name(),
                    'product_price' => $cart_item["product_price"],
                    'quantity' => (int)$cart_item["quantity"],
                ];
            }
        } else {
            $shipping_items = array();
        }

        foreach ( $shipping_items as $item ) {
            $items[] = [
                "cost_currency" => "RUB",
                "droppof_point" => 2,
                "pickup_point" => 1,
                'cost_value' => $item['product_price'],
                'quantity' => $item['quantity'],
                'title' => $item['title'],
                'weight' => $item['weight'],
                'size' => [
                    'height' => $item['height'],
                    'length' => $item['length'],
                    'width' => $item['width'],
                ]
            ];
        }

    // return $shipping_items;
    imicra_var_dump($shipping_packages);
}
// add_action( 'woocommerce_after_checkout_form', 'imicra_get_shipping_items_data', 10 );

/**
 * Get suumary of items in the cart.
 */
function imicra_get_cart_contents_data() {
    $cart_contents_data = array(
        'quantity' => WC()->cart->get_cart_contents_count(),
        'weight' => WC()->cart->get_cart_contents_weight(),
        'cost' => WC()->cart->cart_contents_total,
    );

    // return $cart_contents_data;
    imicra_var_dump(WC()->cart->get_customer()->get_changes()["billing"]["phone"]);
}
// add_action( 'woocommerce_after_checkout_form', 'imicra_get_cart_contents_data', 11 );
