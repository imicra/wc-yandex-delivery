<?php
/**
 * Functions
 */

/**
 * Input value for hold cost from delivery api.
 */
function imicra_shipping_rate_cost( $method ) {
  if ( strstr( $method->id, IMYAD_PLUGIN_ID ) ) {
    echo '<input type="hidden" name="imwcyad_cost" id="imwcyad_cost" />';
  }
}
add_action( 'woocommerce_after_shipping_rate', 'imicra_shipping_rate_cost' );

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
    imicra_var_dump($items);
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
    imicra_var_dump($cart_contents_data);
}
// add_action( 'woocommerce_after_checkout_form', 'imicra_get_cart_contents_data', 11 );
