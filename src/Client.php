<?php
/**
 * Class Client.
 * Client for Yandex Delivery api.
 */

namespace Imicra\WcYandexDelivery;

class Client {
    private const BASE_URL = 'https://b2b.taxi.yandex.net/b2b/cargo/integration/v2';

    // TODO get this from options
    private const TOKEN = 'y0_AgAAAAByecb5AAc6MQAAAADzZWvYmy2Q72usQquHONr7vEXdUJNRcFY';

    // TODO get this from options
    private const WAREHOUSE = 'Домодедово, д. Павловское, ул. Вокзальная, 82';
    private const WAREHOUSE_LON = 37.717761;
    private const WAREHOUSE_LAT = 55.48098;

    /**
     * Required parameters
     */
    private $args;

    /**
     * Destination address
     */
    private $address;

    /**
     * Destination coordinates
     */
    public $position;

    public function __construct( array $position = [], $address ) {
        $this->args = [
            'headers' => [
                'Authorization'   => "Bearer " . self::TOKEN,
                'Accept-Language' => 'ru',
                'Content-Type'    => 'application/json'
            ]
        ];

        $this->position = $position;
        $this->address = $address;
    }

    public function init() {
        $res = $this->create();
        $claim_id = $res['id'];

        $info = $this->getUntilReadyForApproval( $claim_id );

        // pricing.offer
        return [
            $claim_id,
            floatval( $info['pricing']['offer']['price'] ),
            $info
        ];

        // available_cancel_state === free - claims/cancel
    }

    public function getUntilReadyForApproval( $claim_id ) {
        $res = $this->claimInfo( $claim_id );

        if ( 'ready_for_approval' !== $res['status'] ) {
            $i = 1;
			do {
				usleep( 500000 );
				$i ++;
				$res = $this->claimInfo( $claim_id );
			} while ( 'ready_for_approval' !== $res['status'] && $i < 15 );
        }

        if ( 'ready_for_approval' !== $res['status'] ) {
            return "Current status: {$res['status']}";
        }

        // return $this->claimAccept( $claim_id );
        return $this->claimInfo( $claim_id ); // test
    }

    private function getData( string $path, string $query = '', array $options = [] ) {
        $url = self::BASE_URL . "/{$path}?{$query}";
        // $url = add_query_arg( $params, $url );
        $args['body'] = json_encode( $options );
        $args = array_merge( $this->args, $args );

        $response = wp_remote_post( $url, $args );

        $result = wp_remote_retrieve_body( $response );
        $result = json_decode( $result, true );

        return $result;
    }

    private function create() {
        $path =  'claims/create';
        $request_id = uniqid();
        $query = "request_id={$request_id}";
        $options = [
            'callback_properties' => [
                'callback_url' => 'https://localhost:3000/'
            ],
            'items' => $this->claimItems(),
            'route_points' => [
                [
                    'address' => [
                        'coordinates' => [
                            self::WAREHOUSE_LON,
                            self::WAREHOUSE_LAT
                        ],
                        'fullname' => self::WAREHOUSE
                    ],
                    'contact' => [
                        "name" => "Менеджер",
                        "phone" => "+79099999998",
                    ],
                    "point_id" => 1,
                    "type" => "source",
                    "visit_order" => 1
                ],
                [
                    'address' => [
                        'coordinates' => $this->position,
                        'fullname' => $this->address
                    ],
                    'contact' => [
                        "name" => "Иван",
                        "phone" => "+79099999991",
                    ],
                    "point_id" => 2,
                    "type" => "destination",
                    "visit_order" => 2
                ]
            ]
        ];

        $result = $this->getData( $path, $query, $options );

        return $result;
    }

    private function claimInfo( $claim_id ) {
        $path =  'claims/info';
        $query = "claim_id={$claim_id}";

        $result = $this->getData( $path, $query );

        return $result;
    }

    private function claimAccept( $claim_id ) {
        $path =  'claims/accept';
        $query = "claim_id={$claim_id}";
        $options = [
            'version' => 1
        ];

        $result = $this->getData( $path, $query, $options );

        return $result;
    }

    private function claimItems() {
        $items = [];
        // Get packages to calculate shipping for.
        $shipping_packages = WC()->cart->get_shipping_packages();

        if ( ! empty( $shipping_packages[0]["contents"] ) ) {
            $shipping_items = array();

            foreach ( $shipping_packages[0]["contents"] as $cart_item ) {
                $shipping_items[] = [
                    // TODO convert dimentions and weight to m and kg
                    'length' => (int)$cart_item["data"]->get_length() * 0.01,
                    'width' => (int)$cart_item["data"]->get_width() * 0.01,
                    'height' => (int)$cart_item["data"]->get_height() * 0.01,
                    'weight' => (int)$cart_item["data"]->get_weight() * 0.001,
                    'title' => $cart_item["data"]->get_name(),
                    'product_price' => $cart_item["product_price"],
                    'quantity' => (int)$cart_item["quantity"],
                ];
            }
        } else {
            $shipping_items = array();
        }

        // generate items array
        foreach ( $shipping_items as $item ) {
            // TODO create default values for dimentions and weight
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

        return $items;
    }

    public function checkPrice() {
        $path =  'check-price';
        $options = [
            'items' => [
                [
                    'quantity' => 1,
                    'size' => [
                        'height' => 0.45,
                        'length' => 0.45,
                        'width' => 0.45
                    ],
                    'weight' => 0.45
                ]
            ],
            'route_points' => [
                [
                    'coordinates' => $this->position,
                    'fullname' => $this->address
                ]
            ]
        ];

        $result = $this->getData( $path, '', $options );

        return $result;
    }
}
