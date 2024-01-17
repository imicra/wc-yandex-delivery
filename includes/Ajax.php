<?php
/**
 * Class Ajax file.
 */

use Imicra\WcYandexDelivery\Geocoder;
use Imicra\WcYandexDelivery\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
    function __construct() {
        $this->add_ajax_events();
    }

    public function add_ajax_events() {
        $ajax_events = [
            'claims',
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
        WC()->session->set( 'imwcyad_cost', $offerPrice );

        // Get order review fragment.
		ob_start();
		woocommerce_order_review();
		$woocommerce_order_review = ob_get_clean();

        wp_send_json(
			array(
                'offerPrice' => $offerPrice,
				'fragments' => array(
                    '.woocommerce-checkout-review-order-table' => $woocommerce_order_review,
                )
			)
		);

        // $data = array(
        //     $response
        // );

        // wp_send_json( $response );
    }
}

new Ajax;
