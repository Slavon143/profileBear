<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Svea_Checkout_Admin {

    public function __construct( $plugin_name, $version ) {
	    $this->plugin_name = $plugin_name;
	    $this->version     = $version;
    }

    /**
     * Init function
     */
    public function init() {
    	$svea_checkout_gateway = WC_Gateway_Svea_Checkout::get_instance();

	    // Instantiate admin functionality
	    if ( $svea_checkout_gateway->get_option( 'sync_order_completion' ) === 'yes' ) {
		    add_action( 'woocommerce_order_status_completed', array( $this, 'deliver_order' ), 10, 1 );
	    }

	    if ( $svea_checkout_gateway->get_option( 'sync_order_cancellation' ) === 'yes' ) {
		    add_action( 'woocommerce_order_status_cancelled', array( $this, 'cancel_order' ), 10, 1 );
	    }

	    if ( is_admin() && $svea_checkout_gateway->get_option( 'sync_order_rows' ) === 'yes' ) {
		    add_action( 'woocommerce_new_order_item', array( $this, 'admin_add_order_item' ), 10, 3 );
		    add_action( 'woocommerce_update_order_item', array( $this, 'admin_update_order_item' ), 10, 3 );

		    add_action( 'woocommerce_update_order', array( $this, 'admin_update_order' ), 10, 1 );

		    // Use before to get information about the order before it's removed
		    add_action( 'woocommerce_before_delete_order_item', array( $this, 'admin_remove_order_item' ), 10, 1 );
	    }
    }

	/**
	 * Sync order items removed in admin to Svea
	 *
	 * @param $order_item_id int
	 *
	 * @return void
	 */
    public function admin_remove_order_item( $order_item_id ) {
    	$data_store = WC_Data_Store::load( 'order-item' );

    	$order_id = $data_store->get_order_id_by_order_item_id( $order_item_id );

	    $wc_order = wc_get_order( $order_id );

	    if( ! $wc_order || ! is_a( $wc_order, 'WC_Order' ) || $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
		    return;
	    }

	    if( ! ( $svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true ) ) ) {
		    return;
	    }

	    // Only edit orders which are final in Svea
	    if( ! $wc_order->meta_exists( '_svea_co_order_final' ) ) {
	    	return;
	    }

	    $wc_svea_checkout = WC_Gateway_Svea_Checkout::get_instance();

	    WC_Gateway_Svea_Checkout::log( 'Syncing admin edited order item to Svea.' );

	    $svea_order_item_id = $data_store->get_metadata( $order_item_id, '_svea_co_order_row_id', true );

	    if( ! $svea_order_item_id ) {
		    return;
	    }

	    $country_settings = $wc_svea_checkout->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );
	    $checkout_merchant_id = $country_settings['MerchantId'];
	    $checkout_secret = $country_settings['Secret'];

	    $admin_base_url        = $country_settings['AdminBaseUrl'];
	    $admin_connector       = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $admin_base_url );
	    $admin_checkout_client = new \Svea\Checkout\CheckoutAdminClient( $admin_connector );

	    $data['OrderId'] = intval( $svea_order_id );

	    $admin_response = wp_cache_get( intval( $svea_order_id ), 'svea_co_admin_svea_orders' );

	    if( $admin_response === false ) {
		    try {
			    WC_Gateway_Svea_Checkout::log( 'Trying to get with admin checkout client.' );
			    $admin_response = $admin_checkout_client->getOrder( apply_filters( 'woocommerce_sco_get_order', $data ) );

			    wp_cache_set( intval( $svea_order_id ), $admin_response, 'svea_co_admin_svea_orders' );
		    } catch ( Exception $e ) {
			    WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when fetching information from Svea admin: %s', $e->getMessage() ) );

			    return;
		    }
	    }

	    // Check if the actions variable is set, otherwise return here
	    if( ! isset( $admin_response['Actions'] ) ) {
		    return;
	    }

	    if( in_array( 'CanCancelOrderRow', $admin_response['Actions'] ) ) {
	    	$order_row_cancel_request = array(
	    	    'OrderId'       => absint( $svea_order_id ),
			    'OrderRowId'    => absint( $svea_order_item_id ),
		    );

		    try {
			    WC_Gateway_Svea_Checkout::log( 'Trying add an order row with the admin client.' );
			    $admin_add_row_response = $admin_checkout_client->cancelOrderRow( apply_filters( 'woocommerce_sco_remove_order_row', $order_row_cancel_request ) );
		    } catch ( Exception $e ) {
			    WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when removing order row in Svea admin: %s', $e->getMessage() ) );
			    return;
		    }
	    }
    }

	/**
	 * Sync order items updated in admin to Svea
	 *
	 * @param $order_item_id int
	 * @param $order_item WC_Order_Item
	 * @param $order_id int
	 *
	 * @return void
	 */
    public function admin_update_order_item( $order_item_id, $order_item, $order_id ) {
        $wc_order = wc_get_order( $order_id );

	    if( ! $wc_order || ! is_a( $wc_order, 'WC_Order' ) || $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
		    return;
	    }

        if( ! ( $svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true ) ) ) {
            return;
        }

	    // Only edit orders which are final in Svea
	    if( ! $wc_order->meta_exists( '_svea_co_order_final' ) ) {
		    return;
	    }

        $wc_svea_checkout = WC_Gateway_Svea_Checkout::get_instance();

	    WC_Gateway_Svea_Checkout::log( 'Syncing admin edited order item to Svea.' );

	    $svea_order_item_id = $order_item->get_meta( '_svea_co_order_row_id', true );

	    if( ! $svea_order_item_id ) {
		    return;
	    }

	    $country_settings = $wc_svea_checkout->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );
	    $checkout_merchant_id = $country_settings['MerchantId'];
	    $checkout_secret = $country_settings['Secret'];

	    $admin_base_url        = $country_settings['AdminBaseUrl'];
	    $admin_connector       = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $admin_base_url );
	    $admin_checkout_client = new \Svea\Checkout\CheckoutAdminClient( $admin_connector );

	    $data['OrderId'] = intval( $svea_order_id );

	    $admin_response = wp_cache_get( intval( $svea_order_id ),'svea_co_admin_svea_orders' );

	    if( $admin_response === false ) {
		    try {
			    WC_Gateway_Svea_Checkout::log( 'Trying to get with admin checkout client.' );
			    $admin_response = $admin_checkout_client->getOrder( apply_filters( 'woocommerce_sco_get_order', $data ) );

			    wp_cache_set( intval( $svea_order_id ), $admin_response, 'svea_co_admin_svea_orders' );
		    } catch ( Exception $e ) {
			    WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when fetching information from Svea admin: %s', $e->getMessage() ) );

			    return;
		    }
	    }

	    if( ! isset( $admin_response['Actions'] ) || ! in_array( 'CanUpdateOrderRow', $admin_response['Actions'] ) ) {
		    return;
	    }

	    $svea_order_items = $admin_response['OrderRows'];

	    $update_order_row_data = array(
		    'OrderId'		=> absint( $svea_order_id ),
		    'OrderRowId'	=> absint( $svea_order_item_id ),
	    );

	    $order_row_data = array();

	    if( $order_item->is_type( 'line_item' ) ) {
		    $_product = $order_item->get_product();

		    if ( $_product && $_product->exists() && $order_item->get_quantity() ) {
			    $order_row_data = WC_Svea_Checkout_Order::get_svea_cart_item_from_wc_order_item( $wc_order, $order_item );
		    }
	    } else if( $order_item->is_type( array( 'fee', 'shipping' ) ) ) {
		    $order_row_data = WC_Svea_Checkout_Order::get_svea_cart_item_from_wc_order_item( $wc_order, $order_item );
	    }

	    // Only update order row data if it's not empty
	    if( ! is_array( $order_row_data ) || empty( $order_row_data ) ) {
		    return;
	    }

	    if( isset( $order_row_data['VatPercent'] ) ) {
		    $vat_rate = $order_row_data['VatPercent'];

		    if( ! WC_Gateway_Svea_Checkout::is_valid_vat_percentage( $wc_order->get_billing_country(), floatval( $vat_rate ) / 100.00 ) ) {
		    	WC_Gateway_Svea_Checkout::log( $vat_rate . ' is not a valid vat rate in ' . $wc_order->get_billing_country() );
		    	return;
		    }
	    }

	    $svea_order_row_needs_syncing = false;

	    // Loop through Svea order items to see if order item needs syncing
	    // This improves the performance of the sync by only updating attributes that have changed
	    foreach( $svea_order_items as $svea_order_item ) {
			if( $svea_order_item['OrderRowId'] == $svea_order_item_id ) {
				if( ! in_array( 'CanUpdateRow', $svea_order_item['Actions'] ) ) {
					break;
				}

				// Sync if article number has changed
				if( $svea_order_item['ArticleNumber'] != $order_row_data['ArticleNumber'] ) {
					WC_Gateway_Svea_Checkout::log( 'Article Number has changed' );
					$svea_order_row_needs_syncing = true;
					break;
				}

				// Sync if name has changed
				if( $svea_order_item['Name'] != $order_row_data['Name'] ) {
					WC_Gateway_Svea_Checkout::log( 'Name has changed' );
					$svea_order_row_needs_syncing = true;
					break;
				}

				// Sync if quantity has changed
				if( $svea_order_item['Quantity'] != $order_row_data['Quantity'] ) {
					WC_Gateway_Svea_Checkout::log( 'Quantity has changed' );
					$svea_order_row_needs_syncing = true;
					break;
				}

				// Sync if unit price has changed
				if( $svea_order_item['UnitPrice'] != $order_row_data['UnitPrice'] ) {
					WC_Gateway_Svea_Checkout::log( 'Unit price has changed' );
					$svea_order_row_needs_syncing = true;
					break;
				}

				// Sync if quantity has changed
				if( $svea_order_item['VatPercent'] != $order_row_data['VatPercent'] ) {
					WC_Gateway_Svea_Checkout::log( 'Vat percent has changed' );
					$svea_order_row_needs_syncing = true;
					break;
				}
			}
	    }

	    // Only sync if attributes have changed
	    if( $svea_order_row_needs_syncing === false ) {
	    	return;
	    }

	    $update_order_row_data['OrderRow'] = $order_row_data;

	    try {
		    WC_Gateway_Svea_Checkout::log( 'Trying to update order row with the admin checkout client.' );
		    $admin_update_row_response = $admin_checkout_client->updateOrderRow( apply_filters( 'woocommerce_sco_update_order_row', $update_order_row_data ) );

		    $total_order_item_count = count( $svea_order_items );

		    for($i=0;$i<$total_order_item_count;++$i) {
		    	$svea_order_item = $svea_order_items[$i];

			    if( $svea_order_item['OrderRowId'] == $svea_order_item_id ) {
				    // Update data in cache
				    $svea_order_items[$i]['UnitPrice'] = $order_row_data['UnitPrice'];
				    $svea_order_items[$i]['Quantity'] = $order_row_data['Quantity'];
				    break;
			    }
		    }

		    // Update admin response with new order items
		    $admin_response['OrderRows'] = $svea_order_items;

		    wp_cache_set( intval( $svea_order_id ), $admin_response, 'svea_co_admin_svea_orders' );
	    } catch ( Exception $e ) {
		    WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when fetching information from Svea admin: %s', $e->getMessage() ) );
	    }
    }

	/**
	 * Add rounding if order amount does not match in Svea and WooCommerce
	 *
	 * @param $order_id
	 *
	 * @return void
	 */
    public function admin_update_order( $order_id ) {
	    $wc_order = wc_get_order( $order_id );

	    if( ! $wc_order || ! is_a( $wc_order, 'WC_Order' ) || $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
		    return;
	    }

	    if( ! ( $svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true ) ) ) {
		    return;
	    }

	    // Only edit orders which are final in Svea
	    if( ! $wc_order->meta_exists( '_svea_co_order_final' ) ) {
		    return;
	    }

	    $wc_svea_checkout = WC_Gateway_Svea_Checkout::get_instance();

	    WC_Gateway_Svea_Checkout::log( 'Syncing admin edited order to Svea.' );

	    $country_settings = $wc_svea_checkout->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );
	    $checkout_merchant_id = $country_settings['MerchantId'];
	    $checkout_secret = $country_settings['Secret'];

	    $admin_base_url        = $country_settings['AdminBaseUrl'];
	    $admin_connector       = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $admin_base_url );
	    $admin_checkout_client = new \Svea\Checkout\CheckoutAdminClient( $admin_connector );

	    $data['OrderId'] = intval( $svea_order_id );

	    $admin_response = wp_cache_get( intval( $svea_order_id ),'svea_co_admin_svea_orders' );

	    if( $admin_response === false ) {
		    try {
			    WC_Gateway_Svea_Checkout::log( 'Trying to get with admin checkout client.' );
			    $admin_response = $admin_checkout_client->getOrder( apply_filters( 'woocommerce_sco_get_order', $data ) );

			    wp_cache_set( intval( $svea_order_id ), $admin_response, 'svea_co_admin_svea_orders' );
		    } catch ( Exception $e ) {
			    WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when fetching information from Svea admin: %s', $e->getMessage() ) );

			    return;
		    }
	    }

	    if( ! isset( $admin_response['Actions'] ) || ! in_array( 'CanUpdateOrderRow', $admin_response['Actions'] ) ) {
		    return;
	    }

	    $svea_order_items = $admin_response['OrderRows'];

	    $rounding_order_row_id = $wc_order->get_meta( '_svea_co_rounding_order_row_id', true );

	    $svea_total = 0.0;

	    // Calculate diff for total
	    foreach( $svea_order_items as $svea_order_item ) {
		    if( $svea_order_item['OrderRowId'] == $rounding_order_row_id
		        || $svea_order_item['IsCancelled'] ) {
			    continue;
		    }

		    $svea_total += ( floatval( $svea_order_item['UnitPrice'] ) / 100 ) * ( floatval( $svea_order_item['Quantity'] ) / 100 );
	    }

	    $total_diff = $wc_order->get_total() - $svea_total;

	    $total_diff_rounded = round( $total_diff * 100 );

	    if( $total_diff_rounded == 0 && $rounding_order_row_id ) {
		    // Remove current rounding order row
		    if( in_array( 'CanCancelOrderRow', $admin_response['Actions'] ) ) {
			    $order_row_cancel_request = array(
				    'OrderId'       => absint( $svea_order_id ),
				    'OrderRowId'    => absint( $rounding_order_row_id ),
			    );

			    try {
				    WC_Gateway_Svea_Checkout::log( 'Trying to cancel an order row with the admin client.' );
				    $admin_remove_row_response = $admin_checkout_client->cancelOrderRow( apply_filters( 'woocommerce_sco_remove_order_row', $order_row_cancel_request ) );

				    $total_order_item_count = count( $svea_order_items );

				    for($i=0;$i<$total_order_item_count;++$i) {
					    $svea_order_item = $svea_order_items[$i];

					    if( $svea_order_item['OrderRowId'] == $rounding_order_row_id ) {
						    // Update data in cache
						    $svea_order_items[$i]['IsCancelled'] = true;
						    break;
					    }
				    }

				    // Update admin response with new order items
				    $admin_response['OrderRows'] = $svea_order_items;

				    wp_cache_set( intval( $svea_order_id ), $admin_response, 'svea_co_admin_svea_orders' );

				    // Delete the meta data on the order
				    $wc_order->delete_meta_data( '_svea_co_rounding_order_row_id' );
				    $wc_order->save();
			    } catch ( Exception $e ) {
				    WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when removing order row in Svea admin: %s', $e->getMessage() ) );
			    }
		    }
	    } else if( $total_diff_rounded != 0 && absint( $total_diff_rounded ) < 1000 ) {

		    if( $rounding_order_row_id ) {
			    $rounding_cart_item = array(
				    'UnitPrice'     => $total_diff_rounded,
			    );

			    // Update current rounding order row
			    $order_row_update_request = array(
				    'OrderId'       => absint( $svea_order_id ),
				    'OrderRowId'    => absint( $rounding_order_row_id ),
				    'OrderRow'      => $rounding_cart_item,
			    );

			    try {
				    WC_Gateway_Svea_Checkout::log( 'Trying to update an order row with the admin client.' );
				    $admin_update_row_response = $admin_checkout_client->updateOrderRow( apply_filters( 'woocommerce_sco_update_order_row', $order_row_update_request ) );
			    } catch ( Exception $e ) {
				    WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when updating order row in Svea admin: %s', $e->getMessage() ) );
			    }
		    } else {
			    $rounding_cart_item = array(
				    'UnitPrice'     => $total_diff_rounded,
				    'ArticleNumber'         => 'ROUNDING',
				    'Name'                  => __( 'Cash rounding', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
				    'Quantity'              => 100,
				    'UnitPrice'             => $total_diff_rounded,
				    'VatPercent'            => 0,
			    );

			    // Create new rounding order row
			    $order_row_create_request = array(
				    'OrderId'       => absint( $svea_order_id ),
				    'OrderRow'      => $rounding_cart_item,
			    );

			    try {
				    WC_Gateway_Svea_Checkout::log( 'Trying to create an order row with the admin client.' );
				    $admin_add_row_response = $admin_checkout_client->addOrderRow( apply_filters( 'woocommerce_sco_add_order_row', $order_row_create_request ) );

				    if ( isset( $admin_add_row_response['OrderRowId'] ) ) {
					    $wc_order->update_meta_data( '_svea_co_rounding_order_row_id', absint( $admin_add_row_response['OrderRowId'] ) );
					    $wc_order->save();
				    }
			    } catch ( Exception $e ) {
				    WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when adding order row in Svea admin: %s', $e->getMessage() ) );
			    }
		    }
	    }
    }

	/**
	 * Sync order items added in admin to Svea
	 *
	 * @param $order_item_id int
	 * @param $order_item WC_Order_Item
	 * @param $order_id int
	 *
	 * @return void
	 */
    public function admin_add_order_item( $order_item_id, $order_item, $order_id ) {
	    $wc_order = wc_get_order( $order_id );

	    if( ! $wc_order || ! is_a( $wc_order, 'WC_Order' ) || $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
		    return;
	    }

	    if( ! ( $svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true ) ) ) {
		    return;
	    }

	    // Only edit orders which are final in Svea
	    if( ! $wc_order->meta_exists( '_svea_co_order_final' ) ) {
		    return;
	    }

	    $wc_svea_checkout = WC_Gateway_Svea_Checkout::get_instance();

	    WC_Gateway_Svea_Checkout::log( 'Syncing admin added order item to Svea.' );

	    $country_settings = $wc_svea_checkout->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );
	    $checkout_merchant_id = $country_settings['MerchantId'];
	    $checkout_secret = $country_settings['Secret'];

	    $admin_base_url        = $country_settings['AdminBaseUrl'];
	    $admin_connector       = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $admin_base_url );
	    $admin_checkout_client = new \Svea\Checkout\CheckoutAdminClient( $admin_connector );

	    $data['OrderId'] = intval( $svea_order_id );

	    $admin_response = wp_cache_get( intval( $svea_order_id ),'svea_co_admin_svea_orders' );

	    if( $admin_response === false ) {
		    try {
			    WC_Gateway_Svea_Checkout::log( 'Trying to get with admin checkout client.' );
			    $admin_response = $admin_checkout_client->getOrder( apply_filters( 'woocommerce_sco_get_order', $data ) );

			    wp_cache_set( intval( $svea_order_id ), $admin_response, 'svea_co_admin_svea_orders' );
		    } catch ( Exception $e ) {
			    WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when fetching information from Svea admin: %s', $e->getMessage() ) );

			    return;
		    }
	    }

	    if( ! isset( $admin_response['Actions'] ) || ! in_array( 'CanAddOrderRow', $admin_response['Actions'] ) ) {
		    return;
	    }

	    $order_row_data = array();

	    if( $order_item->is_type( 'line_item' ) ) {
		    $_product = $order_item->get_product();

		    if ( $_product->exists() && $order_item->get_quantity() ) {
			    $order_row_data = WC_Svea_Checkout_Order::get_svea_cart_item_from_wc_order_item( $wc_order, $order_item );
		    }
	    } else if( $order_item->is_type( array( 'fee', 'shipping' ) ) ) {
		    $order_row_data = WC_Svea_Checkout_Order::get_svea_cart_item_from_wc_order_item( $wc_order, $order_item );
	    }

	    if( ! empty( $order_row_data ) ) {
		    $new_order_row_data = array(
			    'OrderId'  => absint( $svea_order_id ),
			    'OrderRow' => $order_row_data,
		    );

		    try {
			    WC_Gateway_Svea_Checkout::log( 'Trying add an order row with the admin client.' );
				$admin_add_row_response = $admin_checkout_client->addOrderRow( apply_filters( 'woocommerce_sco_add_order_row', $new_order_row_data ) );

			    if ( isset( $admin_add_row_response['OrderRowId'][0] ) ) {
				    wc_update_order_item_meta( $order_item_id, '_svea_co_order_row_id', absint( $admin_add_row_response['OrderRowId'][0] ) );
			    }
		    } catch ( Exception $e ) {
			    WC_Gateway_Svea_Checkout::log( sprintf( 'Received error when fetching information from Svea admin: %s', $e->getMessage() ) );
		    }
	    }
    }

    /**
     * Process refund of an order
     *
     * @TODO We'll pause this function since it might be confusing together with the part-credit option
     * @param int $order_id ID of the order being refunded
     * @return void
     */
    public function refund_order( $order_id ) {
        $wc_order = wc_get_order( $order_id );

        if( $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
            return;
        }

        $result = WC_Gateway_Svea_Checkout::get_instance()->refund_order( $wc_order->get_id() );
    }

    /**
     * Process delivery of an order
     *
     * @param int $order_id ID of the order being delivered
     * @return void
     */
    public function deliver_order( $order_id ) {
    	$wc_order = wc_get_order( $order_id );

    	if( $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
    		return;
    	}

    	$result = WC_Gateway_Svea_Checkout::get_instance()->deliver_order( $wc_order->get_id() );
    }

    /**
     * Process cancellation of an order
     *
     * @param int $order_id ID of the order being cancelled
     * @return void
     */
    public function cancel_order( $order_id ) {
        $wc_order = wc_get_order( $order_id );

        if( $wc_order->get_payment_method() !== WC_Gateway_Svea_Checkout::GATEWAY_ID ) {
            return;
        }

        $result = WC_Gateway_Svea_Checkout::get_instance()->cancel_order( $wc_order->get_id() );
    }
}
