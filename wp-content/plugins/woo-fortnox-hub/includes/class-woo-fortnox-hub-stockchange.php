<?php

/**
 * This class contains the handling of stockchanges made based on a WooCommerce order
 *
 * @package   Woo_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2021 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Stockchange', false)) {

    class Woo_Fortnox_Hub_Stockchange
    {

        public function __construct()
        {

            add_action('woo_fortnox_hub_processing_stockchange', array($this, 'processing_stockchange'));
            add_action('woo_fortnox_hub_cancelled_stockchange', array($this, 'cancelled_stockchange'));
            add_action('woo_fortnox_hub_completing_stockchange', array($this, 'completing_stockchange'));
            add_action('woo_fortnox_hub_fully_refunded_stockchange', array($this, 'fully_refunded_stockchange'), 10, 2);
            add_action('woo_fortnox_hub_partially_refunded_stockchange', array($this, 'partially_refunded_stockchange'), 10, 2);
            add_filter('woocommerce_prevent_adjust_line_item_product_stock', array($this, 'maybe_adjust_line_item_product_stock'), 10, 3);
            add_filter('woocommerce_order_item_display_meta_key', array($this, 'display_meta_key'), 10, 3);
        }

        /**
         * Changes the meta key presentation on items in order admin
         *
         * @since 5.1
         * @param string $display_key Incoming meta key to display for the order item meta
         * @param WC_Order_Item_Meta $meta Meta data object
         * @param WC_Order_Item $item The order item containing the metadata
         * @return string Outgoing meta key to display for the order item meta
         */
        public function display_meta_key($display_key, $meta, $item)
        {

            if ($meta->key === '_fortnox_reduced_stock') {
                $display_key = __('Fortnox reduction', 'woo-fortnox-hub');
            }

            return $display_key;
        }

        /**
         * Adjust stocklevel in Fortnox based on items in a WooCommerce order
         *
         * @since 5.1
         * @param string $order_id order ID
         * @param bool $cancel true if the adjustment is a cancellation of the order
         */
        public function fortnox_stocklevel_adjustments($order_id, $cancel = false)
        {

            $order = wc_get_order($order_id);
            $order_modified = $order->get_date_modified();
            $stockchange_timestamp = get_post_meta($order_id, '_fortnox_stockchange_timestamp', true);

            if ($order_modified === $stockchange_timestamp) {
                WC_FH()->logger->add(sprintf('fortnox_stocklevel_adjustments (%s): Fortnox stocklevel already adjusted  @%s', $order_id, $stockchange_timestamp));
            }

            if ($cancel && !$stockchange_timestamp->getTimestamp()) {
                WC_FH()->logger->add(sprintf('fortnox_stocklevel_adjustments (%s): Fortnox cancel will not be done on non synced order', $order_id));
                return;
            }

            $order_notes = array();

            foreach ($order->get_items() as $item) {

                $note = $this->update_fortnox_stocklevel($item, $cancel);
                if ($note) {
                    $order_notes[] = $note;
                }
            }

            if (!empty($order_notes)) {
                $order->add_order_note(__('Fortnox stock levels changed:', 'woo-fortnox-hub') . ' ' . implode(', ', $order_notes));
            }

            update_post_meta($order_id, '_fortnox_stockchange_timestamp', $order_modified);
            WC_FH()->logger->add(sprintf('fortnox_stocklevel_adjustments (%s): Fortnox stocklevel adjustments done @%s', $order_id, $order_modified));
        }

        /**
         * Updates the stocklevel on an article in Fortnox based on an order item
         *
         * @param WC_Order_Item $item The order item that holds a number purchased
         * @param bool $cancel true if the adjustment is a cancellation of the order
         * @return string|bool false if no change was made, change text if change was made
         */
        public function update_fortnox_stocklevel($item, $cancel = false)
        {

            $order = $item->get_order();
            $order_id = $order->get_id();

            if (!$item->is_type('line_item')) {
                return false;
            }

            $product = $item->get_product();
            if (!$product) {
                WC_FH()->logger->add(sprintf('fortnox_stocklevel_adjustments (%s): Product not found for item', $order_id));
                return false;
            }

            if (!$product->managing_stock()) {
                WC_FH()->logger->add(sprintf('fortnox_stocklevel_adjustments (%s): Product %s not set to manage stock', $order_id, $product->get_id()));
                return false;
            }

            $article_number = WCFH_Util::get_fortnox_article_number($product);
            if (empty($article_number)) {
                WC_FH()->logger->add(sprintf('fortnox_stocklevel_adjustments (%s): Fortnox article number does not exist for product %s', $order_id, $product->get_id()));
                return false;
            }

            $item_quantity = wc_stock_amount($item->get_quantity());
            $already_reduced_stock = (int) $item->get_meta('_fortnox_reduced_stock', true);
            $refunded_item_quantity = wc_stock_amount($order->get_qty_refunded_for_item($item->get_id()));
            $delta_qty = $cancel ? $already_reduced_stock : $item_quantity + $refunded_item_quantity - $already_reduced_stock;

            if (!$cancel && !$delta_qty) {
                WC_FH()->logger->add(sprintf('fortnox_stocklevel_adjustments (%s): Stocklevel already changed by %s for article %s', $order_id, $item_quantity + $refunded_item_quantity, $article_number));
                return false;
            }

            $item_name = $product->get_formatted_name();

            try {

                $article = WC_FH()->fortnox->get_article($article_number);

                $current_stock = is_numeric($article['QuantityInStock']) ? $article['QuantityInStock'] : 0;
                $new_stock = $current_stock - $delta_qty;

                $article = WC_FH()->fortnox->update_article($article['ArticleNumber'], array(
                    'StockGoods' => true,
                    'QuantityInStock' => $new_stock,
                ));

                if ($cancel) {
                    WC_FH()->logger->add(sprintf('fortnox_stocklevel_adjustments (%s): Stocklevel reversed by %s to %s for article %s due to cancel', $order_id, $delta_qty, $new_stock, $article_number));
                    $item->delete_meta_data('_fortnox_reduced_stock');
                } else {
                    WC_FH()->logger->add(sprintf('fortnox_stocklevel_adjustments (%s): Stocklevel changed by %s to %s for article %s', $order_id, -$delta_qty, $new_stock, $article_number));
                    $item->add_meta_data('_fortnox_reduced_stock', $item_quantity + $refunded_item_quantity, true);
                }

                $item->save();

                return $product->get_formatted_name() . ' ' . $current_stock . '&rarr;' . $new_stock;

            } catch (Fortnox_API_Exception $e) {

                WC_FH()->logger->add(sprintf('update_fortnox_stocklevel (%s): Got message "%s" from Fortnox when updating article "%s"', $order_id, $e->getMessage(), $article_number), true);
                return sprintf(__('Unable to reduce Fortnox stock for item @%s.', 'woo-fortnox-hub'), $item_name);

            }
        }

        public function maybe_adjust_line_item_product_stock($return, $item, $item_quantity = -1)
        {

            $note = $this->update_fortnox_stocklevel($item);

            if (!empty($note)) {
                $order = $item->get_order();
                $order->add_order_note(__('Fortnox stock level changed:', 'woo-fortnox-hub') . ' ' . $note);
            }

            return $return;
        }

        public function processing_stockchange($order_id)
        {

            if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'processing_stockchange', $order_id)) {
                return;
            }

            $this->fortnox_stocklevel_adjustments($order_id);
        }

        public function cancelled_stockchange($order_id)
        {

            if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'cancelled_stockchange', $order_id)) {
                return;
            }

            $this->fortnox_stocklevel_adjustments($order_id, true);
        }

        public function completing_stockchange($order_id)
        {

            if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'completing_stockchange', $order_id)) {
                return;
            }

            $this->fortnox_stocklevel_adjustments($order_id);
        }

        public function fully_refunded_stockchange($order_id, $refund_id)
        {

            if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'fully_refunded_stockchange', $order_id)) {
                return;
            }

            $this->fortnox_stocklevel_adjustments($order_id);
        }

        public function partially_refunded_stockchange($order_id, $refund_id)
        {

            if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'partially_refunded_stockchange', $order_id)) {
                return;
            }

            $this->fortnox_stocklevel_adjustments($order_id);
        }
    }

    new Woo_Fortnox_Hub_Stockchange();
}
