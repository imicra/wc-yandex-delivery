<?php
/**
 * Class Ajax file.
 */

use Imicra\WcYandexDelivery\Helper;
use Imicra\WcYandexDelivery\Geocoder;
use Imicra\WcYandexDelivery\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
    private $token;

    function __construct() {
        $this->token = Helper::getActualShippingMethod()->get_option( 'client_secret' );

        $this->add_ajax_events();
    }

    public function add_ajax_events() {
        $ajax_events = [
            'claims',
            'order_info',
            'order_cancel',
        ];

        foreach ( $ajax_events as $ajax_event ) {
            add_action( 'wp_ajax_imwcyad_' . $ajax_event, array( $this, $ajax_event ) );
			add_action( 'wp_ajax_nopriv_imwcyad_' . $ajax_event, array( $this, $ajax_event ) );
        }
    }

    public function claims() {
        $address = isset( $_POST['address'] ) ? wc_clean( wp_unslash( $_POST['address'] ) ) : null;

        $position = Geocoder::getPoint( $address );

        $response = new Client( $position, $address );
        $response = $response->init();

        $offerPrice = $response[1];

        // WC()->session->set( 'imwcyad_cost', null );
        // WC()->session->set( 'imwcyad_cost', $offerPrice );

        // Get order review fragment.
		// ob_start();
		// woocommerce_order_review();
		// $woocommerce_order_review = ob_get_clean();

        // wp_send_json(
		// 	array(
        //         'offerPrice' => $offerPrice,
		// 		'fragments' => array(
        //             '.woocommerce-checkout-review-order-table' => $woocommerce_order_review,
        //         )
		// 	)
		// );

        // $data = array(
        //     $offerPrice,
        //     $response
        // );

        wp_send_json( $response );
    }

    /**
     * Order info in admin.
     */
    public function order_info() {
        $claim_id = wp_unslash( $_POST['claim_id'] );
        $path =  'claims/info';
        $query = "claim_id={$claim_id}";
        $url = "https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/$path?$query";
        $args = [
            'headers' => [
                'Authorization'   => "Bearer {$this->token}",
                'Accept-Language' => 'ru',
                'Content-Type'    => 'application/json'
            ]
        ];
        $response = wp_remote_post( $url, $args );
        $result = wp_remote_retrieve_body( $response );
        $result = json_decode( $result, true );

        wp_send_json( $result );
    }

    /**
     * Cancel Order in admin.
     */
    public function order_cancel() {
        $claim_id = wp_unslash( $_POST['claim_id'] );
        $order_id = wp_unslash( $_POST['order_id'] );
        $path =  'claims/cancel';
        $query = "claim_id={$claim_id}";
        $url = "https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/$path?$query";
        $headers = [
            'headers' => [
                'Authorization'   => "Bearer {$this->token}",
                'Accept-Language' => 'ru',
                'Content-Type'    => 'application/json'
            ]
        ];
        $body = [
            'cancel_state' => 'free',
            'version' => 1
        ];
        $args['body'] = json_encode( $body );
        $args = array_merge( $headers, $args );
        $response = wp_remote_post( $url, $args );
        $result = wp_remote_retrieve_body( $response );
        $result = json_decode( $result, true );

        // change order status to cancelled
        $order = wc_get_order( $order_id );
        $order->update_status( 'cancelled' );

        wp_send_json( $result );
    }
}
