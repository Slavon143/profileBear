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

if (!class_exists('Fortnox_Hub_YWGC', false)) {

    class Fortnox_Hub_YWGC
    {

        public function __construct()
        {

            add_filter('fortnox_get_sections', array($this, 'add_settings_section'), 200);
            add_filter('woocommerce_get_settings_fortnox_hub', array($this, 'get_settings'), 70, 2);
            add_filter('fortnox_after_get_order_item', array($this, 'maybe_enrich_ywgc_order_item'), 10, 3);
            add_filter('fortnox_after_get_fee_items', array($this, 'maybe_enrich_ywgc_fee_item'), 10, 3);

        }

        public function add_settings_section($sections)
        {
            if (!array_key_exists('ywgc_options', $sections)) {
                $sections = array_merge($sections, array('ywgc_options' => __('Gift Cards', 'woo-fortnox-hub')));
            }
            return $sections;
        }

        public function get_settings($settings, $current_section)
        {
            if ('ywgc_options' === $current_section) {

                $account_selection = apply_filters('fortnox_get_account_selection', array());

                $settings = array(
                    array(
                        'title' => __('Gift card options', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => __('', 'woo-fortnox-hub'),
                        'id' => 'fortnox_ywgc_options',
                    ),
                    array(
                        'title' => __('Gift card sales account', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => '',
                        'options' => $account_selection,
                        'id' => 'fortnox_ywgc_giftcard_sales_account',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_ywgc_options',
                    ),
                );
            }
            return $settings;
        }

        /**
         * Check if the order item is a gift card purchase and enrich the order item
         *
         * @since 4.1.0
         *
         * @param array $row
         * @param WC_Order_Item $item
         * @param WC_Order $order
         *
         * @return array
         */
        public function maybe_enrich_ywgc_order_item($row, $item, $order)
        {

            $item_id = $item->get_id();

            if ($gift_ids = wc_get_order_item_meta($item_id, '_ywgc_gift_card_code')) {

                $row["Description"] = $item->get_name() . ' #' . implode('#', $gift_ids);
                $row["AccountNumber"] = get_option('fortnox_ywgc_giftcard_sales_account');

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
        public function maybe_enrich_ywgc_fee_item($row, $item, $order)
        {

            $order_id = $order->get_id();

            if (get_post_meta($order_id, 'ywgc_gift_card_updated_as_fee', true)) {

                $item_id = $item->get_id();

                if ($item_id === '_ywgc_fee') {

                    $row["AccountNumber"] = get_option('fortnox_ywgc_giftcard_sales_account');

                }

            }
            return WCFH_Util::remove_blanks($row);

        }

    }

    new Fortnox_Hub_YWGC();
}
