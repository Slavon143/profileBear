<?php
/**
 * Provides functions for the plugin settings page in the WordPress admin.
 *
 * Settings can be accessed at WooCommerce -> Settings -> Fortnox Hub.
 *
 * @package   WooCommerce_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WC_Admin_Fortnox_Hub_Settings', false)) {

/**
 * WC_Admin_Fortnox_Hub_Settings.
 */
    class WC_Admin_Fortnox_Hub_Settings extends WC_Settings_Page
    {

        private $license;
        private $fn;
        private $account_selection = array();

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'fortnox_hub';
            $this->label = __('Fortnox Hub', 'woo-fortnox-hub');
            add_action('woocommerce_settings_fortnox_connection_options', array($this, 'show_connection_button'), 20);
            add_action('woocommerce_settings_fortnox_advanced_options', array($this, 'show_clear_cache_button'), 10);
            add_filter('fortnox_get_sections', array($this, 'add_payment_options_settings_section'), 70);
            add_filter('fortnox_get_sections', array($this, 'add_delivery_terms_settings_section'), 80);
            add_action('woocommerce_settings_' . $this->id, array($this, 'authorize_processing'), 5);
            parent::__construct();
        }

        /**
         * Get sections.
         *
         * @return array
         */
        public function get_sections()
        {
            $sections = array(
                '' => __('Connection', 'woo-fortnox-hub'),
                'wc_order' => __('Orders', 'woo-fortnox-hub'),
            );

            $sections = apply_filters('fortnox_get_sections', $sections);

            $sections = array_merge($sections, array(
                'advanced' => __('Advanced', 'woo-fortnox-hub'),
            ));

            return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
        }

        public function add_payment_options_settings_section($sections)
        {
            if (!array_key_exists('payment_options', $sections)) {
                $sections = array_merge($sections, array('payment_options' => __('Payment', 'woo-fortnox-hub')));
            }
            return $sections;
        }

        public function add_delivery_terms_settings_section($sections)
        {
            if (!array_key_exists('shipping_terms', $sections)) {
                $sections = array_merge($sections, array('shipping_terms' => __('Shipping', 'woo-fortnox-hub')));
            }
            return $sections;
        }

        public function show_connection_button()
        {
            $connected = apply_filters('fortnox_is_connected', false);

            echo '<div id=fortnox_titledesc_connect>';
            echo '<tr valign="top">';
            echo '<th scope="row" class="titledesc">';
            if (!$connected) {
                echo '<label for="fortnox_connect">' . __('Connect to Fortnox', 'woo-fortnox-hub') . '<span class="woocommerce-help-tip" data-tip="' . __('Connect the plugn to Fortnox', 'woo-fortnox-hub') . '"></span></label>';
            } else {
                echo '<label for="fortnox_disconnect">' . __('Disconnect from Fortnox', 'woo-fortnox-hub') . '<span class="woocommerce-help-tip" data-tip="' . __('Disconnect the plugin from Fortnox', 'woo-fortnox-hub') . '"></span></label>';
            }
            echo '</th>';
            echo '<td class="forminp forminp-button">';
            if (!$connected) {
                echo '<button name="fortnox_connect" id="fortnox_connect" class="button fortnox_connection">' . __('Connect', 'woo-fortnox-hub') . '</button>';
            } else {
                echo '<button name="fortnox_disconnect" id="fortnox_disconnect" class="button fortnox_connection">' . __('Disconnect', 'woo-fortnox-hub') . '</button>';
            }
            echo '</td>';
            echo '</tr>';
            echo '</div>';
        }

        public function show_clear_cache_button()
        {
            echo '<div id=fortnox_titledesc_clear_cache>';
            echo '<tr valign="top">';
            echo '<th scope="row" class="titledesc">';
            echo '<label for="fortnox_clear_cache">' . __('Clear static data cache', 'woo-fortnox-hub') . '<span class="woocommerce-help-tip" data-tip="' . __('Clear the cache for static data imported from Fortnox. The next pageload after clearing will take a little bit longer time than normal.', 'woo-fortnox-hub') . '"></span></label>';
            echo '</th>';
            echo '<td class="forminp forminp-button">';
            echo '<button name="fortnox_clear_cache" id="fortnox_clear_cache" class="button">' . __('Clear', 'woo-fortnox-hub') . '</button>';
            echo '</td>';
            echo '</tr>';
            echo '</div>';
        }

        /**
         * Output the settings.
         */
        public function output()
        {
            global $current_section;
            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        }

        /**
         * Save settings.
         */
        public function save()
        {
            global $current_section;

            do_action('fortnox_save_settings_' . $current_section);

            if (!empty($sales_from_countries = get_option('fortnox_account_selling_countries', array()))) {
                foreach ($sales_from_countries as $sales_from_country) {
                    $id = 'fortnox_account_selling_countries_' . strtolower($sales_from_country);
                    if (isset($_POST[$id])) {
                        $options = explode("\n", wp_unslash($_POST[$id]));
                        $save_options = array();
                        foreach ($options as $option) {
                            $save_option = trim($option);
                            if (is_numeric($save_option)) {
                                $save_options[] = $save_option;
                            }
                        }
                        update_option($id, $save_options);
                    }
                }
            }

            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::save_fields($settings);
        }

        public function get_payment_options($type)
        {
            $options = array();
            $objects = apply_filters('fortnox_get_' . $type . 's_of_payments', array());
            if (!empty($objects)) {
                $options[''] = 'term' == $type ? __('Select payment term', 'woo-fortnox-hub') : __('Select payment mode', 'woo-fortnox-hub');
                foreach ($objects as $object) {
                    $payment_code = str_replace('_', '', $object['Code']);
                    $options[$payment_code] = $object['Description'];
                }
            }
            return $options;
        }

        private function get_valid_order_statuses()
        {
            $valid_order_statuses = array_merge(array('' => __('Do not create automatically', 'woo-fortnox-hub')), WCFH_Util::get_order_statuses());

            unset($valid_order_statuses['cancelled']);
            unset($valid_order_statuses['refunded']);
            unset($valid_order_statuses['failed']);

            return $valid_order_statuses;
        }

        public function authorize_processing()
        {
            if (array_key_exists('authorization_code', $_REQUEST) && array_key_exists('refresh_token', $_REQUEST)) {
                if (array_key_exists('nonce', $_REQUEST) && trim($_REQUEST['nonce']) === get_site_transient('fortnox_handle_account')) {
                    Fortnox_Notice::clear();

                    update_option('fortnox_refresh_token', $_REQUEST['refresh_token']);
                    update_option('fortnox_authorization_code', $_REQUEST['authorization_code']);
                    delete_site_transient('fortnox_accesstoken');
                    WC_FH()->logger->add(sprintf('Got refresh token %s from service', $_REQUEST['refresh_token']));
                    set_site_transient('fortnox_connect_result', 'success', MINUTE_IN_SECONDS);
                    delete_site_transient('fortnox_handle_account');

                    try {
                        WC_FH()->fortnox->get_access_token();
                        $message = sprintf(__('<strong>Congratulations!</strong> Your plugin was successfully connected to Fortnox.', 'woo-fortnox-hub'));
                        Fortnox_Notice::add($message, 'info');
                        WC_FH()->logger->add(sprintf('Succcessfully authorized, authorization code is %s', $_REQUEST['authorization_code']));
                        return;
                    } catch (Fortnox_API_Exception $e) {
                        WC_FH()->logger->add(print_r($e, true));
                        $e->write_to_logs();
                        $error = $e->getMessage();
                    }

                } else {
                    WC_FH()->logger->add('Nonce not verified at authorize_processing');
                }
            } elseif (array_key_exists('error', $_REQUEST)) {
                WC_FH()->logger->add(sprintf('Error when connecting to Fortnox'));
                Fortnox_Notice::add(
                    sprintf(__('Something went wrong when trying to connect the plugin to your Fortnox account, contact hello@bjorntech.com for assistance', 'woo-fortnox-hub')),
                    'error'
                );
            }

        }

        public function order_print_templates_choice()
        {

            $order_print_templates_choice = array();

            $order_creates = get_option('fortnox_woo_order_creates');

            if (in_array($order_creates, array('order')) && !empty($order_print_templates = apply_filters('fortnox_get_print_templates', array(), 'order'))) {
                $order_print_templates_choice[''] = __('Use Fortnox default', 'woo-fortnox-hub');
                foreach ($order_print_templates as $print_template) {
                    $order_print_templates_choice[$print_template['Template']] = $print_template['Name'];
                }
            }

            return $order_print_templates_choice;

        }

        public function invoice_print_templates_choice()
        {

            $invoice_print_templates_choice = array();

            $order_creates = get_option('fortnox_woo_order_creates');

            if (in_array($order_creates, array('order', 'invoice')) && !empty($invoice_print_templates = apply_filters('fortnox_get_print_templates', array(), 'invoice'))) {
                $invoice_print_templates_choice[''] = __('Use Fortnox default', 'woo-fortnox-hub');
                foreach ($invoice_print_templates as $print_template) {
                    $invoice_print_templates_choice[$print_template['Template']] = $print_template['Name'];
                }
            }

            return $invoice_print_templates_choice;

        }

        /**
         * Get settings array.
         *
         * @param string $current_section Current section name.
         * @return array
         */
        public function get_settings($current_section = '')
        {
            $settings = array();

            $valid_order_statuses = $this->get_valid_order_statuses();

            if ('payment_options' == $current_section) {

                if (!(empty($payment_gateways = WCFH_Util::get_available_payment_gateways()))) {

                    foreach ($payment_gateways as $payment_method => $payment_gateway) {

                        $modes_of_payments = apply_filters('fortnox_get_modes_of_payments', array());
                        if (!empty($modes_of_payments)) {
                            $payment_mode = get_option('fortnox_mode_of_payment_' . $payment_method);
                            foreach ($modes_of_payments as $terms_of_payment) {
                                $payment_code = str_replace('_', '', $terms_of_payment['Code']);
                                if ($payment_mode && $payment_code == $payment_mode) {
                                    update_option('fortnox_payment_account_' . $payment_method, $terms_of_payment['AccountNumber']);
                                }
                            }
                        }

                        $description = (($title = $payment_gateway->get_title()) ? $title : $payment_gateway->get_method_title());
                        $section_settings = array(
                            array(
                                'title' => sprintf(__('Settings for %s "%s"', 'woo-fortnox-hub'), $description, $payment_method),
                                'type' => 'title',
                                'desc' => '',
                                'id' => 'fortnox_payment_section_' . $payment_method,
                            ),
                            array(
                                'title' => __('Term of payment', 'woo-fortnox-hub'),
                                'type' => 'select',
                                'default' => '',
                                'options' => $this->get_payment_options('term'),
                                'id' => 'fortnox_term_of_payment_' . $payment_method,
                            ),
                            array(
                                'title' => __('Mode of payment', 'woo-fortnox-hub'),
                                'type' => 'select',
                                'default' => '',
                                'options' => $this->get_payment_options('mode'),
                                'id' => 'fortnox_mode_of_payment_' . $payment_method,
                            ),
                            array(
                                'title' => __('Payment info on invoice', 'woo-fortnox-hub'),
                                'type' => 'select',
                                'default' => 'yes',
                                'options' => array(
                                    '' => __('Do not add any payment information', 'woo-fortnox-hub'),
                                    'yes' => __('Add payment information as remark', 'woo-fortnox-hub'),
                                    'comment' => __('Add payment information as comment', 'woo-fortnox-hub'),
                                    'yes_comment' => __('Add payment information as remark and comment', 'woo-fortnox-hub'),
                                ),
                                'desc' => __('Check if you do want payment information to be added the Fortnox Order/Invoice.', 'woo-fortnox-hub'),
                                'id' => 'fortnox_invoice_payment_remark_' . $payment_method,
                            ),
                            wc_string_to_bool(get_option('fortnox_create_invoice_from_order_payment_method')) ? array(
                                'title' => __('Create Fortnox Invoice instead of Fortnox Order', 'woo-fortnox-hub'),
                                'type' => 'checkbox',
                                'default' => '',
                                'desc' => __('Check if you do want a Fortnox Invoice to be created instead of a Fortnox Order for this payment method.', 'woo-fortnox-hub'),
                                'id' => 'fortnox_create_invoice_from_order_' . $payment_method,
                            ) : array(),
                            array(
                                'title' => __('Do not sync', 'woo-fortnox-hub'),
                                'type' => 'checkbox',
                                'default' => '',
                                'desc' => __('Check if you do want to prevent orders paid with this payment method to be synced to Fortnox.', 'woo-fortnox-hub'),
                                'id' => 'fortnox_do_not_sync_' . $payment_method,
                            ),
                            array(
                                'type' => 'sectionend',
                                'id' => 'fortnox_payment_section_' . $payment_method,
                            ),
                        );
                        $settings = array_merge($settings, $section_settings);

                    }
                }
            } elseif ('shipping_terms' === $current_section) {

                $terms_of_deliveries_choice = array();
                $way_of_deliveries_choice = array();

                $terms_of_deliveries = apply_filters('fortnox_get_terms_of_deliveries', array());
                if (!empty($terms_of_deliveries)) {
                    $terms_of_deliveries_choice[''] = __('Select terms of delivery', 'woo-fortnox-hub');
                    foreach ($terms_of_deliveries as $terms_of_delivery) {
                        $terms_of_deliveries_choice[$terms_of_delivery['Code']] = $terms_of_delivery['Description'];
                    }
                }

                $way_of_deliveries = apply_filters('fortnox_get_way_of_deliveries', array());
                if (!empty($way_of_deliveries)) {
                    $way_of_deliveries_choice[''] = __('Select way of delivery', 'woo-fortnox-hub');
                    foreach ($way_of_deliveries as $way_of_delivery) {
                        $way_of_deliveries_choice[$way_of_delivery['Code']] = $way_of_delivery['Description'];
                    }
                }

                $response = array();
                $zones = WC_Shipping_Zones::get_zones();

                if (($default_zone = new WC_Shipping_Zone(0))){
                    array_push($zones,$default_zone);
                }

                foreach ($zones as $zone) {

                    $shipping_methods = array();

                    if(is_object($zone)){
                        $shipping_methods = $zone->get_shipping_methods();
                        $zone_formatted_name = $zone->get_formatted_location();
                    } else {
                        $shipping_methods = $zone['shipping_methods'];
                        $zone_formatted_name = $zone['formatted_zone_location'];
                    }

                    foreach ($shipping_methods as $shipping_method) {

                        $description = $shipping_method->get_title();
                        $method_id = $shipping_method->id;
                        $instance_id = $shipping_method->get_instance_id();

                        $settings[] = [
                            'title' => sprintf(__('Settings for %s in Shipping Zone %s', 'woo-fortnox-hub'), $description, $zone_formatted_name),
                            'type' => 'title',
                            'desc' => '',
                            'id' => 'fortnox_delivery_section_' . $method_id . '_' . $instance_id,
                        ];

                        $settings[] = [
                            'title' => __('Term of delivery', 'woo-fortnox-hub'),
                            'type' => 'select',
                            'default' => get_option('fortnox_term_of_delivery_' . $method_id),
                            'options' => $terms_of_deliveries_choice,
                            'id' => 'fortnox_term_of_delivery_' . $method_id . '_' . $instance_id,
                        ];

                        $settings[] = [
                            'title' => __('Way of delivery', 'woo-fortnox-hub'),
                            'type' => 'select',
                            'default' => get_option('fortnox_way_of_delivery_' . $method_id),
                            'options' => $way_of_deliveries_choice,
                            'id' => 'fortnox_way_of_delivery_' . $method_id . '_' . $instance_id,
                        ];

                        $settings[] = [
                            'title' => __('Article number', 'woo-fortnox-hub'),
                            'type' => 'text',
                            'default' => get_option('fortnox_shipping_article_number_' . $method_id, get_option('fortnox_shipping_customer_number')), // Stored in wrong option earlier
                            'desc' => __('Enter the article number to use for the Shipping row, if empty shipping will be created without an article number.', 'woo-fortnox-hub'),
                            'id' => 'fortnox_shipping_article_number_' . $method_id . '_' . $instance_id,
                        ];

                        $settings[] = [
                            'type' => 'sectionend',
                            'id' => 'fortnox_delivery_section_' . $method_id . '_' . $instance_id,
                        ];

                    }

                }

            } elseif ('advanced' === $current_section) {

                $barcode_selection = array(
                    '_fortnox_ean' => __('Use default plugin field', 'woocommerce'),
                    '_barcode' => __('Use the "_barcode" metadatafield.', 'woocommerce'),
                );

                if (class_exists('WC_iZettle_Integration', false)) {
                    $barcode_selection['_izettle_barcode'] = __('Use the barcode from Zettle', 'woo-fortnox-hub');
                }

                $category_options = WCFH_Util::get_category_options();
                $accounting_method = WCFH_Util::get_accounting_method();

                $initial_settings = array(
                    array(
                        'title' => __('Advanced options', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'fortnox_advanced_options',
                    ),
                    array(
                        'title' => __('Disable notices', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Disable notices from Fortnox, please note that you will not get any information about errors if checked.', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_disable_notices',
                    ),
                    array(
                        'title' => __('Queue admin requests', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Requests to Fortnox are normally queued for performande except when a user is logged in as admin. Check if admin requests should also be queued.', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_queue_admin_requests',
                    ),
                    array(
                        'title' => __('Enable housework', 'woo-fortnox-hub'),
                        'default' => '',
                        'type' => 'checkbox',
                        'desc' => __('Enable housework fields on products and when creating Fortnox Order/Invoice.', 'woo-fortnox-hub'),
                        'id' => 'fortnox_enable_housework',
                    ),
                    array(
                        'title' => __('Set Fortnox language', 'woo-fortnox-hub'),
                        'default' => (get_locale() == 'sv_SE') ? 'SV' : 'EN',
                        'type' => 'select',
                        'options' => array(
                            'SV' => __('Set Fortnox invoice/order language to Swedish', 'woo-fortnox-hub'),
                            'EN' => __('Set Fortnox invoice/order language to English', 'woo-fortnox-hub')
                        ),
                        'desc' => __('Set the language on Fortnox orders and invoices', 'woo-fortnox-hub'),
                        'id' => 'fortnox_language',
                    ),
                    array(
                        'title' => __('CRON disabled on server', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('If your server has CRON-jobs disabled you must check this box in order for the plugin to work', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_manual_cron',
                    ),
                    array(
                        'title' => __('Hide order meta', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Hide order meta in the detailed admin screen', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_hide_admin_order_meta',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_advanced_options',
                    ),
                    array(
                        'title' => __('Advanced payout options', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'fortnox_advanced_payout_options',
                    ),
                    array(
                        'title' => __('Stripe payouts', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Enable Stripe payout functions', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_stripe_payouts',
                    ),
                    array(
                        'title' => __('Svea payouts', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Enable Svea payout functions', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_svea_payouts',
                    ),
                    array(
                        'title' => __('Clearhaus payouts', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Enable Clearhaus payout functions', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_clearhaus_payouts',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_advanced_payout_options',
                    ),
                    array(
                        'title' => __('Advanced order options', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'fortnox_advanced_order_options',
                    ),
                    array(
                        'title' => __('Use WooCommerce order-id', 'woo-fortnox-hub'),
                        'default' => '',
                        'type' => 'checkbox',
                        'desc' => __('Use the WooCommerce order-id as Order/Inoice number in Fortnox.', 'woo-fortnox-hub'),
                        'id' => 'fortnox_use_woocommerce_order_number',
                    ),
                    array(
                        'title' => __('Customer order note', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Select where the text from a order note should be placed.', 'woo-fortnox-hub'),
                        'default' => '',
                        'options' => array(
                            '' => __('Set customer order note in comments', 'woo-fortnox-hub'),
                            'remarks' => __('Set customer order note in remarks', 'woo-fortnox-hub'),
                            'nowhere' => __('Do not use customer order note', 'woo-fortnox-hub'),
                        ),
                        'id' => 'fortnox_customer_note_place',
                    ),
                    array(
                        'title' => __('Clean vat number', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Replace existing vat numbers with the format xxxxxxxxxx with the new Fortnox standard xxxxxx-xxxx to prevent customer sync errors.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_clean_vat_number',
                    ),
                    array(
                        'title' => __('Set Admin fee', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Add Admin fee to the Order/Invoice.', 'woo-fortnox-hub'),
                        'default' => 0,
                        'id' => 'fortnox_set_administration_fee',
                    ),
                    array(
                        'title' => __('Admin Fee', 'woo-fortnox-hub'),
                        'type' => 'number',
                        'desc' => __('Admin fee to be added to the Order/Invoice if selected above.', 'woo-fortnox-hub'),
                        'default' => 0,
                        'id' => 'fortnox_administration_fee',
                    ),
                    array(
                        'title' => __('Copy order remarks', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Copy any order remarks from a Fortnox order to the Invoice created', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_order_copy_remarks',
                    ),
                    array(
                        'title' => __('Include coupon rows', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Include any used coupon codes as a row on the Fortnox invoice/order', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_document_use_coupon_rows',
                    ),
                    array(
                        'title' => __('Set 0 Discount', 'woo-fortnox-hub'),
                        'default' => 'yes',
                        'desc' => __('Set discount on order rows to 0. This to prevent that customer discounts are applied on already paid orders.', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_set_discount_to_zero',
                    ),
                    'ACCRUAL' === $accounting_method ? array(
                        'title' => __('Bookkeep invoices', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'default' => '',
                        'desc' => __("Automatically bookkeep invoices when the order is set to completed. This will option will be used if a payment method is used that Fortnox Hub can't identify.", 'woo-fortnox-hub'),
                        'id' => 'fortnox_book_invoice',
                    ) : array(),
                    'CASH' === $accounting_method ? array(
                        'title' => __('Set as printed', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __("Set a created invoice to externally printed when it has been created. When using the cash accounting method this is the way to mark the invoice as ready to be paid. This will option will be used if a payment method is used that Fortnox Hub can't identify.", 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_set_invoice_as_external_printed', 
                    ) : array(),
                    array(
                        'title' => __('Use Freight field', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Use the internal Fortnox Freight field for shipping cost instead of adding shipping as a row on the invoice', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_document_use_shipping_field',
                    ),
                    array(
                        'title' => __('Never clear Freight', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Select if the Freight field should not be cleared when adding shipping as an order row.', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_do_not_clear_freight',
                    ),
                    array(
                        'title' => __('Do not use external references', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Fortnox Hub do store order numbers on Orders/Invoices, if WooCommerce for some reason was reset to reuse old order numbers this setting must be enabled to avoid errors.', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_do_not_use_external_refs',
                    ),
                    ('order' == get_option('fortnox_woo_order_creates')) ? array(
                        'title' => __('Create Fortnox Invoice instead of Fortnox Order for specific payment methods', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Enable the option to automatically create Fortnox Invoices instead Fortnox Orders based on the payment method in the WooCommerce order.', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_create_invoice_from_order_payment_method'  
                    ) : array(),
                    ('order' == get_option('fortnox_woo_order_creates') && wc_string_to_bool(get_option('fortnox_create_invoice_from_order_payment_method'))) ? array(
                        'title' => __('Always create a Fortnox Order before creating a Fortnox Invoice', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __("Enable the option to force Fortnox Hub to create a Fortnox Order for every WooCommerce order. Doesn't work with Fortnox Lager.", 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_force_create_order'  
                    ) : array(),
                    in_array(get_option('fortnox_woo_order_creates'), array('order', 'invoice')) ? array(
                        'title' => __('Automatically set WarehouseReady on Order/Invoice', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Enable the option to automatically set WarehouseReady on orders/invoices that are created by the plugin.', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_set_warehouseready'  
                    ) : array(),
                    array(
                        'title' => __('Backorder qty to zero', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Set Delivered quantity to 0 if the WooCommerce product is on backorder.', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_set_backorder_products_to_zero',
                    ),
                    array(
                        'title' => __('Delay emails', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Delay sending confirmation emails until a document has been created in Fortnox.', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_delay_emails_until_processed',
                    ),
                    array(
                        'title' => __('Max retries', 'woo-fortnox-hub'),
                        'default' => 1,
                        'desc' => __('Minutes to delay before trying to send the email again.', 'woo-fortnox-hub'),
                        'type' => 'number',
                        'id' => 'fortnox_delay_emails_delay_time',
                    ),
                    array(
                        'title' => __('Max retries', 'woo-fortnox-hub'),
                        'default' => 1,
                        'desc' => __('Maximum number of retries before sending mail without Fortnox sycing done.', 'woo-fortnox-hub'),
                        'type' => 'number',
                        'id' => 'fortnox_delay_emails_max_retries',
                    ),
                    array(
                        'title' => __('Legacy Invoice print template', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => '',
                        'options' => $this->invoice_print_templates_choice(),
                        'desc' => __('Select the invoice print template ', 'woo-fortnox-hub'),
                        'id' => 'fortnox_invoice_print_template',
                    ),
                    array(
                        'title' => __('Legacy Order print template', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => '',
                        'options' => $this->order_print_templates_choice(),
                        'desc' => __('Select the invoice print template ', 'woo-fortnox-hub'),
                        'id' => 'fortnox_order_print_template',
                    ),
                    'order' == get_option('fortnox_woo_order_creates') ? array(
                        'title' => __('Create Fortnox Invoice', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => '',
                        'desc' => __('Select in what status to create a Fortnox Invoice from a Fortnox Order', 'woo-fortnox-hub'),
                        'options' => $valid_order_statuses,
                        'id' => 'fortnox_create_invoice_from_order',
                    ) : array(),
                    array(
                        'title' => __('Delete file invoice payments', 'woo-fortnox-hub'),
                        'default' => '',
                        'desc' => __('Delete invoice payments that Fortnox faulty links to Invoices from a bank file import.', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'id' => 'fortnox_delete_invoice_file_payments',
                    ),

                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_advanced_order_options',
                    ),
                    array(
                        'title' => __('Advanced product options', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'fortnox_advanced_product_options',
                    ),
                    array(
                        'title' => __('Update daily', 'woo-fortnox-hub'),
                        'default' => '',
                        'type' => 'checkbox',
                        'desc' => __('Update all products in Fortnox on a daily basis. Useful to to ensure that Fortnox is always updated with correct data from WooCommerce.', 'woo-fortnox-hub'),
                        'id' => 'fortnox_sync_wc_products_daily',
                    ),
                    array(
                        'title' => __('Update sales account', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Update the sales account for sales in Sweden on products based on settings in the plugin. Useful if you are creating some invoices of orders manually in Fortnox.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_wc_product_update_sales_account',
                    ),
                    array(
                        'title' => __('Update product type', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Update product type (stock or if virtual is set service) in Fortnox from WooCommerce', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_wc_product_update_type',
                    ),
                    array(
                        'title' => __('Barcode mapping', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Select what field to update the barcode (EAN) field in Fortnox.', 'woo-fortnox-hub'),
                        'default' => '_fortnox_ean',
                        'options' => $barcode_selection,
                        'id' => 'fortnox_metadata_mapping_ean',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_advanced_product_options',
                    ),
                    array(
                        'title' => __('Advanced "Products from Fortnox" options', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'fortnox_advanced_fn_products_options',
                    ),
                    array(
                        'title' => __('Set backorder option instock', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Select how a product imported from Fortnox should handle backorders when the product has stock level > 0.', 'woo-fortnox-hub'),
                        'default' => '',
                        'options' => array(
                            '' => __('Do not set from Fortnox', 'woo-fortnox-hub'),
                            'no' => __('Do not allow', 'woo-fortnox-hub'),
                            'notify' => __('Allow, but notify customer', 'woo-fortnox-hub'),
                            'yes' => __('Allow', 'woo-fortnox-hub'),
                        ),
                        'id' => 'fortnox_backorder_option_instock',
                    ),
                    array(
                        'title' => __('Set backorder option outofstock', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Select how a product imported from Fortnox should handle backorders when the product has stock level <= 0', 'woo-fortnox-hub'),
                        'default' => '',
                        'options' => array(
                            '' => __('Do not set from Fortnox', 'woo-fortnox-hub'),
                            'no' => __('Do not allow', 'woo-fortnox-hub'),
                            'notify' => __('Allow, but notify customer', 'woo-fortnox-hub'),
                            'yes' => __('Allow', 'woo-fortnox-hub'),
                        ),
                        'id' => 'fortnox_backorder_option_outofstock',
                    ),
                    array(
                        'title' => __('Webshop articles only', 'woo-fortnox-hub'),
                        'default' => '',
                        'type' => 'checkbox',
                        'desc' => __('If checked the plugin will update only from articles set to "Webshop article".', 'woo-fortnox-hub'),
                        'id' => 'fortnox_update_webshop_articles_only',
                    ),
                    array(
                        'title' => __('Create WooCommerce products', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Create WooCommerce simple products if not existing.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_create_simple_product_from_article',
                    ),
                    array(
                        'title' => __('Product status when created', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Select the status for a newly created products.', 'woo-fortnox-hub'),
                        'default' => '',
                        'options' => get_post_statuses(),
                        'id' => 'fortnox_create_simple_product_from_article_status',
                    ),
                    array(
                        'title' => __('Product category when created', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Select the category for a newly created products.', 'woo-fortnox-hub'),
                        'default' => '',
                        'options' => $category_options,
                        'id' => 'fortnox_create_simple_product_from_article_category',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_advanced_fn_products_options',
                    ),
                    array(
                        'title' => __('Advanced account options', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => __('Advanced settings for the account options', 'woo-fortnox-hub'),
                        'id' => 'fortnox_advanced_account_options',
                    ),
                );
                $settings = array_merge($initial_settings, array(
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_advanced_account_options',
                    ),
                    array(
                        'title' => __('Advanced connection options', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => __('Do NOT change these settings without prior contact with BjornTech support.', 'woo-fortnox-hub'),
                        'id' => 'fortnox_advanced_connection_options',
                    ),
                    array(
                        'title' => __('Alternate webhook url', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'description' => __('The url used for webhook callback. Do NOT change unless instructed by BjornTech.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'bjorntech_alternate_webhook_url',
                    ),
                    array(
                        'title' => __('Alternate service url', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'description' => __('The url to the BjornTech Fortnox service. Do NOT change unless instructed by BjornTech.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_service_url',
                    ),
                    array(
                        'title' => __('Fortnox API Code', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'default' => '',
                        'desc' => __('In order to be able to connect this plugin with Fortnox a license you do need the Fortnox integration module', 'woo-fortnox-hub'),
                        'id' => 'fortnox_authorization_code',
                    ),
                    array(
                        'title' => __('Fortnox refresh token', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'description' => __('Should only be changed if instructed by BjornTech support.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_refresh_token',
                    ),
                    array(
                        'title' => __('Check invoices from', 'woo-fortnox-hub'),
                        'default' => '',
                        'type' => 'datetime',
                        'desc' => __('Date and time (in the format YYYY-MM-DD HH:MM) when the plugin last checked Fortnox for changed invoices.', 'woo-fortnox-hub'),
                        'id' => 'fortnox_hub_sync_last_sync_invoices',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_advanced_connection_options',
                    ),
                ));
            } elseif ('wc_order' === $current_section) {

                $costcenter_choice[''] = __('No cost center', 'woo-fortnox-hub');
                $costcenters = apply_filters('fortnox_get_cost_centers', array());
                if (!empty($costcenters)) {
                    foreach ($costcenters as $costcenter) {
                        $costcenter_choice[$costcenter['Code']] = $costcenter['Description'];
                    }
                }

                $project_choice[''] = __('No project', 'woo-fortnox-hub');
                $projects = apply_filters('fortnox_get_projects', array());
                if (!empty($projects)) {
                    foreach ($projects as $project) {
                        $project_choice[$project['ProjectNumber']] = $project['Description'];
                    }
                }

                $invoice_print_templates_choice = $this->invoice_print_templates_choice();
                $order_print_templates_choice = $this->order_print_templates_choice();

                $order_creates = get_option('fortnox_woo_order_creates');

                $settings[] = [
                    'title' => __('Setup for what should happen when a WooCommerce order is created', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_wc_order_options',
                ];
                $settings[] = [
                    'title' => __('WooCommerce order creates', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'desc' => __('Select if you want an order in WooCommerce to create an Fortnox order or invoice or just change the stocklevel in Fortnox based on a WooCommerce order.', 'woo-fortnox-hub'),
                    'options' => array(
                        '' => __('Nothing', 'woo-fortnox-hub'),
                        'invoice' => __('Fortnox Invoice', 'woo-fortnox-hub'),
                        'order' => __('Fortnox Order', 'woo-fortnox-hub'),
                        'stockchange' => __('Fortnox Stockchange', 'woo-fortnox-hub'),
                    ),
                    'id' => 'fortnox_woo_order_creates',
                ];

                if ($order_creates) {

                    $settings[] = [
                        'title' => __('Create on order status', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => '',
                        'desc' => __('Select on what order-status the Fortnox order or invoice will be created.', 'woo-fortnox-hub'),
                        'options' => $valid_order_statuses,
                        'id' => 'fortnox_woo_order_create_automatic_from',
                    ];

                }

                if (in_array($order_creates, array('invoice', 'order'))) {

                    $settings[] = [
                        'title' => __('Our reference', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'desc' => __('Enter a text for the field "Our reference"', 'woo-fortnox-hub'),
                        'id' => 'fornox_our_reference',
                    ];
                    $settings[] = [
                        'title' => __('Display amounts excl. tax', 'woo-fortnox-hub'),
                        'default' => '',
                        'type' => 'checkbox',
                        'desc' => __('Display all amounts on order/invoice excluding tax.', 'woo-fortnox-hub'),
                        'id' => 'fortnox_amounts_excl_tax',
                    ];
                    $settings[] = [
                        'title' => __('Date created', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => '',
                        'desc' => __('Select what creation date to be used on the Fortnox order/invoice.', 'woo-fortnox-hub'),
                        'options' => array(
                            '' => __('Use WooCommerce order date.', 'woo-fortnox-hub'),
                            'last_sync' => __('Use date when synced to Fortnox.', 'woo-fortnox-hub'),
                        ),
                        'id' => 'fortnox_document_date',
                    ];
                    $settings[] = [
                        'title' => __('Use cost center', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => '',
                        'options' => $costcenter_choice,
                        'id' => 'fortnox_cost_center',
                    ];
                    $settings[] = [
                        'title' => __('Use project', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => '',
                        'options' => $project_choice,
                        'id' => 'fortnox_project',
                    ];
                    $settings[] = [
                        'title' => __('Delivery days', 'woo-fortnox-hub'),
                        'type' => 'number',
                        'desc' => __('Number of working days between the order date and the delivery date to be set on the Fortnox order or invoice.', 'woo-fortnox-hub'),
                        'default' => 0,
                        'id' => 'fortnox_default_delivery_days',
                    ];

                    $settings[] = [
                        'title' => __('Article Handling', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => '',
                        'desc' => __('Select what article/product information should be added to the Order/Invoice in Fortnox', 'woo-fortnox-hub'),
                        'options' => array(
                            '' => __('Add the article number to the Order/Invoice - use product name if unavailable', 'woo-fortnox-hub'),
                            'never' => __('Add the product name to the Order/Invoice', 'woo-fortnox-hub'),
                            'error' => __('Stop the creation of the Order/Invoice if no article number is found', 'woo-fortnox-hub'),
                        ),
                        'id' => 'fortnox_no_articlenumber_in_orderrow',
                    ];

                    if (function_exists('WC_PB')) {
                        $settings[] = [
                            'title' => __('Product bundles', 'woo-fortnox-hub'),
                            'type' => 'select',
                            'desc' => __('Select the behaviour for product s.', 'woo-fortnox-hub'),
                            'default' => '',
                            'options' => array(
                                '' => __('Remove bundled_order items', 'woo-fortnox-hub'),
                                'remove_price' => __('Remove bundled order items price', 'woo-fortnox-hub'),
                                'no_change' => __('Leave items unchanged', 'woo-fortnox-hub'),
                            ),
                            'id' => 'fortnox_wc_product_bundles',
                        ];
                    }

                    if (!(empty($payment_gateways = WCFH_Util::get_available_payment_gateways()))) {

                        foreach ($payment_gateways as $payment_method => $payment_gateway) {

                            if (!empty($order_print_templates_choice)) {

                                $settings[] = [
                                    'title' => sprintf(__('Order print template %s', 'woo-fortnox-hub'), $payment_gateway->get_title()),
                                    'type' => 'select',
                                    'default' => get_option('fortnox_order_print_template'),
                                    'options' => $order_print_templates_choice,
                                    'desc' => sprintf(__('Select the order print template to use for orders using %s as payment method.', 'woo-fortnox-hub'), $payment_gateway->get_title()),
                                    'id' => 'fortnox_order_print_template_' . $payment_method,
                                ];

                            } else {

                                if (!empty($invoice_print_templates_choice)) {
                                    $settings[] = [
                                        'title' => sprintf(__('Invoice print template %s', 'woo-fortnox-hub'), $payment_gateway->get_title()),
                                        'type' => 'select',
                                        'default' => get_option('fortnox_invoice_print_template'),
                                        'options' => $invoice_print_templates_choice,
                                        'desc' => sprintf(__('Select the invoice print template to use for orders using %s as payment method.', 'woo-fortnox-hub'), $payment_gateway->get_title()),
                                        'id' => 'fortnox_invoice_print_template_' . $payment_method,
                                    ];

                                }

                            }

                        }

                    }

                }
                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_wc_order_options',
                ];

            } elseif ($current_section == '') {

                $connected = apply_filters('fortnox_is_connected', false);

                if (!$connected) {
                    $instruction_text = __('In order to connect this plugin with Fortnox, the integration module must be active on your Fortnox-account.<br>', 'woo-fortnox-hub');
                    $instruction_text = __('Please press Connect to connect to your Fortnox account.<br>', 'woo-fortnox-hub');
                } else {
                    $valid_to = strtotime(get_option('fortnox_valid_to'));
                    $instruction_text = sprintf(__('<b>Connected to the BjornTech Fortnox service using authorization code:</b> %s<br><br>', 'woo-fortnox-hub'), get_option('fortnox_authorization_code'));

                    $response = WCFH_Util::service_message();
                    if (false !== $response) {
                        $instruction_text .= '<div class="notice-' . $response->type . ' notice-alt notice-large" style="margin-bottom:15px!important">' . $response->message . '</div>';
                    } else {
                        $valid_to = strtotime(get_option('fortnox_valid_to'));
                        $instruction_text .= '<div class="notice-success notice-alt notice-large" style="margin-bottom:15px!important">' . sprintf(__('The service is active and valid to %s', 'woo-fortnox-hub'), WCFH_Util::datetime_display($valid_to)) . '</div>';
                    }
                }

                $settings[] = [
                    'title' => __('Connection with Fortnox', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => $instruction_text,
                    'id' => 'fortnox_connection_options',
                ];

                $settings[] = [
                    'title' => __('Fortnox User email', 'woo-fortnox-hub'),
                    'type' => 'email',
                    'default' => '',
                    'id' => 'fortnox_user_email',
                ];

                $settings[] = [
                    'title' => __('Enable logging', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => sprintf(__('Logging is useful when troubleshooting. You can find the logs <a href="%s">here</a>', 'woo-fortnox-hub'), WC_FH()->logger->get_admin_link()),
                    'type' => 'checkbox',
                    'id' => 'fortnox_logging',
                ];

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_connection_options',
                ];

            }

            return apply_filters('woocommerce_get_settings_' . $this->id, $settings, $current_section);
        }

    }

    return new WC_Admin_Fortnox_Hub_Settings();
}
