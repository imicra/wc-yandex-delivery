<?php
/**
 * Class WC_Yandex_Shipping_Method file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yandex Delivery Shipping Method.
 */
class WC_Yandex_Shipping_Method extends WC_Shipping_Method {
    /**
	 * Constructor.
	 *
	 * @param int $instance_id Instance ID.
	 */
	public function __construct( $instance_id = 0 ) {
        parent::__construct( $instance_id );

		$this->id                 = IMYAD_PLUGIN_ID;
		$this->method_title       = 'Яндекс Доставка';
		$this->method_description = 'Яндекс Доставка IMICRA';
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();
	}

    /**
	 * Initialize settings.
	 */
	public function init() {

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// // Define user set variables.
		$this->title = $this->get_option( 'title' );

		// Actions.
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

    /**
	 * Calculate shipping.
	 *
	 * @param array $package Package information.
	 */
	public function calculate_shipping( $package = array() ) {
        // if ( ! empty( $_REQUEST["post_data"] ) ) {
        //     $post_data = wp_parse_args( $_REQUEST["post_data"] );

        //     if ( ! empty( $post_data["imwcyad_cost"] ) ) {
        //         $cost = (int)$post_data["imwcyad_cost"];
        //         WC()->session->set( 'imwcyad_cost', $cost );
        //     }
        // }

        // $cost = WC()->session->get( 'imwcyad_cost' );

        // if ( empty( $cost ) ) {
        //     $cost = 0;
        // }

		$this->add_rate(
			array(
				'label'   => $this->title,
				'package' => $package,
                //'cost' => $cost // get from api
			)
		);
	}

    /**
	 * Init form fields.
	 */
	public function init_form_fields() {
		$this->instance_form_fields = array(
			'title'      => array(
				'title'       => __( 'Name', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Your customers will see the name of this shipping method during checkout.', 'woocommerce' ),
				'default'     => 'Яндекс Доставка',
				'placeholder' => 'Яндекс Доставка',
				'desc_tip'    => true,
			),
        );

        $this->form_fields = [
            'client_secret' => [
                'title'             => 'Токен API Доставки Яндекс',
                'type'              => 'text',
                'custom_attributes' => [
                    'required' => true,
                ],
            ],
            'yandex_map_api_key' => [
                'type'              => 'text',
                'title'             => 'Токен API Яндекс.Карты',
                'description'       => 'Ключ доступа к API Яндекс. Процесс генерации описан на <a rel="noopener nofollower" href="https://yandex.ru/dev/jsapi-v2-1/doc/ru/#get-api-key" target="_blank">странице</a>.',
                'custom_attributes' => [
                    'required' => true,
                ],
            ],
            'seller_address' => [
                'title'       => 'Адрес продавца',
                'type'        => 'text',
                // 'desc_tip'    => true,
                'description' => 'Адрес точки выдачи',
            ],
            'seller_name'  => [
                'title'             => 'ФИО',
                'type'              => 'text',
                'custom_attributes' => [
                    'required' => true,
                ],
            ],
            'seller_phone' => [
                'title'             => 'Телефон',
                'type'              => 'text',
                // 'desc_tip'          => true,
                'description'       => 'Должен передаваться в международном формате: код страны (для России +7) и сам номер (10 и более цифр)',
                'custom_attributes' => [
                    'required' => true,
                ],
            ],
            'default_weight' => [
				'title'             => 'Вес товара по умолчанию',
				'type'              => 'number',
				'description'       => 'Вес товара в г',
				'custom_attributes' => [
					'min' => 1,
				],
			],
			'default_width' => [
				'title'             => 'Ширина товара по умолчанию',
				'type'              => 'number',
				'description'       => 'Ширина товара в cм',
				'custom_attributes' => [
					'min' => 1,
				],
			],
			'default_length' => [
				'title'             => 'Длина товара по умолчанию',
				'type'              => 'number',
				'description'       => 'Длина товара в cм',
				'custom_attributes' => [
					'min' => 1,
				],
			],
			'default_height' => [
				'title'             => 'Высота товара по умолчанию',
				'type'              => 'number',
				'description'       => 'Высота товара в cм',
				'custom_attributes' => [
					'min' => 1,
				],
			],
            'warehouse_lon' => [
                'type'        => 'hidden',
            ],
            'warehouse_lat' => [
                'type'        => 'hidden',
            ],
        ];
	}

    /**
     * Enable settings page.
     */
    public function has_settings() {
        return true;
    }
}
