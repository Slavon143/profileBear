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

if (!class_exists('Woo_Fortnox_Hub_Refund', false)) {
    require_once plugin_dir_path(self::PLUGIN_FILE) . 'includes/class-woo-fortnox-hub-document.php';

    class Woo_Fortnox_Hub_Refund extends Woo_Fortnox_Hub_Document
    {
        public function __construct()
        {
            add_action('woo_fortnox_hub_fully_refunded_invoice', array($this, 'fully_refunded_invoice'), 10, 2);
            add_action('woo_fortnox_hub_partially_refunded_invoice', array($this, 'partially_refunded_invoice'), 10, 2);
            add_action('woocommerce_order_item_add_action_buttons', array($this, 'display_one_time_refund_only'));

            parent::__construct();
        }

        public function fully_refunded_invoice($order_id, $refund_id)
        {

            if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'fully_refunded_invoice', $order_id)) {
                return;
            }

            $order = wc_get_order($order_id);

            WC_FH()->logger->add(sprintf('fully_refunded_invoice (%s): Processing fully refund order %s', $order_id, $refund_id));

            try {
                $refund_invoice_number = WCFH_Util::get_fortnox_invoice_number($refund_id);

                if ($refund_invoice_number) {
                    WC_FH()->logger->add(sprintf('fully_refunded_invoice (%s): Refund %s already created Fortnox credit invoice %s', $order_id, $refund_id, $refund_invoice_number));
                    return;
                }

                $invoice_number = WCFH_Util::get_fortnox_invoice_number($order_id);

                if ($invoice_number) {
                    $fn_invoice = WC_FH()->fortnox->get_invoice($invoice_number);

                    if (rest_sanitize_boolean($fn_invoice['Cancelled'])) {
                        WC_FH()->logger->add(sprintf('fully_refunded_invoice (%s): Fortnox invoice %s already cancelled', $order_id, $invoice_number));
                        return;
                    }

                    if ($fn_invoice['CreditInvoiceReference']) {
                        WC_FH()->logger->add(sprintf('fully_refunded_invoice (%s): Fortnox invoice %s already credited with credit invoice %s', $order_id, $invoice_number, $fn_invoice['CreditInvoiceReference']));
                        return;
                    }

                    if (!$order->get_date_paid()) {
                        if ('CASH' === $fn_invoice['AccountingMethod'] && !rest_sanitize_boolean($fn_invoice['Sent']) && !$fn_invoice['FinalPayDate']) {
                            WC_FH()->fortnox->cancel_invoice($invoice_number);
                            WC_FH()->logger->add(sprintf('fully_refunded_invoice (%s): Fortnox invoice %s was not sent or paid and have been cancelled', $order_id, $invoice_number));
                            return;
                        }

                        if ('ACCRUAL' === $fn_invoice['AccountingMethod'] && !rest_sanitize_boolean($fn_invoice['Booked'])) {
                            WC_FH()->fortnox->cancel_invoice($invoice_number);
                            WC_FH()->logger->add(sprintf('fully_refunded_invoice (%s): Fortnox invoice %s was not booked and have been cancelled', $order_id, $invoice_number));
                            return;
                        }
                    }

                    if ('ACCRUAL' === $fn_invoice['AccountingMethod'] && !rest_sanitize_boolean($fn_invoice['Booked'])) {
                        WC_FH()->fortnox->book_invoice($fn_invoice['DocumentNumber']);
                        WC_FH()->logger->add(sprintf('fully_refunded_invoice (%s): Fortnox invoice %s have been booked to prepare for credit', $order_id, $fn_invoice['DocumentNumber']));
                    }

                    WC_FH()->fortnox->credit_invoice($fn_invoice['DocumentNumber']);
                    $fn_invoice = WC_FH()->fortnox->get_invoice($fn_invoice['DocumentNumber']);

                    $full_invoice["ExternalInvoiceReference2"] = WCFH_Util::encode_external_reference($refund_id);
                    $fn_credit_invoice = WC_FH()->fortnox->updateInvoice($fn_invoice['CreditInvoiceReference'], $full_invoice);

                    WCFH_Util::set_fortnox_invoice_number($refund_id, $fn_invoice['CreditInvoiceReference']);

                    WC_FH()->logger->add(sprintf('fully_refunded_invoice (%s): Order credited Fortnox invoice %s with refund order %s creating credit invoice %s', $order_id, $invoice_number, $refund_id, $fn_invoice['CreditInvoiceReference']));

                    if (wc_string_to_bool(get_option('fortnox_set_warehouseready'))) {
                        do_action('fortnox_trigger_warehouse_ready', $order_id, $fn_invoice['CreditInvoiceReference']);
                    }

                    WC_FH()->logger->add(json_encode($fn_credit_invoice, JSON_INVALID_UTF8_IGNORE));

                    return;
                }

                $order_number = WCFH_Util::get_fortnox_order_documentnumber($order_id);

                if ($order_number) {
                    $fn_order = WC_FH()->fortnox->getOrder($order_number);

                    if (rest_sanitize_boolean($fn_order['Cancelled'])) {
                        WC_FH()->logger->add(sprintf('fully_refunded_invoice (%s): Fortnox order %s already cancelled', $order_id, $order_number));
                        return;
                    }

                    WC_FH()->fortnox->update_order($order_number, array(
                        "Comments" => sprintf(__('Refunded by WooCommerce refund %s', 'woo-fortnox-hub'), $refund_id),
                    ));
                    WC_FH()->fortnox->cancel_order($order_number);
                    WC_FH()->logger->add(sprintf('fully_refunded_invoice (%s): Cancelled Fortnox order %s', $order_id, $order_number));
                }

            } catch (Fortnox_API_Exception $e) {
                $e->write_to_logs();
                Fortnox_Notice::add(sprintf(__("%s when refunding order %s fully", 'woo-fortnox-hub'), $e->getMessage(), $order->get_order_number()));
            }

        }

        public function partially_refunded_invoice($order_id, $refund_id)
        {
            if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'partially_refunded_invoice', $order_id)) {
                return;
            }

            WC_FH()->logger->add(sprintf('partially_refunded_invoice (%s): Processing partial refund order %s', $order_id, $refund_id));

            try {

                $order = wc_get_order($refund_id);

                $invoice_number = WCFH_Util::get_fortnox_invoice_number($order_id);
                if (!$invoice_number) {
                    return;
                }
                $fn_invoice = WC_FH()->fortnox->get_invoice($invoice_number);

                $refund_invoice_number = WCFH_Util::get_fortnox_invoice_number($refund_id);
                if ($refund_invoice_number || $fn_invoice['CreditInvoiceReference']) {
                    WC_FH()->logger->add(sprintf('partially_refunded_invoice (%s): Fortnox invoice %s already credited with credit invoice %s', $order_id, $invoice_number, $fn_invoice['CreditInvoiceReference']));
                    return;
                }

                if ('ACCRUAL' === $fn_invoice['AccountingMethod'] && !rest_sanitize_boolean($fn_invoice['Booked'])) {
                    WC_FH()->fortnox->book_invoice($fn_invoice['DocumentNumber']);
                    WC_FH()->logger->add(sprintf('partially_refunded_invoice (%s): Fortnox invoice %s have been booked to prepare for credit', $order_id, $invoice_number));
                }

                WC_FH()->fortnox->credit_invoice($invoice_number);

                $original_invoice = WC_FH()->fortnox->get_invoice($invoice_number);
                $credit_invoice = WC_FH()->fortnox->get_invoice($original_invoice['CreditInvoiceReference']);

                $invoice_details = array();

                $invoice_items = $this->get_items($order, 'InvoiceRows', true);

                $fee_items = $this->get_fee_items($order, 'InvoiceRows', true);

                if (wc_string_to_bool(get_option('fortnox_document_use_shipping_field'))) {
                    $shipping_items = array();
                    $invoice_details = array_merge($invoice_details, $this->get_shipping_cost($order));
                } else {
                    $shipping_items = $this->get_shipping_items($order, 'InvoiceRows', true);
                }

                $full_invoice = array_merge_recursive($invoice_details, $invoice_items, $fee_items, $shipping_items);
                $full_invoice["ExternalInvoiceReference2"] = WCFH_Util::encode_external_reference($refund_id);

                $fn_credit_invoice = WC_FH()->fortnox->updateInvoice($original_invoice['CreditInvoiceReference'], $full_invoice);

                WCFH_Util::set_fortnox_invoice_number($refund_id, $original_invoice['CreditInvoiceReference']);

                WC_FH()->logger->add(sprintf(__('partially_refunded_invoice (%s): Order partly refunded Fortnox invoice %s with refund order %s creating credit invoice %s', 'woo-fortnox-hub'), $order_id, $invoice_number, $refund_id, $original_invoice['CreditInvoiceReference']));

                if (wc_string_to_bool(get_option('fortnox_set_warehouseready'))) {
                    do_action('fortnox_trigger_warehouse_ready', $order_id, $fn_invoice['CreditInvoiceReference']);
                }

                WC_FH()->logger->add(json_encode($fn_credit_invoice, JSON_INVALID_UTF8_IGNORE));

            } catch (Fortnox_API_Exception $e) {
                $e->write_to_logs();
                Fortnox_Notice::add(sprintf(__("%s when partally refunding order %s", 'woo-fortnox-hub'), $e->getMessage(), $order->get_order_number()));
            }

        }

        public function display_one_time_refund_only($order)
        {

            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);

            if (in_array($wc_order_creates, array('order', 'invoice'))) {
                if ($order->get_total_refunded()) {
                    ?><p class="fortnox-refund-warning"> <?php esc_html_e("This order has been refunded once. Additional refunds will not be synced to Fortnox", 'woo-fortnox-hub');?> </p><?php
}
            }
        }
    }

    new Woo_Fortnox_Hub_Refund();
}
