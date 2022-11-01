<?php

/**
 * Payouts handler for Nets Easy payments
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2022 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Handler_Nets_Easy', false)) {
    final class Fortnox_Hub_Payouts_Handler_Nets_Easy extends Fortnox_Hub_Payouts_Handler
    {

        public $payout_id;
        public $payment_method;
        public $payout_name;
        public $payouts_handler;
        public $supports = ['voucher'];
        public $fee_vat = [];
        public $handles_invoices = false;

        public function __construct()
        {
            $this->payout_id = 'nets-easy';
            $this->payment_method = 'nets-easy';
            $this->payout_name = __('Nets Easy', 'woo-fortnox-hub');
            parent::__construct();
        }

        public function process_payout_document($start_date)
        {

            include_once dirname(__FILE__) . '/class-fortnox-nets-easy-api.php';
            $api = new Fortnox_Hub_Nets_Easy_API();

            $args = array(
                'fromDate' => strtotime(date('Y-m-d', strtotime($start_date))),
                'currency' => 'SEK',
            );

            $api_response = $api->get_payouts($args);

            $include_reversed_vat = wc_string_to_bool(get_option('fortnox_' . $this->payout_id . '_include_reversed_vat'));
            $outgoing_reversed_vat = get_option('fortnox_' . $this->payout_id . '_outgoing_reversed_vat', FN_OUTGOING_REVERSED_VAT);
            $incoming_calculated_vat = get_option('fortnox_' . $this->payout_id . '_incoming_calculated_vat', FN_INCOMING_CALCULATED_VAT);
            $document_type = get_option('fortnox_' . $this->payout_id . '_document_type', FN_PAYOUT_DOCTYPE);

            $processed_order_ids = [];

            foreach ($api_response->payouts as $payout) {

                $this->payouts_handler->start_document();

                $val = $balance_transaction->amount / 100;

                $this->payouts_handler->create_document_row(
                    $this->balance_account,
                    $balance_transaction->amount,
                    sprintf(__('Nets Easy payout created %s', 'woo-fortnox-hub'), $start_date),
                    $price < 0,
                );

                $this->payouts_handler->create_document_row(
                    $this->fee_account,
                    $balance_transaction->fee,
                    __('Nets Easy fee', 'woo-fortnox-hub'),
                    $fee > 0,
                );

                $this->payouts_handler->create_document_row(
                    $this->bank_account,
                    $balance_transaction->amount - $balance_transaction->fee,
                    sprintf(__('Nets Easy payout created %s', 'woo-fortnox-hub'), $start_date),
                    $amnt > 0
                );

                $this->payouts_handler->create_document(
                    date('Y-m-d', $payout->date),
                    date('Y-m-d', $payout->date),
                    strtoupper($payout->currency),
                    $payout->id
                );

                $this->payouts_handler->maybe_send_document_to_fortnox($payout->id);

            }

            return $processed_order_ids;
        }

    }

    new Fortnox_Hub_Payouts_Handler_Nets_Easy();
}
