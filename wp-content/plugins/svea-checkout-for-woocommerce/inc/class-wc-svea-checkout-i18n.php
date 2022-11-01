<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Svea_Checkout_i18n {

    const TEXT_DOMAIN = 'svea-checkout-for-woocommerce';

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Init function
     */
    public function init() {
        add_action( 'plugins_loaded', array( $this, 'load_language_files' ) );
    }

    /**
     * Loads the language-files to be used throughout the plugin
     *
     * @return  void
     */
    public function load_language_files() {
        load_plugin_textdomain( self::TEXT_DOMAIN, false, plugin_basename( WC_SVEA_CHECKOUT_DIR ) . '/languages' ); 
    }

}
