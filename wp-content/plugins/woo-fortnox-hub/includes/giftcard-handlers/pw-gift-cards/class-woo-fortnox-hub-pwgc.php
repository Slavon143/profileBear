<?php

/**
 * Handle settings for YWGC
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_PWGC', false)) {

    class Fortnox_Hub_PWGC
    {

        public function __construct()
        {

            add_filter('fortnox_get_sections', array($this, 'add_settings_section'), 200);
            add_filter('woocommerce_get_settings_fortnox_hub', array($this, 'get_settings'), 70, 2);
            add_filter('fortnox_after_get_order_item', array($this, 'maybe_enrich_pwgc_order_item'), 10, 3);
            add_filter('fortnox_after_get_fee_items', array($this, 'maybe_enrich_pwgc_fee_item'), 10, 3);
            add_filter('fortnox_after_get_order_items', array($this, 'maybe_add_pwgc_item'), 10, 3);

        }

        public function add_settings_section($sections)
        {
            if (!array_key_exists('pwgc_options', $sections)) {
                $sections = array_merge($sections, array('pwgc_options' => __('Gift Cards', 'woo-fortnox-hub')));
            }
            return $sections;
        }

        public function get_settings($settings, $current_section)
        {
            if ('pwgc_options' === $current_section) {

                $account_selection = apply_filters('fortnox_get_account_selection', array());

                $settings[] = [
                    'title' => __('Gift card options', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => __('', 'woo-fortnox-hub'),
                    'id' => 'fortnox_pwgc_options',
                ];
                $settings[] = [
                    'title' => __('Gift card sales account', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'options' => $account_selection,
                    'id' => 'fortnox_pwgc_giftcard_sales_account',
                ];
                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_pwgc_options',
                ];

            }
            return $settings;
        }

        /**
         * Check if the order item is a gift card purchase and enrich the order item
         *
         * @since 5.1.1
         *
         * @param array $row
         * @param WC_Order_Item $order_item
         * @param WC_Order $order
         *
         * @return array
         */
        public function maybe_enrich_pwgc_order_item($row, $order_item, $order)
        {

            if ($product = $order_item->get_product()) {
                $product_id = !empty($product->get_parent_id()) ? $product->get_parent_id() : $product->get_id();
                $product = wc_get_product($product_id);
                if (is_a($product, 'WC_Product_PW_Gift_Card')) {
                    $row["AccountNumber"] = get_option('fortnox_pwgc_giftcard_sales_account');
                }
            }
            return WCFH_Util::remove_blanks($row);

        }

        /**
         * Check if the order fee item is a gift card payment and enrich the order item
         *
         * @since 4.1.0
         *
         * @param array $row
         * @param WC_Order_Item $item
         * @param WC_Order $order
         *
         * @return array
         */
        public function maybe_enrich_pwgc_fee_item($row, $item, $order)
        {

            $item_type = $item->get_type();

            if ($item_type == 'pw_gift_card') {

                $row["AccountNumber"] = get_option('fortnox_pwgc_giftcard_sales_account');

            }

            return WCFH_Util::remove_blanks($row);

        }

        /**
         * Check if a gift card is used in the order and add it to the order
         *
         * @since 4.9.3
         *
         * @param array $rows
         * @param WC_Order $order
         *
         * @return array of rows
         */
        public function maybe_add_pwgc_item($rows, $order, $rowtype)
        {

            $gift_cards_redeemed = array();
            $gift_card_total = 0;
            foreach ($order->get_items('pw_gift_card') as $line) {
                $gift_cards_redeemed[] = $line->get_card_number();
                $gift_card_total += $line->get_amount('edit');
            }

            if (!empty($gift_card_total)) {
                $price = apply_filters('pwgc_to_order_currency', $gift_card_total * -1, $order);
                $gift_card_numbers = implode(', ', $gift_cards_redeemed);

                $row["AccountNumber"] = get_option('fortnox_pwgc_giftcard_sales_account');
                $row["DeliveredQuantity"] = 1;
                $row["Price"] = $price;
                $row["ArticleNumber"] = 'API_BLANK';
                $row["Description"] = WCFH_Util::clean_fortnox_text(sprintf(_n('Gift card %s:', 'Gift cards %s:', count($gift_cards_redeemed), 'pw-woocommerce-gift-cards'), $gift_card_numbers), 50);

                $rows[$rowtype][] = $row;
            }

            return $rows;
        }

    }

    new Fortnox_Hub_PWGC();
}
