<?php
/**
 * Class Helper.
 */

namespace Imicra\WcYandexDelivery;

class Helper {
    public static function getActualShippingMethod( ?int $instanceId = null ) {
        if ( !is_null( $instanceId ) ) {
            return new \WC_Yandex_Shipping_Method( $instanceId );
        }

        return \WC()->shipping->load_shipping_methods()[IMYAD_PLUGIN_ID];
    }

    /**
     * @param array $item cart_item
     * @param string $unit dimention unit, eg. 'width'
     */
    public static function getPackageDimention( $item, $unit ) {
        $default = self::getActualShippingMethod()->get_option( "default_{$unit}" );
        $methodName = "get_{$unit}";
        $unit = $item["data"]->$methodName();

        $unit = (int)$unit != 0 ? $unit : $default;
        $unit = round( $unit * 0.01, 2 );

        return $unit;
    }

    /**
     * @param array $item cart_item
     */
    public static function getPackageWeight( $item ) {
        $default = self::getActualShippingMethod()->get_option( 'default_weight' );
        $unit = $item["data"]->get_weight();

        $unit = (int)$unit != 0 ? $unit : $default;
        $unit = round( $unit * 0.001, 3 );

        return $unit;
    }
}
