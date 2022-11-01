<?php

/**
 * Creating Supplier invoices from a Klarna payments
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Handler_Klarna', false)) {

    require_once dirname(__FILE__) . '/class-woo-fortnox-hub-payouts-invoice-klarna.php';
    require_once dirname(__FILE__) . '/class-woo-fortnox-hub-payouts-voucher-klarna.php';

    final class Fortnox_Hub_Payouts_Handler_Klarna extends Fortnox_Hub_Payouts_Handler
    {

        public $payout_id;
        public $payment_method;
        public $payout_name;
        public $payouts_handler;
        public $supports = ['detailed_invoice', 'voucher'];
        public $fee_vat = ['incoming'];
        public $handles_invoices = true;

        public function __construct()
        {
            $this->payout_id = 'klarna';
            $this->payment_method = $this->get_klarna_gw_id();
            $this->payout_name = __('Klarna', 'woo-fortnox-hub');
            parent::__construct();
        }

        private function get_klarna_gw_id()
        {

            if (class_exists('WC_Klarna_Payments')) {
                return 'klarna_payments';
            }

            if (class_exists('KCO')) {
                return 'kco';
            }

            return 'klarna';

        }

        public function payment_gw_account_settings($account_selection)
        {

            $settings[] = [
                'title' => __('Commission account', 'woo-fortnox-hub'),
                'type' => 'select',
                'default' => FN_PAYOUT_FEE_ACCOUNT,
                'options' => $account_selection,
                'id' => 'fortnox_klarna_' . $this->payout_id . '_commission_account',
            ];

            return $settings;

        }

        public function process_payout_document($start_date)
        {

            include_once dirname(__FILE__) . '/class-fortnox-klarna-api.php';
            $this->klarna = new Fortnox_Hub_Klarna_API();

            $payout_offset = 0;
            $processed_order_ids = [];

            do {

                $payout_args = array(
                    'start_date' => date('c', strtotime(date('m/d/Y 00:00:01', strtotime($start_date)))),
                    'size' => 20,
                    'offset' => $payout_offset,
                );

                $payouts = $this->klarna->get_payouts($payout_args);

                foreach ($payouts->payouts as $payout) {

                    $this->payouts_handler->start_document();

                    WC_FH()->logger->add(sprintf('Processing Klarna payout %s', $payout->payment_reference));
                    WC_FH()->logger->add(json_encode($payout, JSON_INVALID_UTF8_IGNORE));

                    if (is_a($this->payouts_handler, 'Fortnox_Hub_Payouts_Voucher')) {

                        if (isset($payout->totals->sale_amount) && is_numeric($payout->totals->sale_amount) && $payout->totals->sale_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                $this->balance_account,
                                number_format($payout->totals->sale_amount / 100, 2, '.', ''),
                                __('Sales', 'woo-fortnox-hub'),
                            );
                        }

                        if (isset($payout->totals->repay_amount) && is_numeric($payout->totals->repay_amount) && $payout->totals->repay_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                $this->balance_account,
                                number_format($payout->totals->repay_amount / 100, 2, '.', ''),
                                __('Repay', 'woo-fortnox-hub'),
                            );
                        }

                        if (isset($payout->totals->release_amount) && is_numeric($payout->totals->release_amount) && $payout->totals->release_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                $this->balance_account,
                                number_format($payout->totals->release_amount / 100, 2, '.', ''),
                                __('Release amount', 'woo-fortnox-hub'),
                            );
                        }

                        if (isset($payout->totals->credit_amount) && is_numeric($payout->totals->credit_amount) && $payout->totals->credit_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                $this->balance_account,
                                number_format($payout->totals->credit_amount / 100, 2, '.', ''),
                                __('Credit amount', 'woo-fortnox-hub'),
                            );
                        }

                        if (isset($payout->totals->charge_amount) && is_numeric($payout->totals->charge_amount) && $payout->totals->charge_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                $this->balance_account,
                                number_format($payout->totals->charge_amount / 100, 2, '.', ''),
                                __('Charge amount', 'woo-fortnox-hub'),
                                true,
                            );
                        }

                        if (isset($payout->totals->holdback_amount) && is_numeric($payout->totals->holdback_amount) && $payout->totals->holdback_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                $this->balance_account,
                                number_format($payout->totals->holdback_amount / 100, 2, '.', ''),
                                __('Holdback amount', 'woo-fortnox-hub'),
                                true,
                            );
                        }

                        if (isset($payout->totals->settlement_amount) && is_numeric($payout->totals->settlement_amount) && $payout->totals->settlement_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                $this->bank_account,
                                number_format($payout->totals->settlement_amount / 100, 2, '.', ''),
                                __('Payout to bank account', 'woo-fortnox-hub'),
                                true
                            );
                        }

                        if (isset($payout->totals->reversal_amount) && is_numeric($payout->totals->reversal_amount) && $payout->totals->reversal_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                $this->balance_account,
                                number_format($payout->totals->reversal_amount / 100, 2, '.', ''),
                                __('Reversal amount', 'woo-fortnox-hub'),
                                true
                            );
                        }

                        if (isset($payout->totals->return_amount) && is_numeric($payout->totals->return_amount) && $payout->totals->return_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                $this->balance_account,
                                number_format($payout->totals->return_amount / 100, 2, '.', ''),
                                __('Return amount', 'woo-fortnox-hub'),
                                true
                            );
                        }

                        if (isset($payout->totals->commission_amount) && is_numeric($payout->totals->commission_amount) && $payout->totals->commission_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                get_option('fortnox_' . $this->payout_id . '_commission_account', FN_PAYOUT_FEE_ACCOUNT),
                                number_format($payout->totals->commission_amount / 100, 2, '.', ''),
                                __('Commission amount', 'woo-fortnox-hub'),
                                true
                            );
                        }

                        if (isset($payout->totals->fee_amount) && is_numeric($payout->totals->fee_amount) && $payout->totals->fee_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                get_option('fortnox_' . $this->payout_id . '_fee_account', FN_PAYOUT_FEE_ACCOUNT),
                                number_format($payout->totals->fee_amount / 100, 2, '.', ''),
                                __('Fee amount', 'woo-fortnox-hub'),
                                true,
                            );
                        }

                        if (isset($payout->totals->fee_correction_amount) && is_numeric($payout->totals->fee_correction_amount) && $payout->totals->fee_correction_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                get_option('fortnox_' . $this->payout_id . '_fee_account', FN_PAYOUT_FEE_ACCOUNT),
                                number_format($payout->totals->fee_correction_amount / 100, 2, '.', ''),
                                __('Fee correction amount', 'woo-fortnox-hub'),
                                true,
                            );
                        }

                        if (isset($payout->totals->tax_amount) && is_numeric($payout->totals->tax_amount) && $payout->totals->tax_amount != 0) {
                            $this->payouts_handler->create_document_row(
                                get_option('fortnox_' . $this->payout_id . '_fee_vat_account', FN_PAYOUT_FEE_VAT),
                                number_format($payout->totals->tax_amount / 100, 2, '.', ''),
                                __('VAT Amount'),
                                true
                            );
                        }

                    }

                    $offset = 0;

                    do {
                        $transactions = $this->klarna->get_transactions(array(
                            'payment_reference' => $payout->payment_reference,
                            'size' => 20,
                            'offset' => $offset,
                        ));

                        WC_FH()->logger->add(json_encode($transactions, JSON_INVALID_UTF8_IGNORE));

                        foreach ($transactions->transactions as $transaction) {

                            if (is_a($this->payouts_handler, 'Fortnox_Hub_Payouts_Detailed_Invoice')) {

                                switch ($transaction->type) {
                                    case 'SALE':
                                        $this->payouts_handler->create_document_row(
                                            $this->balance_account,
                                            number_format($transaction->amount / 100, 2, '.', ''),
                                            is_numeric($transaction->merchant_reference1) ? sprintf(__('Sales order %s', 'woo-fortnox-hub'), $transaction->merchant_reference1) : $transaction->merchant_reference1
                                        );
                                        break;
                                    case 'REVERSAL':
                                        $this->payouts_handler->create_document_row(
                                            $this->balance_account,
                                            number_format($transaction->amount / 100, 2, '.', ''),
                                            is_numeric($transaction->merchant_reference1) ? sprintf(__('Reversal order %s', 'woo-fortnox-hub'), $transaction->merchant_reference1) : $transaction->merchant_reference1,
                                            true
                                        );
                                        break;
                                    case 'RETURN':
                                        $this->payouts_handler->create_document_row(
                                            $this->balance_account,
                                            number_format($transaction->amount / 100, 2, '.', ''),
                                            is_numeric($transaction->merchant_reference1) ? sprintf(__('Return order %s', 'woo-fortnox-hub'), $transaction->merchant_reference1) : $transaction->merchant_reference1,
                                            true
                                        );
                                        break;
                                    case 'COMMISSION':
                                        $this->payouts_handler->create_document_row(
                                            get_option('fortnox_' . $this->payout_id . '_commission_account', FN_PAYOUT_FEE_ACCOUNT),
                                            number_format($transaction->amount / 100, 2, '.', ''),
                                            is_numeric($transaction->merchant_reference1) ? sprintf(__('Commission order %s', 'woo-fortnox-hub'), $transaction->merchant_reference1) : $transaction->merchant_reference1,
                                            true
                                        );
                                        break;
                                    case 'FEE':
                                        $this->payouts_handler->create_document_row(
                                            get_option('fortnox_' . $this->payout_id . '_fee_account', FN_PAYOUT_FEE_ACCOUNT),
                                            number_format($transaction->amount / 100, 2, '.', ''),
                                            is_numeric($transaction->merchant_reference1) ? sprintf(__('Fee order %s', 'woo-fortnox-hub'), $transaction->merchant_reference1) : $transaction->merchant_reference1 / 100,
                                            true,
                                        );
                                        if (property_exists($transaction, 'vat_amount') && $transaction->vat_amount) {
                                            $this->payouts_handler->create_document_row(
                                                get_option('fortnox_' . $this->payout_id . '_fee_vat_account', FN_PAYOUT_FEE_VAT),
                                                number_format($transaction->vat_amount / 100, 2, '.', ''),
                                                is_numeric($transaction->merchant_reference1) ? sprintf(__('Fee VAT (%s%%) order %s', 'woo-fortnox-hub'), $transaction->vat_rate / 100, $transaction->merchant_reference1) : $transaction->merchant_reference1,
                                                true
                                            );
                                        }
                                        break;
                                    case 'CREDIT':
                                        $this->payouts_handler->create_document_row(
                                            $this->balance_account,
                                            number_format($transaction->amount / 100, 2, '.', ''),
                                            is_numeric($transaction->merchant_reference1) ? sprintf(__('Credit order %s', 'woo-fortnox-hub'), $transaction->merchant_reference1) : $transaction->merchant_reference1,
                                        );
                                        break;
                                    case 'CHARGE':
                                        $this->payouts_handler->create_document_row(
                                            $this->balance_account,
                                            number_format($transaction->amount / 100, 2, '.', ''),
                                            is_numeric($transaction->merchant_reference1) ? sprintf(__('Charge order %s', 'woo-fortnox-hub'), $transaction->merchant_reference1) : $transaction->merchant_reference1,
                                            true
                                        );
                                        break;

                                }

                                if (isset($transaction->merchant_reference1) && is_numeric($transaction->merchant_reference1)) {
                                    $transaction_reference = trim($transaction->merchant_reference1);
                                    if (!in_array($transaction_reference, $processed_order_ids)) {
                                        $processed_order_ids[] = $transaction_reference;
                                    }
                                }

                            }

                        }

                        $offset += $transactions->pagination->count;

                    } while ($offset < $transactions->pagination->total);

                    $this->payouts_handler->create_document(
                        substr($payout->payout_date, 0, 10),
                        substr($payout->payout_date, 0, 10),
                        $payout->currency_code,
                        $payout->payment_reference
                    );

                    $this->payouts_handler->maybe_send_document_to_fortnox($payout->payment_reference);

                }

                $payout_offset += $payouts->pagination->count;

            } while ($payout_offset < $payouts->pagination->total);

            return $processed_order_ids;
        }

    }

    new Fortnox_Hub_Payouts_Handler_Klarna();
}
