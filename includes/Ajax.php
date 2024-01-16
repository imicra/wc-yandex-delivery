<?php
/**
 * Class Ajax file.
 */

use Imicra\WcYandexDelivery\Geocoder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
    private const DELIVERY_TOKEN = 'y0_AgAAAAByecb5AAc6MQAAAADzZWvYmy2Q72usQquHONr7vEXdUJNRcFY';

    // TODO get this from options
    // 'Домодедово, д. Павловское, ул. Вокзальная, 82';
    private const WAREHOUSE_LON = '37.717761';
    private const WAREHOUSE_LAT = '55.48098';

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

        $data = array(
            $position
        );

        wp_send_json( $data );
    }
}

new Ajax;
