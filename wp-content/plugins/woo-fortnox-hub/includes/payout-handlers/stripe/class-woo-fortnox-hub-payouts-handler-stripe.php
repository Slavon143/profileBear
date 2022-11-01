<?php

/**
 * Creating Supplier invoices from a Stripe payments
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Handler_Stripe', false)) {
    final class Fortnox_Hub_Payouts_Handler_Stripe extends Fortnox_Hub_Payouts_Handler
    {

        public $payout_id;
        public $payment_method;
        public $payout_name;
        public $payouts_handler;
        public $supports = ['voucher', 'detailed_voucher', 'invoice', 'detailed_invoice'];
        public $handles_invoices = true;
        public $fee_vat = ['reversed'];

        public function __construct()
        {
            $this->payout_id = 'stripe';
            $this->payment_method = 'stripe';
            $this->payout_name = __('Stripe', 'woo-fortnox-hub');
            if ('yes' === get_option('fortnox_stripe_payouts')) {
                add_filter('fortnox_hub_stripe_secret_key', array($this, 'get_secret_key'));
            }
            parent::__construct();
        }

        public function get_order_id($text)
        {
            preg_match('/Order([^&#]*)/', $text, $key);
            if (!isset($key[1])) {
                return trim($key[1]);
            }
            return false;
        }

        public function get_secret_key($key)
        {

            if (wc_string_to_bool('fortnox_' . $this->payout_id . '_test_mode')) {
                return get_option('fortnox_' . $this->payout_id . '_client_secret_test');
            } else {
                return get_option('fortnox_' . $this->payout_id . '_client_secret_prod');
            }

        }

        public function payout_gw_specific_settings()
        {
            $settings = [];

            if (wc_string_to_bool(get_option('fortnox_stripe_payouts'))) {

                $settings[] = [
                    'title' => __('Test mode.', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'desc' => sprintf(__('The gateway is in testmode.', 'woo-fortnox-hub')),
                    'default' => 'yes',
                    'id' => 'fortnox_' . $this->payout_id . '_test_mode',
                ];

                if (wc_string_to_bool('fortnox_' . $this->payout_id . '_test_mode')) {

                    $settings[] = [
                        'title' => __('Secret Key (Test)', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'description' => __('The Stripe Secret Key for Test.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_' . $this->payout_id . '_client_secret_test',
                    ];

                } else {

                    $settings[] = [
                        'title' => __('OAuth Client Secret', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'description' => __('The Stripe Secret Key for production.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_' . $this->payout_id . '_client_secret_prod',
                    ];

                }

            }

            return $settings;

        }

        public function process_payout_document($start_date)
        {

            include_once dirname(__FILE__) . '/class-fortnox-stripe-api.php';
            $stripe = new Fortnox_Hub_Stripe_API();

            $include_reversed_vat = wc_string_to_bool(get_option('fortnox_' . $this->payout_id . '_include_reversed_vat'));
            $outgoing_reversed_vat = get_option('fortnox_' . $this->payout_id . '_outgoing_reversed_vat', FN_OUTGOING_REVERSED_VAT);
            $incoming_calculated_vat = get_option('fortnox_' . $this->payout_id . '_incoming_calculated_vat', FN_INCOMING_CALCULATED_VAT);
            $document_type = get_option('fortnox_' . $this->payout_id . '_document_type', FN_PAYOUT_DOCTYPE);
            $document_type = get_option('fortnox_' . $this->payout_id . '_document_type', FN_PAYOUT_DOCTYPE);

            $payout_args = array(
                'created' => array(
                    'gte' => strtotime(date('m/d/Y 00:00:01', strtotime($start_date))),
                ),
                'limit' => 100,
            );

            do {

                $payouts = $stripe->get_payouts($payout_args);

                WC_FH()->logger->add(json_encode($payouts, JSON_INVALID_UTF8_IGNORE));

                foreach (array_reverse($payouts->data) as $payout) {

                    $fees = 0;
                    $balance_args = array('payout' => $payout->id);
                    $this->payouts_handler->start_document();

                    do {

                        $balance_transactions = $stripe->get_balance_transactions($balance_args);

                        WC_FH()->logger->add(json_encode($balance_transactions, JSON_INVALID_UTF8_IGNORE));

                        foreach ($balance_transactions->data as $balance_transaction) {

                            if (!in_array($balance_transaction->type, array('payout', 'payout_cancel', 'payout_failure'))) {

                                $val = $balance_transaction->amount / 100;
                                $price = (float) number_format($val, 2, '.', '');
                                $is_debit = $price < 0;

                                $transaction_desc = $balance_transaction->description;
                                if ('voucher' === $document_type || 'invoice' === $document_type) {
                                    $transaction_desc = __('Stripe sales/refunds', 'woo-fortnox-hub');
                                }

                                $this->payouts_handler->create_document_row(
                                    $this->balance_account,
                                    abs($price),
                                    $transaction_desc,
                                    $is_debit,
                                );

                                if (!empty($balance_transaction->fee_details)) {
                                    foreach ($balance_transaction->fee_details as $fee_detail) {
                                        $val = $fee_detail->amount / 100;
                                        $fee = (float) number_format($val, 2, '.', '');
                                        $desc = $fee_detail->description;
                                        $this->payouts_handler->create_document_row(
                                            $this->fee_account,
                                            abs($fee),
                                            $desc,
                                            $fee > 0,
                                        );
                                        $fees += $fee;
                                    }
                                }

                            }

                            if (isset($balance_transaction->description)) {
                                $order_id = $this->get_order_id($balance_transaction->description);
                                if (false !== $order_id && is_numeric($order_id)) {
                                    $processed_order_ids[] = $order_id;
                                }
                            }

                            $balance_args['starting_after'] = $balance_transaction->id;

                        }

                    } while ($balance_transactions->has_more);

                    if ('voucher' === $document_type || 'detailed_voucher' === $document_type) {

                        $amnt = (float) number_format($payout->amount / 100, 2, '.', '');
                        $this->payouts_handler->create_document_row(
                            $this->bank_account,
                            abs($amnt),
                            sprintf(__('Stripe payout to bank account', 'woo-fortnox-hub'), $start_date),
                            true
                        );

                        if (0 !== $fees && $include_reversed_vat) {

                            $reversed_vat = (float) number_format($fees * 0.25, 2, '.', '');
                            $this->payouts_handler->create_document_row(
                                $outgoing_reversed_vat,
                                abs($reversed_vat),
                                __('Stripe outgoing reversed VAT, 25 %', 'woo-fortnox-hub'),
                                false
                            );
                            $this->payouts_handler->create_document_row(
                                $incoming_calculated_vat,
                                ($reversed_vat),
                                __('Stripe calculated incoming VAT for the fee', 'woo-fortnox-hub'),
                                true
                            );

                        }

                    }

                    $this->payouts_handler->create_document(
                        date('Y-m-d', $payout->arrival_date),
                        date('Y-m-d', $payout->created),
                        strtoupper($payout->currency),
                        $payout->id
                    );

                    $this->payouts_handler->maybe_send_document_to_fortnox($payout->id);

                }

                $payout_args['starting_after'] = $payouts->id;

            } while ($payouts->has_more);

            return $processed_order_ids;

        }

    }

    new Fortnox_Hub_Payouts_Handler_Stripe();
}
