<?php
/**
 * Plugin Name: WooCommerce Yandex Delivery
 * Plugin URI: https://github.com/imicra/wc-yandex-delivery
 * Description: Интеграция Яндекс Доставки с WooCommerce.
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
            require_once dirname( __FILE__ ) . '/' . 'includes/Ajax.php';
            // require_once dirname( __FILE__ ) . '/' . 'includes/Order.php';
        }

        /**
         * Function for getting everything set up and ready to run.
         */
        private function init() {
            // Register shipping method
            add_action( 'woocommerce_shipping_init', [ $this, 'init_shipping_method' ] );
            add_filter( 'woocommerce_shipping_methods', [ $this, 'add_shipping_method' ] );
            add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
            add_action( 'admin_enqueue_scripts', [ $this, 'register_assets_admin' ] );

            new Ajax;
            // Order::instance();
        }

        public function init_shipping_method() {
            require_once __DIR__ . '/includes/WC_Yandex_Shipping_Method.php';
        }

        public function add_shipping_method( $shipping_methods ) {
            $shipping_methods[ IMYAD_PLUGIN_ID ] = 'WC_Yandex_Shipping_Method';

            return $shipping_methods;
        }

        public static function register_assets() {
            if ( is_checkout() ) {
                wp_enqueue_style( IMYAD_PLUGIN_ID . '-checkout', plugins_url( '/assets/css/style.css', __FILE__ ), [], IMYAD_SCRIPT_VERSION );
                wp_enqueue_script( IMYAD_PLUGIN_ID . '-checkout', plugins_url( '/assets/js/checkout.js', __FILE__ ), [], IMYAD_SCRIPT_VERSION, true );

                wp_localize_script( IMYAD_PLUGIN_ID . '-checkout', 'imwcyad',
                    array(
                        'debug' => SCRIPT_DEBUG,
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                    )
                );
            }
        }

        public function register_assets_admin( $hook_suffix ) {
            if ( 'woocommerce_page_wc-orders' === $hook_suffix ) {
                wp_enqueue_script( IMYAD_PLUGIN_ID . '-order', plugins_url( '/assets/js/order.js', __FILE__ ), [], IMYAD_SCRIPT_VERSION, true );

                wp_localize_script( IMYAD_PLUGIN_ID . '-order', 'imwcyad',
                    array(
                        'debug' => SCRIPT_DEBUG,
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'statuses' => json_encode( \Imicra\WcYandexDelivery\Status::namesList() ),
                        'available_cancel_state' => json_encode( \Imicra\WcYandexDelivery\Status::cancelInfo() ),
                    )
                );
            }

            if ( 'woocommerce_page_wc-settings' === $hook_suffix ) {
                wp_enqueue_script( IMYAD_PLUGIN_ID . '-settings', plugins_url( '/assets/js/settings.js', __FILE__ ), [], IMYAD_SCRIPT_VERSION, true );

                wp_localize_script( IMYAD_PLUGIN_ID . '-settings', 'imwcyad',
                    array(
                        'debug' => SCRIPT_DEBUG,
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'plugin_id' => IMYAD_PLUGIN_ID,
                    )
                );
            }
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
    define( 'IMYAD_SCRIPT_VERSION', '1.0.4' );

    $GLOBALS['imwcyad'] = WcYandexDelivery::instance();
}
add_action( 'plugins_loaded', 'imwcyad_plugin_initialize', 10 );
