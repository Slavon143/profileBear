<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add fields to Svea settings page in WooCommerce
return apply_filters( 'wc_svea_checkout_settings',
    array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'label'       => __( 'Enable Svea Checkout', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'yes'
        ),
        'title'   => array(
            'title'       => __( 'Title', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'text',
            'default'     => __( 'Svea Checkout', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
        ),
        'customer_types' => array(
        	'title'       => __( 'Customer Types', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'        => 'select',
	        'options'     => array(
	        	'both'          => __( 'Companies and individuals', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
		        'company'       => __( 'Companies', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
		        'individual'    => __( 'Individuals', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        ),
	        'default'     => 'both',
	        'description' => __( 'Select which customer types you want to accept in your store.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'desc_tip'    => true
        ),
        'default_customer_type' => array(
	        'title'       => __( 'Default Customer Type', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'        => 'select',
	        'options'     => array(
		        'individual'    => __( 'Individual', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
		        'company'       => __( 'Company', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        ),
	        'default'     => 'individual',
	        'description' => __( 'Select which customer type you want to be selected by default. Only applicable if the store accepts companies and individuals.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
        ),
        'standard_checkout_page' => array(
            'title' => __( 'Standard checkout page', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type' => 'select',
            'options' => $this->standard_checkout_page_options(),
            'description' => __( 'If the cart total is 0, Svea Checkout will not work and will instead redirect to this page.', WC_Svea_Checkout_i18n::TEXT_DOMAIN )
        ),
        'preset_value_email_read_only' => array(
        	'title'       => __( 'E-mail read-only when logged in', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'        => 'checkbox',
	        'default'     => 'yes',
	        'description' => __( 'Choose whether or not the e-mail address should be read only for logged in users.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
        ),
        'preset_value_phone_read_only' => array(
	        'title'       => __( 'Phone read-only when logged in', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'        => 'checkbox',
	        'default'     => 'no',
	        'description' => __( 'Choose whether or not the phonenumber should be read only for logged in users.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
        ),
        'preset_value_zip_code_read_only' => array(
	        'title'       => __( 'Zip code read-only when logged in', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'        => 'checkbox',
	        'default'     => 'no',
	        'description' => __( 'Choose whether or not the zip code should be read only for logged in users.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
        ),
        'merchant_id_se' => array(
            'title'       => __( 'Merchant ID - Sweden', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'text',
            'description' => __( 'Please enter your Svea Merchant ID for Sweden. Leave blank to disable.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'default'     => '',
            'desc_tip'    => true
        ),
        'secret_se' => array(
            'title'       => __( 'Secret - Sweden', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'password',
            'description' => __( 'Please enter your Svea Secret for Sweden. Leave blank to disable.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'default'     => '',
            'desc_tip'    => true
        ),
        'testmode_se' => array(
            'title'       => __( 'Testmode - Sweden', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'label'       => __( 'Enable testmode in Sweden', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'no'
        ),
        'merchant_id_no' => array(
            'title'       => __( 'Merchant ID - Norway', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'text',
            'description' => __( 'Please enter your Svea Merchant ID for Norway. Leave blank to disable.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'default'     => '',
            'desc_tip'    => true
        ),
        'secret_no' => array(
            'title'       => __( 'Secret - Norway', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'password',
            'description' => __( 'Please enter your Svea Secret for Norway. Leave blank to disable.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'default'     => '',
            'desc_tip'    => true
        ),
        'testmode_no' => array(
            'title'       => __( 'Testmode - Norway', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'label'       => __( 'Enable testmode in Norway', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'no'
        ),
        'merchant_id_fi' => array(
            'title'       => __( 'Merchant ID - Finland', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'text',
            'description' => __( 'Please enter your Svea Merchant ID for Finland. Leave blank to disable.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'default'     => '',
            'desc_tip'    => true
        ),
        'secret_fi' => array(
            'title'       => __( 'Secret - Finland', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'password',
            'description' => __( 'Please enter your Svea Secret for Finland. Leave blank to disable.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'default'     => '',
            'desc_tip'    => true
        ),
        'testmode_fi' => array(
            'title'       => __( 'Testmode - Finland', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'label'       => __( 'Enable testmode in Finland', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'no'
        ),
        'merchant_id_dk' => array(
	        'title'       => __( 'Merchant ID - Denmark', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'        => 'text',
	        'description' => __( 'Please enter your Svea Merchant ID for Denmark. Leave blank to disable.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default'     => '',
	        'desc_tip'    => true
        ),
        'secret_dk' => array(
	        'title'       => __( 'Secret - Denmark', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'        => 'password',
	        'description' => __( 'Please enter your Svea Secret for Denmark. Leave blank to disable.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default'     => '',
	        'desc_tip'    => true
        ),
        'testmode_dk' => array(
	        'title'       => __( 'Testmode - Denmark', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'label'       => __( 'Enable testmode in Denmark', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'        => 'checkbox',
	        'description' => '',
	        'default'     => 'no'
        ),
        'log' => array(
            'title'       => __( 'Logging', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'label'       => __( 'Enable logs for Svea Checkout', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
            'type'        => 'checkbox',
            'default'     => 'no',
        ),
        'product_widget_title' => array(
	        'title' => __( 'Part payment widget', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'  => 'title',
        ),
        'display_product_widget' => array(
	        'title' => __( 'Display product part payment widget', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type' => 'checkbox',
	        'description' => __( 'Display a widget on the product page which suggests a part payment plan for the customer to use to buy the product.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default' => 'no',
        ),
        'product_widget_position' => array(
	        'title' => __( 'Product part payment widget position', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type' => 'select',
	        'description' => __( 'The position of the part payment widget on the product page. Is only displayed if the widget is activated.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default' => 15,
	        'options' => array(
		        '15' => __( 'Between price and excerpt', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
		        '25' => __( 'Between excerpt and add to cart', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
		        '35' => __( 'Between add to cart and product meta', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        )
        ),
        'hide_not_you' => array(
            'title' => __( 'Hide "Not you?"', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'  => 'checkbox',
	        'description' => __( 'Hide the "Not you?" button in the Svea iframe.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default' => 'no',
        ),
        'hide_change_address' => array(
	        'title' => __( 'Hide "Change address"', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'  => 'checkbox',
	        'description' => __( 'Hide the "Change address" button in from the Svea iframe.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default' => 'no',
        ),
        'hide_anonymous' => array(
	        'title' => __( 'Hide the anonymous flow', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'  => 'checkbox',
	        'description' => __( 'Hide the anonymous flow, forcing users to identify with their national id to perform a purchase.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default' => 'no',
        ),
        'save_cancelled_orders' => array(
            'title' => __( 'Save cancelled orders', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type' => 'checkbox',
	        'description' => __( 'Store orders that are not completed by the customers and set as "cancelled" in Svea. This is a way to follow up non-completed orders but might fill your order list with many cancelled orders.' ),
	        'default' => 'no',
        ),
        'sync_zip_code' => array(
	        'title'         => __( 'Sync ZIP code from Svea', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'          => 'checkbox',
	        'description'   => __( 'Enable ZIP code sync from the Svea Checkout iframe to WooCommerce, this enables usage of ZIP code specific shipping methods. <br />
						<strong>Do not touch this if you do not know what you are doing</strong>.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default'       => 'yes'
        ),
        'sync_settings_title' => array(
	        'title' => __( 'Sync settings', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'  => 'title',
        ),
        'sync_order_completion' => array(
	        'title'         => __( 'Sync order completion', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'          => 'checkbox',
	        'description'   => __( 'Enable automatic sync of completed orders from WooCommerce to Svea. <br />
						<strong>Do not touch this if you do not know what you are doing</strong>.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default'       => 'yes'
        ),
        'sync_order_cancellation' => array(
	        'title'         => __( 'Sync order cancellation', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'          => 'checkbox',
	        'description'   => __( 'Enable automatic sync of cancelled orders from WooCommerce to Svea. <br />
						<strong>Do not touch this if you do not know what you are doing</strong>.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default'       => 'yes'
        ),
        'sync_order_rows' => array(
	        'title'         => __( 'Sync order rows', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'type'          => 'checkbox',
	        'description'   => __( 'Enable automatic sync of order rows changed after purchase from WooCommerce to Svea. <br />
						This functionality only works on payment methods where payment is not made at the time of the purchase. <br />
						<strong>Do not touch this if you do not know what you are doing</strong>.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
	        'default'       => 'yes'
        ),
    )
        
);
