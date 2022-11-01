<?php

/**
 * Handle settings for WooCommerce Product Bundles
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2021 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_WC_Bundles', false)) {

    class Fortnox_Hub_PWGC
    {

        public function __construct()
        {

            add_filter('fortnox_after_get_order_item', array($this, 'maybe_enrich_wc_bundle'), 10, 3);

        }

        /**
         * Check if the order item is a product bundle and if so add the included items
         *
         * @since 5.1.0
         *
         * @param array $row
         * @param WC_Order_Item $order_item
         * @param WC_Order $order
         *
         * @return array
         */
        public function maybe_enrich_wc_bundle($row_in, $order_item, $order)
        {

            $product = $order_item->get_product();
            $row[] = $row_in;

            if ($this->is_product_bundle($product)) {

                $bundled_items = $product->get_bundled_items();

                if (!empty($bundled_items)) {

                    foreach ($bundled_items as $bundled_item_id => $bundled_item) {

                        $bundled_item_quantity = isset($bundled_item_configuration['quantity']) ? absint($bundled_item_configuration['quantity']) : $bundled_item->get_quantity('default');

                        $bundled_product = isset($bundled_item_configuration['variation_id']) && in_array($bundled_item->product->get_type(), array('variable', 'variable-subscription')) ? wc_get_product($bundled_item_configuration['variation_id']) : $bundled_item->product;

                        $bundled_item_discount = isset($bundled_item_configuration['discount']) ? wc_format_decimal($bundled_item_configuration['discount']) : $bundled_item->get_discount();

                    }
                }

            }

            return $row;

        }

        private function is_product_bundle($product)
        {
            return !empty($product) && is_callable(array($product, 'is_type')) && $product->is_type('bundle');
        }

    }

    new Fortnox_Hub_WC_Bundles();
}
