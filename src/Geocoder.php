<?php
/**
 * Class Geocoder.
 * Yandex Geocoder api.
 */

namespace Imicra\WcYandexDelivery;

final class Geocoder {
    private const GEO_BASE_URL = 'https://geocode-maps.yandex.ru/1.x';

    // TODO get this from options
    private const GEO_TOKEN = 'b382e2ff-ac8c-4c06-95b5-8c37e84f5812';
    private $token;

    /**
     * @return array $position coordinates
     */
    public static function getPoint( string $address ) {
        // geocoder
        $params = [
            'geocode' => $address,
            'format' => 'json',
            'apikey' => self::GEO_TOKEN,
        ];
        $url = add_query_arg( $params, self::GEO_BASE_URL );
        $response = wp_remote_get( $url );
        $result = wp_remote_retrieve_body( $response );
        $result = json_decode( $result, true );
        $position = $result['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
        list( $lon, $lat ) = explode( ' ', $position );

        $position = array(
            'lon' => $lon,
            'lat' => $lat
        );

        return $position;
    }
}
