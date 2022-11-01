<?php

/**
 * This class contains common functions for creating Fortnox orders
 *
 * @package   Woo_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Order', false)) {

    require_once plugin_dir_path(self::PLUGIN_FILE) . 'includes/class-woo-fortnox-hub-document.php';

    class Woo_Fortnox_Hub_Order extends Woo_Fortnox_Hub_Document
    {

        public function __construct()
        {

            add_action('woo_fortnox_hub_processing_order', array($this, 'process_order'));
            add_action('woo_fortnox_hub_cancelled_order', array($this, 'cancel_order'));
            add_action('woo_fortnox_hub_finish_order', array($this, 'finish_order'));
            add_action('woo_fortnox_hub_completing_order', array($this, 'complete_order'));
            
            parent::__construct();
        }

        /**
         * Processing WooCommerce order and creates or updates Fortnox Order
         *
         * @param $order_id
         */

        public function process_order($order_id)
        {

            try {

                if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'process_order', $order_id)) {
                    return;
                }

                $order = wc_get_order($order_id);

                $document_number = WCFH_Util::get_fortnox_order_documentnumber($order_id);

                if ($document_number) {
                    $fn_order = WC_FH()->fortnox->get_order($document_number);
                } elseif (!wc_string_to_bool(get_option('fortnox_do_not_use_external_refs'))) {
                    $fn_order = WCFH_Util::check_if_order_already_created(WCFH_Util::encode_external_reference($order_id), false);
                    if ($fn_order) {
                        WCFH_Util::set_fortnox_order_documentnumber($order_id, $fn_order['DocumentNumber']);
                    }
                }

                if ($fn_order) {

                    if ($fn_order['InvoiceReference']) {
                        WC_FH()->logger->add(sprintf('process_order (%s): Fortnox Order %s already created Fortnox Invoice %s and can not be updated', $order_id, $fn_order['DocumentNumber'], $fn_order['InvoiceReference']));
                        WCFH_Util::set_fortnox_invoice_number($order_id, $fn_order['InvoiceReference']);
                        return;
                    }

                    if (rest_sanitize_boolean($fn_order['Cancelled'])) {
                        WC_FH()->logger->add(sprintf('process_order (%s): Fortnox order %s has been cancelled and can not be updated', $order_id, $fn_order['DocumentNumber']));
                        return;
                    }
                }

                do_action('woo_fortnox_hub_create_customer', $order_id, true);

                $order_details = $this->get_details($order);

                $order_items = $this->get_items($order);

                $fee_items = $this->get_fee_items($order);

                if (wc_string_to_bool(get_option('fortnox_document_use_shipping_field'))) {
                    $shipping_items = array();
                    $order_details = array_merge($order_details, $this->get_shipping_cost($order));
                } else {
                    $shipping_items = $this->get_shipping_items($order);
                }

                $coupon_items = $this->get_coupon_items($order);

                $full_order = apply_filters('fortnox_before_processing_order', array_merge_recursive($order_details, $order_items, $fee_items, $shipping_items, $coupon_items), $order);

                if ($fn_order) {

                    $fn_order = WC_FH()->fortnox->update_order($fn_order['DocumentNumber'], array(
                        'OrderRows' => array(),
                    ));

                    unset($full_order['DocumentNumber']);
                    $fn_order = WC_FH()->fortnox->update_order($fn_order['DocumentNumber'], $full_order);
                    WC_FH()->logger->add(sprintf(__('WooCommerce order %s updated Fortnox order %s', 'woo-fortnox-hub'), $order_id, $fn_order['DocumentNumber']));
                } else {

                    $fn_order = WC_FH()->fortnox->create_order($full_order);
                    WC_FH()->logger->add(sprintf(__('WooCommerce order %s created Fortnox order %s', 'woo-fortnox-hub'), $order_id, $fn_order['DocumentNumber']));
                }

                WCFH_Util::set_fortnox_order_documentnumber($order_id, $fn_order['DocumentNumber']);

                WC_FH()->logger->add(json_encode($full_order, JSON_INVALID_UTF8_IGNORE));

                $payment_method = WCFH_Util::get_payment_method($order, 'process_order');

                if (wc_string_to_bool(get_option('fortnox_set_warehouseready'))) {
                    do_action('fortnox_trigger_warehouse_ready', $order_id, $fn_order['DocumentNumber'],true);
                }

                $create_invoice_automatically = wc_string_to_bool(get_option('fortnox_create_invoice_from_order_' . $payment_method));

                if (wc_string_to_bool(get_option('fortnox_force_create_order')) && $create_invoice_automatically){
                    WC_FH()->logger->add(sprintf(__('Automatically generating Fortnox invoice from WooCommerce order %s and Fortnox order %s', 'woo-fortnox-hub'), $order_id, $fn_order['DocumentNumber']));
                    do_action('woo_fortnox_hub_finish_order',$order_id);
                }

            } catch (Fortnox_API_Exception $e) {
                $e->write_to_logs();
                Fortnox_Notice::add(sprintf(__("%s when processing order %s", 'woo-fortnox-hub'), $e->getMessage(), $order->get_order_number()));
                do_action('fortnox_order_sync_failed', $e);
            } catch (Fortnox_Exception $e) {
                WC_FH()->logger->add($e->getMessage());
                Fortnox_Notice::add(sprintf(__("%s when processing order %s", 'woo-fortnox-hub'), $e->getMessage(), $order->get_order_number()));
                do_action('fortnox_order_sync_failed', $e);
            }

        }

        /**
         * Cancels Fortnox Order
         *
         * @param $order_id
         */

        public function cancel_order($order_id)
        {

            try {

                if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'cancel_order', $order_id)) {
                    return;
                }

                $order = wc_get_order($order_id);

                WC_FH()->logger->add(sprintf('cancel_order (%s): Cancel WooCommerce order', $order_id));

                if ($fn_order_number = WCFH_Util::get_fortnox_order_documentnumber($order_id)) {

                    $fn_order = WC_FH()->fortnox->getOrder($fn_order_number);

                    if (rest_sanitize_boolean($fn_order['Cancelled'])) {
                        WC_FH()->logger->add(sprintf('cancel_order (%s): Fortnox order %s already cancelled', $order_id, $fn_order['DocumentNumber']));
                        return;
                    }

                    WC_FH()->fortnox->update_order($fn_order['DocumentNumber'], array(
                        "Comments" => __('Cancelled from WooCommerce', 'woo-fortnox-hub'),
                    ));

                    WC_FH()->fortnox->cancel_order($fn_order['DocumentNumber']);
                }

            } catch (Fortnox_API_Exception $e) {
                $e->write_to_logs();
                Fortnox_Notice::add(sprintf(__("%s when processing order %s", 'woo-fortnox-hub'), $e->getMessage(), $order->get_order_number()));
                do_action('fortnox_order_sync_failed', $e);
            }

        }

        /**
         * Finish the Fortnox order -> creates a Fortnox invoice.
         *
         * @param $order_id
         */

        public function finish_order($order_id)
        {

            try {

                if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'finish_order', $order_id)) {
                    return;
                }

                $order = wc_get_order($order_id);

                if ($fn_order_number = WCFH_Util::get_fortnox_order_documentnumber($order_id)) {

                    $fn_order = WC_FH()->fortnox->finish_order($fn_order_number);
                    WCFH_Util::set_fortnox_invoice_number($order_id, $fn_order['InvoiceReference']);

                    $message = sprintf('Fortnox order %s created Fortnox invoice %s', $fn_order_number, $fn_order['InvoiceReference']);

                    $order->set_status('completed', $message);

                    WC_FH()->logger->add(sprintf('finish_order (%s): %s', $order_id, $message));
                }

            } catch (Fortnox_API_Exception $e) {

                if (400 == $e->getCode()) {
                    WC_FH()->logger->add(sprintf('finish_order (%s): %s', $order_id, $e->getMessage()));
                    $fn_order = WC_FH()->fortnox->get_order($fn_order_number);
                    WCFH_Util::set_fortnox_invoice_number($order_id, $fn_order['InvoiceReference']);
                    return;
                }

                $e->write_to_logs();
                Fortnox_Notice::add(sprintf(__("%s when processing order %s", 'woo-fortnox-hub'), $e->getMessage(), $order->get_order_number()));
                do_action('fortnox_order_sync_failed', $e);

            }

        }

        /**
         * Completes Fortnox Order
         *
         * @param $order_id
         */

        public function complete_order($order_id)
        {

            if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'complete_order', $order_id)) {
                return;
            }

            WC_FH()->logger->add(sprintf('complete_order (%s): Start to complete Fortnox Order', $order_id));

            if (!WCFH_Util::get_fortnox_order_documentnumber($order_id)) {

                do_action('woo_fortnox_hub_processing_order', $order_id);
            }

            if ($fortnox_invoice_number = WCFH_Util::get_fortnox_invoice_number($order_id)) {

                if ('yes' == get_option('fortnox_check_invoices_automatically')) {

                    do_action('fortnox_process_changed_invoices', $fortnox_invoice_number);
                }
            }
        }
    }

    new Woo_Fortnox_Hub_Order();
}
