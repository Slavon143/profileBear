<?php

/**
 * Utility functions for WooCommerce Fortnox Hub.
 *
 * @package   WooCommerce_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */
// Prevent direct file access

defined('ABSPATH') || exit;

if (!class_exists('WCFH_Util', false)) {
    class WCFH_Util
    {

        /**
         * replace empty fields with API_BLANK (= the field is cleared)
         */
        public static function api_blank(&$indata, $key)
        {
            if (!$indata) {
                $indata = 'API_BLANK';
            }
        }

        public static function remove_blanks($items)
        {
            if (is_array($items)) {
                foreach ($items as $key => $item) {
                    if ($item === '') {
                        unset($items[$key]);
                    }
                }
            }
            return $items;
        }

        public static function get_bank_account($order)
        {
            return strval(get_option('fortnox_' . self::get_payment_method($order, 'get_bank_account') . '_bank_account'));
        }

        /**
         * sset_fortnox_order_vouchernumber function
         *
         * Set the Fortnox Order DocumentNumber on an order
         *
         * @access public
         * @return void
         */
        public static function set_fortnox_order_vouchernumber($order_id, $fortnox_payment_vouchernumber)
        {
            if ($fortnox_payment_vouchernumber != "") {
                update_post_meta($order_id, '_fortnox_order_vouchernumber', $fortnox_payment_vouchernumber);
            } else {
                delete_post_meta($order_id, '_fortnox_order_vouchernumber');
            }
        }

        /**
         * get_fortnox_order_vouchernumber
         *
         * If the order has a Fortnox Order DocumentNumber, we will return it. If not present we return FALSE.
         *
         * @access public
         * @return string
         * @return bool
         */
        public static function get_fortnox_order_vouchernumber($order_id)
        {
            return (($result = get_post_meta($order_id, '_fortnox_order_vouchernumber', true)) == "" ? false : $result);
        }

        /**
         * sset_fortnox_payment_vouchernumber function
         *
         * Set the Fortnox Order DocumentNumber on an order
         *
         * @access public
         * @return void
         */
        public static function set_fortnox_payment_vouchernumber($order_id, $fortnox_payment_vouchernumber)
        {
            if ($fortnox_payment_vouchernumber != "") {
                update_post_meta($order_id, '_fortnox_payment_vouchernumber', $fortnox_payment_vouchernumber);
            } else {
                delete_post_meta($order_id, '_fortnox_payment_vouchernumber');
            }
        }

        /**
         * get_fortnox_payment_vouchernumber
         *
         * If the order has a Fortnox payment documentNumber, we will return it. If not present we return FALSE.
         *
         * @access public
         * @return string
         * @return bool
         */
        public static function get_fortnox_payment_vouchernumber($order_id)
        {
            return (($result = get_post_meta($order_id, '_fortnox_payment_vouchernumber', true)) == "" ? false : $result);
        }

        /**
         * set_fortnox_invoice_number
         *
         * Set the Fortnox Invoice DocumentNumber on an order
         *
         * @access public
         * @return void
         */
        public static function set_fortnox_invoice_number($order_id, $fortnox_order_documentnumber)
        {
            if ($fortnox_order_documentnumber != "") {
                update_post_meta($order_id, '_fortnox_invoice_number', $fortnox_order_documentnumber);
            } else {
                delete_post_meta($order_id, '_fortnox_invoice_number');
            }
        }

        /**
         * get_fortnox_invoce_documentnumber
         *
         * If the order has a Fortnox Order DocumentNumber, we will return it. If not present we return FALSE.
         *
         * @access public
         * @return string
         * @return bool
         */
        public static function get_fortnox_invoice_number($order_id)
        {
            $result = get_post_meta($order_id, '_fortnox_invoice_number', true);
            if ((!$result)) {
                $result = get_post_meta($order_id, 'Fortnox Invoice number', true);
            }

            return $result;
        }

        /**
         * Create currencydata if the order is paid via stripe
         *
         * Set the Fortnox Order DocumentNumber on an order
         *
         * @param WC_Order $order
         *
         * @access public
         * @return void
         */
        public static function create_currency_payment_data($order)
        {
            $stripe_currency = $order->get_meta('_stripe_currency', true);
            $order_currency = $order->get_currency();
            $woo_cost = $order->get_total();

            WC_FH()->logger->add(sprintf('create_currency_payment_data (%s): Order total is %s %s', $order->get_id(), $order_currency, $woo_cost));

            if ($stripe_currency && $stripe_currency == 'SEK' && $order_currency != 'SEK' && empty($order->get_refunds())) {
                $stripe_cost = $order->get_meta('_stripe_fee', true) + $order->get_meta('_stripe_net', true);
                $currency_rate = floatval($stripe_cost / $woo_cost);

                WC_FH()->logger->add(sprintf('create_currency_payment_data (%s): Stripe total is %s %s', $order->get_id(), $stripe_currency, $stripe_cost));

                if ($currency_rate > 0) {
                    return array(
                        'CurrencyRate' => $currency_rate,
                        'CurrencyUnit' => 1,
                    );
                }
            }

            if ($order_currency != 'SEK') {
                return array(
                    'CurrencyRate' => 'API_BLANK',
                    'CurrencyUnit' => 'API_BLANK',
                );
            }

            return array(
                'CurrencyRate' => 1,
                'CurrencyUnit' => 1,
            );
        }

        /**
         * set_fortnox_order_documentnumber function
         *
         * Set the Fortnox Order DocumentNumber on an order
         *
         * @access public
         * @return void
         */

        public static function set_fortnox_order_documentnumber($order_id, $fortnox_order_documentnumber)
        {
            if ($fortnox_order_documentnumber != "") {
                update_post_meta($order_id, '_fortnox_order_documentnumber', $fortnox_order_documentnumber);
            } else {
                delete_post_meta($order_id, '_fortnox_order_documentnumber');
            }
        }

        /**
         * get_fortnox_order_documentnumber
         *
         * If the order has a Fortnox Order DocumentNumber, we will return it. If not present we return FALSE.
         *
         * @access public
         * @return string
         * @return bool
         */
        public static function get_fortnox_order_documentnumber($order_id)
        {
            $result = get_post_meta($order_id, '_fortnox_order_documentnumber', true);
            if (!$result) {
                $result = get_post_meta($order_id, 'FORTNOX_ORDER_DOCUMENTNUMBER', true);
            }

            return ($result == "" ? false : $result);
        }

        public static function clean_fortnox_text($str, $max_len = false, $empty = '')
        {
            $re = '/[^\p{L}\’\\\\\x{030a}a-zåäöéáœæøüA-ZÅÄÖÉÁÜŒÆØ0-9 –:\.`´’,;\^¤#%§£$€¢¥©™°&\/\(\)=\+\-\*_\!?²³®½\@\x{00a0}\n\r]*/u';
            $subst = '';

            $result = preg_replace($re, $subst, $str);

            if ($max_len !== false) {
                $result = substr($result, 0, $max_len);
            }

            return empty($result) ? $empty : $result;
        }

        public static function get_product_categories()
        {
            $cat_args = array(
                'orderby' => 'name',
                'order' => 'asc',
                'hide_empty' => false,
            );
            return get_terms('product_cat', $cat_args);
        }

        public static function get_category_options()
        {
            $category_options = array();
            $product_categories = self::get_product_categories();

            if (!empty($product_categories)) {
                foreach ($product_categories as $category) {
                    $category_options[$category->slug] = $category->name;
                }
            }

            return $category_options;
        }

        public static function is_izettle($order)
        {
            if ('shop_order_refund' == $order->get_type()) {
                $parent_id = $order->get_parent_id();
                $parent = wc_get_order($parent_id);
                return in_array($parent->get_created_via(), array('izettle', 'zettle'));
            } else {
                return in_array($order->get_created_via(), array('izettle', 'zettle'));
            }
        }

        public static function set_fortnox_article_number($product, $article_number)
        {
            $product->set_sku(apply_filters('fortnox_set_sku', $article_number, $product));
        }

        public static function get_fortnox_article_number($product)
        {
            return apply_filters('fortnox_get_sku', $product->get_sku('edit'), $product);
        }

        /**
         * Find a WooCommerce product from a Fortnox Article
         *
         * @since 1.0.0
         *
         * @param string $article_number A Fortnox article number
         *
         * @return int $product_id A Wocommerce product id or 0 if not found
         */
        public static function get_product_id_from_article_number($article_number)
        {
            if (!empty($article_number)) {
                return wc_get_product_id_by_sku($article_number);
            }

            return 0;
        }

        public static function decode_external_reference($external_reference)
        {
            return strstr(WCFH_Util::encode_external_reference($external_reference), ':', true);
        }

        public static function encode_external_reference($external_reference)
        {
            $cost_center = get_option('fortnox_cost_center');
            $project = get_option('fortnox_project');
            return implode(':', array($external_reference, $cost_center, $project));
        }

        /**
         * Set_fortnox_customer_number function
         *
         * Set the Fortnox CustomerNumber on an order
         *
         * @access private
         * @return void
         */
        public static function set_fortnox_customer_number($order_id, $fortnox_customer_number)
        {
            if ($fortnox_customer_number != "") {
                update_post_meta($order_id, '_fortnox_customer_number', $fortnox_customer_number);
            } else {
                delete_post_meta($order_id, '_fortnox_customer_number');
            }
        }

        /**
         * get_fortnox_customer_number
         *
         * If the order has a Fortnox CustomerNumber, we will return it. If not present we return FALSE.
         *
         * @access public
         * @return string
         */
        public static function get_fortnox_customer_number($order)
        {
            if (!WCFH_Util::is_izettle($order)) {
                return (get_post_meta($order->get_id(), '_fortnox_customer_number', true));
            } else {
                return get_option('fortnox_izettle_customer_number', false);
            }
        }

        /*
         * Inserts a new key/value before the key in the array.
         *
         * @param $key
         *   The key to insert before.
         * @param $array
         *   An array to insert in to.
         * @param $new_key
         *   The key to insert.
         * @param $new_value
         *   An value to insert.
         *
         * @return
         *   The new array if the key exists, FALSE otherwise.
         *
         * @see array_insert_after()
         */
        public static function array_insert_before($key, array &$array, $new_key, $new_value)
        {
            if (array_key_exists($key, $array)) {
                $new = array();
                foreach ($array as $k => $value) {
                    if ($k === $key) {
                        $new[$new_key] = $new_value;
                    }
                    $new[$k] = $value;
                }
                return $new;
            }
            return false;
        }

        /*
         * Inserts a new key/value after the key in the array.
         *
         * @param $key
         *   The key to insert after.
         * @param $array
         *   An array to insert in to.
         * @param $new_key
         *   The key to insert.
         * @param $new_value
         *   An value to insert.
         *
         * @return
         *   The new array if the key exists, FALSE otherwise.
         *
         * @see array_insert_before()
         */
        public static function array_insert_after($key, array &$array, $new_key, $new_value)
        {
            if (array_key_exists($key, $array)) {
                $new = array();
                foreach ($array as $k => $value) {
                    $new[$k] = $value;
                    if ($k === $key) {
                        $new[$new_key] = $new_value;
                    }
                }
                return $new;
            }
            return false;
        }

        public static function maybe_get_option($option, $default)
        {
            if (false === $default) {
                return get_option($option);
            }
            return '';
        }

        public static function check_sync_config($ps_sync = false, $ps_pricelist = false, $order_creates = false, $pr_sync = false, $pr_pricelist = false, $product_stocklevel = false)
        {
            $ps_sync = WCFH_Util::maybe_get_option('fortnox_sync_from_fortnox_automatically', $ps_sync);
            $ps_pricelist = WCFH_Util::maybe_get_option('fortnox_process_price', $ps_pricelist);
            $pr_sync = WCFH_Util::maybe_get_option('fortnox_create_products_automatically', $pr_sync);
            $pr_pricelist = WCFH_Util::maybe_get_option('fortnox_wc_product_pricelist', $pr_pricelist);

            if ('yes' == $ps_sync && 'yes' == $pr_sync) {
                if ('' != $ps_pricelist && '' != $pr_pricelist && $ps_pricelist != $pr_pricelist) {
                    return __('When syncing both "product" and "price & stocklevel" automatically they must be using the same pricelist. Automated syncing stopped.', 'woo-fortnox-hub');
                }
            }

            return false;
        }

        public static function datetime_display($datetime)
        {
            return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $datetime);
        }

        public static function service_message()
        {
            $valid_to = strtotime(get_option('fortnox_valid_to'));
            $now = time();
            if ($valid_to && $now > $valid_to) {
                return (object) array(
                    'message' => sprintf(__('Your BjornTech Fortnox Hub service expired %s, go to <a href="%s">our webshop</a> to purchase a subscription', 'woo-fortnox-hub'), WCFH_Util::datetime_display($valid_to), self::get_purchase_link()),
                    'type' => 'error',
                );
            }
            return false;
        }

        /**
         * @return string
         */
        public static function get_purchase_link()
        {
            $authorization_code = get_option('fortnox_authorization_code');
            return 'https://bjorntech.com/sv/produkt/fortnox-1000/?token=' . $authorization_code . "&utm_source=wp-fortnox&utm_medium=plugin&utm_campaign=product";
        }

        public static function check_if_invoice_already_created($id, $check_cancel = true)
        {
            $invoices = WC_FH()->fortnox->getInvoicesByExternalInvoiceReference1($id);
            if ($invoices["MetaInformation"]["@TotalResources"] > 0) {
                foreach ($invoices['Invoices'] as $invoice) {
                    if (!$check_cancel || !rest_sanitize_boolean($invoice['Cancelled'])) {
                        return WC_FH()->fortnox->get_invoice($invoice['DocumentNumber']);
                    }
                }
            }
            return false;
        }

        public static function check_if_order_already_created($id, $check_cancel = true)
        {
            $orders = WC_FH()->fortnox->getOrdersByExternalInvoiceReference1($id);
            if ($orders["MetaInformation"]["@TotalResources"] > 0) {
                foreach ($orders['Orders'] as $order) {
                    if (!$check_cancel || !rest_sanitize_boolean($order['Cancelled'])) {
                        return WC_FH()->fortnox->get_order($order['DocumentNumber']);
                    }
                }
            }
            return false;
        }

        public static function wc_version_check($version = '4.0')
        {
            if (class_exists('WooCommerce')) {
                global $woocommerce;
                if (version_compare($woocommerce->version, $version, ">=")) {
                    return true;
                }
            }
            return false;
        }

        public static function prices_include_tax()
        {
            return (wc_tax_enabled() && 'yes' == get_option('woocommerce_prices_include_tax'));
        }

        /**
         * Checks what type of interaction to perform with Fortnox
         */
        public static function fortnox_wc_order_creates($order)
        {
            if (!is_object($order)) {
                $order = wc_get_order($order);
            }

            $creates = get_option('fortnox_woo_order_creates');
            if ('order' == $creates && WCFH_Util::is_izettle($order)) {
                return 'invoice';
            }
            return apply_filters('fortnox_wc_order_creates', $creates, $order);
        }

        public static function get_available_payment_gateways()
        {
            $available_gateways = array();

            if (WC()->payment_gateways()) {
                $wc_payment_gateways = WC_Payment_Gateways::instance();
                foreach ($wc_payment_gateways->payment_gateways() as $gateway) {
                    if (wc_string_to_bool($gateway->enabled)) {
                        $available_gateways[$gateway->id] = $gateway;
                    }
                }
            }

            return apply_filters('fortnox_payment_gateways', $available_gateways);
        }

        public static function is_european_country($country)
        {
            $countries = new WC_Countries();
            return in_array($country, $countries->get_european_union_countries('eu_vat'));
        }

        public static function get_countries()
        {
            $countries = new WC_Countries();
            return $countries->get_countries();
        }

        public static function do_not_queue_requests()
        {

            if (is_admin() && !wc_string_to_bool(get_option('fortnox_queue_admin_requests'))) {
                return true;
            } else {
                return false;
            }
        }

        public static function get_accounting_method()
        {
            if (!empty($financial_year = apply_filters('fortnox_get_financial_year', array(), time()))) {
                $accounting_method = reset($financial_year)['AccountingMethod'];
            } else {
                $accounting_method = '';
            }

            return $accounting_method;
        }

        public static function get_order_statuses()
        {
            $order_statuses = array();

            foreach (wc_get_order_statuses() as $slug => $name) {
                $order_statuses[str_replace('wc-', '', $slug)] = $name;
            }

            return $order_statuses;
        }

        public static function weight_to_grams($weight, $unit = 'kg')
        {
            $weight = (float) $weight;

            switch ($unit) {
                case 'kg':
                    $response = $weight * 1000;
                    break;
                case 'lbs':
                    $response = $weight * 453.59237;
                    break;
                case 'oz':
                    $response = $weight * 28.3495231;
                    break;
                case 'g':
                default:
                    $response = $weight;
            }

            return $response;
        }

        public static function weight_from_grams($weight, $unit = 'kg')
        {
            $response = $weight;

            if (is_numeric($weight)) {
                switch ($unit) {
                    case 'kg':
                        $response = $weight / 1000;
                        break;
                    case 'lbs':
                        $response = $weight / 453.59237;
                        break;
                    case 'oz':
                        $response = $weight / 28.3495231;
                        break;
                    case 'g':
                    default:
                        $response = $weight;
                }
            }

            return $response;
        }

        public static function dimension_to_millimeters($dimension, $unit = 'cm')
        {
            $response = $dimension;

            if (is_numeric($dimension)) {
                switch ($unit) {
                    case 'm':
                        $response = $dimension * 1000;
                        break;
                    case 'cm':
                        $response = $dimension * 10;
                        break;
                    case 'in':
                        $response = $dimension * 25.4;
                        break;
                    case 'yd':
                        $response = $dimension * 914.4;
                        break;
                }
            }

            return $response;
        }

        public static function dimension_from_millimeters($dimension, $unit = 'cm')
        {
            $response = $dimension;

            if (is_numeric($dimension)) {
                switch ($unit) {
                    case 'm':
                        $response = $dimension / 1000;
                        break;
                    case 'cm':
                        $response = $dimension / 10;
                        break;
                    case 'in':
                        $response = $dimension / 25.4;
                        break;
                    case 'yd':
                        $response = $dimension / 914.4;
                        break;
                }
            }

            return $response;
        }

        public static function display_name($id)
        {
            switch ($id) {
                case 'wcfh_sync_wc_products':
                    return __('WooCommerce products', 'woo-izettle-integration');
                    break;
                case 'wcfh_sync_fn_products':
                    return __('Fortnox articles', 'woo-izettle-integration');
                    break;
            }
            return '';
        }

        public static function get_processing_queue($group)
        {
            return as_get_scheduled_actions(
                array(
                    'group' => $group,
                    'status' => ActionScheduler_Store::STATUS_PENDING,
                    'claimed' => false,
                    'per_page' => -1,
                ),
                'ids'
            );
        }

        public static function display_sync_button($id, $class = '')
        {
            if (!empty($processing_queue = self::get_processing_queue($id))) {
                echo '<div id=' . $id . '_status name="' . $id . '" class="wcfh_processing_status" ></div>';
                $button_text = __('Cancel', 'woo-fortnox-hub');
            } else {
                $button_text = __('Start', 'woo-fortnox-hub');
            }

            echo '<div id=' . $id . '_titledesc>';
            echo '<tr valign="top">';
            echo '<th scope="row" class="titledesc ' . $class . '">';
            echo '<label for="' . $id . '">' . __('Action', 'woo-fortnox-hub') . '</label>';
            echo '</th>';
            echo '<td class="forminp forminp-button">';
            echo '<button id="' . $id . '" class="button wcfh_processing_button">' . $button_text . '</button>';
            echo '</td>';
            echo '</tr>';
            echo '</div>';
        }

        public static function get_term_by_slug($slug)
        {
            $term = get_term_by('slug', $slug, 'product_cat');
            return $term->term_id ? $term->term_id : '';
        }

        /**
         * Check if the product should be synced or nor
         *
         * @since 4.1.0
         *
         * @param WC_Product $product The WooCommerce product to be checked
         *
         * @return bool True if the product can be synced and falese if not
         */
        public static function is_syncable($product)
        {
            $product_id = $product->get_id();

            if ($product->is_type('variation') && ($parent_id = $product->get_parent_id()) && ($parent = wc_get_product($parent_id))) {
                WC_FH()->logger->add(sprintf('is_syncable (%s): Changed check to product parent %s', $product_id, $parent_id));
            } else {
                $parent_id = $product_id;
                $parent = $product;
            }

            $product_type = $parent->get_type();
            $products_include = get_option('fortnox_wc_products_include', array('simple', 'variable'));
            if (!in_array($product_type, $products_include)) {
                WC_FH()->logger->add(sprintf('is_syncable (%s): Product type "%s" is not within "%s"', $product_id, $product_type, implode(',', $products_include)));
                return false;
            }

            $product_statuses = get_option('fortnox_wc_get_product_status', array('publish'));
            $status = $parent->get_status('edit');
            if (!in_array($status, $product_statuses)) {
                WC_FH()->logger->add(sprintf('is_syncable (%s): Product status "%s" is not within "%s"', $product_id, $status, implode(',', $product_statuses)));
                return false;
            }

            $category_ids = $parent->get_category_ids('edit');
            $product_categories = !($product_categories_raw = get_option('fortnox_wc_products_product_categories')) ? array() : array_map('self::get_term_by_slug', $product_categories_raw);
            if (!empty($product_categories) && empty(array_intersect($category_ids, $product_categories))) {
                WC_FH()->logger->add(sprintf('is_syncable (%s): Product categories "%s" is not within "%s"', $product_id, implode(',', $category_ids), implode(',', $product_categories)));
                return false;
            }

            return true;
        }

        public static function get_tax_rate($product)
        {
            $tax_array = array(
                'country' => WC()->countries->get_base_country(),
                'state' => WC()->countries->get_base_state(),
                'city' => WC()->countries->get_base_city(),
                'postcode' => WC()->countries->get_base_postcode(),
                'tax_class' => $product->get_tax_class(),
            );

            return WC_Tax::find_rates($tax_array);
        }

        public static function maybe_add_vat($price, $product)
        {
            $tax_rates = self::get_tax_rate($product);

            if ($tax_rates) {
                $tax_rate = round(reset($tax_rates)['rate']);

                if (false !== $tax_rate) {
                    $tax_multiplier = 1 + ($tax_rate / 100);
                    $original_price = $price;
                    $price = strval($price * $tax_multiplier);
                } else {
                    WC_FH()->logger->add(sprintf('maybe_add_vat (%s): No VAT tax rate found', $product->get_id()));
                }
            }

            return $price;
        }

        public static function maybe_remove_vat($price, $product)
        {
            $tax_rates = self::get_tax_rate($product);

            if ($tax_rates) {
                $tax_rate = round(reset($tax_rates)['rate']);

                if (false !== $tax_rate) {
                    $tax_multiplier = 1 + ($tax_rate / 100);
                    $original_price = $price;
                    $price = substr(strval($price / $tax_multiplier), 0, 15);
                } else {
                    WC_FH()->logger->add(sprintf('maybe_remove_vat: No VAT tax rate found for product %s', $product->get_id()));
                }
            }

            return $price;
        }

        public static function get_product_types()
        {
            $types = wc_get_product_types();
            if (isset($types['grouped'])) {
                unset($types['grouped']);
            }
            if (isset($types['external'])) {
                unset($types['external']);
            }
            return $types;
        }

        /**
         * Get an array of available variations for the current product.
         * Use our own to get all variations regardless of filtering
         *
         * @param WC_Product $product
         * @return array
         */
        public static function get_all_variations($product)
        {
            $available_variations = array();

            foreach ($product->get_children() as $child_id) {
                $variation = wc_get_product($child_id);

                $available_variations[] = $product->get_available_variation($variation);
            }
            $available_variations = array_values(array_filter($available_variations));

            return $available_variations;
        }

        public static function object_diff(stdClass $obj1, stdClass $obj2): bool
        {
            $array1 = json_decode(json_encode($obj1, JSON_INVALID_UTF8_IGNORE), true);
            $array2 = json_decode(json_encode($obj2, JSON_INVALID_UTF8_IGNORE), true);
            return self::array_diff($array1, $array2);
        }

        public static function array_diff(array $array1, array $array2): bool
        {
            foreach ($array1 as $key => $value) {
                if (array_key_exists($key, $array2)) {
                    if ($value instanceof stdClass) {
                        $r = self::object_diff((object) $value, (object) $array2[$key]);
                        if ($r === true) {
                            return true;
                        }
                    } elseif (is_array($value)) {
                        $r = self::array_diff((array) $value, (array) $array2[$key]);
                        if ($r === true) {
                            return true;
                        }
                    } elseif (is_double($value)) {
                        // required to avoid rounding errors due to the
                        // conversion from string representation to double
                        if (0 !== bccomp($value, $array2[$key], 12)) {
                            WC_FH()->logger->add(sprintf('array_diff: Key {%s} was changed from "%s" to "%s"', $key, $array2[$key], $value));
                            return true;
                        }
                    } else {
                        if ($value != $array2[$key]) {
                            WC_FH()->logger->add(sprintf('array_diff: Key {%s} was changed from "%s" to "%s"', $key, $array2[$key], $value));
                            return true;
                        }
                    }
                } else {
                    WC_FH()->logger->add(sprintf('array_diff: Key {%s} does not exist in old data', $array1[$key]));
                    return true;
                }
            }
            return false;
        }

        public static function get_option_key($key, $default_key)
        {
            $update_key = get_option("fortnox_metadata_mapping_{$key}", $default_key ? $default_key : "_fortnox_{$key}");
            return $update_key;
        }

        public static function decamelize($string)
        {
            return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
        }

        public static function get_metadata($product, $fortnox_key, $default_key = false)
        {
            if (!is_object($product)) {
                $product = wc_get_product($product);
            }

            $key = self::decamelize($fortnox_key);
            return $product->get_meta(apply_filters('fortnox_get_metadata_key', self::get_option_key($key, $default_key), $product, $fortnox_key, $default_key), true, 'edit');
        }

        public static function update_metadata($product, $fortnox_key, $metadata, $save = false, $default_key = false)
        {
            if (!is_object($product)) {
                $product = wc_get_product($product);
            }

            $key = self::decamelize($fortnox_key);

            $product->update_meta_data(apply_filters('fortnox_update_metadata_key', self::get_option_key($key, $default_key), $product, $fortnox_key, $default_key, $metadata), $metadata);

            if ($save) {
                $product->save();
            }
        }

        public static function delete_metadata($product, $fortnox_key, $default_key = false)
        {
            if (!is_object($product)) {
                $product = wc_get_product($product);
            }

            $key = self::decamelize($fortnox_key);

            $product->delete_meta_data(apply_filters('fortnox_delete_metadata_key', self::get_option_key($key, $default_key), $product, $fortnox_key, $default_key));
        }

        public static function add_failed_order($order_id)
        {
            $failed_orders = ($data = get_site_transient('fortnox_failed_orders')) ? $data : array();
            $failer_orders[$order_id] = time();
            set_site_transient('fortnox_failed_orders', $failed_orders);
        }

        public static function clear_failed_order($order_id)
        {
            $failed_orders = ($data = get_site_transient('fortnox_failed_orders')) ? $data : array();
            if (array_key_exists($order_id, $failed_orders)) {
                $failed_orders = array_diff($failed_orders, array($order_id => ''));
                if (empty($failed_orders)) {
                    delete_site_transient('fortnox_failed_orders');
                } else {
                    set_site_transient('fortnox_failed_orders', $failed_orders);
                }
            }
        }

        public static function valid_housework_types()
        {
            $type_none = array(
                '' => __("None", 'woo-fortnox-hub'),
            );
            return array_merge($type_none, self::valid_housework_types_rot(), self::valid_housework_types_rut(), self::valid_housework_types_green());
        }

        public static function valid_housework_types_rot()
        {
            return array(
                'rot_CONSTRUCTION' => __("Rot - Construction", 'woo-fortnox-hub'),
                'rot_ELECTRICITY' => __("Rot - Electricity", 'woo-fortnox-hub'),
                'rot_GLASSMETALWORK' => __("Rot - Glass & metal work", 'woo-fortnox-hub'),
                'rot_GROUNDDRAINAGEWORK' => __("Rot - Grounddrainage work", 'woo-fortnox-hub'),
                'rot_MASONRY' => __("Rot - Masonry", 'woo-fortnox-hub'),
                'rot_PAINTINGWALLPAPERING' => __("Rot - Painting & Wallpapering", 'woo-fortnox-hub'),
                'rot_HVAC' => __("Rot - HVAC", 'woo-fortnox-hub'),
                'rot_OTHERCOSTS' => __("Rot - Other costs", 'woo-fortnox-hub'),
            );
        }

        public static function valid_housework_types_rut()
        {
            return array(
                'rut_HOMEMAINTENANCE' => __("Rut - Home mainrenance", 'woo-fortnox-hub'),
                'rut_FURNISHING' => __("Rut - Furnsishing", 'woo-fortnox-hub'),
                'rut_TRANSPORTATIONSERVICES' => __("Rut - Transportation services", 'woo-fortnox-hub'),
                'rut_WASHINGANDCAREOFCLOTHING' => __("Rut - Washing and care of chlothing", 'woo-fortnox-hub'),
                'rut_MAJORAPPLIANCEREPAIR' => __("Rut - Major appliance repair", 'woo-fortnox-hub'),
                'rut_MOVINGSERVICES' => __("Rut - Moving serivices", 'woo-fortnox-hub'),
                'rut_ITSERVICES' => __("Rut - IT services ", 'woo-fortnox-hub'),
                'rut_CLEANING' => __("Rut - Cleaning", 'woo-fortnox-hub'),
                'rut_TEXTILECLOTHING' => __("Rut - Textileclothing", 'woo-fortnox-hub'),
                'rut_SNOWPLOWING' => __("Rut - Snowplowing", 'woo-fortnox-hub'),
                'rut_GARDENING' => __("Rut - Gardening", 'woo-fortnox-hub'),
                'rut_BABYSITTING' => __("Rut - Baysitting", 'woo-fortnox-hub'),
                'rut_OTHERCARE' => __("Rut - Other care", 'woo-fortnox-hub'),
                'rut_OTHERCOSTS' => __("Rut - Other costs", 'woo-fortnox-hub'),
            );
        }

        public static function valid_housework_types_green()
        {
            return array(
                'green_SOLARCELLS' => __("Green - Solar cells", 'woo-fortnox-hub'),
                'green_STORAGESELFPRODUCEDELECTRICTY' => __("Green - Storage selfproduced electricity", 'woo-fortnox-hub'),
                'green_CHARGINGSTATIONELECTRICVEHICLE' => __("Green - Charging station electring vehicle", 'woo-fortnox-hub'),
                'green_OTHERCOSTS' => __("Green - Other costs", 'woo-fortnox-hub'),
            );
        }

        public static function get_invoice_email_information($order)
        {
            if ('yes' == get_option('fortnox_send_customer_email_invoice_' . self::get_payment_method($order, 'get_invoice_email_information'), get_option('fortnox_send_customer_email_invoice'))) {
                return array(
                    "EmailInformation" => array(
                        "EmailAddressFrom" => ($email_from = get_option('fornox_invoice_email_from')) ? $email_from : 'API_BLANK',
                        "EmailAddressTo" => ($billing_email = $order->get_billing_email()) ? $billing_email : 'API_BLANK',
                        "EmailSubject" => ($email_subject = get_option('fornox_invoice_email_subject')) ? $email_subject : 'API_BLANK',
                        "EmailBody" => ($email_body = get_option('fornox_invoice_email_body')) ? $email_body : 'API_BLANK',
                    ),
                );
            }

            return array();
        }

        public static function eu_number_is_validated($order)
        {
            if ('true' == $order->get_meta('_vat_number_is_validated', true, 'edit')) {
                return true;
            }

            if ('valid' == $order->get_meta('_vat_number_validated', true, 'edit')) {
                return true;
            }

            if (is_callable($order, 'is_order_eu_vat_exempt')) {
                return $order->is_order_eu_vat_exempt();
            }

            return false;
        }

        public static function get_payment_method($order, $function = '')
        {
            if ($order->get_type() == 'shop_order_refund'){
                return 'shop_refund';
            } else {
                return apply_filters('fortnox_get_order_payment_method', $order->get_payment_method(), $order, $function);
            }
        }

        public static function is_payment_gateway($payment_method){
            return array_key_exists($payment_method, self::get_available_payment_gateways());
        }

        public static function get_order_by_order_number($order_number)
        {

            if (!$order_number) {
                return null;
            }

            if (!has_filter('woocommerce_order_number')) {
                return wc_get_order($order_number);
            }

            $args = array(
                'posts_per_page' => -1,
                'post_type' => 'shop_order',
                'meta_key' => '_alg_wc_custom_order_number',
                'meta_value' => $order_number,
                'fields' => 'ids',
                'post_status' => wc_get_order_statuses(),
            );

            $query = new WP_Query($args);

            if ($query->have_posts()) {

                $posts = $query->get_posts();

                WC_FH()->logger->add(sprintf('get_order_by_order_number: Got "%s" as orders for order number %s', implode(',', $posts), $order_number));

                return wc_get_order(reset($posts));

            }

            return null;

        }
    }
}
