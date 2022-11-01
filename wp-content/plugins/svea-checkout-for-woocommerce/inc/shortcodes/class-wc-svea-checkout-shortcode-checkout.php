<?php if( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Checkout shortcode
 * 
 * Used on the checkout page to display the checkout
 * 
 */
class WC_Svea_Checkout_Shortcode_Checkout {
    
    const SVEA_MAX_NAME_LENGTH = 40;

    private static $sco_wc_order_id = false;
    private static $skip_next_sco_order_update_hook = false;
    private static $preset_values = false;

	/**
	 * Constructor
	 *
	 * @param $plugin_name
	 * @param $version
	 */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Init function
     */
    public function init() {
        add_shortcode( 'svea_checkout', array( $this, 'display_svea_checkout_page' ) ); 
        add_action( 'wp', array( $this, 'load_svea_checkout_order' ) );
        add_action( 'wc_ajax_refresh_sco_snippet', array( $this, 'refresh_sco_snippet' ) );
        add_action( 'wc_ajax_update_sco_order_information', array( $this, 'update_order_information' ) );
        add_action( 'woocommerce_thankyou', array( $this, 'display_thank_you_box' ), 10, 1 );
        add_filter( 'woocommerce_is_checkout', array( $this, 'set_svea_checkout_as_checkout_page' ), 40, 1 );
        add_filter( 'body_class', array( $this, 'svea_confirmation_page_body_class' ), 10, 2 );
        add_filter( 'body_class', array( $this, 'add_checkout_body_class' ) );
    }

	/**
	 * Add css-class to body element on the checkout page
	 *
	 * @param array $classes array of css-classes to body
	 *
	 * @return array $classes
	 */
    public function add_checkout_body_class( $classes ) {
        if( self::$sco_wc_order_id !== false ) {
            $classes[] = 'svea-checkout-page';
        }
       
        return $classes;	
    }

	/**
	 * Add css-class to body element on the confirmation page
	 *
	 * @param array $classes array of css-classes to body
	 *
	 * @return array $classes
	 */
    public function svea_confirmation_page_body_class( $classes ) {
        global $wp_query;
       
        if( ! isset( $wp_query->query_vars['order-received'] ) ) {
            return $classes;
        }
        
        $order_id = $wp_query->query_vars['order-received'];
        
        $wc_order = wc_get_order( $order_id );
        
        if( ! $wc_order ) {
            return $classes;
        }

        if( $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
            return $classes;
        }

        if( ! ( $svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true ) ) ) {
            return $classes;
        }
        
        $classes[] = 'svea-checkout-confirmation-page';
          
        return $classes;
    }
    
    /**
     * This function checks if Svea Checkout page is set as the checkout in WooCommerce
     * 
     * @param boolean $is_checkout true or false
     * @return boolean $is_checkout returns true or false if its set in the settings in WooCommerce
     */
    public function set_svea_checkout_as_checkout_page( $is_checkout ) {
        if( self::$sco_wc_order_id !== false ) {
            $is_checkout = true;
        }

        return $is_checkout;
    }
    /**
     * Display iframe on order received page
     *
     * @param int $order_id ID of the order being displayed
     * @return void
     */
    public function display_thank_you_box( $order_id ) {
        $wc_order = wc_get_order( $order_id );

        if( ! $wc_order ) {
            return;
        }
        
        if( $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
            return;
        }

        if( ! ( $svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true ) ) ) {
            return;
        }

        $svea_order_id = absint( $svea_order_id );

        if( $svea_order_id <= 0 ) {
            return;
        }
        
        WC_Gateway_Svea_Checkout::log( sprintf( 'Displaying order thank you box for order %s', $wc_order->get_id() ) );
        $country_settings = WC_Gateway_Svea_Checkout::get_instance()->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );
        $checkout_merchant_id = $country_settings['MerchantId'];
        $checkout_secret = $country_settings['Secret'];
        $base_url = $country_settings['BaseUrl'];
        $connector = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $base_url );
        $checkout_client = new \Svea\Checkout\CheckoutClient( $connector );

        $data = array(
            'OrderId'   => absint( $svea_order_id ),
        );

        try {
            
            $response = $checkout_client->get( apply_filters( 'woocommerce_sco_get_order', $data ) );
            
        } catch( Exception $e ) {
            // Log errors from svea when getting order
            WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when getting Svea order: %s', $e->getMessage() ) );
            return;
        }

        ?>
        <div class="wc-svea-checkout-thank-you-box">
            <?php echo $response['Gui']['Snippet']; ?>
        </div>
        <?php
    }

	/**
     * Saves row numbers from Svea to WooCommerce order rows
     *
	 * @param $svea_cart_items
     *
     * @return void
	 */
    public static function link_wc_order_items_with_svea( $svea_cart_items, $wc_order ) {
	    foreach( $svea_cart_items as $svea_cart_item ) {
		    if( ! isset( $svea_cart_item['TemporaryReference'] ) || ! isset( $svea_cart_item['RowNumber'] ) ) {
			    continue;
		    }

		    $svea_temporary_reference = $svea_cart_item['TemporaryReference'];
		    $svea_row_number  = absint( $svea_cart_item['RowNumber'] );

		    // Save ID of rounding order row
		    if( $svea_temporary_reference === 'ROUNDING' ) {
		        $wc_order->update_meta_data( '_svea_co_rounding_order_row_id', $svea_row_number );
		        $wc_order->save();
            } else {
			    $wc_order_item_id = absint( $svea_temporary_reference );

			    if ( $wc_order_item_id > 0 && $svea_row_number > 0 ) {
				    wc_update_order_item_meta( $wc_order_item_id, '_svea_co_order_row_id', $svea_row_number );
			    }
		    }
	    }
    }

    /**
     * Update order information from ajax
     *
     * @return void
     */
    public function update_order_information() {
        check_ajax_referer( 'update-sco-order-information', 'security' );
        
        if( ! isset( $_POST['form_data'] ) ) {
            die();
        }

        $wc_order_id = absint( WC()->session->get( 'order_awaiting_payment' ) );

        // Check if there's an ongoing order
        if( ! $wc_order_id || ! ( $wc_order = wc_get_order( $wc_order_id ) )
            || $wc_order->get_created_via() != 'svea_checkout' || ! current_user_can( 'pay_for_order', $wc_order_id ) ) {
            die();
        }

        parse_str( $_POST['form_data'], $form_data );

	    do_action( 'woocommerce_sco_before_update_order_information', $wc_order, $form_data );
       
        $enable_signup = get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) == 'yes' ? true : false;

        // Check if user isn't loggedin and WooCommerce settings "woocommerce_enable_signup_and_login_from_checkout" is true
        if( $enable_signup && ! is_user_logged_in() ) {            
            $should_create_account = isset( $form_data['createaccount'] ) && $form_data['createaccount'] == '1';

            $wc_order->update_meta_data( '_should_create_account', $should_create_account );

            WC_Gateway_Svea_Checkout::log( sprintf( 'Create new WooCommerce account: %s' . ' with order id: %n', $should_create_account, $wc_order->get_id() ) );
        }
        
        // Check if there is a comment
        if( isset( $form_data['order_comments'] ) && $form_data['order_comments'] !== '' ) {
            $wc_order->set_customer_note(
                isset( $form_data['order_comments'] ) ? $form_data['order_comments'] : ''
            );

            WC_Gateway_Svea_Checkout::log( sprintf( 'Add notes to order: %s', $wc_order->get_customer_note() ) );
        }



        // Save $_POST to meta
	    $wc_order->update_meta_data( '_sco_post_data', $form_data );

        // Save the order
        $wc_order->save();

	    do_action( 'woocommerce_sco_after_update_order_information', $wc_order, $form_data );
    }
    
    /**
     * Update order and refresh Svea Checkout snippet
     *
     * @return void
     */
    public function refresh_sco_snippet() {
        check_ajax_referer( 'refresh-sco-snippet', 'security' );

	    wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

        // Error message if the cart is empty
        if ( WC()->cart->is_empty() ) {
            $data = array(
                'fragments' => apply_filters( 'woocommerce_update_order_review_fragments', array(
                    '.wc-svea-checkout-checkout-module' => '<div class="wc-svea-checkout-checkout-module"><div class="woocommerce-error">' . __( 'Sorry, your session has expired.', 'woocommerce' ) . ' <a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="wc-backward">' . __( 'Return to shop', 'woocommerce' ) . '</a></div></div>'
                ) )
            );
            wp_send_json( $data );
            
            die();
        }

	    $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

	    if ( isset( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ) {
		    foreach ( $_POST['shipping_method'] as $i => $value ) {
			    $chosen_shipping_methods[ $i ] = wc_clean( $value );
		    }
	    }

	    WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
	    WC()->session->set( 'chosen_payment_method', empty( $_POST['payment_method'] ) ? '' : $_POST['payment_method'] );
	    WC()->customer->set_props( array(
		    'billing_country'   => isset( $_POST['country'] ) ? $_POST['country']     : null,
		    'billing_state'     => isset( $_POST['state'] ) ? $_POST['state']         : null,
		    'billing_postcode'  => isset( $_POST['postcode'] ) ? $_POST['postcode']   : null,
		    'billing_city'      => isset( $_POST['city'] ) ? $_POST['city']           : null,
		    'billing_address_1' => isset( $_POST['address'] ) ? $_POST['address']     : null,
		    'billing_address_2' => isset( $_POST['address_2'] ) ? $_POST['address_2'] : null,
	    ) );

	    WC()->customer->set_props( array(
		    'shipping_country'   => isset( $_POST['country'] ) ? $_POST['country']     : null,
		    'shipping_state'     => isset( $_POST['state'] ) ? $_POST['state']         : null,
		    'shipping_postcode'  => isset( $_POST['postcode'] ) ? $_POST['postcode']   : null,
		    'shipping_city'      => isset( $_POST['city'] ) ? $_POST['city']           : null,
		    'shipping_address_1' => isset( $_POST['address'] ) ? $_POST['address']     : null,
		    'shipping_address_2' => isset( $_POST['address_2'] ) ? $_POST['address_2'] : null,
	    ) );

	    if ( ! empty( $_POST['country'] ) ) {
		    WC()->customer->set_calculated_shipping( true );
	    }

	    WC()->customer->save();
	    WC()->cart->calculate_totals();

	    // Get messages if reload checkout is not true
	    $messages = '';
	    if ( ! isset( WC()->session->reload_checkout ) ) {
		    ob_start();
		    wc_print_notices();
		    $messages = ob_get_clean();
	    }

	    // Get order review fragment
        $fragments = array();

        // If cart total is 0, reload the page, this might redirect to wc checkout page if one is set
        $reload = isset( WC()->session->reload_checkout ) || WC()->cart->total <= 0 ? 'true' : 'false';
	    $reload = apply_filters( 'woocommerce_sco_refresh_snippet_reload', $reload );

	    if( ! filter_var( $reload, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ) {
		    $review_order_template = locate_template( 'svea-checkout-for-woocommerce/checkout/review-order.php' );

		    if( $review_order_template == '' ) {
			    $review_order_template = WC_SVEA_CHECKOUT_DIR . '/templates/checkout/review-order.php';
		    }

		    ob_start();

		    include( $review_order_template );

		    $fragments['.woocommerce-checkout-review-order-table'] = ob_get_clean();
        }

        // Update cart totals without syncing Svea order
        self::$sco_wc_order_id = WC_Svea_Checkout_Order::create_order();

        $svea_checkout_module = self::get_svea_checkout_module( WC()->cart );
        
	    wp_send_json( array(
		    'result'    => empty( $messages ) ? 'success' : 'failure',
		    'messages'  => $messages,
		    'reload'    => $reload,
		    'fragments' => $fragments,
            'sco_snippet' => '<div class="wc-svea-checkout-checkout-module">' . $svea_checkout_module['snippet'] . '</div>',
            'sco_order_id'  => isset( $svea_checkout_module['sco_order_id'] ) ? intval( $svea_checkout_module['sco_order_id'] ) : false,
	    ) );
    }
    
    /**
     * Load Svea order
     *
     * @return void
     */
    public function load_svea_checkout_order() {
        if( ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            || ( defined( 'DOING_WC_AJAX' ) && DOING_WC_AJAX ) ) {
            return;
        }

        global $wp_query;
        $post = $wp_query->queried_object;
            
        if( is_null( $post ) ) {
            return;
        }
        
        // Check we are on the checkout page
        if( ! isset( $post->post_content ) || ! has_shortcode( $post->post_content, 'svea_checkout' ) ) {
            return;
        }

        if( is_wc_endpoint_url( 'order-received' ) && isset( $wp_query->query_vars['order-received'] ) ) {
            return;
            
        // Check if we are on the confirmation page
        } else if( isset( $_GET['confirmation'] ) && $_GET['confirmation'] == '1' && isset( $_GET['order_id'] ) && isset( $_GET['key'] ) ) {

            $wc_order_id = absint( $_GET['order_id'] );
            $wc_order_key = empty( $_GET['key'] ) ? '' : wc_clean( $_GET['key'] );
            $wc_order = false;

            if( $wc_order_id > 0 ) {
                $wc_order = wc_get_order( $wc_order_id );

                if( ! $wc_order || ! $wc_order->key_is_valid( $wc_order_key ) ) {
                    $wc_order = false;
                }
            }
          
            if( $wc_order === false ) {
                wp_redirect( get_the_permalink() );
                exit;
            }
            
            WC_Gateway_Svea_Checkout::get_instance()->process_confirmation( $wc_order->get_id() );
            
        } else {
            // Redirect if cart is empty
            if( WC()->cart->is_empty() ) {
                wp_redirect( wc_get_cart_url() );
                exit;
            }
            
            if( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
                define( 'WOOCOMMERCE_CHECKOUT', true );
            }

            // Allow for checks before the regular redirect
            do_action( 'woocommerce_sco_before_maybe_redirect_regular_checkout' );

            // if cart total sum is 0, maybe redirect to standard woocommerce checkout
            if ( WC()->cart->get_total( 'compare' ) <= 0 ) {
                $this->maybe_redirect_checkout_to_wc();
            }

            // Set Svea Checkout as chosen payment method
	        WC()->session->set( 'chosen_payment_method', WC_Gateway_Svea_Checkout::GATEWAY_ID );

            self::$sco_wc_order_id = WC_Svea_Checkout_Order::create_order();
        }
    }

    /**
     * If the cart total sum is 0, redirect to Woocommerce checkout page. 
     *
     * @return void
     **/
    public function maybe_redirect_checkout_to_wc() {
        $wc_sco = WC_Gateway_Svea_Checkout::get_instance();
        $standard_checkout_page = (int) $wc_sco->get_option( 'standard_checkout_page' );

        $standard_checkout_page_exists = $standard_checkout_page && get_post_status ( $standard_checkout_page ) === 'publish';
        
        if ( $standard_checkout_page_exists ) {
            wp_safe_redirect( get_permalink( $standard_checkout_page ) );
            exit;
        }
    }

    public static function update_cart_totals_without_sync() {
        // Skip the next update hook
        self::$skip_next_sco_order_update_hook = true;
        // Calculate cart totals
        WC()->cart->calculate_totals();
    }

	/**
	 * This function includes the template for the Svea Checkout
     *
	 * @return string template to display the checkout
	 */
    public function display_svea_checkout_page() {
        global $wp_query;
        ob_start();
        // Display thankyou page.
        if( is_wc_endpoint_url( 'order-received' ) && isset( $wp_query->query_vars['order-received'] ) ) {
            $wc_order_id = $wp_query->query_vars['order-received'];
            wc_print_notices();
            $wc_order = false;
            // Get the order
            $wc_order_id  = apply_filters( 'woocommerce_thankyou_order_id', absint( $wc_order_id ) );
            $wc_order_key = apply_filters( 'woocommerce_thankyou_order_key', empty( $_GET['key'] ) ? '' : wc_clean( $_GET['key'] ) );
            if ( $wc_order_id > 0 ) {
                $wc_order = wc_get_order( $wc_order_id );
                if ( ! $wc_order->key_is_valid( $wc_order_key ) ) {
                    $wc_order = false;
                }
            }
            
            wc_get_template( 'checkout/thankyou.php', array( 'order' => $wc_order ) );
            
        // Display checkout page
        } else if( self::$sco_wc_order_id !== false ) {
			if ( ! is_wp_error( self::$sco_wc_order_id ) ) {
				$wc_order = wc_get_order( self::$sco_wc_order_id );
				
				$svea_checkout_module = self::get_svea_checkout_module( WC()->cart );
				$template = locate_template( 'svea-checkout-for-woocommerce/svea-checkout.php' );
				if( $template === '' ) {
					$template = WC_SVEA_CHECKOUT_DIR . '/templates/svea-checkout.php';
				}

				$should_create_account = $wc_order->get_meta( '_should_create_account', true );
				$customer_note = $wc_order->get_customer_note();

				include ( $template );
			} {
				// TODO Show WP error
			}
        }
        
        return ob_get_clean();
    }
    
    /**
     * This function returns the Svea Checkout module.
     * 
     * @param WC_Cart $cart WooCommerce cart
     *
     * @return array|string The Svea Checkout snippet
     */
    public static function get_svea_checkout_module( $cart ) {
	    $svea_gateway_class = WC_Gateway_Svea_Checkout::get_instance();

        $preset_values = array();

        $customer = WC()->customer;
        $user_email = $customer->get_billing_email();
        $user_zipcode = $customer->get_billing_postcode();
        $user_phone = $customer->get_billing_phone();

        // Set preset values
        if( isset( $user_email ) && ! empty( $user_email ) ) {
            array_push( $preset_values,
                array(
                    'TypeName'      => 'EmailAddress',
                    'Value'         => $user_email,
                    'IsReadOnly'    => $svea_gateway_class->is_preset_email_read_only()
                )
            );
        }

        if( isset( $user_zipcode ) && ! empty( $user_zipcode )  ) {
            array_push( $preset_values,
                array(
                   'TypeName'       => 'PostalCode',
                   'Value'          => $user_zipcode,
                   'IsReadOnly'     => $svea_gateway_class->is_preset_zip_code_read_only()
                )
            );
        }

        if( isset( $user_phone ) && ! empty( $user_phone ) ) {
            array_push( $preset_values,
                array(
                   'TypeName'       => 'PhoneNumber',
                   'Value'          => $user_phone,
                   'IsReadOnly'     => $svea_gateway_class->is_preset_phone_read_only()
                )
            );
        }

        $country_settings = $svea_gateway_class->get_merchant_settings();
        $checkout_merchant_id = $country_settings['MerchantId'];
        $checkout_secret = $country_settings['Secret'];

        // Check if merchant ID and secret is set, else display a message
        if( ! isset( $checkout_merchant_id[0] ) || ! isset( $checkout_secret[0] ) ) {
            return array(
                'snippet' => sprintf( '<ul class="woocommerce-error"><li>%s</li></ul>', __( 'Merchant ID and secret must be set to use Svea Checkout', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) )
            );
        }

        $order_id = self::$sco_wc_order_id;
        $wc_order = wc_get_order( $order_id );

        // Only use incomplete orders
        if( ! current_user_can( 'pay_for_order', $order_id ) ) {
            return array(
                'snippet' => sprintf( '<ul class="woocommerce-error"><li>%s</li></ul>', __( 'There was an error when trying to load Svea Checkout, please reload and try again.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) ),
            );
        }
        
        $data = array(
            'Cart'  => array(
                'Items' => WC_Svea_Checkout_Order::get_svea_cart_items_from_wc_order( $wc_order ),
            ),
        );

        // Get supported customer types in the store
        $customer_types = $svea_gateway_class->get_customer_types();

        // Check if the checkout should limit the customer type selection
        if( $customer_types == 'both' ) {
            $preset_values[] = array(
                'TypeName'      => 'IsCompany',
                'Value'         => $svea_gateway_class->is_company_default(),
                'IsReadOnly'    => false,
            );
        } else {
            if( $customer_types === 'company' ) {
                $preset_values[] = array(
                    'TypeName'      => 'IsCompany',
                    'Value'         => true,
                    'IsReadOnly'    => true,
                );
            } else if( $customer_types === 'individual' ) {
	            $preset_values[] = array(
		            'TypeName'      => 'IsCompany',
		            'Value'         => false,
		            'IsReadOnly'    => true,
	            );
            }
        }

        $data['IdentityFlags'] = array(
            'HideNotYou' => $svea_gateway_class->should_hide_not_you(),
            'HideChangeAddress' => $svea_gateway_class->should_hide_change_address(),
            'HideAnonymous' => $svea_gateway_class->should_hide_anonymous(),
        );

        $data['PresetValues'] = $preset_values;

        // Set endpoint url. Eg. test or prod
        $base_url = $country_settings['BaseUrl'];
        
        $connector = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $base_url );
        
        $checkout_client = new \Svea\Checkout\CheckoutClient( $connector );
     
        $order_key = $wc_order->get_order_key();

        if( ( $svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true ) ) ) {
            $get_data = array(
                'OrderId'   => absint( $svea_order_id ),
            );

            // Get the Svea order
            try {
                $response = $checkout_client->get( apply_filters( 'woocommerce_sco_get_order', $get_data ) );
            } catch( Exception $e ) {
                WC()->session->__unset( 'order_awaiting_payment' );
                WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when getting Svea order: %s', $e->getMessage() ) );

	            return array(
	                'snippet' => sprintf( '<ul class="woocommerce-error"><li>%s</li></ul>', WC_Svea_Checkout_Helper::get_svea_error_message( $e ) ),
                );
            }

        } else {
            // Set partner key
            $data['PartnerKey'] = '1D8C75CE-06AC-43C8-B845-0283E100CEE1';

            $data['ClientOrderNumber'] = sanitize_text_field( apply_filters( 'woocommerce_sco_client_order_number', $wc_order->get_order_number() ) );
            $data['CountryCode'] = $wc_order->get_billing_country();
            $data['Currency'] = $wc_order->get_currency();
            $data['Locale'] = WC_Svea_Checkout_Helper::get_svea_locale( get_locale() );

            $data['MerchantSettings'] = array(
                'TermsUri'          => wc_get_page_permalink( 'terms' ),
                'CheckoutUri'       => wc_get_checkout_url(),
                'ConfirmationUri'   => add_query_arg( array(
                    'order_id'      => $order_id,
                    'key'           => $order_key,
                    'confirmation'  => '1',
                ), wc_get_checkout_url() ),
                'PushUri'           => add_query_arg( array(
                    'order_id'   => $order_id,
                    'key'        => $order_key,
                ), home_url( '/wc-api/svea_checkout_push' ) ),
            );

            try {
                $response = $checkout_client->create( apply_filters( 'woocommerce_sco_create_order', $data ) );
                WC_Gateway_Svea_Checkout::log( sprintf( 'Svea order created with client order number: %s', $order_id ) );
            } catch( Exception $e ) {
                WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when create Svea order: %s', $e->getMessage() ) );

	            return array(
		            'snippet' => sprintf( '<ul class="woocommerce-error"><li>%s</li></ul>', WC_Svea_Checkout_Helper::get_svea_error_message( $e ) ),
	            );
            }

            // Save Svea Order Row ID:s
            if( isset( $response['Cart']['Items'] ) ) {
                self::link_wc_order_items_with_svea( $response['Cart']['Items'], $wc_order );
            }

            $svea_order_id = $response['OrderId'];

            $wc_order->update_meta_data( '_svea_co_order_id', $svea_order_id );
            $wc_order->save();
        }

        return array(
            'snippet'          => $response['Gui']['Snippet'],
            'sco_order_id'     => $svea_order_id,
        );
    }
}
