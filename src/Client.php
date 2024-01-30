<?php
/**
 * Class Client.
 * Client for Yandex Delivery api.
 */

namespace Imicra\WcYandexDelivery;

use WC_Shipping_Method;

class Client {
    private const BASE_URL = 'https://b2b.taxi.yandex.net/b2b/cargo/integration/v2';

    protected WC_Shipping_Method $deliveryMethod;

    // TODO get this from options
    private const WAREHOUSE_LON = 37.717761;
    private const WAREHOUSE_LAT = 55.48098;

    /**
     * Destination address
     */
    private $address;

    /**
     * Destination coordinates
     */
    public $position;

    public function __construct( array $position = [], $address ) {
        $this->position = $position;
        $this->address = $address;

        $this->deliveryMethod = Helper::getActualShippingMethod();
    }

    public function init() {
        $res = $this->create();
        $claim_id = $res['id'];

        $info = $this->getUntilReadyForApproval( $claim_id );

        return $info;
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
        return $this->claimInfo( $claim_id );
    }

    private function getData( string $path, string $query = '', array $options = [] ) {
        $url = self::BASE_URL . "/{$path}?{$query}";

        $args['body'] = json_encode( $options );
        $headers = $this->getHeaders();
        $args = array_merge( $headers, $args );

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
                'callback_url' => 'https://' . $_SERVER['SERVER_NAME']
            ],
            'items' => $this->claimItems(),
            'route_points' => [
                [
                    'address' => [
                        'coordinates' => [
                            self::WAREHOUSE_LON,
                            self::WAREHOUSE_LAT
                        ],
                        'fullname' => $this->deliveryMethod->get_option( 'seller_address' )
                    ],
                    'contact' => [
                        "name" => $this->deliveryMethod->get_option( 'seller_name' ),
                        "phone" => $this->deliveryMethod->get_option( 'seller_phone' ),
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
                    'contact' => $this->claimCustomer(),
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

    private function claimCancel( $claim_id ) {
        $path =  'claims/cancel';
        $query = "claim_id={$claim_id}";
        $options = [
            'cancel_state' => 'free',
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
                    'length' => Helper::getPackageDimention( $cart_item, 'length' ),
                    'width' => Helper::getPackageDimention( $cart_item, 'width' ),
                    'height' => Helper::getPackageDimention( $cart_item, 'height' ),
                    'weight' => Helper::getPackageWeight( $cart_item ),
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

    private function claimCustomer() {
        $contact = [];
        $customer = WC()->cart->get_customer();
        $changes = $customer->get_changes();

        if ( $customer->get_id() ) {
            // if customer exists or logged in
            $first_name = $customer->get_billing_first_name() ? $customer->get_billing_first_name() : $customer->get_shipping_first_name();
            $last_name = $customer->get_billing_last_name() ? $customer->get_billing_last_name() : $customer->get_shipping_last_name();
            $phone = $customer->get_billing_phone() ? $customer->get_billing_phone() : $customer->get_shipping_phone();
        } else {
            $first_name = $changes["billing"]["first_name"] ? $changes["billing"]["first_name"] : $changes["shipping"]["first_name"];
            $last_name = $changes["billing"]["last_name"] ? $changes["billing"]["last_name"] : $changes["shipping"]["last_name"];
            $phone = $changes["billing"]["phone"] ? $changes["billing"]["phone"] : $changes["shipping"]["phone"];
        }

        $name = $first_name . ' ' . $last_name;

        // TODO this data maybe wrong - because its gets from session
        $contact["name"] = $name;
        $contact["phone"] = $phone;

        return $contact;
    }

    private function getHeaders() {
        $args = [
            'headers' => [
                'Authorization'   => "Bearer " . $this->deliveryMethod->get_option( 'client_secret' ),
                'Accept-Language' => 'ru',
                'Content-Type'    => 'application/json'
            ]
        ];

        return $args;
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
