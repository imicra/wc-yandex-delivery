<?php
/**
 * Class Geocoder.
 * Yandex Geocoder api.
 */

namespace Imicra\WcYandexDelivery;

final class Geocoder {
    private const BASE_URL = 'https://geocode-maps.yandex.ru/1.x';

    /**
     * @param string $address post data
     * @return array $position coordinates
     */
    public static function getPoint( string $address ) {
        $apikey = Helper::getActualShippingMethod()->get_option( 'yandex_map_api_key' );
        $params = [
            'geocode' => $address,
            'format' => 'json',
            'apikey' => $apikey,
        ];

        $url = self::BASE_URL;
        $url = add_query_arg( $params, $url );

        $response = wp_remote_get( $url );

        $result = wp_remote_retrieve_body( $response );
        $result = json_decode( $result, true );

        $position = $result['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
        list( $lon, $lat ) = explode( ' ', $position );

        $position = array_map( 'floatval', [$lon, $lat] );

        return $position;
    }
}
