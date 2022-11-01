<?php

/**
 * This class handles syncing customers with Fortnox.
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Price_Stocklevel_Handler', false)) {

    class Woo_Fortnox_Hub_Price_Stocklevel_Handler
    {

        public function __construct()
        {

            add_action('init', array($this, 'schedule_wcfh_sync_fn_products'));

            add_action('wcfh_sync_fn_products_action', array($this, 'wcfh_sync_fn_products'), 10, 2);
            add_filter('wcfh_sync_fn_products_filter', array($this, 'wcfh_sync_fn_products'), 10, 2);
            add_action('wcfh_sync_fn_products_process_add', array($this, 'wcfh_sync_fn_products_process_add'), 10, 2);
            add_action('wcfh_sync_fn_products_process', array($this, 'wcfh_sync_fn_products_process'), 10, 2);
            add_action('wcfh_daily_sync_from_fortnox', array($this, 'daily_sync_from_fortnox'), 10, 2);

            add_filter('fortnox_get_sections', array($this, 'add_settings_section'), 60);
            add_filter('woocommerce_get_settings_fortnox_hub', array($this, 'get_settings'), 60, 2);
            add_filter('woocommerce_save_settings_fortnox_hub_updates_from_fortnox', array($this, 'save_settings_section'));
            add_action('woocommerce_settings_fortnox_price_stocklevel_options', array($this, 'show_sync_all_button'), 10);
        }

        public function schedule_wcfh_sync_fn_products()
        {

            if ('yes' == get_option('fortnox_sync_from_fortnox_automatically')) {
                if (false === as_next_scheduled_action('wcfh_sync_fn_products_action')) {
                    as_schedule_recurring_action(time(), MINUTE_IN_SECONDS, 'wcfh_sync_fn_products_action');
                }
            } else {
                if (false !== as_next_scheduled_action('wcfh_sync_fn_products_action')) {
                    as_unschedule_all_actions('wcfh_sync_fn_products_action');
                }
            }
        }

        /**
         * Add section for price and stocklevel settings
         */
        public function add_settings_section($sections)
        {
            if (!array_key_exists('updates_from_fortnox', $sections)) {
                $sections = array_merge($sections, array('updates_from_fortnox' => __('Products from Fortnox', 'woo-fortnox-hub')));
            }
            return $sections;
        }

        /**
         * Save settings, possibly do some action when done
         */
        public function save_settings_section($true)
        {
            if (isset($_POST['fortnox_sync_from_fortnox_automatically']) && rest_sanitize_boolean($_POST['fortnox_sync_from_fortnox_automatically']) && !wc_string_to_bool(get_option('fortnox_sync_from_fortnox_automatically'))) {
                $this_sync_time = date('Y-m-d H:i', current_time('timestamp', true) + get_option('fortnox_gmt_offset', 0));
                update_option('fortnox_last_sync_products', $this_sync_time, true);
                WC_FH()->logger->add(sprintf('save_settings_section: Setting product last sync time to %s', $this_sync_time));
            }
            return $true;
        }

        /**
         * Settings for price and stocklevel settings
         */
        public function get_settings($settings, $current_section)
        {
            if ('updates_from_fortnox' == $current_section) {

                $settings[] = [
                    'title' => __('Products from Fortnox', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '<div class=fortnox_infobox>' . __('In this section you can select if you want to update WooCommerce products if selected data changes on its linked article in Fortnox.</BR></BR>Click "Update all" to update all WooCommerce products with linked Fortnox articles. If you have many products the update will take some time.</BR></BR>In order to link a Fortnox article, set the SKU on a WooCommerce simple product or on a variant of a variable product.</BR></BR>If you change the settings on what to update you need to click "Update all" when you have saved your new settings.', 'woo-fortnox-hub') . '</div>',
                    'id' => 'fortnox_price_stocklevel_options',
                ];
                $settings[] = [
                    'title' => __('Update automatically', 'woo-fortnox-hub'),
                    'default' => '',
                    'type' => 'checkbox',
                    'desc' => __('Update WooCommerce automatically when selected data is changed in Fortnox. Set what to update below.', 'woo-fortnox-hub'),
                    'id' => 'fortnox_sync_from_fortnox_automatically',
                ];
                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_price_stocklevel_options',
                ];

                $settings[] = [
                    'title' => __('Stock handling', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_fn_stocklevel_update',
                ];

                $settings[] = [
                    'title' => __('Manage stock', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => __('Set the WooCommerce product to "Manage stock" if "Stock goods" is set on the article in Fortnox.', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'id' => 'fortnox_maybe_change_manage_stock',
                ];

                $settings[] = [
                    'title' => __('Stock level', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => __('Set the stock level on the product in WooCommerce to the same value as on the article in Fortnox.', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'id' => 'fortnox_process_stocklevel',
                ];

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_fn_stocklevel_update',
                ];

                $fortnox_pricelists = apply_filters('fortnox_get_pricelist', array());

                $pricelists = array(
                    '' => __('Do not update the price', 'woo-fortnox-hub'),
                );

                if (!empty($fortnox_pricelists)) {
                    foreach ($fortnox_pricelists['PriceLists'] as $fortnox_pricelist) {
                        $pricelists[$fortnox_pricelist['Code']] = $fortnox_pricelist['Description'];
                    }
                }

                $settings[] = [
                    'title' => __('Prices', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_fn_prices',
                ];

                if (WCFH_Util::prices_include_tax()) {
                    $settings[] = [
                        'title' => __('Remove VAT from WooCommerce prices', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Your WooCommerce system is configured to include taxes in product prices. Check this box to remove VAT from prices before updating Fortnox pricelists and to add VAT when importing prices to WooCommerce. Always backup your data before changing this setting. Contact BjornTech support if you are not sure of how to use this.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_remove_vat_from_prices',
                    ];
                }

                $settings[] = [
                    'title' => __('Zero price handling', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'desc' => __('Select how to handle a price-update from Fortnox where the price is zero.', 'woo-fortnox-hub'),
                    'options' => array(
                        '' => __('Do not update a price in WooCommerce if the value in Fortnox is zero', 'woo-fortnox-hub'),
                        'remove' => __('Remove the price in WooCommerce if the value in Fortnox is zero', 'woo-fortnox-hub'),
                        'always' => __('Always update WooCommerce with all values including zero', 'woo-fortnox-hub'),
                    ),
                    'id' => 'fortnox_handle_zero_price',
                ];

                $settings[] = [
                    'title' => __('Update purchase price', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => __('Update the purchase price on the WooCommerce Fortnox tab from Fortnox.', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'id' => 'fortnox_maybe_change_purchase_price',
                ];

                $settings[] = [
                    'title' => __('Regular Price', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'desc' => __('Select what pricelist in Fortnox to use when updating a WooCommerce product regular price.', 'woo-fortnox-hub'),
                    'options' => $pricelists,
                    'id' => 'fortnox_process_price',
                ];

                $settings[] = [
                    'title' => __('Sale Price', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'desc' => __('Select what pricelist in Fortnox to use when updating a WooCommerce product sale price.', 'woo-fortnox-hub'),
                    'options' => $pricelists,
                    'id' => 'fortnox_process_sale_price',
                ];

                if (function_exists('b2bking_run') || function_exists('b2bkinglite_run')) {

                    $groups = get_posts(array('post_type' => 'b2bking_group', 'post_status' => 'publish', 'numberposts' => -1));

                    foreach ($groups as $group) {

                        $settings[] = [
                            'title' => $group->post_title . ' ' . __('Regular Price', 'woo-fortnox-hub'),
                            'type' => 'select',
                            'desc' => __('Select if and what pricelist to update this B2BKing group regular price here.', 'woo-fortnox-hub'),
                            'default' => '',
                            'options' => $pricelists,
                            'id' => 'fortnox_process_price_b2bk_' . esc_attr($group->ID),
                        ];

                        $settings[] = [
                            'title' => $group->post_title . ' ' . __('Sale Price', 'woo-fortnox-hub'),
                            'type' => 'select',
                            'desc' => __('Select if and what pricelist to update this B2BKing group sale price here.', 'woo-fortnox-hub'),
                            'default' => '',
                            'options' => $pricelists,
                            'id' => 'fortnox_process_sale_price_b2bk_' . esc_attr($group->ID),
                        ];
                    }
                }

                if (class_exists('WC_Product_Price_Based_Country', false)) {

                    if ($zones = WCPBC_Pricing_Zones::get_zones()) {

                        foreach ($zones as $zone) {

                            $settings[] = [
                                'title' => __('Pricing zone', 'woo-izettle-integration') . ' ' . $zone->get_name(),
                                'type' => 'select',
                                'default' => '',
                                'desc' => sprintf(__('Select what pricelist in Fortnox to use when updating a WCPC pricing zone %s price.', 'woo-fortnox-hub'), $zone->get_name()),
                                'options' => $pricelists,
                                'id' => 'fortnox_process_wcpbc_' . $zone->get_zone_id(),
                            ];
                        }
                    }
                }

                if (class_exists('WooCommerceWholeSalePrices', false)) {

                    global $wc_wholesale_prices;

                    $wholesale_roles = $wc_wholesale_prices->wwp_wholesale_roles->getAllRegisteredWholesaleRoles();

                    foreach ($wholesale_roles as $key => $wholesale_role) {

                        $settings[] = [
                            'title' => sprintf(__('%s price', 'woo-fortnox-hub'), $wholesale_role['roleName']),
                            'type' => 'select',
                            'desc' => sprintf(__('Select what pricelist in Fortnox to use when updating the price for role "%s"', 'woo-fortnox-hub'), $wholesale_role['roleName']),
                            'default' => '',
                            'options' => $pricelists,
                            'id' => 'fortnox_process_wholesale_price_' . $key,
                        ];
                    }
                }

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_fn_prices',
                ];

                $settings[] = [
                    'title' => __('Product data', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_fn_product_data_update',
                ];

                $settings[] = [
                    'title' => __('Product name', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => __('Update WooCommerce product name if the product name is changed in Fortnox and the WooCommerce product is of type Simple product', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'id' => 'fortnox_maybe_change_wc_product_name',
                ];
                $settings[] = [
                    'title' => __('Dimensions', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => __('Update dimensions in WooCommerce if changed in Fortnox.', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'id' => 'fortnox_maybe_change_product_dimensions',
                ];
                $settings[] = [
                    'title' => __('Manufacturer', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => __('Update manufacturer if changed in Fortnox.', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'id' => 'fortnox_maybe_change_manufacturer',
                ];
                $settings[] = [
                    'title' => __('Manufacturer article', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => __('Update Manufacturer article if changed in Fortnox.', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'id' => 'fortnox_maybe_change_manufacturer_article_number',
                ];
                $settings[] = [
                    'title' => __('Stock place', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => __('Update stock place if changed in Fortnox.', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'id' => 'fortnox_maybe_change_stock_place',
                ];
                $settings[] = [
                    'title' => __('Unit', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => __('Update unit if changed in Fortnox.', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'id' => 'fortnox_maybe_change_unit',
                ];
                $settings[] = [
                    'title' => __('Barcode', 'woo-fortnox-hub'),
                    'default' => '',
                    'desc' => __('Update barcode if changed in Fortnox.', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'id' => 'fortnox_maybe_change_barcode',
                ];
                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_fn_product_data_update',
                ];
            }
            return $settings;
        }

        public function show_sync_all_button()
        {
            WCFH_Util::display_sync_button('wcfh_sync_fn_products');
        }

        /**
         * Add a product id to the syncing queue
         */
        public function wcfh_sync_fn_products_process_add($article_number, $sync_all = false)
        {
            as_schedule_single_action(as_get_datetime_object(), 'wcfh_sync_fn_products_process', array(
                $article_number,
                $sync_all,
            ), $sync_all ? 'wcfh_sync_fn_products' : '');
        }

        public function daily_sync_from_fortnox()
        {
            $synced = $this->wcfh_sync_fn_products(0, true);
        }

        public function wcfh_sync_fn_products($total_synced = 0, $sync_all = false)
        {

            if (false === WC_FH()->do_not_sync) {

                $create_simple_products = 'yes' == get_option('fortnox_create_simple_product_from_article');

                if ($sync_all && !$create_simple_products) {

                    $args = array(
                        'limit' => -1,
                        'return' => 'objects',
                        'type' => array_keys(WCFH_Util::get_product_types()),
                    );

                    $products = wc_get_products($args);

                    $total_synced = 0;

                    if (count($products) > 0) {

                        foreach ($products as $product) {

                            if ('variable' == $product->get_type()) {

                                $variations = WCFH_Util::get_all_variations($product);

                                foreach ($variations as $variation) {

                                    if (!is_object($variation)) {
                                        $variation = wc_get_product($variation['variation_id']);
                                    }

                                    if ($sku = $variation->get_sku('edit')) {
                                        do_action('wcfh_sync_fn_products_process_add', $sku, $sync_all);
                                        $total_synced++;
                                    }
                                }
                            } else {

                                if ($sku = $product->get_sku('edit')) {
                                    do_action('wcfh_sync_fn_products_process_add', $sku, $sync_all);
                                    $total_synced++;
                                }
                            }
                        }

                        WC_FH()->logger->add(sprintf('wcfh_sync_fn_products: Added %s Fortnox article(s) to process queue by sync all', $total_synced));
                    }
                } else {

                    try {

                        $last_synced = ($create_simple_products && $sync_all) ? false : get_option('fortnox_last_sync_products', false);
                        $this_sync_time = date('Y-m-d H:i', current_time('timestamp', true) + get_option('fortnox_gmt_offset', 0));
                        $articles = WC_FH()->fortnox->get_all_articles(true, $last_synced);
                        update_option('fortnox_last_sync_products', $this_sync_time, true);

                        if (($total_synced = count($articles)) > 0) {

                            $type = ($sync_all && $create_simple_products) ? 'sync all' : 'incremental';
                            WC_FH()->logger->add(sprintf('Added %s Fortnox article(s) to process queue by %s', $total_synced, $type));

                            foreach ($articles as $key => $article) {

                                do_action('wcfh_sync_fn_products_process_add', $article['ArticleNumber'], $sync_all);
                            }
                        }
                    } catch (Throwable $t) {
                        if (method_exists($t, 'write_to_logs')) {
                            $t->write_to_logs();
                        } else {
                            WC_FH()->logger->add(print_r($t, true));
                        }
                    }
                }
            }

            return $total_synced;
        }

        public function wcfh_sync_fn_products_process($article_number, $sync_all = false)
        {

            if (!$article_number) {
                return;
            }

            $article = WC_FH()->fortnox->get_article($article_number);

            $product_id = WCFH_Util::get_product_id_from_article_number($article_number);

            if (!apply_filters('fortnox_update_woocommerce_product_from_article', true, $product_id, $article, $sync_all)) {
                return;
            }

            if ('yes' == get_option('fortnox_update_webshop_articles_only') && !rest_sanitize_boolean($article['WebshopArticle'])) {
                WC_FH()->logger->add(sprintf('wcfh_sync_fn_products_process: Fortnox article %s is not set as webshop article', $article_number));
                return;
            }

            do_action('fortnox_remove_product_update_actions');

            try {

                if (!$product_id) {

                    if ('yes' == get_option('fortnox_create_simple_product_from_article')) {

                        $product = new WC_Product_Simple();
                        WCFH_Util::set_fortnox_article_number($product, $article_number);

                        $initial_category = get_option('fortnox_create_simple_product_from_article_category');
                        if ($initial_category) {
                            $product->set_category_ids(array($initial_category));
                        }

                        $initial_status = get_option('fortnox_create_simple_product_from_article_status');
                        if ($initial_status) {
                            $product->set_status($initial_status);
                        }

                        $product->set_name($article['Description']);

                        $product->save();
                        $product_id = $product->get_id();

                        WC_FH()->logger->add(sprintf('wcfh_sync_fn_products_process (%s): Added simple WooCommerce product with status "%s" and category "%s" from %s', $product_id, $initial_status, $initial_category, $article_number));
                    } else {

                        WC_FH()->logger->add(sprintf('wcfh_sync_fn_products_process: Fortnox article %s not found in WooCommerce', $article_number));
                        return;
                    }
                } else {

                    $product = wc_get_product($product_id);
                }

                $product_type = $product->get_type('edit');

                if (in_array($product_type, array('grouped', 'external', 'variable'))) {
                    WC_FH()->logger->add(sprintf('wcfh_sync_fn_products_process (%s): Do not process %s products', $product_id, $product_type));
                    return;
                }

                WC_FH()->logger->add(sprintf('wcfh_sync_fn_products_process (%s): Starting to process Fortnox article %s to a %s product', $product_id, $article_number, $product_type));

                $changed = false;

                if ($pricelist = get_option('fortnox_process_price')) {
                    $changed = $this->process_price('regular', $changed, $product, $article, $pricelist);
                }

                if ($pricelist = get_option('fortnox_process_sale_price')) {
                    $changed = $this->process_price('sale', $changed, $product, $article, $pricelist);
                }

                if (function_exists('b2bking_run') || function_exists('b2bkinglite_run')) {

                    $groups = get_posts(array('post_type' => 'b2bking_group', 'post_status' => 'publish', 'numberposts' => -1));

                    foreach ($groups as $group) {
                        if ($pricelist = get_option('fortnox_process_price_b2bk_' . esc_attr($group->ID))) {
                            $changed = $this->process_b2bk_price('regular', $changed, $product, $article, $pricelist, $group->ID);
                        }

                        if ($pricelist = get_option('fortnox_process_sale_price_b2bk_' . esc_attr($group->ID))) {
                            $changed = $this->process_b2bk_price('sale', $changed, $product, $article, $pricelist, $group->ID);
                        }
                    }
                }

                if (class_exists('WooCommerceWholeSalePrices', false)) {

                    global $wc_wholesale_prices;

                    $wholesale_roles = $wc_wholesale_prices->wwp_wholesale_roles->getAllRegisteredWholesaleRoles();

                    foreach ($wholesale_roles as $key => $wholesale_role) {
                        if ($pricelist = get_option('fortnox_process_wholesale_price_' . $key)) {
                            $changed = $this->process_wholesale_price($changed, $product, $article, $pricelist, $key);
                        }
                    }
                }

                if (class_exists('WC_Product_Price_Based_Country', false)) {

                    if ($zones = WCPBC_Pricing_Zones::get_zones()) {

                        foreach ($zones as $zone) {
                            $zone_id = $zone->get_zone_id();
                            if ($pricelist = get_option('fortnox_process_wcpbc_' . $zone_id)) {
                                $changed = $this->process_price_based_country($changed, $product, $article, $pricelist, $zone_id);
                            }
                        }
                    }
                }

                if ('yes' == get_option('fortnox_maybe_change_manage_stock')) {
                    $changed = $this->process_manage_stock($changed, $product, $article);
                }

                if ('yes' == get_option('fortnox_process_stocklevel')) {
                    $changed = $this->process_stocklevel($changed, $product, $article);
                }

                if ('yes' == get_option('fortnox_maybe_change_wc_product_name')) {
                    $changed = $this->maybe_change_wc_product_name($changed, $product, $article);
                }

                if ('yes' == get_option('fortnox_maybe_change_product_dimensions')) {
                    $changed = $this->maybe_change_product_dimensions($changed, $product, $article);
                }

                if ('yes' == get_option('fortnox_maybe_change_manufacturer')) {
                    $changed = $this->maybe_change_product_meta($changed, $product, $article, 'Manufacturer');
                }

                if ('yes' == get_option('fortnox_maybe_change_manufacturer_article_number')) {
                    $changed = $this->maybe_change_product_meta($changed, $product, $article, 'ManufacturerArticleNumber');
                }

                if ('yes' == get_option('fortnox_maybe_change_stock_place')) {
                    $changed = $this->maybe_change_product_meta($changed, $product, $article, 'StockPlace');
                }

                if ('yes' == get_option('fortnox_maybe_change_unit')) {
                    $units = WC_FH()->fortnox->get_units();
                    foreach ($units as $unit) {
                        if ($unit['Code'] == $article['Unit']) {
                            $changed = $this->maybe_change_product_meta($changed, $product, $article, 'Unit', $unit['Description']);
                        }
                    }
                }

                if ('yes' == get_option('fortnox_maybe_change_barcode')) {
                    $changed = $this->maybe_change_product_meta($changed, $product, $article, 'EAN');
                }

                if ('yes' == get_option('fortnox_maybe_change_purchase_price')) {
                    $changed = $this->maybe_change_product_meta_price($changed, $product, $article, '_fortnox_purchase_price', 'PurchasePrice');
                }

                if ($changed) {
                    $product->save();
                    WC_FH()->logger->add(sprintf('wcfh_sync_fn_products_process (%s): Processing of Fortnox article %s saved data in WooCommerce', $product_id, $article_number));
                } else {
                    WC_FH()->logger->add(sprintf('wcfh_sync_fn_products_process (%s): Processing of Fortnox article %s did not result in any changes', $product_id, $article_number));
                }
            } catch (Throwable $t) {
                if (method_exists($t, 'write_to_logs')) {
                    $t->write_to_logs();
                } else {
                    WC_FH()->logger->add(print_r($t, true));
                }
            }

            do_action('fortnox_add_product_update_actions');
        }

        public function process_b2bk_price($type, $changed, &$product, $article, $pricelist, $group_id)
        {

            try {

                $product_id = $product->get_id();

                $current_price = get_post_meta($product_id, 'b2bking_' . $type . '_product_price_group_' . $group_id, true);

                $prices = WC_FH()->fortnox->get_prices($article['ArticleNumber'], $pricelist);

                if ('yes' == get_option('fortnox_remove_vat_from_prices') && WCFH_Util::prices_include_tax()) {
                    $new_price = WCFH_Util::maybe_add_vat($prices['Price'], $product);
                } else {
                    $new_price = $prices['Price'];
                }

                if ($current_price != $new_price) {

                    $handle_zero_price = get_option('fortnox_handle_zero_price');

                    if (0 != $new_price || 'always' == $handle_zero_price) {

                        update_post_meta($product_id, 'b2bking_' . $type . '_product_price_group_' . $group_id, $new_price);
                        $changed = true;
                        WC_FH()->logger->add(sprintf('process_b2bk_price: Changed %s price for Fortnox article %s using pricelist %s from %s to %s for b2bk group %s', $type, $article['ArticleNumber'], $pricelist, $current_price, $new_price, $group_id));
                    } elseif (('remove' == $handle_zero_price) && $current_price) {

                        delete_post_meta($product_id, 'b2bking_' . $type . '_product_price_group_' . $group_id);
                        $changed = true;
                        WC_FH()->logger->add(sprintf('process_b2bk_price: Fortnox article %s price was 0, removing price from product %s', $article['ArticleNumber'], $product_id));
                    } elseif ($current_price) {

                        WC_FH()->logger->add(sprintf('process_b2bk_price (%s): Fortnox article %s price was 0, not overwriting WooCommerce price', $product_id, $article['ArticleNumber']));
                    }
                }
            } catch (Fortnox_API_Exception $e) {

                if (404 != $e->getCode()) {
                    throw new $e($e->getMessage(), $e->getCode(), $e);
                }

                WC_FH()->logger->add(sprintf('process_b2bk_price: No %s price set from Fortnox article %s in pricelist %s, not updating b2bk group  %s', $type, $article['ArticleNumber'], $pricelist, $group_id));
            }

            return $changed;
        }

        public function process_price($type, $changed, &$product, $article, $pricelist)
        {

            try {

                $product_id = $product->get_id();

                $current_price = 'regular' == $type ? $product->get_regular_price('edit') : $product->get_sale_price('edit');

                $prices = WC_FH()->fortnox->get_prices($article['ArticleNumber'], $pricelist);

                if ('yes' == get_option('fortnox_remove_vat_from_prices') && WCFH_Util::prices_include_tax()) {
                    $new_price = WCFH_Util::maybe_add_vat($prices['Price'], $product);
                } else {
                    $new_price = $prices['Price'];
                }

                if ($current_price != $new_price) {

                    $handle_zero_price = get_option('fortnox_handle_zero_price');

                    if (0 != $new_price || 'always' == $handle_zero_price) {

                        if ('regular' == $type) {
                            $product->set_regular_price($new_price);
                        } else {
                            $product->set_sale_price($new_price);
                        }
                        $changed = true;
                        WC_FH()->logger->add(sprintf('process_price (%s): Changed %s price from Fortnox article %s using pricelist %s from %s to %s', $product_id, $type, $article['ArticleNumber'], $pricelist, $current_price, $new_price));
                    } elseif (('remove' == $handle_zero_price) && $current_price) {

                        WC_FH()->logger->add(sprintf('process_price (%s): Fortnox article %s price was 0, removed price from product', $product_id, $article['ArticleNumber']));
                        if ('regular' == $type) {
                            $product->set_regular_price('');
                        } else {
                            $product->set_sale_price('');
                        }
                        $changed = true;
                    } elseif ($current_price) {

                        WC_FH()->logger->add(sprintf('process_price (%s): Fortnox article %s price was 0, not updating WooCommerce product', $product_id, $article['ArticleNumber']));
                    }
                }
            } catch (Fortnox_API_Exception $e) {

                if (404 != $e->getCode()) {
                    throw new $e($e->getMessage(), $e->getCode(), $e);
                }

                WC_FH()->logger->add(sprintf('process_price: No %s price set for Fortnox article %s in pricelist %s, not overwriting %s in WooCommerce', $type, $article['ArticleNumber'], $pricelist, $current_price));
            }

            return $changed;
        }

        public function process_wholesale_price($changed, &$product, $article, $pricelist, $key)
        {

            try {

                $type = $key . '_wholesale_price';

                $product_id = $product->get_id();

                $prices = WC_FH()->fortnox->get_prices($article['ArticleNumber'], $pricelist);

                if ('yes' == get_option('fortnox_remove_vat_from_prices') && WCFH_Util::prices_include_tax()) {
                    $new_price = WCFH_Util::maybe_add_vat($prices['Price'], $product);
                } else {
                    $new_price = $prices['Price'];
                }

                $current_price = $product->get_meta($key . '_wholesale_price', true);

                if (('yes' != $product->get_meta($key . '_have_wholesale_price', true)) || ($current_price != $new_price)) {

                    $handle_zero_price = get_option('fortnox_handle_zero_price');

                    if (0 != $new_price || 'always' == $handle_zero_price) {

                        $product->update_meta_data($key . '_wholesale_price', $new_price);
                        $product->update_meta_data($key . '_have_wholesale_price', 'yes');
                        $changed = true;
                        WC_FH()->logger->add(sprintf('process_wholesale_price (%s): Changed "%s" price from Fortnox article %s using pricelist %s from %s to %s', $product_id, $type, $article['ArticleNumber'], $pricelist, $current_price, $new_price));
                    } elseif (('remove' == $handle_zero_price) && $current_price) {

                        WC_FH()->logger->add(sprintf('process_wholesale_price (%s): Fortnox article %s price was 0, removed "%s" price from product', $product_id, $article['ArticleNumber'], $type));
                        $product->delete_meta_data($key . '_wholesale_price');
                        $product->delete_meta_data($key . '_have_wholesale_price');
                        $changed = true;
                    } elseif ($current_price) {

                        WC_FH()->logger->add(sprintf('process_wholesale_price (%s): Fortnox article %s price was 0, not overwriting "%s" price %s in WooCommerce', $product_id, $article['ArticleNumber'], $type, $current_price));
                    }
                }
            } catch (Fortnox_API_Exception $e) {

                if (404 != $e->getCode()) {
                    throw new $e($e->getMessage(), $e->getCode(), $e);
                }

                WC_FH()->logger->add(sprintf('process_wholesale_price (%s): No %s price set for Fortnox article %s in pricelist %s, not overwriting %s in WooCommerce', $product_id, $type, $article['ArticleNumber'], $pricelist, $current_price));
            }

            return $changed;
        }

        public function process_price_based_country($changed, &$product, $article, $pricelist, $zone_id)
        {

            try {

                $pricing_zone = WCPBC_Pricing_Zones::get_zone_by_id($zone_id);

                $pricing_zone_name = $pricing_zone->get_name();

                $product_id = $product->get_id();

                $current_price = $pricing_zone->get_post_price($product_id, '_regular_price');

                $prices = WC_FH()->fortnox->get_prices($article['ArticleNumber'], $pricelist);

                if ('yes' == get_option('fortnox_remove_vat_from_prices') && WCFH_Util::prices_include_tax()) {
                    $new_price = WCFH_Util::maybe_add_vat($prices['Price'], $product);
                } else {
                    $new_price = $prices['Price'];
                }

                if ($current_price != $new_price) {

                    $handle_zero_price = get_option('fortnox_handle_zero_price');

                    $postmetakey = $pricing_zone->get_postmetakey('_regular_price');

                    if (0 != $new_price || 'always' == $handle_zero_price) {

                        $pricing_zone->set_manual_price($product_id);
                        $product->update_meta_data($postmetakey, $new_price);
                        $changed = true;
                        WC_FH()->logger->add(sprintf('process_price_based_country (%s): Changed price on wcpbc pricing zone %s from Fortnox article %s using pricelist %s from %s to %s ', $product_id, $pricing_zone_name, $article['ArticleNumber'], $pricelist, $current_price, $new_price));
                    } elseif (('remove' == $handle_zero_price) && $current_price) {

                        WC_FH()->logger->add(sprintf('process_price_based_country (%s): Fortnox article %s price was 0, removed price from wcpbc pricing zone %s ', $product_id, $article['ArticleNumber'], $pricing_zone_name));
                        $pricing_zone->set_exchange_rate_price($product_id);
                        $product->delete_meta_data($postmetakey);
                        $changed = true;
                    } elseif ($current_price) {

                        WC_FH()->logger->add(sprintf('process_price_based_country (%s): Fortnox article %s price was 0, not overwriting price in wcpbc pricing zone %s', $product_id, $article['ArticleNumber'], $current_price, $pricing_zone_name));
                    }
                }
            } catch (Fortnox_API_Exception $e) {

                if (404 != $e->getCode()) {
                    throw new $e($e->getMessage(), $e->getCode(), $e);
                }

                WC_FH()->logger->add(sprintf('process_price_based_country (%s): No price set for Fortnox article %s in pricelist %s, not overwriting %s in WooCommerce', $product_id, $article['ArticleNumber'], $pricelist, $current_price));
            }

            return $changed;
        }

        public function process_stocklevel($changed, &$product, $article)
        {

            if (!array_key_exists('StockGoods', $article) || !array_key_exists('DisposableQuantity', $article)) {
                return $changed;
            }

            $stock_goods = rest_sanitize_boolean($article['StockGoods']);

            if (false === $stock_goods) {
                return $changed;
            }

            $product_id = $product->get_id();
            $current_quantity = $product->get_stock_quantity();
            $disposable_quantity = $article['DisposableQuantity'];

            if ($current_quantity != $disposable_quantity) {
                $new_stocklevel = wc_update_product_stock($product, $disposable_quantity, 'set', true);
                $this->trigger_stock_change_notifications($product, $new_stocklevel);
                $changed = true;
                WC_FH()->logger->add(sprintf('process_stocklevel: Changed stock level on WooCommerce product %s from %s to %s', $product_id, $current_quantity, $new_stocklevel));
            }

            if ($disposable_quantity > 0) {
                $backorder_option = get_option('fortnox_backorder_option_instock');
                if ($backorder_option && $product->get_backorders() != $backorder_option) {
                    $product->set_backorders($backorder_option);
                    $changed = true;
                    WC_FH()->logger->add(sprintf('process_stocklevel: WooCommerce product %s set instock backorder option to %s', $product_id, $backorder_option));
                }
            } else {
                $backorder_option = get_option('fortnox_backorder_option_outofstock');
                if ($backorder_option && $product->get_backorders() != $backorder_option) {
                    $product->set_backorders($backorder_option);
                    $changed = true;
                    WC_FH()->logger->add(sprintf('process_stocklevel: WooCommerce product %s set outofstock backorder option to %s', $product_id, $backorder_option));
                }
            }

            return $changed;

        }

        public function process_manage_stock($changed, &$product, $article)
        {

            if (!array_key_exists('StockGoods', $article)) {
                return $changed;
            }

            $product_id = $product->get_id();
            $manage_stock = $product->get_manage_stock('view');
            $stock_goods = rest_sanitize_boolean($article['StockGoods']);

            if ('parent' !== $manage_stock && $stock_goods !== $manage_stock) {
                $product->set_manage_stock($stock_goods);
                WC_FH()->logger->add(sprintf('process_manage_stock: Fortnox article %s has StockGoods set to "%s" changing product %s to match it', $article['ArticleNumber'], $stock_goods ? 'true' : 'false', $product_id));
                $changed = true;
            }

            return $changed;

        }

        public function maybe_change_wc_product_name($changed, &$product, $article)
        {

            if ($product->is_type('simple')) {

                $product_id = $product->get_id();

                if (isset($article['Description']) && (($product_name = $product->get_name()) != $article['Description'])) {
                    $product->set_name($article['Description']);
                    $changed = true;
                    WC_FH()->logger->add(sprintf('maybe_change_wc_product_name (%s): Changed name from %s to %s', $product_id, $product_name, $article['Description']));
                }
            }
            return $changed;
        }

        public function maybe_change_product_dimensions($changed, &$product, $article)
        {

            $product_id = $product->get_id();

            $weight_unit = get_option('woocommerce_weight_unit', 'kg');

            if (($current = $product->get_weight('edit')) && !isset($article['Weight'])) {
                $product->set_weight('');
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_dimensions (%s): Cleared weight from %s ', $product_id, $current));
            } elseif (($weight = WCFH_Util::weight_from_grams($article['Weight'], $weight_unit)) != $current) {
                $product->set_weight($weight);
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_dimensions (%s): Changed weight from %s to %s based on Fortnox %s', $product_id, $current, $weight, $article['Weight']));
            }

            $dimension_unit = get_option('woocommerce_dimension_unit', 'cm');

            if (($current = $product->get_length('edit')) && !isset($article['Depth'])) {
                $product->set_length('');
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_dimensions (%s): Cleared length from %s', $product_id, $current));
            } elseif (($length = WCFH_Util::dimension_from_millimeters($article['Depth'], $dimension_unit)) != $current) {
                $product->set_length($length);
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_dimensions (%s): Changed length from %s to %s based on Fortnox %s', $product_id, $current, $length, $article['Depth']));
            }

            if (($current = $product->get_width('edit')) && !isset($article['Width'])) {
                $product->set_width('');
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_dimensions (%s): Cleared width from %s', $product_id, $current));
            } elseif (($width = WCFH_Util::dimension_from_millimeters($article['Width'], $dimension_unit)) != $current) {
                $product->set_width($width);
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_dimensions (%s): Changed width from %s to %s based on Fortnox %s', $product_id, $current, $width, $article['Width']));
            }

            if (($current = $product->get_height('edit')) && !isset($article['Height'])) {
                $product->set_height('');
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_dimensions (%s): Cleared height from %s', $product_id, $current));
            } elseif (($height = WCFH_Util::dimension_from_millimeters($article['Height'], $dimension_unit)) != $current) {
                $product->set_height($height);
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_dimensions (%s): Changed height from %s to %s based on Fortnox %s', $product_id, $current, $height, $article['Height']));
            }

            return $changed;
        }

        public function maybe_change_product_meta($changed, &$product, $article, $fortnox_key, $value = false)
        {

            $value = $value ? $value : $article[$fortnox_key];
            if (($current = WCFH_Util::get_metadata($product, $fortnox_key)) && (!array_key_exists($fortnox_key, $article) || empty($article[$fortnox_key]))) {
                WCFH_Util::delete_metadata($product, $fortnox_key);
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_meta (%s): Cleared %s from "%s"', $product->get_id(), $fortnox_key, $current));
            } elseif (array_key_exists($fortnox_key, $article) && !empty($article[$fortnox_key]) && $value != $current) {
                WCFH_Util::update_metadata($product, $fortnox_key, $value);
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_meta (%s): Changed %s from "%s" to "%s"', $product->get_id(), $fortnox_key, $current, $value));
            }

            return $changed;
        }

        public function maybe_change_product_meta_price($changed, &$product, $article, $meta_key, $fortnox_key)
        {

            $current_price = apply_filters('fortnox_purchase_price', $product->get_meta($meta_key, true, 'edit'));

            if (array_key_exists($fortnox_key, $article) && !empty($article[$fortnox_key])) {

                if ('yes' == get_option('fortnox_remove_vat_from_prices') && WCFH_Util::prices_include_tax()) {
                    $new_price = WCFH_Util::maybe_add_vat($article[$fortnox_key], $product);
                } else {
                    $new_price = $article[$fortnox_key];
                }

                if ($new_price != $current_price) {
                    $product->update_meta_data($meta_key, $new_price);
                    $changed = true;
                    WC_FH()->logger->add(sprintf('maybe_change_product_meta_price (%s): Changed %s from "%s" to "%s"', $product->get_id(), $fortnox_key, $current_price, $new_price));
                }
            } elseif ($current_price) {
                $product->delete_meta_data($meta_key);
                $changed = true;
                WC_FH()->logger->add(sprintf('maybe_change_product_meta_price (%s): Cleared %s from "%s"', $product->get_id(), $fortnox_key, $current));
            }

            return $changed;
        }

        /**
         * After stock change events, triggers emails and adds order notes.
         *
         * @since 3.5.0
         * @param WC_Order $order order object.
         * @param array    $changes Array of changes.
         */
        public function trigger_stock_change_notifications($product, $change_to)
        {

            $no_stock_amount = absint(get_option('woocommerce_notify_no_stock_amount', 0));
            $low_stock_amount = absint(wc_get_low_stock_amount($product));

            if ($change_to <= $no_stock_amount) {
                do_action('woocommerce_no_stock', $product);
            } elseif ($change_to <= $low_stock_amount) {
                do_action('woocommerce_low_stock', $product);
            }

        }

    }

    new Woo_Fortnox_Hub_Price_Stocklevel_Handler();
}
