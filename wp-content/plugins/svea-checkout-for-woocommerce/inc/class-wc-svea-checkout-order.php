<?php if( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class WC_Svea_Checkout_Order {

	const SVEA_MAX_NAME_LENGTH = 40;
    
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
	    add_action( 'wc_ajax_sco_heartbeat', array( $this, 'sco_heartbeat' ) );

	    add_action( 'pre_get_posts', array( $this, 'hide_sco_incomplete_orders' ), 40, 1 );

	    // Only hide pending SCO-orders in admin
        if( is_admin() ) {
	        add_filter( 'wp_count_posts', array( $this, 'hide_sco_incomplete_orders_from_count' ), 10, 3 );
        }

        add_action( 'woocommerce_cancel_unpaid_orders', array( $this, 'cleanup_expired_orders' ), 20, 1 );

        add_filter( 'user_has_cap', array( $this, 'reject_access_to_ongoing_sco_order' ), 40, 4 );

        // Hide Svea Checkout order item meta
	    add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_svea_checkout_order_item_meta' ), 10, 1 );

	    // Show Svea address lines for generic orders
	    add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'show_svea_billing_address_lines_in_order' ), 10, 2 );
	    add_filter( 'woocommerce_order_formatted_shipping_address', array( $this, 'show_svea_shipping_address_lines_in_order' ), 10, 2 );

	    add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'show_svea_billing_complete_address_lines_in_order' ), 10, 1 );
	    add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'show_svea_shipping_complete_address_lines_in_order' ), 10, 1 );
    }

    public function show_svea_billing_complete_address_lines_in_order( $wc_order ) {
	    $billing_address_lines = $wc_order->get_meta( '_svea_co_billing_address_lines' );

	    // Check if address lines are set
	    if( empty( $billing_address_lines ) || ! is_array( $billing_address_lines ) ) {
		    return;
	    }

	    ?>
		<p>
			<strong><?php _e( 'Address (Svea Checkout):', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?></strong><br />
			<?php echo implode( '<br />', $billing_address_lines ); ?>
		</p>
		<?php
    }

	public function show_svea_shipping_complete_address_lines_in_order( $wc_order ) {
		$billing_address_lines = $wc_order->get_meta( '_svea_co_shipping_address_lines' );

		// Check if address lines are set
		if( empty( $billing_address_lines ) || ! is_array( $billing_address_lines ) ) {
			return;
		}

		?>
		<p>
			<strong><?php _e( 'Address (Svea Checkout):', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?></strong><br />
			<?php echo implode( '<br />', $billing_address_lines ); ?>
		</p>
		<?php
	}

	/**
	 * Display Svea billing address lines in order
	 *
	 * @param $address Array
	 * @param $wc_order WC_Order
	 *
	 * @return Array
	 */
    public function show_svea_billing_address_lines_in_order( $address, $wc_order ) {
    	$billing_address_lines = $wc_order->get_meta( '_svea_co_billing_address_lines' );

    	// Check if address lines are set
    	if( empty( $billing_address_lines ) || ! is_array( $billing_address_lines ) ) {
    		return $address;
	    }

	    // Remove all string indexes
	    $billing_address_lines = array_values( $billing_address_lines );

	    $fields_to_override = array(
		    'address_1',
		    'address_2',
		    'city',
		    'state',
		    'postcode',
	    );

	    foreach( $billing_address_lines as $index => $address_line ) {
			if( ! isset( $fields_to_override[$index] ) ) {
				break;
			}

			$address[$fields_to_override[$index]] = $address_line;
	    }

	    return $address;
	}

	/**
	 * Display Svea shipping address lines in order
	 *
	 * @param $address Array
	 * @param $wc_order WC_Order
	 *
	 * @return Array
	 */
	public function show_svea_shipping_address_lines_in_order( $address, $wc_order ) {
		$shipping_address_lines = $wc_order->get_meta( '_svea_co_shipping_address_lines' );

		// Check if address lines are set
		if( empty( $shipping_address_lines ) || ! is_array( $shipping_address_lines ) ) {
			return $address;
		}

		// Remove all string indexes
		$shipping_address_lines = array_values( $shipping_address_lines );

		$fields_to_override = array(
			'address_1',
			'address_2',
			'city',
			'state',
			'postcode',
		);

		foreach( $shipping_address_lines as $index => $address_line ) {
			if( ! isset( $fields_to_override[$index] ) ) {
				break;
			}

			$address[$fields_to_override[$index]] = $address_line;
		}

		return $address;
	}

    public function hide_svea_checkout_order_item_meta( $hidden_order_itemmeta ) {
        $hidden_order_itemmeta = array_merge( $hidden_order_itemmeta,
	        array(
	            '_svea_co_order_row_id',
	        )
        );

        return $hidden_order_itemmeta;
    }

	/**
	 * Disallow users to see ongoing Svea orders in their account page
	 *
	 * @param $allcaps
	 * @param $caps
	 * @param $args
	 * @param $user
	 *
	 * @return array Array containing the capabilities for the user
	 */
    public function reject_access_to_ongoing_sco_order( $allcaps, $caps, $args, $user ) {
    	if( in_array( 'view_order', $caps ) && isset( $args[2] ) ) {
			$order_id = $args[2];

			$wc_order = wc_get_order( $order_id );

			if( $wc_order && $wc_order->has_status( 'pending' )
				&& $wc_order->get_payment_method() == WC_Gateway_Svea_Checkout::GATEWAY_ID
				&& ! $wc_order->meta_exists( '_svea_co_order_final' ) ) {
				unset( $allcaps['view_order'] );
			}
	    }

	    return $allcaps;
    }

	/**
	 * Heartbeat to check if the current user needs to
	 * reload their browser
	 *
	 * @return void
	 */
    public function sco_heartbeat() {

    	// If security isn't set, send error
    	if( empty( $_POST['security'] ) ) {
    		wp_send_json_error();
	    }

	    // If nonce is invalid, reload the checkout
    	if( ! wp_verify_nonce( $_POST['security'], 'sco_heartbeat' ) ) {
			wp_send_json(
				array(
					'reload'    => 'true',
				)
			);
	    }

	    $wc_order_id = WC()->session->get( 'order_awaiting_payment' );

	    $reload_checkout = false;

	    if( ! $wc_order_id ) {
		    $reload_checkout = true;
	    } else {
		    $wc_order = wc_get_order( $wc_order_id );

		    if( $wc_order ) {
			    // Update order dates to prevent order from being removed
			    $wc_order->set_date_created( current_time( 'timestamp', true ) );
			    $wc_order->set_date_modified( current_time( 'timestamp', true ) );
			    $wc_order->save();

			    $reload_checkout = false;
		    } else {
			    $reload_checkout = true;
		    }
	    }

	    $data = array(
			'reload'    => $reload_checkout === true ? 'true' : 'false',
	    );

	    wp_send_json( $data );
    }

	/**
	 * Cleanup expired Svea Checkout orders
	 *
	 * @return void
	 */
    public function cleanup_expired_orders() {

	    // Remove orders that are more than 14 days old
		$days = apply_filters( 'sco_cleanup_days', 14 );
	    $held_duration = 60 * 24 * $days;

	    $date = strtotime( '-' . absint( $held_duration ) . ' MINUTES', current_time( 'timestamp' ) );

		global $wpdb;

		// Fetch all unpaid Svea Checkout orders
	    $unpaid_sco_orders = $wpdb->get_col(
		    $wpdb->prepare(
			    "
	    	    SELECT ID
	    	    FROM {$wpdb->posts}
	    	    WHERE post_type IN('" . implode( "','", wc_get_order_types() ) . "')
	    	    AND post_status = 'wc-pending'
	    	    AND post_modified < %s
	    	    AND ID IN (
	    	        SELECT post_id
	    	        FROM {$wpdb->postmeta}
	    	        WHERE meta_key = '_created_via'
	    	        AND meta_value = 'svea_checkout'
	    	    )
	    	    ",
			    date( 'Y-m-d H:i:s', $date )
		    )
	    );

	    if( $unpaid_sco_orders ) {
	    	foreach( $unpaid_sco_orders as $unpaid_order_id ) {
	    		$wc_order = wc_get_order( $unpaid_order_id );

	    		// Don't delete orders that are not processed with the Checkout gateway or which are final in Svea
	    		if( $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID
			        || $wc_order->meta_exists( '_svea_co_order_final' ) ) {
	    			continue;
			    }

			    $wc_order->delete( true );

	    		WC_Gateway_Svea_Checkout::log( 'Deleted pending Svea Checkout WC order by id "' . $unpaid_order_id . '" since it hasn\'t been paid in 14 days.' );
		    }
	    }
    }

	/**
	 * Hide incomplete Svea Checkout orders from post status counts
	 *
	 * @param array $counts counts of different post statuses
	 * @param string $type post type being queried
	 * @param string $perm permissions
	 *
	 * @return object counts-object containing total counts for different post statuses
	 */
    public function hide_sco_incomplete_orders_from_count( $counts, $type, $perm = '' ) {
    	// Only modify order counts
    	if( $type == 'shop_order' ) {
		    global $wpdb;

		    // Get number of pending orders excluding Svea Checkout orders
		    $pending_count = $wpdb->get_var(
			    "
				SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE ID NOT IN (
					SELECT post_id
	                FROM {$wpdb->postmeta}
	                WHERE meta_key = '_created_via'
                    AND meta_value = 'svea_checkout'
                )
                AND ID NOT IN (
                	SELECT post_id
                	FROM {$wpdb->postmeta}
                	WHERE meta_key = '_svea_co_order_final'
                	AND meta_value != ''
                )
                AND post_type IN('" . implode( "','", wc_get_order_types() ) . "')
                AND post_status = 'wc-pending'
                "
		    );

		    $counts_arr = (array) $counts;

		    $counts_arr['wc-pending'] = $pending_count;

		    $counts = (object) $counts_arr;
	    }

	    return $counts;
    }

	/**
	 * Hide incomplete Svea Checkout orders from the order list in admin
	 *
	 * @param WP_Query $query the query being modified
 	 * @return void
	 */
    public function hide_sco_incomplete_orders( $query ) {

    	$post_types = $query->get( 'post_type' );

    	if( ! is_array( $post_types ) ) {
    		$post_types = explode( ',', $post_types );
	    }

	    $is_order_query = false;

    	foreach( $post_types as $post_type ) {
    		if( in_array( $post_type, wc_get_order_types() ) ) {
    			$is_order_query = true;
    			break;
		    }
	    }

    	if( $is_order_query ) {
    		global $wpdb;

		    // Get Svea Checkout order ids to hide from order list
    		$sco_hide_ids = $wpdb->get_col(
                "
				SELECT post_id
				FROM {$wpdb->postmeta}
				WHERE post_id IN (
					SELECT ID
	                FROM {$wpdb->posts}
	                WHERE post_type IN('" . implode( "','", wc_get_order_types() ) . "')
	                AND post_status = 'wc-pending'
                )
                AND post_id NOT IN (
                    SELECT post_id
                	FROM {$wpdb->postmeta}
                	WHERE meta_key = '_svea_co_order_final'
                	AND meta_value != ''
                )
                AND meta_key = '_created_via'
                AND meta_value = 'svea_checkout'
                "
		    );

    		if( ! empty( $sco_hide_ids ) && is_array( $sco_hide_ids ) ) {
    			$query->set( 'post__not_in', $sco_hide_ids );

    			$post_in = $query->get( 'post__in' );

    			if( is_array( $post_in ) ) {
					$post_in = array_diff( $post_in, $sco_hide_ids );
					$query->set( 'post__in', $post_in );
			    }
		    }
	    }
    }
    
    /**
     * Register Svea Checkout Incomplete order status
     *
     * @since  1.0.0
     */
    public function register_svea_incomplete_order_status() {
        register_post_status( 'wc-sco-incomplete', array(
            'label'                     => __( 'Svea Checkout incomplete', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'public'                    => false,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => false,
            'internal'                  => true,
            'label_count'               => _n_noop( 'Svea Checkout incomplete <span class="count">(%s)</span>', 'Svea Checkout incomplete <span class="count">(%s)</span>', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
        ) );
    }

    public static function customer_has_active_order() {
        // Check if we have an active order already
        if( WC()->session->get( 'order_awaiting_payment' ) && wc_get_order( WC()->session->get( 'order_awaiting_payment' ) ) ) {
            $order_id = WC()->session->get( 'order_awaiting_payment' );
            $order = wc_get_order( $order_id );
            // If the order has it's status changed, we'll want a new order created
            if( ! $order->has_status( 'sco-incomplete' ) ) {
                $order = false;
            }
        }

        return $order !== false;
    }

    /**
     * Prepares local order.
     *
     * Creates local order.
     *
     * @since  1.0.0
     * @access public
     *
     * @return int WooCommerce order ID.
     */
    public static function create_order() {
        if( ! isset( WC()->cart ) ) {
            return false;
        }

        if( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
            define( 'WOOCOMMERCE_CHECKOUT', true );
        }

        WC_Svea_Checkout_Shortcode_Checkout::update_cart_totals_without_sync();

        $wc_order_id = absint( WC()->session->get( 'order_awaiting_payment' ) );

    	$cart_hash = WC()->cart->get_cart_hash();

        $billing_country = WC()->customer->get_billing_country();

        if( empty( $billing_country ) ) {
            $billing_country = WC()->countries->get_base_country();
        }

        $order_data = array(
            'payment_method'    => 'svea_checkout',
            'status'            => 'pending',

            // Billing fields
			'billing_country'   => $billing_country,
			'billing_email'		=> WC()->customer->get_billing_email(),

            // Shipping fields
            'shipping_country'  => $billing_country,
		);

        if ( $wc_order_id && ( $wc_order = wc_get_order( $wc_order_id ) ) && $wc_order->has_status( array( 'pending', 'failed' ) ) ) {

            // Check for country missmatch
            if( $wc_order->get_currency() != get_woocommerce_currency() || $wc_order->get_billing_country() != $billing_country ) {
                WC()->session->__unset( 'order_awaiting_payment' );
                $wc_order_id = 0;
                $wc_order = false;
            } else if( ( $sco_order_id = $wc_order->get_meta( '_svea_co_order_id', true ) ) ) {
                // Check Svea order before changing the WC order

                // Set endpoint url. Eg. test or prod
                $country_settings = WC_Gateway_Svea_Checkout::get_instance()->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );
                $checkout_merchant_id = $country_settings['MerchantId'];
                $checkout_secret = $country_settings['Secret'];
                $base_url = $country_settings['BaseUrl'];

                if( ! empty( $checkout_merchant_id ) && ! empty( $checkout_secret ) && ! empty( $base_url ) ) {
	                $connector = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $base_url );

	                $checkout_client = new \Svea\Checkout\CheckoutClient( $connector );

	                $data = array(
		                'OrderId' => absint( $sco_order_id ),
	                );

	                try {
		                $response = $checkout_client->get( apply_filters( 'woocommerce_sco_get_order', $data ) );
	                } catch ( Exception $e ) {
		                WC()->session->__unset( 'order_awaiting_payment' );
		                WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when getting Svea order: %s', $e->getMessage() ) );
	                }

	                // Unset session if order status isn't "created"
	                if ( isset( $response['Status'] ) && strtoupper( $response['Status'] ) != 'CREATED' ) {
		                WC_Gateway_Svea_Checkout::log( 'Order status was not "CREATED" when updating. Unsetting order.' );
		                WC()->session->__unset( 'order_awaiting_payment' );
		                $wc_order_id = 0;
		                $wc_order    = false;
	                }
                }
            }

            if( $wc_order ) {
                // Compare WC Order with cart
                if( ! self::wc_order_needs_update( $wc_order ) ) {
                    return $wc_order->get_id();
                }

				$order_data['order_comments'] = $wc_order->get_customer_note();

                $wc_order->set_cart_hash( $cart_hash );
				$wc_order->save();
            }
		}

		// Update or create WC order
		$wc_order_id = self::create_wc_order( $order_data );

        if( is_wp_error( $wc_order_id ) ) {
            return $wc_order_id;
        }

        $wc_order = wc_get_order( $wc_order_id );

        // Update Svea order with new WC order data
        if( ( $svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true ) ) ) {
            $data = array(
                'OrderId'   => intval( $svea_order_id ),
                'Cart'      => array(
                    'Items' => self::get_svea_cart_items_from_wc_order( $wc_order ),
                ),
            );

            // Set endpoint url. Eg. test or prod
            $svea_gateway_class = WC_Gateway_Svea_Checkout::get_instance();
            $country_settings = $svea_gateway_class->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );
            $checkout_merchant_id = $country_settings['MerchantId'];
            $checkout_secret = $country_settings['Secret'];
            $base_url = $country_settings['BaseUrl'];

	        if( ! empty( $checkout_merchant_id ) && ! empty( $checkout_secret ) && ! empty( $base_url ) ) {
		        $connector       = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $base_url );
		        $checkout_client = new \Svea\Checkout\CheckoutClient( $connector );

		        // Update the svea order
		        try {
			        $response = $checkout_client->update( apply_filters( 'woocommerce_sco_update_order', $data ) );
		        } catch ( Exception $e ) {
			        WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when update Svea order: %s', $e->getMessage() ) );
		        }

		        // Save Svea Order Row ID:s
		        if ( isset( $response['Cart']['Items'] ) ) {
			        WC_Svea_Checkout_Shortcode_Checkout::link_wc_order_items_with_svea( $response['Cart']['Items'], $wc_order );
		        }
	        }
        }

        $wc_order->save();

        // Update the post modified date
	    wp_update_post(
	        array(
	        	'ID'    => $wc_order->get_id(),
	        )
	    );

        WC()->session->set( 'order_awaiting_payment', $wc_order->get_id() );

        return $wc_order->get_id();
    }

	/**
	 * Fork of WooCommerce standard function for creating orders
	 *
	 * @param array $order_data
	 * 
	 * @return void
	 */
	public static function create_wc_order( $data ) {
		// Give plugins the opportunity to create an order themselves.
		$order_id = apply_filters( 'woocommerce_create_order', null, WC()->checkout() );
		if ( $order_id ) {
			return $order_id;
		}

		try {
			$order_id           = absint( WC()->session->get( 'order_awaiting_payment' ) );
			$cart_hash          = WC()->cart->get_cart_hash();
			$order              = $order_id ? wc_get_order( $order_id ) : null;

			/**
			 * If there is an order pending payment, we can resume it here so
			 * long as it has not changed. If the order has changed, i.e.
			 * different items or cost, create a new order. We use a hash to
			 * detect changes which is based on cart items + order total.
			 */
			if ( $order && $order->has_cart_hash( $cart_hash ) && $order->has_status( array( 'pending', 'failed' ) ) ) {
				// Action for 3rd parties.
				do_action( 'woocommerce_resume_order', $order_id );

				// Remove all items - we will re-add them later.
				$order->remove_order_items();
			} else {
				$order = new WC_Order();
			}

			$fields_prefix = array(
				'shipping' => true,
				'billing'  => true,
			);

			$shipping_fields = array(
				'shipping_method' => true,
				'shipping_total'  => true,
				'shipping_tax'    => true,
			);

			foreach ( $data as $key => $value ) {
				if ( is_callable( array( $order, "set_{$key}" ) ) ) {
					$order->{"set_{$key}"}( $value );
					// Store custom fields prefixed with wither shipping_ or billing_. This is for backwards compatibility with 2.6.x.
				} elseif ( isset( $fields_prefix[ current( explode( '_', $key ) ) ] ) ) {
					if ( ! isset( $shipping_fields[ $key ] ) ) {
						$order->update_meta_data( '_' . $key, $value );
					}
				}
			}

			$order->set_created_via( 'svea_checkout' );
			$order->set_cart_hash( $cart_hash );
			$order->set_customer_id( apply_filters( 'woocommerce_checkout_customer_id', get_current_user_id() ) );
			$order_vat_exempt = WC()->cart->get_customer()->get_is_vat_exempt() ? 'yes' : 'no';
			$order->add_meta_data( 'is_vat_exempt', $order_vat_exempt, true );
			$order->set_currency( get_woocommerce_currency() );
			$order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
			$order->set_customer_ip_address( WC_Geolocation::get_ip_address() );
			$order->set_customer_user_agent( wc_get_user_agent() );
			$order->set_customer_note( isset( $data['order_comments'] ) ? $data['order_comments'] : '' );
			$order->set_payment_method( WC_Gateway_Svea_Checkout::get_instance() );
			$order->set_shipping_total( WC()->cart->get_shipping_total() );
			$order->set_discount_total( WC()->cart->get_discount_total() );
			$order->set_discount_tax( WC()->cart->get_discount_tax() );
			$order->set_cart_tax( WC()->cart->get_cart_contents_tax() + WC()->cart->get_fee_tax() );
			$order->set_shipping_tax( WC()->cart->get_shipping_tax() );
			$order->set_total( WC()->cart->get_total( 'edit' ) );
			WC()->checkout()->create_order_line_items( $order, WC()->cart );
			WC()->checkout()->create_order_fee_lines( $order, WC()->cart );
			WC()->checkout()->create_order_shipping_lines( $order, WC()->session->get( 'chosen_shipping_methods' ), WC()->shipping()->get_packages() );
			WC()->checkout()->create_order_tax_lines( $order, WC()->cart );
			WC()->checkout()->create_order_coupon_lines( $order, WC()->cart );

			/**
			 * Action hook to adjust order before save.
			 *
			 * @since 3.0.0
			 */
			do_action( 'woocommerce_checkout_create_order', $order, $data );

			// Save the order.
			$order_id = $order->save();

			do_action( 'woocommerce_checkout_update_order_meta', $order_id, $data );

			return $order_id;
		} catch ( Exception $e ) {
			if ( $order && $order instanceof WC_Order ) {
				$order->get_data_store()->release_held_coupons( $order );
			}

			return new WP_Error( 'checkout-error', $e->getMessage() );
		}
	}

    /**
     * Check if WooCommerce needs to be synced with the cart
     *
     * @param $wc_order WC_Order
     *
     * @return bool
     */
    public static function wc_order_needs_update( $wc_order ) {
        $cart_hash = WC()->cart->get_cart_hash();

        // Check if cart contents have changed
        if( ! $wc_order->has_cart_hash( $cart_hash ) ) {
        	WC_Gateway_Svea_Checkout::log( 'Order cart hash does not match.' );
            return true;
        }

        // Check if chosen shipping method has changed
        $order_shipping_methods = $wc_order->get_shipping_methods();

        $shipping_method_ids = [];

        foreach( $order_shipping_methods as $shipping_method ) {
            $shipping_method_ids[] = sprintf(
				'%s:%s',
				$shipping_method->get_method_id(),
				$shipping_method->get_instance_id()
			);
		}
		
        if( $shipping_method_ids !== WC()->session->get( 'chosen_shipping_methods' ) ) {
	        WC_Gateway_Svea_Checkout::log( 'Order shipping does not match.' );
            return true;
        }

        return false;
    }

	public static function get_svea_cart_items_from_wc_order( $wc_order ) {
		$cart_items = array();

		$totals_in_taxes = array();

		// Subtotal before shipping and fees
		// $cart_subtotal = 0;

		// Total amount we will be sending to Svea
		$svea_total = 0.0;

		// Check if the order in WooCommerce contains any products
		if ( sizeof( $wc_order->get_items() ) > 0 ) {
			foreach ( $wc_order->get_items() as $item_key => $order_item ) {

				$_product = $order_item->get_product();

				if ( $_product->exists() && $order_item->get_quantity() ) {
					$item_tax_percentage = 0.00;

					if( $wc_order->get_total_tax() > 0 && $wc_order->get_line_total( $order_item, false ) > 0 ) {
						$item_tax_percentage = round( ( $wc_order->get_line_tax( $order_item ) / $wc_order->get_line_total( $order_item, false ) ) * 100 );
					}

					$item_tax_percentage_label = intval( round( $item_tax_percentage ) );
					$quantity = max( 1, $order_item->get_quantity() );

					if( ! isset( $totals_in_taxes[$item_tax_percentage_label] ) ) {
						$totals_in_taxes[$item_tax_percentage_label] = floatval( $wc_order->get_item_subtotal( $order_item, true ) ) * $quantity;
					} else {
						$totals_in_taxes[$item_tax_percentage_label] += floatval( $wc_order->get_item_subtotal( $order_item, true ) ) * $quantity;
					}

					// Increment Svea total
					$svea_total += ( floatval( round( $wc_order->get_item_total( $order_item, true ) * 100 ) ) / 100 ) * $quantity;

					// Increment cart subtotal
					// $cart_subtotal += ( floatval( round( $wc_order->get_item_subtotal( $order_item, true ) * 100 ) ) / 100 ) * $quantity;

					$cart_items[] = self::get_svea_cart_item_from_wc_order_item( $wc_order, $order_item );
				}
			}
		}

		// Add discount
		/*if( ( $total_discount = $wc_order->get_total_discount( false ) ) > 0 ) {
			foreach( $totals_in_taxes as $tax_rate => $total ) {
				$percentage_of_total = $total / $cart_subtotal;
				$discount_amount = $total_discount * $percentage_of_total;

				// Increment Svea total
				$svea_total += ( floatval( - round( $discount_amount * 100 ) ) / 100 );

				$cart_items[] = array(
					'Type'              => 'discount',
					'ArticleNumber'     => sprintf( 'DISCOUNT (%s)', $tax_rate . '%' ),
					'Name'              => __( 'Discount', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
					'Quantity'          => 100,
					'UnitPrice'         => - round( $discount_amount * 100 ),
					'VatPercent'        => $tax_rate * 100,
				);
			}
		}*/

		// Add order fees
		foreach( $wc_order->get_fees() as $fee ) {
			$quantity = max( 1, $fee->get_quantity() );

			// Increment Svea total
			$svea_total += ( floatval( round( ( floatval( $fee->get_total_tax() ) + floatval( $fee->get_total() ) ) * 100 ) ) / 100 ) * $quantity;

			$cart_items[] = self::get_svea_cart_item_from_wc_order_item( $wc_order, $fee );
		}

		// Add order shipping
		foreach( $wc_order->get_shipping_methods() as $shipping ) {
			$quantity = max( 1, $shipping->get_quantity() );

			// Increment Svea total
			$svea_total += ( floatval( round( ( floatval( $shipping->get_total_tax() ) + floatval( $shipping->get_total() ) ) * 100 ) ) / 100 ) * $quantity;

			$cart_items[] = self::get_svea_cart_item_from_wc_order_item( $wc_order, $shipping );
		}

		$total_diff = $wc_order->get_total() - $svea_total;

		$total_diff_rounded = round( $total_diff * 100 );

		if( $total_diff_rounded != 0 ) {
			$cart_items[] = array(
				'ArticleNumber'         => 'ROUNDING',
				'Name'                  => __( 'Cash rounding', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
				'Quantity'              => 100,
				'UnitPrice'             => $total_diff_rounded,
				'VatPercent'            => 0,
				'TemporaryReference'    => 'ROUNDING'
			);
		}

		return apply_filters( 'woocommerce_sco_svea_cart_items_from_wc_order', $cart_items, $wc_order );
	}

	public static function get_svea_cart_item_from_wc_order_item( $wc_order, $wc_order_item ) {
    	if( $wc_order_item->is_type( 'line_item' ) ) {
		    $_product = $wc_order_item->get_product();

		    if ( $_product->exists() && $wc_order_item->get_quantity() ) {
			    // Get SKU or product id
			    $article_number = '';

			    if ( $_product->get_sku() ) {
				    $article_number = $_product->get_sku();
			    } else {
				    $article_number = $_product->get_id();
			    }

			    $item_name = trim( $wc_order_item->get_name() );

			    if( function_exists( 'mb_strlen' ) ) {
				    if( mb_strlen( $item_name ) > self::SVEA_MAX_NAME_LENGTH ) {
					    $item_name = mb_substr( $item_name, 0, self::SVEA_MAX_NAME_LENGTH - 3 ) . '...';
				    }
			    } else if( strlen( $item_name ) > self::SVEA_MAX_NAME_LENGTH ) {
				    $item_name = substr( $item_name, 0, self::SVEA_MAX_NAME_LENGTH - 3 ) . '...';
			    }

			    $item_tax_percentage = 0.00;

			    if( $wc_order->get_total_tax() > 0 && $wc_order->get_line_total( $wc_order_item, false ) > 0 ) {
				    $item_tax_percentage = round( ( $wc_order->get_line_tax( $wc_order_item ) / $wc_order->get_line_total( $wc_order_item, false ) ) * 100 );
			    }

			    $quantity = max( 1, $wc_order_item->get_quantity() );

			    return array(
				    'ArticleNumber'         => $article_number,
				    'Name'                  => $item_name,
				    'Quantity'              => ( $quantity * 100 ),
				    'UnitPrice'             => round( $wc_order->get_item_total( $wc_order_item, true ) * 100 ),
				    'VatPercent'            => $item_tax_percentage * 100,
				    'TemporaryReference'    => $wc_order_item->get_id(), // Send the order item ID to link the order rows for future administration
			    );
		    }
	    } else if( $wc_order_item->is_type( 'fee' ) ) {
		    $item_id = sanitize_title( $wc_order_item->get_name() );
		    $item_name = trim( $wc_order_item->get_name() );

		    if( function_exists( 'mb_strlen' ) ) {
			    if( mb_strlen( $item_name ) > self::SVEA_MAX_NAME_LENGTH ) {
				    $item_name = mb_substr( $item_name, 0, self::SVEA_MAX_NAME_LENGTH - 3 ) . '...';
			    }
		    } else if( strlen( $item_name ) > self::SVEA_MAX_NAME_LENGTH ) {
			    $item_name = substr( $item_name, 0, self::SVEA_MAX_NAME_LENGTH - 3 ) . '...';
		    }

		    $item_tax_percentage = 0.00;

		    if( $wc_order->get_total_tax() > 0 && $wc_order->get_line_total( $wc_order_item, false ) > 0 ) {
				$item_tax_percentage = round( ( $wc_order->get_line_tax( $wc_order_item ) / $wc_order->get_line_total( $wc_order_item, false ) ) * 100 );
			}

		    $quantity = max( 1, $wc_order_item->get_quantity() );

		    return array(
			    'ArticleNumber'         => $item_id,
			    'Name'                  => $item_name,
			    'Quantity'              => ( $quantity * 100 ),
			    'UnitPrice'             => round( $wc_order->get_item_total( $wc_order_item, true ) * 100 ),
			    'VatPercent'            => $item_tax_percentage * 100,
			    'TemporaryReference'    => $wc_order_item->get_id(), // Send the order item ID to link the order rows for future administration
		    );
	    } else if( $wc_order_item->is_type( 'shipping' ) ) {
		    $item_id = sanitize_title( $wc_order_item->get_name() );
		    $item_name = trim( __( 'Shipping:', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) . ' ' . trim( $wc_order_item->get_name() ) );

		    if( function_exists( 'mb_strlen' ) ) {
			    if( mb_strlen( $item_name ) > self::SVEA_MAX_NAME_LENGTH ) {
				    $item_name = mb_substr( $item_name, 0, self::SVEA_MAX_NAME_LENGTH - 3 ) . '...';
			    }
		    } else if( strlen( $item_name ) > self::SVEA_MAX_NAME_LENGTH ) {
			    $item_name = substr( $item_name, 0, self::SVEA_MAX_NAME_LENGTH - 3 ) . '...';
		    }

		    $item_tax_percentage = 0.00;

		    if( $wc_order->get_total_tax() > 0 && $wc_order->get_line_total( $wc_order_item, false ) > 0 ) {
				$item_tax_percentage = round( ( $wc_order->get_line_tax( $wc_order_item ) / $wc_order->get_line_total( $wc_order_item, false ) ) * 100 );
			}

		    $quantity = max( 1, $wc_order_item->get_quantity() );

		    return array(
			    'Type'                  => 'shipping_fee',
			    'ArticleNumber'         => $item_id,
			    'Name'                  => $item_name,
			    'Quantity'              => ( $quantity * 100 ),
			    'UnitPrice'             => round( $wc_order->get_item_total( $wc_order_item, true ) * 100 ),
			    'VatPercent'            => $item_tax_percentage * 100,
			    'TemporaryReference'    => $wc_order_item->get_id(), // Send the order item ID to link the order rows for future administration
		    );
	    }

	    return array();
	}
    
}
