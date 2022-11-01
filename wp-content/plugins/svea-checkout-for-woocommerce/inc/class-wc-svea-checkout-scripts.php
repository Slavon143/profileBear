<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Svea_Checkout_Scripts {

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Init function
     *
     * @return void
     */
    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_scripts' ) );
    }

    /**
     * Enqueues scripts and styles for the frontend
     *
     * @return void
     */
    public function enqueue_frontend_scripts() {

	    /**
	     * Enqueue part payment widget scrips on product-page if part payment widget is active
	     */
	    if( is_product() && WC_Gateway_Svea_Checkout::get_instance()->display_product_widget === 'yes' ) {

		    wp_enqueue_style( 'wc-svea-checkout-part-payment-widget', plugins_url( 'assets/css/frontend/part-payment/part_payment_widget.min.css', __DIR__ ), array(), $this->version );
	    }

        /**
         * Only enqueue frontend scripts on checkout page
         */
	    if( ( ! function_exists( 'is_checkout' ) || ! is_checkout() )
	        && ( ! function_exists( 'is_checkout_pay_page' ) || ! is_checkout_pay_page() ) ) {
		    return;
	    }

        wp_enqueue_style( 'wc-svea-checkout-frontend', plugins_url( 'assets/css/frontend/application.min.css', __DIR__ ), array(), $this->version );
        wp_enqueue_script( 'wc-svea-checkout-frontend', plugins_url( 'assets/js/frontend/application.min.js',  __DIR__ ), array( 'jquery', 'wc-checkout' ), $this->version, true );

	    $svea_checkout_gateway = WC_Gateway_Svea_Checkout::get_instance();

        wp_localize_script('wc-svea-checkout-frontend', 'wc_sco_params', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'security' => wp_create_nonce( 'wc-svea-checkout' ),
                'refresh_sco_snippet_nonce' => wp_create_nonce( 'refresh-sco-snippet' ),
                'update_sco_order_information' => wp_create_nonce( 'update-sco-order-information' ),
                'sco_heartbeat_nonce' => wp_create_nonce( 'sco_heartbeat' ),
		        'sync_zip_code' => $svea_checkout_gateway->get_option( 'sync_zip_code' ) === 'yes',
            )
        ); 
    }

    /**
     * Enqueues scripts and styles for the backend
     *
     * @return void
     */
    public function enqueue_backend_scripts() { }

}