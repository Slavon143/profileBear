<?php

/**
 * This class handles the creation of a Fortnox Order or Invoice array.
 *
 * @package   Woo_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Document', false)) {

    class Woo_Fortnox_Hub_Document
    {

        private $include_tax;
        private $wc_order_creates;

        public function __construct()
        {

            if (function_exists('WC_PB')) {

                $wc_pb_option = get_option('fortnox_wc_product_bundles');

                if (!$wc_pb_option) {
                    add_filter('fortnox_include_product_item', array($this, 'check_product_bundles'), 5, 3);
                } elseif ('remove_price' == $wc_pb_option) {
                    add_filter('fortnox_after_get_order_item', array($this, 'remove_bundled_item_price'), 5, 3);
                }
            }

            $this->include_tax = !wc_string_to_bool(get_option('fortnox_amounts_excl_tax'));
        }

        public function check_product_bundles($return, $item, $order)
        {
            if (wc_pb_is_bundled_order_item($item, $order)) {
                return false;
            }
            return $return;
        }

        public function remove_bundled_item_price($row, $item, $order)
        {
            if (wc_pb_is_bundled_order_item($item, $order)) {
                $row["Price"] = 'API_BLANK';
            }
            return $row;
        }

        public function fortnox_order_number_content($column)
        {
            global $post;
            $order = wc_get_order($post->ID);
            $order_status = $order->get_status();

            if ('fortnox_order_number' === $column) {
                if (($order_number_meta = WCFH_Util::get_fortnox_order_documentnumber($post->ID)) !== false) {
                    echo sprintf('%8s', $order_number_meta);
                }
            }
            $fortnox_invoicenumber = WCFH_Util::get_fortnox_invoice_number($post->ID);
            if ('fortnox_sync_order' === $column && (('cancelled' != $order_status && 'refunded' != $order_status && 'completed' != $order_status) || ('completed' === $order_status && !$fortnox_invoicenumber))) {
                $fortnox_ordernumber = WCFH_Util::get_fortnox_order_documentnumber($post->ID);
                if ($fortnox_ordernumber) {
                    echo '<a class="button button wc-action-button fortnox sync" data-order-id="' . esc_html($order->get_id()) . '">Resync</a>';
                } else {
                    echo '<a class="button button wc-action-button fortnox sync" data-order-id="' . esc_html($order->get_id()) . '">Sync</a>';
                }
            }
        }

        public function document_type($order, $type)
        {
            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
            if ('order' === $wc_order_creates) {
                return 'Order' . $type;
            }
            return 'Invoice' . $type;
        }

        public function is_backorder($product)
        {

            if ($product->is_on_backorder() && wc_string_to_bool(get_option('fortnox_set_backorder_products_to_zero'))) {
                return true;
            }

            return false;
        }

        public function get_items($order, $rowtype = false, $is_credit = false)
        {

            $rows = array();

            $rowtype = $rowtype ? $rowtype : $this->document_type($order, 'Rows');

            $items = $order->get_items();

            $tax_reduction_type = '';

            if (!empty($items)) {

                $rows[$rowtype] = array();

                foreach ($items as $item) {

                    if (apply_filters('fortnox_include_product_item', true, $item, $order)) {

                        $item_id = $item->get_id();

                        $article = false;

                        $row = array();

                        $ordered_qty = ($qty = $item->get_quantity()) ? $qty : 1;
                        $delivered_qty = $ordered_qty;

                        $price = ($price = $order->get_item_total($item, $this->include_tax)) ? $price : $order->get_line_total($item, $this->include_tax);

                        if (wc_get_order_item_meta($item_id, '_woosb_parent_id', true)) {
                            $price = 0;
                        }

                        $article_account = '';

                        if (($product = $item->get_product()) && ($article_number = WCFH_Util::get_fortnox_article_number($product))) {

                            $delivered_qty = $this->is_backorder($product) ? 0 : $delivered_qty;

                            try {

                                $article = WC_FH()->fortnox->get_article($article_number);
                                $article_account = isset($article['SalesAccount']) ? $article['SalesAccount'] : false;
                            } catch (Fortnox_API_Exception $e) {

                                $error_code = $e->getCode();
                                if ((404 == $error_code || 400 == $error_code) && 'error' == get_option('fortnox_no_articlenumber_in_orderrow')) {
                                    throw new $e($e->getMessage(), $e->getCode(), $e);
                                }
                            }

                            if ($reduction_type = $product->get_meta('fortnox_tax_reduction_type', true)) {

                                $reduction_info = explode('_', $reduction_type);
                                $tax_reduction_type = $reduction_info[0];
                                $row["HouseWorkType"] = $reduction_info[1];
                                $row["HouseWork"] = true;

                                if ($reduction_hours = $product->get_meta('fortnox_tax_reduction_hours', true)) {
                                    $row['HouseWorkHoursToReport'] = $reduction_hours;
                                }

                                if ($reduction_price = $product->get_meta('fortnox_tax_reduction_price', true)) {
                                    $price = $reduction_price;
                                }
                            }
                        }

                        $purchase_account = WCFN_Accounts::get_purchase_account($order, $item);

                        $row["AccountNumber"] = $purchase_account ? $purchase_account : $article_account;
                        $row["DeliveredQuantity"] = $delivered_qty;
                        $row["Price"] = $price;

                        if (wc_string_to_bool(get_option('fortnox_set_discount_to_zero', 'yes'))) {
                            $row["Discount"] = 0;
                        }

                        if ($article && 'never' !== get_option('fortnox_no_articlenumber_in_orderrow')) {
                            $row["ArticleNumber"] = $article['ArticleNumber'];
                            $row["Description"] = $article['Description'];
                        } else {
                            $row["ArticleNumber"] = 'API_BLANK';
                            $row["Description"] = WCFH_Util::clean_fortnox_text($item->get_name('edit'), 50);
                        }

                        $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
                        if (!$is_credit && 'order' === $wc_order_creates) {
                            $row["OrderedQuantity"] = $ordered_qty;
                        }

                        $row = apply_filters('fortnox_after_get_order_item', $row, $item, $order);

                        if (is_array($row[0])) {
                            $rows[$rowtype] = array_merge($rows[$rowtype], WCFH_Util::remove_blanks($row));
                        } else {
                            $rows[$rowtype][] = WCFH_Util::remove_blanks($row);
                        }
                    }
                }
            }

            if ($tax_reduction_type) {
                $rows["TaxReductionType"] = $tax_reduction_type;
            }

            return apply_filters('fortnox_after_get_order_items', $rows, $order, $rowtype);
        }

        /**
         * Get order fee items and create a Fortnox order/invoice item
         *
         * @param WC_Order $order
         *
         * @return array
         */
        public function get_fee_items($order, $rowtype = false, $is_credit = false)
        {

            $rows = array();

            $rowtype = $rowtype ? $rowtype : $this->document_type($order, 'Rows');

            $fees = $order->get_fees();

            if (!empty($fees)) {

                foreach ($fees as $fee) {

                    if (apply_filters('fortnox_include_fee_item', true, $fee, $order)) {

                        $row = array(
                            "ArticleNumber" => 'API_BLANK',
                            "Price" => $order->get_line_total($fee, $this->include_tax),
                            "Description" => $fee->get_name(),
                            "DeliveredQuantity" => floatval("1"),
                        );

                        $purchase_account = WCFN_Accounts::get_fee_account($order, $fee);

                        $row["AccountNumber"] = $purchase_account ? $purchase_account : 'API_BLANK';

                        if (wc_string_to_bool(get_option('fortnox_set_discount_to_zero', 'yes'))) {
                            $row["Discount"] = 0;
                        }

                        $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
                        if (!$is_credit && 'order' === $wc_order_creates) {
                            $row["OrderedQuantity"] = floatval("1");
                        }

                        $rows[$rowtype][] = apply_filters('fortnox_after_get_fee_items', $row, $fee, $order);
                    }
                }
            }

            return WCFH_Util::remove_blanks($rows);
        }

        public function create_shipping_row($price, $shipping_item, $order, $tax_percent = '', $is_credit = false)
        {

            $method_id = $shipping_item->get_method_id();
            $instance_id = $shipping_item->get_instance_id();

            $shipping_article_number = get_option('fortnox_shipping_article_number_' . $method_id . '_' . $instance_id);
            if (!$shipping_article_number) {
                $shipping_article_number = get_option('fortnox_shipping_article_number_' . $method_id, get_option('fortnox_shipping_customer_number'));
            }

            $row = array(
                "DeliveredQuantity" => 1,
                "Description" => WCFH_Util::clean_fortnox_text(sprintf(__('Shipping - %s', 'woo-fortnox-hub'), $shipping_item->get_method_title()), 50),
                "AccountNumber" => WCFN_Accounts::get_shipping_account($order, $tax_percent),
                "ArticleNumber" => $shipping_article_number ? $shipping_article_number : 'API_BLANK',
                "Price" => $price ? $price : 0,
            );

            if (wc_string_to_bool(get_option('fortnox_set_discount_to_zero', 'yes'))) {
                $row["Discount"] = 0;
            }

            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
            if (!$is_credit && 'order' === $wc_order_creates) {
                $row["OrderedQuantity"] = 1;
            }

            return WCFH_Util::remove_blanks($row);
        }

        public function get_coupon_items ($order, $rowtype = false, $is_credit = false) {
            $rows = array();

            if (!wc_string_to_bool(get_option('fortnox_document_use_coupon_rows'))) {
                return WCFH_Util::remove_blanks($rows);
            }

            $rowtype = $rowtype ? $rowtype : $this->document_type($order, 'Rows');

            $coupons = $order->get_coupons();

            if (!empty($coupons)) {

                foreach ($coupons as $coupon) {

                    if (apply_filters('fortnox_include_coupon_item', true, $coupon, $order)) {

                        $row = array(
                            "Description" => $coupon->get_code()
                        );

                        $rows[$rowtype][] = apply_filters('fortnox_after_get_coupon_items', $row, $coupon, $order);
                    }
                }
            }

            return WCFH_Util::remove_blanks($rows);
        }

        public function get_shipping_cost($order)
        {

            $document = array(
                "Freight" => $order->get_shipping_total() + ($this->include_tax ? $order->get_shipping_tax() : 0),
            );

            return $document;
        }

        public function get_shipping_items($order, $rowtype = false, $is_credit = false)
        {

            $rows = array();

            $rowtype = $rowtype ? $rowtype : $this->document_type($order, 'Rows');

            foreach ($order->get_shipping_methods() as $item) {

                $tax = $item->get_total_tax('edit');
                $total = $item->get_total('edit');

                if ($tax) {

                    $tax_amounts = $item->get_taxes('edit');

                    if (array_key_exists('total', $tax_amounts)) {

                        $tax_rate = array_key_first($tax_amounts['total']);
                        $tax_percent = WC_Tax::get_rate_percent_value($tax_rate);

                        $amount = $order->get_line_total($item, $this->include_tax);
                        $rows[$rowtype][] = $this->create_shipping_row($amount, $item, $order, $tax_percent, $is_credit);

                        WC_FH()->logger->add(sprintf('get_shipping_items: Shipping amount is %s excluding %s%% (%s) tax', $total, $tax_percent, $tax));
                    }
                } else {

                    $rows[$rowtype][] = $this->create_shipping_row($total, $item, $order, '', $is_credit);
                    WC_FH()->logger->add(sprintf('get_shipping_items: Shipping amount is %s and has no tax', $total));
                }
            }

            if (wc_string_to_bool(get_option('fortnox_do_not_clear_freight'))) {
                $rows["Freight"] = 'API_BLANK';
            }

            return $rows;
        }

        public function get_details($order, $refund = true)
        {

            $remarks = array();
            $comments = array();
            $order_id = $order->get_id();
            $date_created = $order->get_date_created();
            $delivery_correct = 0;
            if (0 < ($default_delivery_days = get_option('fortnox_default_delivery_days', 0))) {
                $delivery_correct = (false === ($pos = array_search($date_created->date('N'), array((string) 6, (string) 5))) ? $default_delivery_days : $default_delivery_days + 1 + $pos);
            };

            if ($customer_comments = get_post_meta($order_id, '_fortnox_customer_comments', true)) {
                $comments[] = sprintf(__('Comments in customer profile: %s', 'woo-fortnox-hub'), $customer_comments);
            }

            if ($wp_customer_note = $order->get_customer_note()) {

                $customer_message = sprintf(__('Message from customer: %s', 'woo-fortnox-hub'), $wp_customer_note);

                if (!($note_place = get_option('fortnox_customer_note_place'))) {
                    $comments[] = $customer_message;
                } elseif ($note_place == 'remarks') {
                    $remarks[] = $customer_message;
                }
            }

            $cost_center = get_option('fortnox_cost_center');
            $project = get_option('fortnox_project');

            $common_data = array(
                "CustomerNumber" => WCFH_Util::get_fortnox_customer_number($order),
                "Currency" => $order->get_currency(),
                "YourOrderNumber" => $order->get_order_number(),
                "OurReference" => ($reference = get_option('fornox_our_reference')) ? $reference : 'API_BLANK',
                "ExternalInvoiceReference1" => WCFH_Util::encode_external_reference($order_id),
                'CostCenter' => $cost_center ? $cost_center : 'API_BLANK',
                'Project' => $project ? $project : 'API_BLANK',
                'VATIncluded' => $this->include_tax,
                "Language" => get_option('fortnox_language', (get_locale() == 'sv_SE') ? 'SV' : 'EN'),

            );

            if ('yes' == get_option('fortnox_use_woocommerce_order_number')) {
                $common_data['DocumentNumber'] = (string) $order->get_order_number();
            }

            if ('yes' == get_option('fortnox_set_administration_fee')) {
                $common_data['AdministrationFee'] = apply_filters('fortnox_administration_fee', get_option('fortnox_administration_fee', 0));
            }

            if ($document_date = get_option('fortnox_document_date')) {
                $common_data[$this->document_type($order, 'Date')] = date('Y-m-d', current_time('timestamp', true) + get_option('fortnox_gmt_offset', 0));
            } else {
                $common_data[$this->document_type($order, 'Date')] = $date_created->date('Y-m-d');
            }

            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);
            if (!'order' === $wc_order_creates) {
                $billing_country = WCFN_Accounts::get_billing_country($order);
                if (('SE' !== $billing_country) && WCFH_Util::is_european_country($billing_country) && WCFH_Util::eu_number_is_validated($order)) {
                    $common_data["EUQuarterlyReport"] = true;
                }
            }

            $payment_method = WCFH_Util::get_payment_method($order, 'get_details');

            $payment_data = array_merge(array(
                "DeliveryDate" => date('Y-m-d', $date_created->date('U') + ($delivery_correct * (24 * 60 * 60))),
                "TermsOfPayment" => get_option('fortnox_term_of_payment_' . $payment_method, 'API_BLANK'),
            ), WCFH_Util::create_currency_payment_data($order));

            $payment_gateways = WCFH_Util::get_available_payment_gateways();

            if (($payment_remark = get_option('fortnox_invoice_payment_remark_' . $payment_method, 'yes')) && $order->get_date_paid()) {

                if ($payment_method) {
                    $payment_text[] = sprintf(__('Payment via %s (%s)', 'woo-fortnox-hub'), isset($payment_gateways[$payment_method]) ? $payment_gateways[$payment_method]->get_title() : ucfirst($payment_method), $order->get_transaction_id());
                }

                $payment_text[] = sprintf(
                    __('Paid on %1$s @ %2$s', 'woo-fortnox-hub'),
                    wc_format_datetime($order->get_date_paid()),
                    wc_format_datetime($order->get_date_paid(), get_option('time_format'))
                );

                if (false !== strpos($payment_remark, 'yes')) {
                    $remarks = $payment_text;
                }

                if (false !== strpos($payment_remark, 'comment')) {
                    $comments = $payment_text;
                }
            }

            $wc_order_creates = WCFH_Util::fortnox_wc_order_creates($order);

            $common_data['Remarks'] = !empty($remarks) ? WCFH_Util::clean_fortnox_text(implode('. ', $remarks), 1024) : 'API_BLANK';
            if ('order' === $wc_order_creates && ('yes' == get_option('fortnox_order_copy_remarks'))) {
                $common_data['CopyRemarks'] = true;
            }

            $common_data['Comments'] = !empty($comments) ? WCFH_Util::clean_fortnox_text(implode('. ', $comments), 1024) : 'API_BLANK';

            if ('order' === $wc_order_creates && ($order_print_template = get_option('fortnox_order_print_template_' . $payment_method, get_option('fortnox_order_print_template')))) {
                $common_data['PrintTemplate'] = $order_print_template;
            } elseif ($invoice_print_template = get_option('fortnox_invoice_print_template_' . $payment_method, get_option('fortnox_invoice_print_template'))) {
                $common_data['PrintTemplate'] = $invoice_print_template;
            }

            if (in_array(WCFH_Util::fortnox_wc_order_creates($order_id), array('order')) && wc_string_to_bool(get_option('fortnox_set_warehouseready'))) {
                $common_data['DeliveryState'] = "delivery";
            }

            $shipping_items = $order->get_shipping_methods();

            $shipping_item = reset($shipping_items);

            $delivery_data = array();
            if ($shipping_item) {

                $method_id = $shipping_item->get_method_id();
                $instance_id = $shipping_item->get_instance_id();

                $term_of_delivery = get_option('fortnox_term_of_delivery_' . $method_id . '_' . $instance_id);
                if (!$term_of_delivery) {
                    $term_of_delivery = get_option('fortnox_term_of_delivery_' . $method_id, 'API_BLANK');
                }

                $way_of_delivery = get_option('fortnox_way_of_delivery_' . $method_id . '_' . $instance_id);
                if (!$way_of_delivery) {
                    $way_of_delivery = get_option('fortnox_way_of_delivery_' . $method_id, 'API_BLANK');
                }

                $delivery_data = array(
                    "TermsOfDelivery" => $term_of_delivery,
                    "WayOfDelivery" => $way_of_delivery,
                );
            }

            return apply_filters('fortnox_after_get_details', array_merge($common_data, $payment_data, $delivery_data), $order);
        }
    }
}
