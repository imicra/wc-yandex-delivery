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
        $response = $response->create();

        $data = array(
            $response
        );

        wp_send_json( $data );
    }
}

new Ajax;
