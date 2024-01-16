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

    private function getData( string $path, array $options, string $query = '' ) {
        $url = self::BASE_URL . "/{$path}?{$query}";
        // $url = add_query_arg( $params, $url );
        $args['body'] = json_encode( $options );
        $args = array_merge( $this->args, $args );

        $response = wp_remote_post( $url, $args );

        $result = wp_remote_retrieve_body( $response );
        $result = json_decode( $result, true );

        return $result;
    }

    public function create() {
        $path =  'claims/create';
        $request_id = uniqid();
        $query = "request_id={$request_id}";
        $options = [
            'callback_properties' => [
                'callback_url' => 'https://localhost:3000'
            ],
            'items' => [
                [
                    "cost_currency" => "RUB",
                    "cost_value" => "2.00",
                    "droppof_point" => 2,
                    "pickup_point" => 1,
                    "quantity" => 1,
                    'size' => [
                        'height' => 0.45,
                        'length' => 0.45,
                        'width' => 0.45
                    ],
                    "title" => "Плюмбус",
                    'weight' => 0.45
                ]
            ],
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

        $result = $this->getData( $path, $options, $query );

        return $result;
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

        $result = $this->getData( $path, $options );

        return $result;
    }
}
