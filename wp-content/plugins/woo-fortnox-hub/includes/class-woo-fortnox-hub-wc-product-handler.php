<?php

/**
 * This class handles syncing products to Fortnox.
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

require_once plugin_dir_path(self::PLUGIN_FILE) . 'includes/class-woo-fortnox-hub-product-admin.php';

if (!class_exists('Woo_Fortnox_Hub_WC_Product_Handler', false)) {

    class Woo_Fortnox_Hub_WC_Product_Handler
    {

        /**
         * Constructor for Woo_Fortnox_Hub_WC_Product_Handler
         *
         * @since 1.0.0
         */
        public function __construct()
        {

            add_action('wcfh_sync_wc_products_action', array($this, 'start_wc_product_sync'), 10, 2);
            add_filter('wcfh_sync_wc_products_filter', array($this, 'start_wc_product_sync'), 10, 2);
            add_action('wcfh_sync_wc_products_process', array($this, 'wcfh_sync_wc_products_process'), 10, 2);

            add_action('fortnox_push_product_to_queue', array($this, 'push_product_to_queue'), 10, 4);
            add_filter('fortnox_get_sections', array($this, 'add_settings_section'), 50);
            add_action('fortnox_sync_product_to_article', array($this, 'sync_product_to_article'), 10, 3);
            add_action('fortnox_sync_article_stocklevel', array($this, 'update_fortnox_stocklevel'));

            add_filter('woocommerce_get_settings_fortnox_hub', array($this, 'get_settings'), 50, 2);
            add_filter('woocommerce_save_settings_fortnox_hub_wc_products', array($this, 'save_settings_section'));
            add_action('woocommerce_settings_fortnox_wc_products_selection', array($this, 'show_start_sync_button'), 10);
            add_filter('woocommerce_duplicate_product_exclude_meta', array($this, 'duplicate_product_exclude_meta'));
            add_action('woocommerce_product_duplicate_before_save', array($this, 'product_duplicate_before_save'), 10, 2);

            /**
             * Actions to control product updates
             */
            add_action('fortnox_remove_product_update_actions', array($this, 'remove_product_update_actions'));
            add_action('fortnox_add_product_update_actions', array($this, 'add_product_update_actions'));

            /**
             * Use hooks if realtime updates and init if not
             */

            if ('yes' == get_option('fortnox_create_products_automatically')) {
                do_action('fortnox_add_product_update_actions');
                add_action('init', array($this, 'schedule_daily_sync_wc_products'));
            }

            add_action('wcfh_daily_sync_to_fortnox', array($this, 'daily_sync_to_fortnox'));
        }

        public function add_product_update_actions()
        {

            add_action('woocommerce_update_product', array($this, 'woocommerce_update_product'), 600, 2);
            add_action('woocommerce_update_product_variation', array($this, 'woocommerce_update_product_variation'), 600, 2);
            add_action('woocommerce_new_product', array($this, 'wc_product_was_created'), 600, 2);
        }

        public function remove_product_update_actions()
        {

            remove_action('woocommerce_update_product', array($this, 'woocommerce_update_product'), 600);
            remove_action('woocommerce_update_product_variation', array($this, 'woocommerce_update_product_variation'), 600);
            remove_action('woocommerce_new_product', array($this, 'wc_product_was_created'), 600);
        }

        /**
         * Add created WooCommerce product to queue
         *
         * @param $product_id
         * @param $product
         *
         * @since 4.0.0
         */
        public function wc_product_was_created($product_id, $product = false)
        {

            WC_FH()->logger->add(sprintf('wc_product_was_created (%s): WooCommerce product created', $product_id));
            do_action('fortnox_push_product_to_queue', $product_id);
        }

        /**
         * Add changed WooCommerce product variation to queue
         *
         * @param $product_id
         * @param $product
         *
         * @since 4.1.0
         */
        public function woocommerce_update_product_variation($product_id, $product = false)
        {

            WC_FH()->logger->add(sprintf('woocommerce_update_product_variation (%s): WooCommerce product variation updated', $product_id));
            do_action('fortnox_push_product_to_queue', $product_id);
        }

        /**
         * Add changed WooCommerce product to queue
         *
         * @param $product_id
         * @param $product
         *
         * @since 4.0.0
         */
        public function woocommerce_update_product($product_id, $product = false)
        {

            WC_FH()->logger->add(sprintf('get_article (%s): WooCommerce product updated', $product_id));
            do_action('fortnox_push_product_to_queue', $product_id);
        }

        /**
         * Setup action scheduler to sync all wc products every morning
         *
         * @since 4.0.0
         */
        public function schedule_daily_sync_wc_products()
        {

            if ('yes' == get_option('fortnox_sync_wc_products_daily')) {

                if (false === as_next_scheduled_action('wcfh_daily_sync_to_fortnox')) {
                    as_schedule_cron_action(strtotime('tomorrow 1 am'), '0 1 * * *', 'wcfh_daily_sync_to_fortnox');
                }
            } else {

                if (false !== as_next_scheduled_action('wcfh_daily_sync_to_fortnox')) {
                    as_unschedule_all_actions('wcfh_daily_sync_to_fortnox');
                }
            }
        }

        public function daily_sync_to_fortnox()
        {
            $synced = $this->start_wc_product_sync(0, true);
        }

        /**
         * Clear SKU when a product is duplicated
         *
         * @param $duplicate
         * @param $product
         *
         * @since 1.0.0
         */
        public function product_duplicate_before_save($duplicate, $product)
        {

            $duplicate->set_sku('');
        }

        public function duplicate_product_exclude_meta($meta_to_exclude)
        {

            array_push($meta_to_exclude, '_fortnox_article_number');
            return $meta_to_exclude;
        }

        /**
         * Add section for WC products settings
         */
        public function add_settings_section($sections)
        {
            if (!array_key_exists('wc_products', $sections)) {
                $sections = array_merge($sections, array('wc_products' => __('Products to Fortnox', 'woo-fortnox-hub')));
            }
            return $sections;
        }

        /**
         * Save settings, possibly do some action when done
         */
        public function save_settings_section($true)
        {
            return $true;
        }

        public function price_settings()
        {

            $fortnox_pricelists = apply_filters('fortnox_get_pricelist', array());
            $pricelists = array('' => __('Do not update price on the Fortnox article', 'woo-fortnox-hub'));
            if (!empty($fortnox_pricelists)) {
                foreach ($fortnox_pricelists['PriceLists'] as $fortnox_pricelist) {
                    $pricelists[$fortnox_pricelist['Code']] = $fortnox_pricelist['Description'];
                }
            };

            $settings[] = [
                'title' => __('Prices', 'woo-fortnox-hub'),
                'type' => 'title',
                'desc' => '',
                'id' => 'fortnox_wc_prices',
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
                'title' => __('Regular price', 'woo-fortnox-hub'),
                'type' => 'select',
                'desc' => __('Select if and what pricelist to uptate with the price from WooCommerce', 'woo-fortnox-hub'),
                'default' => '',
                'options' => $pricelists,
                'id' => 'fortnox_wc_product_pricelist',
            ];

            $settings[] = [

                'title' => __('Sale price', 'woo-fortnox-hub'),
                'type' => 'select',
                'desc' => __('Select if and what pricelist to uptate with the sale price from WooCommerce', 'woo-fortnox-hub'),
                'default' => '',
                'options' => $pricelists,
                'id' => 'fortnox_wc_product_sale_pricelist',
            ];

            $settings[] = [
                'title' => __('Purchase price', 'woo-fortnox-hub'),
                'type' => 'checkbox',
                'desc' => __('Update purchase price.', 'woo-fortnox-hub'),
                'default' => '',
                'id' => 'fortnox_wc_product_update_purchase_price',
            ];

            if (function_exists('b2bking_run') || function_exists('b2bkinglite_run')) {

                $groups = get_posts(array('post_type' => 'b2bking_group', 'post_status' => 'publish', 'numberposts' => -1));
                foreach ($groups as $group) {

                    $settings[] = [
                        'title' => $group->post_title . ' ' . __('Regular Price', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Select if and what pricelist to update from this B2BKing group regular price here.', 'woo-fortnox-hub'),
                        'default' => '',
                        'options' => $pricelists,
                        'id' => 'fortnox_wc_product_pricelist_b2bk_' . esc_attr($group->ID),
                    ];

                    $settings[] = [
                        'title' => $group->post_title . ' ' . __('Sale Price', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Select if and what pricelist to update from this B2BKing group sale price here.', 'woo-fortnox-hub'),
                        'default' => '',
                        'options' => $pricelists,
                        'id' => 'fortnox_wc_product_sale_pricelist_b2bk_' . esc_attr($group->ID),
                    ];
                }
            }

            if (class_exists('WooCommerceWholeSalePrices', false)) {

                global $wc_wholesale_prices;

                $wholesale_roles = $wc_wholesale_prices->wwp_wholesale_roles->getAllRegisteredWholesaleRoles();

                foreach ($wholesale_roles as $key => $wholesale_role) {

                    $settings[] = [
                        'title' => $wholesale_role['roleName'],
                        'type' => 'select',
                        'desc' => sprintf(__('Select if and what pricelist to update with the price for the role "%s"', 'woo-fortnox-hub'), $wholesale_role['roleName']),
                        'default' => '',
                        'options' => $pricelists,
                        'id' => 'fortnox_wc_product_pricelist_' . $key . '_wholesale_price',
                    ];
                }
            }

            $settings[] = [
                'type' => 'sectionend',
                'id' => 'fortnox_wc_prices',
            ];

            return $settings;
        }

        /**
         * Settings for WC products to Fortnox
         */
        public function get_settings($settings, $current_section)
        {
            if ('wc_products' == $current_section) {

                $category_options = WCFH_Util::get_category_options();
                $product_selection = WCFH_Util::get_product_types();

                if (class_exists('Learndash_WooCommerce', false)) {
                    $product_selection['course'] = __('Learndash Course', 'woo-fortnox-hub');
                }

                if (function_exists('woosb_init')) {
                    $product_selection['woosb'] = __('Smart bundle', 'woo-fortnox-hub');
                }

                if (function_exists('WC_PB')) {
                    $product_selection['bundle'] = __('WooCommerce Product Bundles', 'woo-fortnox-hub');
                }

                $settings = array(
                    array(
                        'title' => __('Products to Fortnox', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => '<div class=fortnox_infobox>' . __('In this section you can select if and how Fortnox articles should be created and updated from WooCommerce products.</BR></BR>Click "Update all" to create or update Fortnox articles from the selection of WooCommerce products.</BR></BR>It is possible to configure the update to if Fortnox should create article id:s or to use existing if existing in WooCommerce</BR></BR>In order to link a Fortnox article, set the article field on a WooCommerce simple product or on a variant of a variable product.</BR></BR>If you change the settings on what to update you need to click "Update all" when you have saved your new settings.', 'woo-fortnox-hub') . '</div>',

                        'id' => 'fortnox_wc_products_selection',
                    ),
                    array(
                        'title' => __('Update automatically', 'woo-fortnox-hub'),
                        'default' => '',
                        'type' => 'checkbox',
                        'desc' => __('Create or update WooCommerce products to Fortnox articles automatically', 'woo-fortnox-hub'),
                        'id' => 'fortnox_create_products_automatically',
                    ),
                    array(
                        'title' => __('Create articles', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Select if you want a Fortnox Article to be created if no match of a WooCommerce product is found in Fortnox', 'woo-fortnox-hub'),
                        'default' => '',
                        'options' => array(
                            '' => __('Create only if a SKU is set in WooCommerce', 'woo-fortnox-hub'),
                            'always_create' => __('Always create. If no SKU is set on the product. Fortnox will create a number.', 'woo-fortnox-hub'),
                            'never_create' => __('Never create. No products will be created in Fortnox by the plugin', 'woo-fortnox-hub'),
                        ),
                        'id' => 'fortnox_create_products_from_wc',
                    ),
                    array(
                        'title' => __('Include product type', 'woo-fortnox-hub'),
                        'type' => 'multiselect',
                        'class' => 'wc-enhanced-select',
                        'css' => 'width: 400px;',
                        'desc' => __('Select the type of product to be included in the product update', 'woo-fortnox-hub'),
                        'default' => array('simple', 'variable'),
                        'options' => $product_selection,
                        'custom_attributes' => array(
                            'data-placeholder' => __('Select product statuses to include in the product update.', 'woo-fortnox-hub'),
                        ),
                        'id' => 'fortnox_wc_products_include',
                    ),
                    array(
                        'title' => __('Include products with status', 'woo-fortnox-hub'),
                        'type' => 'multiselect',
                        'class' => 'wc-enhanced-select',
                        'css' => 'width: 400px;',
                        'default' => array('publish'),
                        'desc' => __('Select the product statuses to be included in the product update.', 'woo-fortnox-hub'),
                        'custom_attributes' => array(
                            'data-placeholder' => __('Select product statuses to include in the product update.', 'woo-fortnox-hub'),
                        ),
                        'options' => get_post_statuses(),
                        'id' => 'fortnox_wc_get_product_status',
                    ),
                    array(
                        'title' => __('Product categories to update', 'woo-fortnox-hub'),
                        'type' => 'multiselect',
                        'class' => 'wc-enhanced-select',
                        'css' => 'width: 400px;',
                        'id' => 'fortnox_wc_products_product_categories',
                        'default' => '',
                        'description' => __('If you only want to update products included in certain product categories, select them here. Leave blank to enable for all categories.', 'woo-fortnox-hub'),
                        'options' => $category_options,
                        'custom_attributes' => array(
                            'data-placeholder' => __('Select product categories or leave empty for all', 'woo-fortnox-hub'),
                        ),
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_wc_products_selection',
                    ),
                );
                $optional_data = array(
                    array(
                        'title' => __('Select optional data', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'fortnox_wc_products_data',
                    ),
                    array(
                        'title' => __('Update description', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Update the description field in Fortnox with the product name from WooCommerce', 'woo-fortnox-hub'),
                        'default' => 'yes',
                        'id' => 'fortnox_wc_product_name',
                    ),
                    array(
                        'title' => __('Update notes field', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Update the notes field in Fortnox with the short or long description field in WooCommerce', 'woo-fortnox-hub'),
                        'default' => '',
                        'options' => array(
                            '' => __('Do not update notes field', 'woo-fortnox-hub'),
                            'short_description' => __('WooCommerce short description', 'woo-fortnox-hub'),
                            'description' => __('WooCommerce description', 'woo-fortnox-hub'),
                        ),
                        'id' => 'fortnox_wc_product_description',
                    ),
                    array(
                        'title' => __('Update Manufacturer', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Update manufacturer name in Fortnox.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_wc_product_update_manufacturer',
                    ),
                    array(
                        'title' => __('Manufacturer article', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Update the Manufacturer article field', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_wc_product_update_manufacturer_article_number',
                    ),
                    array(
                        'title' => __('Stock place', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Update the stock place field', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_wc_product_update_stock_place',
                    ),
                    array(
                        'title' => __('Unit', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Update the unit field', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_wc_product_update_unit',
                    ),
                    array(
                        'title' => __('EAN', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Update the EAN (barcode) field', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_wc_product_update_ean',
                    ),
                    array(
                        'title' => __('Set as webshop article', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('If selected the article in Fortnox is set as a webshop article', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_wc_product_webshop_article',
                    ),
                    array(
                        'title' => __('Update product dimensions', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Update weight, lenght, width and height in Fortnox.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_wc_product_update_product_dimensions',
                    ),
                    array(
                        'title' => __('Stock goods', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Set the Fortnox article to "Stock goods" if "Manage stock" is set on the product in WooCommerce.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_wc_product_update_stock_data',
                    ),
                    array(
                        'title' => __('Stock level', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'desc' => __('Set the stock level on the article in Fortnox to the same value as on the product in WooCommerce.', 'woo-fortnox-hub'),
                        'default' => '',
                        'options' => array(
                            '' => __('Do not update stocklevel', 'woo-fortnox-hub'),
                            'yes' => __('When doing a full manual sync', 'woo-fortnox-hub'),
                            'always' => __('Always update', 'woo-fortnox-hub'),
                            'manually' => __('When creating new product/variation or pressing update on product/varation', 'woo-fortnox-hub'),
                        ),
                        'id' => 'fortnox_wc_product_update_stock_level',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_wc_products_data',
                    ),
                );

                $settings = array_merge($settings, $this->price_settings(), $optional_data);
            }

            return $settings;
        }

        public function show_start_sync_button()
        {
            WCFH_Util::display_sync_button('wcfh_sync_wc_products');
        }

        /**
         * Add a wc product id to the syncing queue
         */
        public function push_product_to_queue($product_id, $sync_all = false)
        {

            if (WCFH_Util::do_not_queue_requests()) {
                do_action(
                    'wcfh_sync_wc_products_process',
                    $product_id,
                    $sync_all
                );
            } else {
                as_schedule_single_action(as_get_datetime_object(), 'wcfh_sync_wc_products_process', array(
                    $product_id,
                    $sync_all,
                ));
            }
        }

        public function get_article($product)
        {

            $product_id = $product->get_id();
            $article_number = WCFH_Util::get_fortnox_article_number($product);

            if (!$article_number) {
                return false;
            }

            try {

                $article = WC_FH()->fortnox->get_article($article_number);
            } catch (Fortnox_API_Exception $e) {

                $code = $e->getCode();
                $message = $e->getMessage();

                if (400 == $code) {
                    WC_FH()->logger->add(sprintf('get_article (%s): %s', $product_id, $message));
                    return false;
                }

                if (404 == $code) {
                    WC_FH()->logger->add(sprintf('get_article (%s): Article %s not found in Fortnox', $product_id, $article_number));
                    return false;
                }

                throw new $e($e->getMessage(), $e->getCode(), $e);
            }

            return $article;
        }

        public function get_or_create_article($product)
        {

            $product_id = $product->get_id();
            $article = $this->get_article($product);

            if (false === $article) {

                $article_number = WCFH_Util::get_fortnox_article_number($product);

                $products_from_wc = get_option('fortnox_create_products_from_wc');

                if ((!$products_from_wc && $article_number) || 'always_create' == $products_from_wc) {

                    try {

                        $new_article = array(
                            'ArticleNumber' => $article_number,
                            'Description' => WCFH_Util::clean_fortnox_text(wp_filter_nohtml_kses($product->get_name('edit')), 50),
                        );

                        $stock_option = get_option('fortnox_wc_product_update_stock_level');
                        if ($stocklevel_set = in_array($stock_option, array('manually', 'always'))) {
                            $stocklevel = $product->get_stock_quantity('edit');
                            $new_article = $this->create_stocklevel_data($new_article, $stocklevel);
                        }

                        $article = WC_FH()->fortnox->create_article($new_article);
                        $article_number = $article['ArticleNumber'];

                        if ($stocklevel_set) {
                            WC_FH()->logger->add(sprintf('get_or_create_article (%s): WooCommerce product created Fortnox article %s and set stocklevel to %s', $product_id, $article_number, $stocklevel));
                        } else {
                            WC_FH()->logger->add(sprintf('get_or_create_article (%s): WooCommerce product created Fortnox article %s', $product_id, $article_number));
                        }

                        $product = wc_get_product($product->get_id());
                        if ($article_number != WCFH_Util::get_fortnox_article_number($product)) {
                            WCFH_Util::set_fortnox_article_number($product, $article_number);
                            $product->save();
                        }
                    } catch (Fortnox_API_Exception $e) {

                        $code = $e->getCode();
                        $message = $e->getMessage();
                        Fortnox_Notice::add(sprintf(__('%s when creating article from WooCommerce product %s using SKU "%s"', 'woo-fortnox-hub'), $message, $product_id, $article_number), 'error');
                        WC_FH()->logger->add(sprintf('get_or_create_article (%s): %s:%s when creating article with number "%s"', $product_id, $code, $message, $article_number));
                    } catch (WC_Data_Exception $e) {
                        $code = $e->getCode();
                        $message = $e->getMessage();
                        Fortnox_Notice::add(sprintf(__('%s when creating article from WooCommerce product %s with new article number "%s" received from Fortnox', 'woo-fortnox-hub'), $message, $product_id, $article_number), 'error');
                        WC_FH()->logger->add(sprintf('get_or_create_article (%s): %s when creating article with new article number "%s" received from Fortnox', $product_id, $message, $article_number));
                    }
                } else {

                    WC_FH()->logger->add(sprintf('get_or_create_article (%s): WooCommerce product has no article number and will not be updated', $product_id));
                }
            }

            return $article;
        }

        public function sync_product_to_article($product, $sync_all = false, $is_checked = false)
        {

            if ($is_checked || WCFH_Util::is_syncable($product)) {

                if ($article = $this->get_or_create_article($product)) {

                    $this->update_article($article, $product, $sync_all);

                    if (function_exists('b2bking_run') || function_exists('b2bkinglite_run')) {

                        $product_id = $product->get_id();

                        $groups = get_posts(array('post_type' => 'b2bking_group', 'post_status' => 'publish', 'numberposts' => -1));

                        foreach ($groups as $group) {

                            if ($pricelist = get_option('fortnox_wc_product_pricelist_b2bk_' . $group->ID)) {
                                if ($group_price = get_post_meta($product_id, 'b2bking_regular_product_price_group_' . $group->ID, true)) {
                                    $this->update_price($article, $group_price, $pricelist, $product, 'regular_b2bk_' . $group->ID);
                                }
                            }

                            if ($pricelist = get_option('fortnox_wc_product_sale_pricelist_b2bk_' . $group->ID)) {
                                if ($group_price = get_post_meta($product_id, 'b2bking_sale_product_price_group_' . $group->ID, true)) {
                                    $this->update_price($article, $group_price, $pricelist, $product, 'sale_b2bk_' . $group->ID);
                                }
                            }
                        }
                    }

                    if ($pricelist = get_option('fortnox_wc_product_pricelist')) {
                        $this->update_price($article, $product->get_regular_price('edit'), $pricelist, $product, 'price');
                    }

                    if ($pricelist = get_option('fortnox_wc_product_sale_pricelist')) {
                        $this->update_price($article, $product->get_sale_price('edit'), $pricelist, $product, 'sale_price');
                    }

                    if (class_exists('WooCommerceWholeSalePrices', false)) {
                        global $wc_wholesale_prices;

                        $wholesale_roles = $wc_wholesale_prices->wwp_wholesale_roles->getAllRegisteredWholesaleRoles();

                        foreach ($wholesale_roles as $key => $wholesale_role) {
                            if ($pricelist = get_option('fortnox_wc_product_pricelist_' . $key . '_wholesale_price')) {
                                if ('yes' == $product->get_meta($key . '_have_wholesale_price', true)) {
                                    $wholesale_price = $product->get_meta($key . '_wholesale_price', true);
                                    $this->update_price($article, $wholesale_price, $pricelist, $product, $key . '_wholesale_price');
                                }
                            }
                        }
                    }
                }
            }
        }

        public function update_article($article, $product, $sync_all)
        {

            $article_data = apply_filters('fortnox_before_processing_article', $this->create_article_data($product, $article, $sync_all), $product, $article, $sync_all);

            if (false !== $article_data) {

                $article = WC_FH()->fortnox->update_article($article['ArticleNumber'], $article_data);

            } else {

                WC_FH()->logger->add(sprintf('update_article (%s): Processing Fortnox Article %s did not result in any changes', $product->get_id(), $article['ArticleNumber']));

            }
        }

        public function update_price($article, $new_price, $pricelist, $product, $type)
        {

            $product_id = $product->get_id();

            if ($new_price) {

                if ('yes' == get_option('fortnox_remove_vat_from_prices') && WCFH_Util::prices_include_tax()) {
                    $new_price = WCFH_Util::maybe_remove_vat($new_price, $product);
                }

                $price_array['Price'] = array(
                    'ArticleNumber' => $article['ArticleNumber'],
                    'Price' => $new_price,
                    "PriceList" => $pricelist,
                );

                try {

                    $current_price = WC_FH()->fortnox->get_prices($article['ArticleNumber'], $pricelist);

                    if ($current_price['Price'] != $new_price) {

                        $updated_price = WC_FH()->fortnox->update_price($pricelist, $article['ArticleNumber'], $price_array);

                        WC_FH()->logger->add(sprintf('update_price (%s): Updating %s for Fortnox article %s using pricelist %s to %s', $product_id, $type, $article['ArticleNumber'], $pricelist, $new_price));
                    }
                } catch (Fortnox_API_Exception $e) {

                    if (404 != $e->getCode()) {
                        throw new $e($e->getMessage(), $e->getCode(), $e);
                    }

                    $created_price = WC_FH()->fortnox->create_price($price_array);

                    WC_FH()->logger->add(sprintf('update_price (%s): Creating %s for Fortnox article %s using pricelist %s to %s', $product_id, $type, $article['ArticleNumber'], $pricelist, $new_price));
                }
            } else {

                WC_FH()->logger->add(sprintf('update_price (%s): No %s set on WooCommerce product', $product_id, $type));
            }
        }

        public function article_data_from_meta($changed, $product, $article, $sync_all, $fortnox_key, $value = false)
        {
            $metadata = $value ? $value : WCFH_Util::get_metadata($product, $fortnox_key);
            if ($article[$fortnox_key] != $metadata) {
                WC_FH()->logger->add(sprintf('Updating %s in Fortnox from "%s" to "%s"', $fortnox_key, $article[$fortnox_key], $metadata));
                return array(
                    $fortnox_key => $metadata,
                );
            }

            return false;
        }

        public function maybe_create_unit($product)
        {

            if ($unit_name = WCFH_Util::get_metadata($product, 'Unit')) {

                $units = apply_filters('fortnox_get_units', false);

                foreach ($units as $unit) {
                    if ($unit['Code'] == $unit_name || $unit['Description'] == $unit_name) {
                        return $unit['Code'];
                    }
                }

                $unit_code = sanitize_title($unit_name);

                try {
                    WC_FH()->fortnox->create_unit(
                        array(
                            'Code' => $unit_code,
                            'Description' => $unit_name,
                        )
                    );
                } catch (Fortnox_API_Exception $e) {
                    $code = $e->getCode();
                    $message = $e->getMessage();
                    if (400 == $code) {
                        WC_FH()->logger->add(sprintf('maybe_create_unit (%s): %s when creating Unit "%s" with code "%s"', $product->get_id(), $message, $unit_name, $unit_code));
                    } else {
                        throw new $e($e->getMessage(), $e->getCode(), $e);
                    }
                }

                delete_site_transient('fortnox_units');

                return $unit_code;
            }

            return false;
        }

        private function get_bool_manage_stock($product)
        {
            return $product->get_manage_stock() ? true : false;
        }

        public function create_article_data($product, $article, $sync_all = false)
        {

            $changed = false;

            $product_id = $product->get_id();

            $article_data = array(
                'ArticleNumber' => $article['ArticleNumber'],
            );

            $current_name = wp_filter_nohtml_kses($product->get_name('edit'));
            $clean_name = WCFH_Util::clean_fortnox_text($current_name, 200);

            if (('yes' == get_option('fortnox_wc_product_name', 'yes')) && ($article['Description'] != $clean_name)) {
                $article_data['Description'] = $clean_name;
                WC_FH()->logger->add(sprintf('Updating description in Fortnox to: "%s" based on "%s"', $clean_name, $current_name));
                $changed = true;
            }

            if ('yes' == get_option('fortnox_wc_product_webshop_article') && $article['WebshopArticle'] == false) {
                $article_data['WebshopArticle'] = true;
                WC_FH()->logger->add('Updating webshop article in in Fortnox to: true');
                $changed = true;
            }

            switch (get_option('fortnox_wc_product_description')) {
                case 'short_description':
                    if ($article['Note'] != ($clean_text = WCFH_Util::clean_fortnox_text($product->get_short_description('edit')))) {
                        $article_data['Note'] = $clean_text ? $clean_text : 'API_BLANK';
                        WC_FH()->logger->add(sprintf('Updating note in Fortnox to short description "%s"', $clean_text));
                        $changed = true;
                    }
                    break;
                case 'description':
                    if ($article['Note'] != ($clean_text = WCFH_Util::clean_fortnox_text($product->get_description('edit')))) {
                        $article_data['Note'] = $clean_text ? $clean_text : 'API_BLANK';
                        WC_FH()->logger->add(sprintf('Updating note in Fortnox from "%s" to description "%s"', $article_data['Note'], $clean_text));
                        $changed = true;
                    }
                    break;
                case 'purchase_note':
                    if ($article['Note'] != ($clean_text = WCFH_Util::clean_fortnox_text($product->get_purchase_note('edit')))) {
                        $article_data['Note'] = $clean_text ? $clean_text : 'API_BLANK';
                        WC_FH()->logger->add(sprintf('Updating note in Fortnox to purchase note "%s"', $clean_text));
                        $changed = true;
                    }
                    break;
            }

            $manage_stock = $this->get_bool_manage_stock($product);
            $stock_goods = rest_sanitize_boolean($article['StockGoods']);
            $update_stock_data = wc_string_to_bool(get_option('fortnox_wc_product_update_stock_data'));
            if (true === $update_stock_data && $stock_goods != $manage_stock) {
                $article_data['StockGoods'] = $manage_stock;
                WC_FH()->logger->add(sprintf('Updating stock goods in Fortnox to: "%s"', $manage_stock ? 'true' : 'false'));
                $changed = true;
            }

            if ('yes' == get_option('fortnox_wc_product_update_product_dimensions')) {

                $weight_unit = get_option('woocommerce_weight_unit', 'kg');

                if ($article['Weight'] != ($weight = WCFH_Util::weight_to_grams($current = $product->get_weight('edit'), $weight_unit))) {
                    $article_data['Weight'] = $weight ? $weight : 'API_BLANK';
                    WC_FH()->logger->add(sprintf('Updating weight in Fortnox from: %s to: %s g based on WooCommerce %s %s', $article['Weight'], $weight, $current, $weight_unit));
                    $changed = true;
                }

                $dimension_unit = get_option('woocommerce_dimension_unit', 'cm');

                if ($article['Depth'] != ($length = WCFH_Util::dimension_to_millimeters($current = $product->get_length('edit'), $dimension_unit))) {
                    $article_data['Depth'] = $length ? $length : 'API_BLANK';
                    WC_FH()->logger->add(sprintf('Updating depth in Fortnox from: %s to: %s mm based on WooCommerce %s %s', $article['Depth'], $length, $current, $dimension_unit));
                    $changed = true;
                }

                if ($article['Width'] != ($width = WCFH_Util::dimension_to_millimeters($current = $product->get_width('edit'), $dimension_unit))) {
                    $article_data['Width'] = $width ? $width : 'API_BLANK';
                    WC_FH()->logger->add(sprintf('Updating width in Fortnox from: %s to: %s mm based on WooCommerce %s %s', $article['Width'], $width, $current, $dimension_unit));
                    $changed = true;
                }

                if ($article['Height'] != ($height = WCFH_Util::dimension_to_millimeters($current = $product->get_height('edit'), $dimension_unit))) {
                    $article_data['Height'] = $height ? $height : 'API_BLANK';
                    WC_FH()->logger->add(sprintf('Updating height in Fortnox from: %s to: %s mm based on WooCommerce %s %s', $article['Height'], $height, $current, $dimension_unit));
                    $changed = true;
                }
            }

            if ('yes' == get_option('fortnox_wc_product_update_type') && $article['Type'] != ($product_type = $product->get_virtual() ? 'SERVICE' : 'STOCK')) {
                $article_data['Type'] = $product_type;
                WC_FH()->logger->add(sprintf('Updating product type in Fortnox to "%s"', $product_type));
                $changed = true;
            }

            if (('yes' == get_option('fortnox_wc_product_update_manufacturer')) && ($changed_article = $this->article_data_from_meta($changed, $product, $article, $sync_all, 'Manufacturer'))) {
                $article_data = array_merge($article_data, $changed_article);
                $changed = true;
            }

            if (('yes' == get_option('fortnox_wc_product_update_manufacturer_article_number')) && ($changed_article = $this->article_data_from_meta($changed, $product, $article, $sync_all, 'ManufacturerArticleNumber'))) {
                $article_data = array_merge($article_data, $changed_article);
                $changed = true;
            }

            if (('yes' == get_option('fortnox_wc_product_update_stock_place')) && ($changed_article = $this->article_data_from_meta($changed, $product, $article, $sync_all, 'StockPlace'))) {
                $article_data = array_merge($article_data, $changed_article);
                $changed = true;
            }

            if (('yes' == get_option('fortnox_wc_product_update_ean')) && ($changed_article = $this->article_data_from_meta($changed, $product, $article, $sync_all, 'EAN'))) {
                $article_data = array_merge($article_data, $changed_article);
                $changed = true;
            }

            if ('yes' == get_option('fortnox_wc_product_update_unit')) {

                if (($unit = $this->maybe_create_unit($product)) && ($changed_article = $this->article_data_from_meta($changed, $product, $article, $sync_all, 'Unit', $unit))) {
                    $changed_article['Unit'] = $unit;
                    $article_data = array_merge($article_data, $changed_article);
                    $changed = true;
                }
            }

            if ('yes' == get_option('fortnox_wc_product_update_sales_account') && $article['SalesAccount'] != ($account = WCFN_Accounts::get_product_account($product))) {
                $article_data['SalesAccount'] = $account;
                WC_FH()->logger->add(sprintf('Updating Sales Account in Fortnox to "%s"', $account));
                $changed = true;
            }

            if (($account = get_option('fortnox_se_purchase_account')) && $article['PurchaseAccount'] != $account) {
                $article_data['PurchaseAccount'] = $account;
                WC_FH()->logger->add(sprintf('Updating Purchase Account in Fortnox to "%s"', $account));
                $changed = true;
            }

            if ('yes' == get_option('fortnox_wc_product_update_purchase_price')) {

                if ($purchase_price = str_replace(',', '.', apply_filters('fortnox_purchase_price', get_post_meta($product_id, '_fortnox_purchase_price', true)))) {

                    if (wc_string_to_bool(get_option('fortnox_remove_vat_from_prices')) && WCFH_Util::prices_include_tax()) {
                        $purchase_price = WCFH_Util::maybe_remove_vat($purchase_price, $product);
                    }

                    if ($article['PurchasePrice'] != $purchase_price) {
                        $article_data['PurchasePrice'] = $purchase_price;
                        WC_FH()->logger->add(sprintf('Updating purchase price in Fortnox to "%s"', $purchase_price));
                        $changed = true;
                    }
                }
            }

            $wc_qty = $product->get_stock_quantity('edit');
            $fn_qty = $article['QuantityInStock'];
            $sl_option = get_option('fortnox_wc_product_update_stock_level');

            if ((($sync_all && 'yes' === $sl_option) || 'always' === $sl_option) && $product->get_manage_stock('edit') && $fn_qty != $wc_qty) {
                $article_data = $this->update_fortnox_article_stocklevel($article_data, $fn_qty, $wc_qty, false);
                $changed = true;
            }

            return $changed === true ? $article_data : false;
        }

        public function create_stocklevel_data($article_data, $stock_quantity)
        {

            $article_data['QuantityInStock'] = $stock_quantity;
            $article_data['StockGoods'] = true;

            return $article_data;
        }

        public function update_fortnox_stocklevel($product)
        {

            if (WCFH_Util::is_syncable($product)) {

                $product_id = $product->get_id();
                $product_type = $product->get_type();

                WC_FH()->logger->add(sprintf('update_fortnox_stocklevel (%s): Starting to process %s product', $product_id, $product_type));

                if ('variable' == $product_type) {

                    $variations = WCFH_Util::get_all_variations($product);

                    foreach ($variations as $variation) {

                        if (!is_object($variation)) {
                            $variation = wc_get_product($variation['variation_id']);
                        }

                        $article = $this->get_article($variation);

                        if ($variation->get_manage_stock('edit') && $article) {
                            $stock_quantity = $variation->get_stock_quantity('edit');
                            $article_data = array(
                                'ArticleNumber' => $article['ArticleNumber'],
                            );
                            $result = $this->update_fortnox_article_stocklevel($article_data, $article['QuantityInStock'], $stock_quantity);
                        }
                    }
                } else {

                    $article = $this->get_article($product);

                    if ($product->get_manage_stock('edit') && $article) {
                        $stock_quantity = $product->get_stock_quantity('edit');
                        $article_data = array(
                            'ArticleNumber' => $article['ArticleNumber'],
                        );
                        $result = $this->update_fortnox_article_stocklevel($article_data, $article['QuantityInStock'], $stock_quantity);
                    }
                }

                WC_FH()->logger->add(sprintf('update_fortnox_stocklevel (%s): Finished processing stocklevel', $product_id));
            }
        }

        public function update_fortnox_article_stocklevel($article_data, $from_stock, $to_stock, $update = true)
        {

            if ($from_stock != $to_stock) {

                WC_FH()->logger->add(sprintf('update_fortnox_article_stocklevel: Changing stocklevel on Fortnox Article %s from %s to %s', $article_data['ArticleNumber'], $from_stock, $to_stock));

                $article_data = $this->create_stocklevel_data($article_data, $to_stock);

                if ($update) {
                    $article = WC_FH()->fortnox->update_article($article_data['ArticleNumber'], $article_data);
                }

            } else {

                WC_FH()->logger->add(sprintf('update_fortnox_stocklevel: No need to change stock on Fortnox Article %s from %s', $article_data['ArticleNumber'], $from_stock));

            }

            return $article_data;
        }

        /**
         * Initiate the sync of wc products to Fortnox
         */
        public function start_wc_product_sync($number_of_products, $sync_all = false)
        {
            if (false === WC_FH()->do_not_sync) {

                try {

                    $include_products = get_option('fortnox_wc_products_include', array('simple', 'variable'));

                    $args = array(
                        'limit' => -1,
                        'return' => 'ids',
                        'type' => $include_products,
                        'status' => get_option('fortnox_wc_get_product_status', array('publish')),
                    );

                    $this_sync_time = gmdate('U');

                    $last_sync_done = get_option('fortnox_last_wc_product_sync_done');

                    if (!$sync_all) {
                        if (false !== $last_sync_done) {
                            $args['date_modified'] = $last_sync_done . '...' . $this_sync_time;
                        } else {
                            $args['date_modified'] = ($this_sync_time - DAY_IN_SECONDS) . '...' . $this_sync_time;
                        }
                        update_option('fortnox_last_wc_product_sync_done', $this_sync_time);
                    } else {
                        WC_FH()->logger->add('Manual sync of all products in WooCommerce to Fortnox requested');
                    }

                    if (!empty($product_categories = get_option('fortnox_wc_products_product_categories', ''))) {
                        $args['category'] = $product_categories;
                    }

                    $default_lang = apply_filters('wpml_default_language', null);

                    if ($default_lang) {
                        $args['suppress_filters'] = true;
                        WC_FH()->logger->add(sprintf('WMPL or Polylang detected, using products with language code %s when syncing products', $default_lang));
                    }

                    $products_ids = wc_get_products($args);
                    $number_of_products = count($products_ids);

                    $sync_products = array();
                    if ($number_of_products > 0) {
                        $products_added = array();
                        foreach ($products_ids as $key => $original_product_id) {
                            if ($default_lang) {
                                $product_id = apply_filters('wpml_object_id', $original_product_id, 'product', true, $default_lang);
                                if (!in_array($product_id, $products_added)) {

                                    $products_added[] = $product_id;

                                    if ($product_id != $original_product_id) {
                                        WC_FH()->logger->add(sprintf('Added product id %s to the sync queue instead of product id %s as the default language is %s', $product_id, $original_product_id, $default_lang));
                                    }
                                } else {
                                    WC_FH()->logger->add(sprintf('Skipping product id %s as it was a language duplicate for product id %s', $original_product_id, $product_id));
                                }
                            } else {
                                $products_added[] = $original_product_id;
                            }
                        }

                        $number_of_products = count($products_added);

                        foreach ($products_added as $key => $product_added) {
                            if ($sync_all) {
                                as_schedule_single_action(as_get_datetime_object(), 'wcfh_sync_wc_products_process', array(
                                    $product_added,
                                    $sync_all,
                                ), 'wcfh_sync_wc_products');
                            } else {
                                do_action('fortnox_push_product_to_queue', $product_added);
                            }
                        }
                    }

                    if ($number_of_products > 0) {
                        WC_FH()->logger->add(sprintf('Added %d WooCommerce products to queue for updating Fortnox', $number_of_products));
                    } else {
                        WC_FH()->logger->add(sprintf('No WooCommerce products changed since %s', date("Y-m-d H:i:s", $last_sync_done)));
                    }
                } catch (Throwable $t) {
                    if (method_exists($t, 'write_to_logs')) {
                        $t->write_to_logs();
                    } else {
                        WC_FH()->logger->add(print_r($t, true));
                    }
                }
            }

            return $number_of_products;
        }

        public function wcfh_sync_wc_products_process($product_id, $sync_all = false)
        {
            try {

                if ($product = wc_get_product($product_id)) {

                    if (WCFH_Util::is_syncable($product)) {

                        do_action('fortnox_remove_product_update_actions');

                        $product_type = $product->get_type();

                        WC_FH()->logger->add(sprintf('wcfh_sync_wc_products_process (%s): Starting to process %s product', $product_id, $product_type));

                        if ('variable' == $product_type) {

                            $variations = WCFH_Util::get_all_variations($product);

                            foreach ($variations as $variation) {

                                if (!is_object($variation)) {
                                    $variation = wc_get_product($variation['variation_id']);
                                }

                                do_action('fortnox_sync_product_to_article', $variation, $sync_all, true);
                            }
                        } else {

                            do_action('fortnox_sync_product_to_article', $product, $sync_all, true);
                        }

                        WC_FH()->logger->add(sprintf('wcfh_sync_wc_products_process (%s): Finished processing product data', $product_id, $product_type));

                        do_action('fortnox_add_product_update_actions');
                    }
                }
            } catch (Fortnox_API_Exception $e) {

                $e->write_to_logs();
                Fortnox_Notice::add(sprintf(__("%s when syncing product %s", 'woo-fortnox-hub'), $e->getMessage(), $product_id));
            }
        }
    }

    new Woo_Fortnox_Hub_WC_Product_Handler();
}
