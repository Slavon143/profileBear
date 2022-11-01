<?php if( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class WC_Svea_Checkout_Compat {

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
     * Init function, add hooks
     *
     * @return void
     */
    public function init() {
        add_action( 'init', [ $this, 'check_for_plugins'] );
    }  


    /**
     * Check for plugins that might need compatability
     *
     * @return void
     */
    public function check_for_plugins() {
        if ( function_exists( 'YITH_YWGC' ) ) {
            require_once WC_SVEA_CHECKOUT_DIR . '/inc/compat/class-wc-svea-checkout-yith-gift-cards.php';

            $gift_cards = new WC_Svea_Checkout_Yith_Gift_Cards( $this->plugin_name, $this->version );
            $gift_cards->init();
        }
    }
}
