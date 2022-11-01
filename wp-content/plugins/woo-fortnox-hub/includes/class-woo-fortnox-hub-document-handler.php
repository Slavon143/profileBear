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

if (!class_exists('Woo_Fortnox_Hub_Document_Handler', false)) {

    class Woo_Fortnox_Hub_Document_Handler
    {

        public function __construct()
        {

            if ($order_status = get_option('fortnox_woo_order_create_automatic_from')) {

                add_action('woocommerce_order_status_' . $order_status, array($this, 'processing_document'), 30);
                add_action('woocommerce_order_status_cancelled', array($this, 'cancelled_document'), 20);
                add_action('woocommerce_order_status_completed', array($this, 'completed_document'), 40);
                add_action('woocommerce_order_fully_refunded', array($this, 'fully_refunded_document'), 20, 2);
                add_action('woocommerce_order_partially_refunded', array($this, 'partially_refunded_document'), 20, 2);

                if ('yes' == get_option('fortnox_delay_emails_until_processed')) {
                    add_filter('woocommerce_defer_transactional_emails', '__return_true');
                    add_filter('woocommerce_allow_send_queued_transactional_email', array($this, 'queue_deferred_woocommerce_email'), 10, 3);
                    add_action('fortnox_maybe_send_deferred_woocommerce_email', array($this, 'maybe_send_deferred_woocommerce_email'), 10, 4);
                }

                if ($invoice_status = get_option('fortnox_create_invoice_from_order')) {
                    add_action('woocommerce_order_status_' . $invoice_status, array($this, 'processing_document_order'), 100);
                }
            }

            if (wc_string_to_bool(get_option('fortnox_create_invoice_from_order_payment_method'))) {
                add_filter('fortnox_wc_order_creates',array($this,'maybe_switch_to_invoice'), 10, 2);
            }

            add_filter('fortnox_hub_filter_woocommerce_order', array($this, 'filter_woocommerce_order'), 10, 3);
        }

        public function processing_document($order_id)
        {

            $order = wc_get_order($order_id);
            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
            WC_FH()->logger->add(sprintf('processing_document (%s): Processing document by %s', $order_id, $wc_order_creates));

            if ('order' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {
                    WC_FH()->logger->add(sprintf('processing_document (%s): Creating Fortnox Order', $order_id));
                    do_action('woo_fortnox_hub_processing_order', $order_id);
                } else {
                    WC_FH()->logger->add(sprintf('processing_document (%s): Queuing creation of Fortnox Order', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_processing_order', array($order_id));
                }
            } elseif ('invoice' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {
                    WC_FH()->logger->add(sprintf('processing_document (%s): Creating Fortnox Invoice', $order_id));
                    do_action('woo_fortnox_hub_processing_invoice', $order_id);
                } else {
                    WC_FH()->logger->add(sprintf('processing_document (%s): Queuing creation of Fortnox Invoice', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_processing_invoice', array($order_id));
                }
            } elseif ('stockchange' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {
                    WC_FH()->logger->add(sprintf('processing_document (%s): Changing stocklevel in Fortnox', $order_id));
                    do_action('woo_fortnox_hub_processing_stockchange', $order_id);
                } else {
                    WC_FH()->logger->add(sprintf('processing_document (%s): Queuing changing stocklevel in Fortnox', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_processing_stockchange', array($order_id));
                }
            }
        }

        public function processing_document_order($order_id)
        {

            $order = wc_get_order($order_id);
            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
            WC_FH()->logger->add(sprintf('processing_document_order (%s): Processing document order by %s', $order_id, $wc_order_creates));

            if ('order' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {
                    WC_FH()->logger->add(sprintf('processing_document_order (%s): Finishing Fortnox Order', $order_id));
                    do_action('woo_fortnox_hub_finish_order', $order_id);
                } else {
                    WC_FH()->logger->add(sprintf('processing_document_order (%s): Queuing finishing of Fortnox Order', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_finish_order', array($order_id));
                }
            }
        }

        public function cancelled_document($order_id)
        {

            $order = wc_get_order($order_id);
            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
            WC_FH()->logger->add(sprintf('cancelled_document (%s): Cancel order by %s', $order_id, $wc_order_creates));

            if ('order' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {
                    WC_FH()->logger->add(sprintf('cancelled_document (%s): Cancel Fortnox Order', $order_id));
                    do_action('woo_fortnox_hub_cancelled_order', $order_id);
                } else {
                    WC_FH()->logger->add(sprintf('cancelled_document (%s): Queuing cancellation of Fortnox Order', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_cancelled_order', array($order_id));
                }
            } elseif ('invoice' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {
                    WC_FH()->logger->add(sprintf('cancelled_document (%s): Cancel Fortnox Invoice', $order_id));
                    do_action('woo_fortnox_hub_cancelled_invoice', $order_id);
                } else {
                    WC_FH()->logger->add(sprintf('cancelled_document (%s): Queuing cancellation of Fortnox Invoice', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_cancelled_invoice', array($order_id));
                }
            } elseif ('stockchange' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {
                    WC_FH()->logger->add(sprintf('cancelled_document (%s): Cancel stocklevel in Fortnox', $order_id));
                    do_action('woo_fortnox_hub_cancelled_stockchange', $order_id);
                } else {
                    WC_FH()->logger->add(sprintf('cancelled_document (%s): Queuing cancel stocklevel in Fortnox', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_cancelled_stockchange', array($order_id));
                }
            }
        }

        public function fully_refunded_document($order_id, $refund_id)
        {

            $order = wc_get_order($order_id);
            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
            WC_FH()->logger->add(sprintf('fully_refunded_document (%s): Fully refund order by %s', $order_id, $wc_order_creates));

            if (in_array($wc_order_creates, array('order', 'invoice'))) {

                if (WCFH_Util::do_not_queue_requests()) {

                    WC_FH()->logger->add(sprintf('fully_refunded_document (%s): Full refund of Fortnox Invoice', $order_id));
                    do_action('woo_fortnox_hub_fully_refunded_invoice', $order_id, $refund_id);
                } else {

                    WC_FH()->logger->add(sprintf('fully_refunded_document (%s): Queuing full refund of Fortnox Invoice', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_fully_refunded_invoice', array($order_id, $refund_id));
                }
            } elseif ('stockchange' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {

                    WC_FH()->logger->add(sprintf('fully_refunded_document (%s): Queuing full refund of stockchange', $order_id));
                    do_action('woo_fortnox_hub_fully_refunded_stockchange', $order_id, $refund_id);
                } else {

                    WC_FH()->logger->add(sprintf('fully_refunded_document (%s): Queuing full refund of stockchange', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_fully_refunded_stockchange', array($order_id, $refund_id));
                }
            }
        }

        public function partially_refunded_document($order_id, $refund_id)
        {

            $order = wc_get_order($order_id);
            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
            WC_FH()->logger->add(sprintf('partially_refunded_document (%s): Partially refund order by %s', $order_id, $wc_order_creates));

            if (in_array($wc_order_creates, array('order', 'invoice'))) {

                if (WCFH_Util::do_not_queue_requests()) {

                    WC_FH()->logger->add(sprintf('partially_refunded_document (%s): Partial refund of Fortnox Invoice', $order_id));
                    do_action('woo_fortnox_hub_partially_refunded_invoice', $order_id, $refund_id);

                } else {

                    WC_FH()->logger->add(sprintf('partially_refunded_document (%s): Queuing partial refund of Fortnox Invoice', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_partially_refunded_invoice', array($order_id, $refund_id));

                }

            } elseif ('stockchange' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {

                    WC_FH()->logger->add(sprintf('partially_refunded_document (%s): Partial refund stockchange', $order_id));
                    do_action('woo_fortnox_hub_partially_refunded_stockchange', $order_id, $refund_id);

                } else {

                    WC_FH()->logger->add(sprintf('partially_refunded_document (%s): Queuing partial refund of stockchange', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_partially_refunded_stockchange', array($order_id, $refund_id));

                }

            }

        }

        public function completed_document($order_id)
        {

            $order = wc_get_order($order_id);
            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
            WC_FH()->logger->add(sprintf('completed_document (%s): Completed order by %s', $order_id, $wc_order_creates));

            if ('order' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {
                    WC_FH()->logger->add(sprintf('completed_document (%s): Completing Fortnox Order', $order_id));
                    do_action('woo_fortnox_hub_completing_order', $order_id);
                } else {
                    WC_FH()->logger->add(sprintf('completed_document (%s): Queuing completion of Fortnox Order', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_completing_order', array($order_id));
                }
            } elseif ('invoice' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {
                    WC_FH()->logger->add(sprintf('completed_document (%s): Completing Fortnox Invoice', $order_id));
                    do_action('woo_fortnox_hub_completing_invoice', $order_id);
                } else {
                    WC_FH()->logger->add(sprintf('completed_document (%s): Queuing completion of Fortnox Invoice', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_completing_invoice', array($order_id));
                }
            } elseif ('stockchange' === $wc_order_creates) {

                if (WCFH_Util::do_not_queue_requests()) {
                    WC_FH()->logger->add(sprintf('processing_document (%s): Completing stocklevel in Fortnox', $order_id));
                    do_action('woo_fortnox_hub_completing_stockchange', $order_id);
                } else {
                    WC_FH()->logger->add(sprintf('processing_document (%s): Queuing completion stocklevel in Fortnox', $order_id));
                    as_schedule_single_action(as_get_datetime_object(), 'woo_fortnox_hub_completing_stockchange', array($order_id));
                }
            }
        }

        public function queue_deferred_woocommerce_email($true, $filter, $args)
        {

            $order_id = $args[0];

            WC_FH()->logger->add(sprintf('queue_deferred_woocommerce_email (%s): Queuing email', $order_id));
            $this->queue_woocommerce_email($order_id, $filter, $args);

            return false;
        }

        public function queue_woocommerce_email($order_id, $filter, $args, $retries = 0)
        {
            as_schedule_single_action(as_get_datetime_object(current_time('timestamp', true) + (MINUTE_IN_SECONDS * get_option('fortnox_delay_emails_delay_time', 1))), 'fortnox_maybe_send_deferred_woocommerce_email', array($order_id, $filter, $args, $retries));
        }

        public function send_email($filter, $args)
        {
            remove_filter('woocommerce_allow_send_queued_transactional_email', array($this, 'queue_deferred_woocommerce_email'), 10);
            WC_Emails::send_queued_transactional_email($filter, $args);
            add_filter('woocommerce_allow_send_queued_transactional_email', array($this, 'queue_deferred_woocommerce_email'), 10, 3);
        }

        public function maybe_send_deferred_woocommerce_email($order_id, $filter, $args, $retries)
        {

            $order = wc_get_order($order_id);
            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
            $fortnox_document_number = false;

            if ('order' === $wc_order_creates) {
                $fortnox_document_number = WCFH_Util::get_fortnox_order_documentnumber($order_id);
            } elseif ('invoice' === $wc_order_creates) {
                $fortnox_document_number = WCFH_Util::get_fortnox_invoice_number($order_id);
            }

            if ($fortnox_document_number) {
                $this->send_email($filter, $args);
                WC_FH()->logger->add(sprintf('maybe_send_deferred_woocommerce_email (%s): email sent', $order_id));
            } elseif ($retries < get_option('fortnox_delay_emails_max_retries', 1)) {
                $this->queue_woocommerce_email($order_id, $filter, $args, $retries + 1);
                WC_FH()->logger->add(sprintf('maybe_send_deferred_woocommerce_email (%s): requeuing email', $order_id));
            } else {
                $this->send_email($filter, $args);
                WC_FH()->logger->add(sprintf('maybe_send_deferred_woocommerce_email (%s): email requeued maximum number of times (%s), email sent.', $order_id, $retries));
            }
        }

        public function get_temp_path()
        {
            $path = ini_get('upload_tmp_dir');
            if (!empty($path)) {
                return trailingslashit($path);
            }
            return trailingslashit(sys_get_temp_dir());
        }

        public function attach_invoice_pdf($attachments, $email_id, $order)
        {

            if (!is_a($order, 'WC_Order') || !isset($email_id)) {
                return $attachments;
            }

            if ('customer_processing_order' == $email_id) {
                $order_id = $order->get_id();
                $invoicenumber = WCFH_Util::get_fortnox_invoice_number($order_id);
                $pdffile = WC_FH()->fortnox->getInvoicePDF($invoicenumber);
                $filename = $this->get_temp_path() . uniqid($order_id) . '.pdf';
                $fp = fopen($filename, "w");
                fwrite($fp, $pdffile);
                fclose($fp);
                $attachments[] = $filename;
            }

            return $attachments;
        }

        public function filter_woocommerce_order($response, $type, $order_id)
        {

            $order = wc_get_order($order_id);
            $payment_method = WCFH_Util::get_payment_method($order, 'filter_woocommerce_order');

            if (('yes' === get_option('fortnox_do_not_sync_' . $payment_method))) {
                WC_FH()->logger->add(sprintf('filter_woocommerce_order (%s): Do not sync order with payment method "%s" at "%s"', $order_id, $payment_method, $type));
                $response = false;
            }

            return $response;
        }

        public function maybe_switch_to_invoice($fortnox_order_creates,$order){;
            $payment_method = WCFH_Util::get_payment_method($order, 'maybe_switch_to_invoice');

            if (wc_string_to_bool(get_option('fortnox_create_invoice_from_order_' . $payment_method))) {
                $fortnox_order_creates = 'invoice';
            }

            return $fortnox_order_creates;

        }
    }

    new Woo_Fortnox_Hub_Document_Handler();
}
