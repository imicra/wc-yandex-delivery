<?php
/**
 * Class Order file.
 */

use Imicra\WcYandexDelivery\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Order {
    /**
     * The single instance of the class.
     */
    protected static $_instance = null;

    protected function __construct() {
        $this->init();
    }

    /**
     * Main Order Instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function init() {
        add_action( 'woocommerce_checkout_create_order', [ $this, 'create_order' ] );
        add_action( 'woocommerce_payment_complete', [ $this, 'accept_claims' ] );
        add_action( 'woocommerce_admin_order_items_after_shipping', [ $this, 'order_buttons' ] );
    }

    /**
     * Set total and shipping_total to a new order when a new order is create.
     */
    private function create_order( $order ) {
        if ( isset( $_POST['imwcyad_cost'] ) && ! empty( $_POST['imwcyad_cost'] ) ) {
            $cost = (int)$_POST["imwcyad_cost"];
            $total = $order->get_total();
            $total = $total + $cost;

            $order->set_shipping_total( $cost );
            $order->set_total( $total );
        }

        // create order meta for keep claim id in order
        if ( isset( $_POST['imwcyad_data'] ) && ! empty( $_POST['imwcyad_data'] ) ) {
            $claim_id = wp_unslash( $_POST['imwcyad_data'] );

            $order->update_meta_data( 'imwcyad_claim_id', $claim_id );
        }
    }

    /**
     * Create request to api for accept claim when order status changed from pending to processing.
     */
    private function accept_claims( $order_id ) {
        $order = wc_get_order( $order_id );
        $claim_id = $order->get_meta( 'imwcyad_claim_id' );

        if ( $claim_id ) {
            $path =  'claims/accept';
            $query = "claim_id={$claim_id}";
            $url = "https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/$path?$query";
            $token = Helper::getActualShippingMethod()->get_option( 'client_secret' );
            $headers = [
                'headers' => [
                    'Authorization'   => "Bearer {$token}",
                    'Accept-Language' => 'ru',
                    'Content-Type'    => 'application/json'
                ]
            ];
            $body = [
                'version' => 1
            ];
            $args['body'] = json_encode( $body );
            $args = array_merge( $headers, $args );
            $response = wp_remote_post( $url, $args );
            $result = wp_remote_retrieve_body( $response );
            $result = json_decode( $result, true );

            // create order meta for keep claim data in order
            $order->update_meta_data( 'imwcyad_data', $result );
        }
    }

    /**
     * Buttons in order item shipping.
     */
    private function order_buttons( $order_id ) {
        $order = wc_get_order( $order_id );
        $claim_data = $order->get_meta( 'imwcyad_data' );

        if ( $claim_data ) :
        ?>
            <tr>
                <td></td>
                <td>
                    <?php
                    // if accept claim success
                    if ( array_key_exists( 'id', $claim_data ) ) :
                    ?>
                        <button type="button" class="button imwcyad_btn_info" data-id="<?php echo $claim_data['id']; ?>">Информация по заявке</button>
                        <button type="button" class="button imwcyad_btn_cancel" data-claim-id="<?php echo $claim_data['id']; ?>" data-order-id="<?php echo $order_id; ?>">Отмена заявки</button>
                        <div class="imwcyad_order_info" style="display: none;">
                            <div class="cancel">Возможность отмены: <b>-</b></div>
                            <div class="status">Статус заявки: <b>-</b></div>
                            <div class="message">Ошибка: <b>Нет ошибок</b></div>
                        </div>
                    <?php
                    // if accept claim error
                    else :
                    ?>
                        <?php echo $claim_data['code'] . ' : ' . $claim_data['message']; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php
        endif;
    }
}
