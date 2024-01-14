<?php
/**
 * Plugin Name: WooCommerce Yandex Delivery
 * Plugin URI: https://github.com/imicra/
 * Description: Интеграция Яндекс Доставки.
 * Version: 1.0.0
 * Author: Imicra
 * Author URI: https://github.com/imicra/
 * Developer: Imicra
 * Developer URI: https://github.com/imicra/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit;

/**
 * Activation and deactivation hooks for WordPress
 */
function imwcyad_plugin_activate() {
    // Your activation logic goes here.
}
register_activation_hook( __FILE__, 'imwcyad_plugin_activate' );

function imwcyad_plugin_deactivate() {
    // Your deactivation logic goes here.

    // Don't forget to:
    // Remove Scheduled Actions
    // Remove Notes in the Admin Inbox
    // Remove Admin Tasks
}
register_deactivation_hook( __FILE__, 'imwcyad_plugin_deactivate' );


if ( ! class_exists( 'WcYandexDelivery' ) ) :
    /**
     * My Extension core class
     */
    class WcYandexDelivery {

        /**
         * The single instance of the class.
         */
        protected static $_instance = null;

        /**
         * Constructor.
         */
        protected function __construct() {
            $this->includes();
            $this->init();
        }

        /**
         * Main Extension Instance.
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Cloning is forbidden.
         */
        public function __clone() {
            wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'woocommerce' ), '2.1' );
        }

        /**
         * Unserializing instances of this class is forbidden.
         */
        public function __wakeup() {
            wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'woocommerce' ), '2.1' );
        }

        /**
        * Function for loading dependencies.
        */
        private function includes() {
            $loader = include_once dirname( __FILE__ ) . '/' . 'vendor/autoload.php';

            if ( ! $loader ) {
                throw new Exception( 'vendor/autoload.php missing please run `composer install`' );
            }

            require_once dirname( __FILE__ ) . '/' . 'includes/functions.php';
        }

        /**
         * Function for getting everything set up and ready to run.
         */
        private function init() {
            // Register shipping method
            add_action( 'woocommerce_shipping_init', [ $this, 'init_shipping_method' ] );
            add_filter( 'woocommerce_shipping_methods', [ $this, 'add_shipping_method' ] );
        }

        public function init_shipping_method() {
            require_once __DIR__ . '/includes/WC_Yandex_Shipping_Method.php';
        }

        public function add_shipping_method( $shipping_methods ) {
            $shipping_methods[ IMYAD_PLUGIN_ID ] = 'WC_Yandex_Shipping_Method';

            return $shipping_methods;
        }
    }
endif;

/**
 * Function for delaying initialization of the extension until after WooComerce is loaded.
 */
function imwcyad_plugin_initialize() {

    // This is also a great place to check for the existence of the WooCommerce class
    if ( ! class_exists( 'WooCommerce' ) ) {
    // You can handle this situation in a variety of ways,
    //   but adding a WordPress admin notice is often a good tactic.
        return;
    }

    define( 'IMYAD_PLUGIN_ID', 'imicra-yandex-delivery' );

    $GLOBALS['imwcyad'] = WcYandexDelivery::instance();
}
add_action( 'plugins_loaded', 'imwcyad_plugin_initialize', 10 );
