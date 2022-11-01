<?php

/**
 * This class handles the extra product information
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2021 BjornTech AB
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Product_Data_Tabs', false)) {

    class Woo_Fortnox_Hub_Product_Data_Tabs
    {
        public function __construct()
        {
            /**
             * Show new fields on variant, pricing and inventory
             */
            add_action('woocommerce_product_after_variable_attributes', array($this, 'show_fortnox_fields_variable'), 30, 3);
            add_filter('woocommerce_product_options_pricing', array($this, 'show_fortnox_pricing_fields'), 50, 1);
            add_filter('woocommerce_product_options_inventory_product_data', array($this, 'show_fortnox_inventory_fields'), 50, 1);

            /**
             * Save our own metadata when the product or product variation is saved
             */
            add_action('woocommerce_admin_process_product_object', array($this, 'save_product'));
            add_action('woocommerce_admin_process_variation_object', array($this, 'save_product_variation'), 10, 2);
        }

        public function show_fortnox_fields_variable($loop, $variation_data, $variation)
        {
            include 'views/html-product-data-article-variable.php';
        }

        public function show_fortnox_pricing_fields()
        {
            global $post, $thepostid, $product_object;
            include 'views/html-product-data-article-pricing.php';
        }

        public function show_fortnox_inventory_fields()
        {
            global $post, $thepostid, $product_object;
            include 'views/html-product-data-article-inventory.php';
        }

        public function save_product($product)
        {

            WCFH_Util::update_metadata($product, 'Manufacturer', isset($_POST["fortnox_manufacturer"]) ? wc_clean(wp_unslash($_POST["fortnox_manufacturer"])) : '');
            WCFH_Util::update_metadata($product, 'ManufacturerArticleNumber', isset($_POST["fortnox_manufacturer_article_number"]) ? wc_clean(wp_unslash($_POST["fortnox_manufacturer_article_number"])) : '');
            WCFH_Util::update_metadata($product, 'PurchasePrice', isset($_POST["fortnox_purchase_price"]) ? wc_clean(wp_unslash($_POST["fortnox_purchase_price"])) : '');
            WCFH_Util::update_metadata($product, 'StockPlace', isset($_POST["fortnox_stock_place"]) ? wc_clean(wp_unslash($_POST["fortnox_stock_place"])) : '');
            WCFH_Util::update_metadata($product, 'Unit', isset($_POST["fortnox_unit"]) ? wc_clean(wp_unslash($_POST["fortnox_unit"])) : '');
            WCFH_Util::update_metadata($product, 'EAN', isset($_POST["fortnox_barcode"]) ? wc_clean(wp_unslash($_POST["fortnox_barcode"])) : '');

            if ('yes' == get_option('fortnox_enable_housework')) {
                $product->update_meta_data('fortnox_tax_reduction_type', isset($_POST["_fortnox_tax_reduction_type"]) ? wc_clean(wp_unslash($_POST["_fortnox_tax_reduction_type"])) : '');
                $product->update_meta_data('fortnox_tax_reduction_hours', isset($_POST["_fortnox_tax_reduction_hours"]) ? wc_clean(wp_unslash($_POST["_fortnox_tax_reduction_hours"])) : '');
                $product->update_meta_data('fortnox_tax_reduction_price', isset($_POST["_fortnox_tax_reduction_price"]) ? wc_clean(wp_unslash($_POST["_fortnox_tax_reduction_price"])) : '');
            }
        }

        public function save_product_variation($product, $i)
        {

            WCFH_Util::update_metadata($product, 'Manufacturer', isset($_POST["fortnox_manufacturer_{$i}"]) ? $_POST["fortnox_manufacturer_{$i}"] : '');
            WCFH_Util::update_metadata($product, 'ManufacturerArticleNumber', isset($_POST["fortnox_manufacturer_article_number_{$i}"]) ? $_POST["fortnox_manufacturer_article_number_{$i}"] : '');
            WCFH_Util::update_metadata($product, 'PurchasePrice', isset($_POST["fortnox_purchase_price_{$i}"]) ? $_POST["fortnox_purchase_price_{$i}"] : '');
            WCFH_Util::update_metadata($product, 'StockPlace', isset($_POST["fortnox_stock_place_{$i}"]) ? $_POST["fortnox_stock_place_{$i}"] : '');
            WCFH_Util::update_metadata($product, 'Unit', isset($_POST["fortnox_unit_{$i}"]) ? $_POST["fortnox_unit_{$i}"] : '');
            WCFH_Util::update_metadata($product, 'EAN', isset($_POST["fortnox_barcode_{$i}"]) ? $_POST["fortnox_barcode_{$i}"] : '');
        }
    }

    new Woo_Fortnox_Hub_Product_Data_Tabs();
}
