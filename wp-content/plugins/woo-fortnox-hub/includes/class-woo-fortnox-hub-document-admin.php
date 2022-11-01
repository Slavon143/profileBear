<?php

/**
 * This class contains common functions for creating invoices and orders
 *
 * @package   Woo_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Document_Admin', false)) {

    class Woo_Fortnox_Hub_Document_Admin
    {

        public function __construct()
        {

            add_filter('manage_edit-shop_order_columns', array($this, 'document_number_header'), 20);
            add_action('manage_shop_order_posts_custom_column', array($this, 'invoice_number_content'));
            add_action('wp_ajax_fortnox_sync', array($this, 'sync_single_order'));
            add_filter('handle_bulk_actions-edit-shop_order', array($this, 'handle_bulk_actions'), 10, 3);
            add_filter('bulk_actions-edit-shop_order', array($this, 'define_bulk_actions'));
            add_action('woo_fortnox_hub_sync_order_manually', array($this, 'sync_order_manually'));

            if (!wc_string_to_bool(get_option('fortnox_hide_admin_order_meta'))) {
                add_action('woocommerce_admin_order_data_after_order_details', array($this, 'order_meta_general'));
            }

        }

        public function order_meta_general($order)
        {

            $ocr = $order->get_meta('fortnox_invoice_ocr', true);
            echo '<br class="clear" />';
            echo '<h4>Fortnox</h4>';
            echo '<div class="address">';
            echo '<p><strong>OCR: </strong>' . $ocr . '</p>';
            echo '</div>';

        }

        public function document_number_header($columns)
        {

            $creates = get_option('fortnox_woo_order_creates');
            $use_woocommerce_order_number = 'yes' == get_option('fortnox_use_woocommerce_order_number');

            $new_columns = array();

            foreach ($columns as $column_name => $column_info) {

                $new_columns[$column_name] = $column_info;

                if ('order_number' === $column_name) {

                    if (('invoice' == $creates && !$use_woocommerce_order_number) || ('order' == $creates && $use_woocommerce_order_number)) {
                        $new_columns['fortnox_invoice_number'] = __('Fortnox Invoice', 'woo-fortnox-hub');
                    } elseif ('order' == $creates) {
                        $new_columns['fortnox_order_number'] = __('Fortnox Order/Invoice', 'woo-fortnox-hub');
                    }

                    $new_columns['fortnox_sync_document'] = __('Fortnox', 'woo-fortnox-hub');

                }
            }

            return $new_columns;

        }

        public function refunded_not_synced($order)
        {
            if (!empty($refunds = $order->get_refunds())) {
                $refund = reset($refunds);
                $refund_id = $refund->get_id();
                $fn_invoice_number = WCFH_Util::get_fortnox_invoice_number($refund_id);
                if (!$fn_invoice_number) {
                    return $refund_id;
                }
            }
            return false;
        }

        public function invoice_number_content($column)
        {
            global $post;

            if ('fortnox_invoice_number' == $column || 'fortnox_order_number' == $column) {
                $fn_invoice = WCFH_Util::get_fortnox_invoice_number($post->ID);

                if ('fortnox_invoice_number' == $column) {
                    echo sprintf('%s', $fn_invoice ? $fn_invoice : '-');
                }

                if ('fortnox_order_number' == $column) {
                    $fn_order = WCFH_Util::get_fortnox_order_documentnumber($post->ID);
                    echo sprintf('%s/%s', $fn_order ? $fn_order : '-', $fn_invoice ? $fn_invoice : '-');
                }
            }

            if ('fortnox_sync_document' === $column) {
                $order = wc_get_order($post->ID);
                $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);

                $already_synced = false;
                if ('order' === $wc_order_creates) {
                    $already_synced = WCFH_Util::get_fortnox_order_documentnumber($post->ID);
                } elseif ('invoice' === $wc_order_creates) {
                    $already_synced = WCFH_Util::get_fortnox_invoice_number($post->ID);
                } elseif ('stockchange' === $wc_order_creates) {
                    $already_synced = $order->get_meta('_fortnox_stockchange_timestamp', true);
                }

                if ($already_synced) {
                    echo '<a class="button button wc-action-button fortnox sync" data-order-id="' . esc_html($order->get_id()) . '">Resync</a>';
                } else {
                    echo '<a class="button button wc-action-button fortnox sync" data-order-id="' . esc_html($order->get_id()) . '">Sync</a>';
                }

            }
        }

        public function sync_order_manually($order_id)
        {

            $order = wc_get_order($order_id);
            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);

            if ('order' === $wc_order_creates) {

                if ('cancelled' == $order->get_status('edit')) {
                    do_action('woo_fortnox_hub_cancelled_order', $order_id);
                    return;
                }

                if (!WCFH_Util::get_fortnox_invoice_number($order_id)) {
                    do_action('woo_fortnox_hub_processing_order', $order_id);
                }

            } elseif ('invoice' === $wc_order_creates) {

                if ('cancelled' == $order->get_status('edit')) {
                    do_action('woo_fortnox_hub_cancelled_invoice', $order_id);
                    return;
                }

                do_action('woo_fortnox_hub_processing_invoice', $order_id);

            } elseif ('stockchange' === $wc_order_creates) {

                if ('cancelled' == $order->get_status('edit')) {
                    do_action('woo_fortnox_hub_cancelled_stockchange', $order_id);
                    return;
                }

                do_action('woo_fortnox_hub_processing_stockchange', $order_id);

            }

            $check_invoices = wc_string_to_bool(get_option('fortnox_check_invoices_automatically'));

            if ($refund_id = $this->refunded_not_synced($order)) {

                if ($order->get_remaining_refund_amount() > 0 || ($order->has_free_item() && $order->get_remaining_refund_items() > 0)) {

                    if (in_array($wc_order_creates, array('order', 'invoice'))) {
                        do_action('woo_fortnox_hub_partially_refunded_invoice', $order_id, $refund_id);
                    } elseif ('stockchange' === $wc_order_creates) {
                        do_action('woo_fortnox_hub_partially_refunded_stockchange', $order_id, $refund_id);
                    }

                } else {

                    if (in_array($wc_order_creates, array('order', 'invoice'))) {
                        do_action('woo_fortnox_hub_fully_refunded_invoice', $order_id, $refund_id);
                    } elseif ('stockchange' === $wc_order_creates) {
                        do_action('woo_fortnox_hub_fully_refunded_stockchange', $order_id, $refund_id);
                    }

                }

            } elseif (($refund_number = WCFH_Util::get_fortnox_invoice_number($refund_id)) && $check_invoices) {
                do_action('fortnox_process_changed_invoices', $refund_number);
            }

            if (($invoice_number = WCFH_Util::get_fortnox_invoice_number($order_id)) && $check_invoices) {
                do_action('fortnox_process_changed_invoices', $invoice_number);
            }

        }

        public function sync_single_order()
        {

            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            $order_id = sanitize_key($_POST['order_id']);

            WC_FH()->logger->add(sprintf('sync_single_order (%s): Order sync requested', $order_id));

            do_action('woo_fortnox_hub_sync_order_manually', $order_id);

            wp_send_json_success();

        }

        public function handle_bulk_actions($redirect_to, $action, $ids)
        {
            if ('fortnox_sync_order' == $action) {
                foreach (array_reverse($ids) as $order_id) {
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_sync_order_manually', array($order_id));
                }
            }
            return esc_url_raw($redirect_to);
        }

        public function define_bulk_actions($actions)
        {
            $actions['fortnox_sync_order'] = __('Sync Order to Fortnox', 'woo-fortnox-hub');
            return $actions;
        }

    }

    new Woo_Fortnox_Hub_Document_Admin();
}
