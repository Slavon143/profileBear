<?php

/**
 * This class contains common functions for handling products
 *
 * @package   Woo_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Product_Admin', false)) {

    class Woo_Fortnox_Hub_Product_Admin
    {
        public function __construct()
        {

            /**
             * Adding columns inclusive Sync button product list page
             */
            add_filter('manage_edit-product_columns', array($this, 'fortnox_product_header'), 20);
            add_action('manage_product_posts_custom_column', array($this, 'fortnox_product_content'));
            add_action('wp_ajax_fortnox_update_article', array($this, 'update_fortnox_article'));
            add_action('woocommerce_variation_header', array($this, 'show_sync_button_in_variaion'), 5, 1);

            add_filter('manage_edit-product_variation_columns', array($this, 'fortnox_product_variation_header'), 20);
            add_action('manage_product_variation_posts_custom_column', array($this, 'fortnox_product_variation_content'));
        }

        public function fortnox_product_variation_header($columns)
        {

            $columns = array_merge($columns, array('fortnox_update_product_variation' => __('Fortnox', 'woo-fortnox-hub')));
            return $columns;
        }

        public function fortnox_product_variation_content($column)
        {
            global $post;

            if ('fortnox_update_product_variation' === $column) {
                echo '<a class="button wc-action-button fortnox update_product" name="fortnox_update_article" data-product-id="' . esc_html($post->ID) . '">' . __('Update', 'woo-fortnox-hub') . '</a>';
            }
        }

        public function fortnox_product_header($columns)
        {

            $columns = array_merge($columns, array('fortnox_update_product' => __('Fortnox', 'woo-fortnox-hub')));
            return $columns;
        }

        public function fortnox_product_content($column)
        {
            global $post;

            if ('fortnox_update_product' === $column) {
                echo '<a class="button wc-action-button fortnox update_product" name="fortnox_update_article" data-product-id="' . esc_html($post->ID) . '">' . __('Update', 'woo-fortnox-hub') . '</a>';
            }
        }

        public function update_fortnox_article()
        {

            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            $product_id = $_POST['product_id'];

            $product = wc_get_product($product_id);

            try {

                do_action('wcfh_sync_wc_products_process', $product_id);

                if ('manually' == get_option('fortnox_wc_product_update_stock_level')) {
                    do_action('fortnox_sync_article_stocklevel', $product);
                }
            } catch (Fortnox_API_Exception $e) {

                $e->write_to_logs();
                Fortnox_Notice::add(sprintf(__("%s when manually syncing product %s", 'woo-fortnox-hub'), $e->getMessage(), $product_id));
            }

            echo true;
            die;
        }

        public function show_sync_button_in_variaion($variation)
        {

            echo '<a class="button wc-action-button fortnox update_variation" name="fortnox_update_article" data-product-id="' . esc_html($variation->ID) . '">' . __('Update Fortnox', 'woo-fortnox-hub') . '</a>';
        }
    }

    new Woo_Fortnox_Hub_Product_Admin();
}
