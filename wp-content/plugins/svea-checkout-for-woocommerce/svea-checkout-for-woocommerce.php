<?php if( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * @wordpress-plugin
 * Plugin Name: Svea Checkout for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/svea-checkout-for-woocommerce/
 * Description: Process payments in WooCommerce via Svea Checkout.
 * Version: 1.18.3
 * Author: The Generation AB
 * Author URI: https://thegeneration.se
 * Text Domain: svea-checkout-for-woocommerce
 * Domain Path: languages
 * WC requires at least: 4.0.0
 * WC tested up to: 6.5.1
 */

/**
 * Define absolute constants for paths to be used in the plugin files
 */
if( ! defined( 'WC_SVEA_CHECKOUT_DIR' ) )
	define( 'WC_SVEA_CHECKOUT_DIR', __DIR__ );

if( ! defined( 'WC_SVEA_CHECKOUT_FILE' ) )
	define( 'WC_SVEA_CHECKOUT_FILE', __FILE__ );

if( ! class_exists( 'WC_Svea_Checkout' ) ) :

    class WC_Svea_Checkout {

	    /**
	     * @var string Name of the plugin
	     */
	    private $plugin_name;

	    /**
	     * @var string Version of the plugin
	     */
	    private $version;

	    /**
	     * @var string Description of the plugin
	     */
		private $plugin_description;

	    /**
	     * @var string Label of the plugin
	     */
		private $plugin_label;

        private static $instance = null;

        /**
         * Constructor
         */
        public function __construct() {
	        if( is_null( self::$instance ) ) {
		        self::$instance = $this;
	        }

            // The plugin text domain
            $this->plugin_name = 'svea-checkout-for-woocommerce';
            // The version of the plugin
            $this->version = '1.18.3';

            // Load all dependencies
            $this->load_dependencies();

            $this->init_notices();

            $this->init_language();

            if( ! self::is_woocommerce_installed() ) {
                if( isset( $_GET['action'] ) && ! in_array( $_GET['action'], array( 'activate-plugin', 'upgrade-plugin', 'activate', 'do-plugin-upgrade' ) ) ) {
                    return;
                }

                self::add_admin_notice(
                    'error',
                    __( 'WooCommerce Svea Checkout Gateway has been deactivated because WooCommerce is not installed. Please install WooCommerce and re-activate.', WC_Svea_Checkout_i18n::TEXT_DOMAIN )
                );

                add_action( 'admin_init', array( $this, 'deactivate_plugin' ) );
                return;
            }

            $this->init_modules();

            $this->add_hooks();

            // Register activation hook
            register_activation_hook( WC_SVEA_CHECKOUT_FILE, __CLASS__ . '::activate_plugin' );

            // Register de-activation hook
	        register_deactivation_hook( WC_SVEA_CHECKOUT_FILE, __CLASS__ . '::handle_deactivation' );
            
            // The description of the plugin
            $this->plugin_description = __( 'Process payments in WooCommerce via Svea Checkout.', WC_Svea_Checkout_i18n::TEXT_DOMAIN );
            // The label of the plugin
            $this->plugin_label = __( 'WooCommerce Svea Checkout Payment Gateway', WC_Svea_Checkout_i18n::TEXT_DOMAIN );
        }

        public static function get_instance() {
	        if( is_null( self::$instance ) ) {
		        self::$instance = new WC_Gateway_Svea_Checkout();
	        }

	        return self::$instance;
        }

        /**
         * Add hooks for admin notices
         *
         * @return void
         */
        public function init_notices() {
            add_action( 'admin_notices', array( $this, 'display_admin_notices' ), 10, 1 );
        }

        /**
         * Display admin notices
         *
         * @return void
         */
        public function display_admin_notices() {
            if( $notices = get_option( 'svea_checkout_admin_notices' ) ) {
                foreach( $notices as $notice ) {
                    echo '<div class="'.$notice['type'].'"><p>'.$notice['message'].'</p></div>';
                }

                delete_option( 'svea_checkout_admin_notices' );
            }
        }

        /**
         * Add admin notices to be displayed
         *
         * @param string $type The type of message
         * @param string $message The message to be displayed
         * @return boolean whether or not the notices were saved
         */
        public static function add_admin_notice( $type, $message ) {
            $notices = get_option( 'svea_checkout_admin_notices', array() );

            $notice = array(
                'type'      => $type,
                'message'   => $message,
            );

            if( in_array( $notice, $notices ) ) {
                return false;
            }

            $notices[] = $notice;

            return update_option( 'svea_checkout_admin_notices', $notices );
        }

        /**
         * Deactivate this plugin
         *
         * @return void
         */
        public function deactivate_plugin() {
            if( ! function_exists( 'deactivate_plugins' ) ) {
	            return;
            }

            deactivate_plugins( plugin_basename( __FILE__ ) );
        }

        /**
         * Check if WooCommerce is installed and activated
         *
         * @return boolean whether or not WooCommerce is installed
         */
        public static function is_woocommerce_installed() {

            /**
             * Get a list of active plugins
             */
            $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
			
            /**
             * Loop through the active plugins
             */
            foreach ( $active_plugins as $plugin ) {

                /**
                 * If the plugin name matches WooCommerce
                 * it means that WooCommerce is active
                 */
                if ( preg_match( '/.+\/woocommerce\.php/', $plugin ) ) {
                    return true;
                }
			}
			
			// Get a list of network activated plugins
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				$active_plugins = get_site_option( 'active_sitewide_plugins' );

				// Get keys from active plugins array
				if ( is_array( $active_plugins ) ) {
					$active_plugins = array_keys( $active_plugins );
				} else {
					$active_plugins = [];
				}

				foreach ( $active_plugins as $plugin ) {

					/**
					 * If the plugin name matches WooCommerce
					 * it means that WooCommerce is active
					 */
					if ( preg_match( '/.+\/woocommerce\.php/', $plugin ) ) {
						return true;
					}
				}
			}

            return false;
        }

        /**
         * Actions to be run when the plugin has been activated
         *
         * @return void
         */
        public static function activate_plugin() {
            self::create_pages();
        }

	    /**
	     * Actions to be run when the plugin has been deactivated
	     *
	     * @return void
	     */
        public static function handle_deactivation() {

        	// Load Gateway class if not loaded
        	if( ! class_exists( 'WC_Gateway_Svea_Checkout' ) ) {
        		require_once WC_SVEA_CHECKOUT_DIR . '/inc/class-wc-gateway-svea-checkout.php';
	        }

	        // Clear merchant cache
	        WC_Gateway_Svea_Checkout::clear_merchant_validation_cache();
        }

        /**
         * Require dependencies
         *
         * @return void
         */
        public function load_dependencies() {
            /**
             * Require the composer autoloader
             */
            require_once WC_SVEA_CHECKOUT_DIR . '/vendor/autoload.php';
            
            /**
             * Require all the classes we need
             */
            require_once WC_SVEA_CHECKOUT_DIR . '/inc/class-wc-svea-checkout-i18n.php';
	        require_once WC_SVEA_CHECKOUT_DIR . '/inc/class-wc-svea-checkout-helper.php';
            require_once WC_SVEA_CHECKOUT_DIR . '/inc/class-wc-svea-checkout-scripts.php';
            require_once WC_SVEA_CHECKOUT_DIR . '/inc/class-wc-svea-checkout-order.php';
            require_once WC_SVEA_CHECKOUT_DIR . '/inc/shortcodes/class-wc-svea-checkout-shortcode-checkout.php';
            require_once WC_SVEA_CHECKOUT_DIR . '/inc/compat/class-wc-svea-checkout-compat.php';
        }

        /**
         * Create pages that the plugin relies on, storing page id's in variables.
         */
        public static function create_pages() {  
            $should_create_checkout_page = false;

            $checkout_page_id = get_option( 'wc_svea_checkout_page_id', false );
            if( ! $checkout_page_id ) {
                $should_create_checkout_page = true;
            } else {
                $found_checkout_post = get_post( $checkout_page_id );

                if( is_null( $found_checkout_post ) || $found_checkout_post->post_status == 'trash') {
                    $should_create_checkout_page = true;
                }
            }

            if( $should_create_checkout_page ) {

                $checkout_page = array(
                    'post_type'     => 'page',
                    'post_status'   => 'publish',
                    'post_title'    => _x( 'Svea Checkout', 'Page title', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                    'post_name'     => _x( 'svea-checkout', 'Page slug', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                    'post_content'  => '[svea_checkout]',
                );

                $checkout_post_id = wp_insert_post( $checkout_page );
                update_option( 'wc_svea_checkout_page_id', $checkout_post_id );
            }

        }

        /**
         * Get setting link.
         *
         * @since 1.0.0
         *
         * @return string Setting link
         */
        public function get_setting_link() {
            $use_id_as_section = version_compare( WC()->version, '2.6', '>=' );
            $section_slug = $use_id_as_section ? 'svea-checkout' : strtolower( 'WC_Gateway_Svea_Checkout' );

            return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $section_slug );
        }

        /**
         * Initialize the gateway. Called very early - in the context of the plugins_loaded action
         *
         * @since 1.0.0
         */
        public function init_gateways() {
            if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
                return;
            }

            include_once ( WC_SVEA_CHECKOUT_DIR . '/inc/class-wc-gateway-svea-checkout.php' );

            add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );
        }

	    /**
	     *
	     */
        public function init_admin() {
	        require_once WC_SVEA_CHECKOUT_DIR . '/inc/class-wc-svea-checkout-admin.php';

	        $admin = new WC_Svea_Checkout_Admin( $this->plugin_name, $this->version );
	        $admin->init();
        }

        /**
         * Add the gateway WC_Gateway_Svea_Checkout to Woocommerce
         *
         * @since 1.0.0
         */
        public function add_gateways( $methods ) {
            $methods[] = 'WC_Gateway_Svea_Checkout';

            return $methods;
        }

        /**
         * Init plugin modules for functionality
         *
         * @return void
         */
        public function init_modules() {
            $i18n = new WC_Svea_Checkout_i18n( $this->plugin_name, $this->version );
            $i18n->init();

            $scripts = new WC_Svea_Checkout_Scripts( $this->plugin_name, $this->version );
            $scripts->init();
            
            $shortcode = new WC_Svea_Checkout_Shortcode_Checkout( $this->plugin_name, $this->version );
            $shortcode->init();

            $order = new WC_Svea_Checkout_Order( $this->plugin_name, $this->version );
            $order->init();
            
            $compat = new WC_Svea_Checkout_Compat( $this->plugin_name, $this->version );
            $compat->init();
        }

        /**
         * Init plugin language module
         *
         * @return void
         */
        public function init_language() {
            $i18n = new WC_Svea_Checkout_i18n( $this->plugin_name, $this->version );
            $i18n->init();
        }

        /** 
         * Add function hooks
         *
         * @return void
         */
        public function add_hooks() {
            add_action( 'plugins_loaded', array( $this, 'init_gateways' ), 10, 1 );
	        add_action( 'plugins_loaded', array( $this, 'init_admin' ), 15, 1 );
            add_action( 'admin_init', array( $this, 'check_compatibility' ) );
        }

	    /**
	     * Check if the shop meets the requirements
	     *
	     * @return void
	     */
        public function check_compatibility() {
			$wc_price_num_decimals = get_option( 'woocommerce_price_num_decimals' );

			if( $wc_price_num_decimals !== false && $wc_price_num_decimals < 2 ) {
				self::add_admin_notice(
					'error',
					sprintf(
						__( 'WooCommerce decimals is set to %d, lower than 2 which is the required setting for Svea Checkout to work properly. '
						    . 'If you want to hide decimals altogether, add this snippet to your functions.php: %s'
							. 'If you have just changed the setting, you can ignore this message.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
						$wc_price_num_decimals,
						'<br /><br /><code>/**<br />&nbsp;&nbsp;* Trim zeros in price decimals<br />&nbsp;&nbsp;*/<br />add_filter( \'woocommerce_price_trim_zeros\', \'__return_true\' );</code><br /><br />'
					)
				);
			}
        }
    }

    new WC_Svea_Checkout;

endif;
