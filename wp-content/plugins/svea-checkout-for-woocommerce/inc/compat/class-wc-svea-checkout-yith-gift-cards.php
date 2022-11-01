<?php if( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Compability with the YITH gift cards plugin
 */
class WC_Svea_Checkout_Yith_Gift_Cards {

    /**
     * Plugin name
     *
     * @var string
     */
    public $plugin_name;

    /**
     * Version number
     *
     * @var string
     */
    public $version;

    /**
     * Run class
     *
     * @param string $plugin_name
     * @param string $version
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Is it the premium version of the plugin
     *
     * @return boolean
     */
    public function is_premium() {
        return class_exists('YITH_YWGC_Backend_Premium');
    }
    
    /**
     * Init function, add hooks
     *
     * @return void
     */
    public function init() {
        if ( class_exists( 'YITH_YWGC_Cart_Checkout' ) ) {
            
            // Make sure the totals are correct and then maybe redirect
            add_action( 'woocommerce_sco_before_maybe_redirect_regular_checkout', [ $this, 'set_total_after_gift_card' ] );

            // Re-hook this so it updates on order completion insead
            add_action( 'woocommerce_new_order', [ $this, 'remove_yith_hook' ], 5, 2 );

            // Register gift card on order process
            add_action( 'woocommerce_checkout_order_processed', [ $this, 'register_gift_cards_usage' ] );
            
            // Recalc with coupons on push from Svea
            add_action( 'woocommerce_sco_after_push_order', [ $this, 'recalc_on_order_save' ] );
        }
    }
    
    /**
     * Recalc the order with the gift cards
     *
     * @param \WC_Order $wc_order
     * @return void
     */
    public function recalc_on_order_save( $wc_order ) {
        if ( $wc_order->meta_exists('_svea_co_order_final') ) {
            if ( $this->is_premium() ) {
                YITH_YWGC_Backend_Premium::get_instance()->update_totals_on_save_order_items( $wc_order, null );
            } else {
                YITH_YWGC_Backend::get_instance()->update_totals_on_save_order_items( $wc_order->get_id(), null );
            }
        }
    }

    /**
     * Remove the YITH hooks when the order comes from svea, we'll do it ourself in another step of the process
     *
     * @param int $order_id
     * @param \WC_Order $wc_order
     * @return void
     */
    public function remove_yith_hook( $order_id, $wc_order ) {
        if ( $wc_order->get_payment_method() == WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
            remove_action( 'woocommerce_new_order', [ YITH_YWGC_Cart_Checkout::get_instance(), 'register_gift_cards_usage' ] );
        }
    }

    /**
     * Update the balance for all gift cards applied to an order.
     *
     * @param int $order_id
     */
    public function register_gift_cards_usage( $order_id ) {
        // For the regular checkout this will be made from YITH     
        $method = get_post_meta( $order_id, '_payment_method', true );
        
        // Since some updates can come from a webhook we need to make sure that the order is an Svea order
        if ( $method === WC_Gateway_Svea_Checkout::GATEWAY_ID ){
            $yith = YITH_YWGC_Cart_Checkout::get_instance();
            
            $yith->apply_gift_cards_discount( WC()->cart );
            $yith->register_gift_cards_usage( $order_id );
        }
    }


    /**
     * Setup the totals from YITH
     *
     * @return void
     */
    public function set_total_after_gift_card() {
        // Make YITH calculate the totals before we go here
        $yith = YITH_YWGC_Cart_Checkout::get_instance( WC()->cart );
        $yith->apply_gift_cards_discount( WC()->cart );
    }
}
