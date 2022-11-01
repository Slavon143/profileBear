<?php

/**
 * This class contains function to handle accounts for Fortnox
 *
 * @package   Woo_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Account_Handler', false)) {

    class Woo_Fortnox_Hub_Account_Handler
    {

        private $id = 'accounts';

        public function __construct()
        {
            add_filter('fortnox_get_sections', array($this, 'add_settings_section'), 100);
            add_action('fortnox_save_settings_' . $this->id, array($this, 'save_settings'), 100);
            add_filter('woocommerce_get_settings_fortnox_hub', array($this, 'get_settings'), 100, 2);
            add_filter('woocommerce_save_settings_fortnox_hub_' . $this->id, array($this, 'save_settings_section'));
        }

        public function add_settings_section($sections)
        {
            if (!array_key_exists($this->id, $sections)) {
                $sections = array_merge($sections, array($this->id => __('Accounts', 'woo-fortnox-hub')));
            }
            return $sections;
        }

        public function save_settings()
        {

        }

        public function save_settings_section($true)
        {
            return $true;
        }

        public function get_settings($settings, $current_section)
        {

            if ($this->id === $current_section) {

                $account_selection = apply_filters('fortnox_get_account_selection', array());
                $sales_from_countries = get_option('fortnox_account_selling_countries', array());

                /**
                 * Sales Accounts setting
                 */

                $settings[] = [
                    'title' => __('Sales Accounts', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_sales_accounts',
                ];

                $settings[] = [
                    'title' => __('Sales to Sweden 25%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_25_vat',
                ];

                $settings[] = [
                    'title' => __('Sales to Sweden 12%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_12_vat',
                ];

                $settings[] = [
                    'title' => __('Sales to Sweden 6%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_6_vat',
                ];

                $settings[] = [
                    'title' => __('Sales to Sweden without tax', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_0_vat',
                ];

                $settings[] = [
                    'title' => __('Sales to EU (incl. VAT)', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_eu_incl_vat',
                ];

                $settings[] = [
                    'title' => __('Sales to EU (reversed VAT)', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_eu_excl_vat',
                ];

                $settings[] = [
                    'title' => __('Sales to rest of the world', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_world_vat',
                ];

                if (!empty($sales_from_countries)) {
                    $settings = array_merge($settings, $this->sales_from_country('', __('Sales', 'woo-fortnox-hub'), $account_selection, $sales_from_countries));
                }

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_sales_accounts',
                ];

                /**
                 * Sales Accounts setting (virtual products)
                 */

                $settings[] = [
                    'title' => __('Sales Accounts (virtual products)', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_sales_accounts_virtual',
                ];

                $settings[] = [
                    'title' => __('Sales to Sweden 25%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_25_vat_virtual',
                ];

                $settings[] = [
                    'title' => __('Sales to Sweden 12%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_12_vat_virtual',
                ];

                $settings[] = [
                    'title' => __('Sales to Sweden 6%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_6_vat_virtual',
                ];

                $settings[] = [
                    'title' => __('Sales to Sweden without tax', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_0_vat_virtual',
                ];

                $settings[] = [
                    'title' => __('Sales to EU (incl. VAT)', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_eu_incl_vat_virtual',
                ];

                $settings[] = [
                    'title' => __('Sales to EU (reversed VAT)', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_eu_excl_vat_virtual',
                ];

                $settings[] = [
                    'title' => __('Sales to rest of the world', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_world_vat_virtual',
                ];

                if (!empty($sales_from_countries)) {
                    $settings = array_merge($settings, $this->sales_from_country('_virtual', __('Sales', 'woo-fortnox-hub'), $account_selection, $sales_from_countries));
                }

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_sales_accounts_virtual',
                ];

                /**
                 * Shipping Accounts setting
                 */

                $settings[] = [
                    'title' => __('Shipping Accounts', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_shipping_accounts',
                ];

                $settings[] = [
                    'title' => __('Shipping to Sweden 25%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_25_shipping',
                ];

                $settings[] = [
                    'title' => __('Shipping to Sweden 12%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_12_shipping',
                ];

                $settings[] = [
                    'title' => __('Shipping to Sweden 6%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_6_shipping',
                ];

                $settings[] = [
                    'title' => __('Shipping to Sweden without tax', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_0_shipping',
                ];

                $settings[] = [
                    'title' => __('Shipping to EU (incl. VAT)', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_eu_incl_shipping',
                ];

                $settings[] = [
                    'title' => __('Shipping to EU (reversed VAT)', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_eu_excl_vat_shipping',
                ];

                $settings[] = [
                    'title' => __('Shipping to rest of the world', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_world_shipping',
                ];

                if (!empty($sales_from_countries)) {
                    $settings = array_merge($settings, $this->sales_from_country('_shipping', __('Shipping', 'woo-fortnox-hub'), $account_selection, $sales_from_countries));
                }

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_shipping_accounts',
                ];

                /**
                 * Fee Accounts setting
                 */

                $settings[] = [
                    'title' => __('Fee Accounts', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_fee_accounts',
                ];

                $settings[] = [
                    'title' => __('Fee to Sweden 25%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_25_vat_fee',
                ];

                $settings[] = [
                    'title' => __('Fee to Sweden 12%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_12_vat_fee',
                ];

                $settings[] = [
                    'title' => __('Fee to Sweden 6%', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_6_vat_fee',
                ];

                $settings[] = [
                    'title' => __('Fee to Sweden without tax', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_0_vat_fee',
                ];

                $settings[] = [
                    'title' => __('Fee to EU (incl. VAT)', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_eu_incl_vat_fee',
                ];

                $settings[] = [
                    'title' => __('Fee to EU (reversed VAT)', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_eu_excl_vat_fee',
                ];

                $settings[] = [
                    'title' => __('Fee to rest of the world', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_world_fee',
                ];

                if (!empty($sales_from_countries)) {
                    $settings = array_merge($settings, $this->sales_from_country('_fee', __('Fee', 'woo-fortnox-hub'), $account_selection, $sales_from_countries));
                }

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_fee_accounts',
                ];

                $settings[] = [
                    'title' => __('OSS Settings', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_oss_settings',
                ];

                $countries = WCFH_Util::get_countries();

                $settings[] = [
                    'title' => __('OSS Countries', 'woo-fortnox-hub'),
                    'type' => 'multiselect',
                    'class' => 'wc-enhanced-select',
                    'css' => 'width: 400px;',
                    'desc' => __('Select countries to report tax according to the OSS rules.', 'woo-fortnox-hub'),
                    'default' => array(),
                    'options' => $countries,
                    'id' => 'fortnox_account_selling_countries',
                ];

                $vat_from_country_options = array();
                if (!empty($sales_from_countries = get_option('fortnox_account_selling_countries', array()))) {
                    foreach ($sales_from_countries as $sales_from_country) {
                        $id = 'fortnox_account_selling_countries_' . strtolower($sales_from_country);
                        $settings[] = [
                            'title' => sprintf(__('VAT rates for %s', 'woo-fortnox-hub'), $countries[$sales_from_country]),
                            'desc_tip' => __('List tax rates you need for this country below (1 per line, e.g. 20). Do not use any percentage sign.', 'woo-fortnox-hub'),
                            'id' => $id,
                            'css' => 'height: 65px;',
                            'type' => 'textarea',
                            'default' => '',
                            'is_option' => false,
                            'value' => implode("\n", get_option($id, array())
                            ),
                        ];
                    }
                }

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_oss_settings',
                ];

                /**
                 * Purchase account
                 */

                $settings[] = [
                    'title' => __('Accounts on products', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_purchse_accounts',
                ];

                $settings[] = [
                    'title' => __('Purchase Account', 'woo-fortnox-hub'),
                    'desc' => __('Set the purchase account on products when updating in Fortnox.', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_se_purchase_account',
                ];

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_purchse_accounts',
                ];

            }
            return $settings;
        }

        public function sales_from_country($type, $name, $account_selection, $sales_from_countries)
        {

            $salesfrom_options = array();
            $countries = WCFH_Util::get_countries();
            foreach ($sales_from_countries as $country) {
                $id = 'fortnox_account_selling_countries_' . strtolower($country);
                $tax_rates = get_option($id, array());
                foreach ($tax_rates as $tax_rate) {

                    if ('0' === $tax_rate) {
                        $title = sprintf(__('%s from %s without tax', 'woo-fortnox-hub'), $name, $countries[$country]);
                    } else {
                        $title = sprintf(__('%s from %s %s%%', 'woo-fortnox-hub'), $name, $countries[$country], $tax_rate);
                    }

                    $salesfrom_options[] = array(
                        'title' => $title,
                        'type' => 'select',
                        'default' => '',
                        'options' => $account_selection,
                        'id' => WCFN_Accounts::format_oss_option($country, $tax_rate, $type),
                    );
                }
            }

            return $salesfrom_options;
        }

    }

    new Woo_Fortnox_Hub_Account_Handler();

    class WCFN_Accounts
    {

        /**
         * Get the tax rate for a item
         *
         * @param WC_Order_Item $item
         *
         * @return string Taxrate for the item or 0 if not found
         */
        public static function get_shop_tax_rate($item)
        {

            $tax_class = $item->get_tax_class('edit');

            $tax_rates = WC_Tax::get_base_tax_rates($tax_class);

            if (count($tax_rates)) {
                $tax_rate = reset($tax_rates);
                return $tax_rate['rate'];
            } else {
                return '0';
            }

            return $tax_rate;
        }

        public static function get_order_tax_rate($order, $item)
        {

            $tax_class = $item->get_tax_class('edit');

            if ('shop_order_refund' == $order->get_type()) {
                $order = wc_get_order($order->get_parent_id());
            }

            $args = array(
                'country' => $order->get_billing_country() ? $order->get_billing_country() : WC()->countries->get_base_country(),
                'state' => $order->get_billing_state() ? $order->get_billing_state() : WC()->countries->get_base_state(),
                'city' => $order->get_billing_city() ? $order->get_billing_city() : WC()->countries->get_base_city(),
                'postcode' => $order->get_billing_postcode() ? $order->get_billing_postcode() : WC()->countries->get_base_postcode(),
                'tax_class' => $tax_class,
            );

            $tax_rates = WC_Tax::find_rates($args);

            if (count($tax_rates)) {
                $tax_rate = reset($tax_rates);
                return $tax_rate['rate'];
            } else {
                return '0';
            }

            return $tax_rate;
        }

        /**
         * Get the billing country for order or paremt order (if refund)
         *
         * @param WC_Order|WC_Order_Refund $order Order to get billing country from
         *
         * @since 4.3.0
         *
         * @return string Billing country for order
         */
        public static function get_billing_country($order)
        {

            if ('shop_order_refund' == $order->get_type()) {

                $order = wc_get_order($order->get_parent_id());

            }

            return ($country = $order->get_billing_country('edit')) ? $country : 'SE';

        }

        /**
         * Get the account for items
         *
         * @param WC_Order $order
         * @param string $tax_rate
         *
         * @return string Account number to be used or blank if none found
         */
        public static function get_purchase_account($order, $item)
        {
            $sales_from_countries = get_option('fortnox_account_selling_countries', array());

            $country = self::get_billing_country($order);

            $is_european_country = WCFH_Util::is_european_country($country);

            $eu_number_is_validated = WCFH_Util::eu_number_is_validated($order);

            if (($wc_product = $item->get_product()) && $wc_product->get_virtual('edit')) {
                $type = '_virtual';
            } else {
                $type = '';
            }

            $tax_rate = self::get_order_tax_rate($order, $item);

            if (in_array($country, $sales_from_countries) && !($is_european_country && $eu_number_is_validated)) {

                $account = get_option(self::format_oss_option($country, $tax_rate, $type));
                $selected = 'oss';

            } elseif ($country == 'SE') {

                $account = get_option('fortnox_se_' . $tax_rate . '_vat' . $type);
                $selected = 'se';

            } elseif ($is_european_country) {

                if ($eu_number_is_validated) {
                    $account = get_option('fortnox_eu_excl_vat' . $type);
                    $selected = 'eu_reversed';
                } else {
                    $account = get_option('fortnox_eu_incl_vat' . $type);
                    $selected = 'eu_reversed';
                }

            } else {

                $account = get_option('fortnox_world_vat');
                $selected = 'world';

            }

            WC_FH()->logger->add(sprintf('get_purchase_account (%s): Sales with key {%s%s}_{%s}_{%s} got account "%s"', $order->get_id(), $selected, $type, $country, $tax_rate, $account));

            return $account;
        }

        /**
         * Get the account for fees
         *
         * @param WC_Order $order
         * @param string $tax_rate
         *
         * @return string Account number to be used or blank if none found
         */
        public static function get_fee_account($order, $item)
        {
            $sales_from_countries = get_option('fortnox_account_selling_countries', array());

            $country = self::get_billing_country($order);

            $tax_rate = self::get_order_tax_rate($order, $item);

            if (in_array($country, $sales_from_countries) && !(WCFH_Util::is_european_country($country) && WCFH_Util::eu_number_is_validated($order))) {

                $account = get_option(self::format_oss_option($country, $tax_rate, '_fee'));

            } elseif ($country == 'SE') {

                $account = get_option('fortnox_se_' . $tax_rate . '_vat_fee');

            } elseif (WCFH_Util::is_european_country($country)) {

                if (WCFH_Util::eu_number_is_validated($order)) {
                    $account = get_option('fortnox_eu_excl_vat_fee');
                } else {
                    $account = get_option('fortnox_eu_incl_vat_fee');
                }

            } else {

                $account = get_option('fortnox_world_fee');

            }

            WC_FH()->logger->add(sprintf('get_fee_account (%s): Get account "%s" for fees to %s with tax rate %s', $order->get_id(), $account, $country, $tax_rate));

            return $account;
        }

        /**
         * Get the account for shiping items
         *
         * @param WC_Order $order
         * @param string $tax_rate Tax rate to be used when finding the account
         *
         * @return string Account number to be used or blank if none found
         */
        public static function get_shipping_account($order, $tax_rate)
        {

            $sales_from_countries = get_option('fortnox_account_selling_countries', array());
            $country = self::get_billing_country($order);

            $tax_rate = ($trim = trim($tax_rate, '%')) ? $trim : '0';

            if (in_array($country, $sales_from_countries) && !(WCFH_Util::is_european_country($country) && WCFH_Util::eu_number_is_validated($order))) {

                $account = get_option(self::format_oss_option($country, $tax_rate, '_shipping'));

            } elseif ($country == 'SE') {

                $account = get_option('fortnox_se_' . $tax_rate . '_shipping');
                WC_FH()->logger->add(sprintf('Shipping tax rate %s is using account %s', $tax_rate, $account ? $account : 'default'));

            } elseif (WCFH_Util::is_european_country($country)) {

                if (WCFH_Util::eu_number_is_validated($order)) {
                    $account = get_option('fortnox_eu_excl_vat_shipping');
                    WC_FH()->logger->add(sprintf('Shipping for %s ex vat is using account %s', $country, $account ? $account : 'default'));
                } else {
                    $account = get_option('fortnox_eu_incl_shipping');
                    WC_FH()->logger->add(sprintf('Shipping for %s is using account %s', $country, $account ? $account : 'default'));
                }

            } else {

                $account = get_option('fortnox_world_shipping');
                WC_FH()->logger->add(sprintf('Shipping for world is using account %s', $account ? $account : 'default'));

            }

            return $account;

        }

        /**
         * Get the account for sales in Sweden on a product
         *
         * @param WC_Product $product
         *
         * @return string Account number to be used or blank if none found
         */
        public static function get_product_account($product)
        {

            $type = '';
            if ($product->get_virtual()) {
                $type = '_virtual';
            }

            $tax_rate = self::get_shop_tax_rate($product);

            $account = strval(get_option('fortnox_se_' . $tax_rate . '_vat' . $type));

            return $account;

        }

        public static function format_oss_option($country, $tax_rate, $type)
        {

            $country = strtolower($country);
            $tax_rate = str_replace(".", "_", $tax_rate);

            return 'fortnox_' . $country . '_' . $tax_rate . '_vat' . $type;

        }

    }

}
