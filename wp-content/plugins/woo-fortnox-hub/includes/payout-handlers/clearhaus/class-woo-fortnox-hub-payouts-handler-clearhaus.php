<?php

/**
 * Payouts handler for Clearhaus payments
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2022 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Handler_Clearhaus', false)) {

    final class Fortnox_Hub_Payouts_Handler_Clearhaus extends Fortnox_Hub_Payouts_Handler
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
            $this->payout_id = 'clearhaus';
            $this->payment_method = 'clearhaus';
            $this->payout_name = __('Clearhaus', 'woo-fortnox-hub');
            parent::__construct();
        }

        public function payment_gw_credentials_settings()
        {
            $settings[] = [
                'title' => __('Test mode.', 'woo-fortnox-hub'),
                'type' => 'checkbox',
                'desc' => sprintf(__('The gateway is in testmode.', 'woo-fortnox-hub')),
                'default' => 'yes',
                'id' => 'fortnox_' . $this->payout_id . '_test_mode',
            ];

            $settings[] = [
                'title' => __('OAuth Client ID (test)', 'woo-fortnox-hub'),
                'type' => 'text',
                'description' => __('The test OAuth Client ID. Create one in the admin pages.', 'woo-fortnox-hub'),
                'default' => '',
                'id' => 'fortnox_' . $this->payout_id . '_client_id_test',
            ];

            $settings[] = [
                'title' => __('OAuth Client Secret (test)', 'woo-fortnox-hub'),
                'type' => 'text',
                'description' => __('The test OAuth Client Secret. Create one in the admin pages.', 'woo-fortnox-hub'),
                'default' => '',
                'id' => 'fortnox_' . $this->payout_id . '_client_secret_test',
            ];

            $settings[] = [
                'title' => __('OAuth Client ID (production)', 'woo-fortnox-hub'),
                'type' => 'text',
                'description' => __('The production OAuth Client ID. Create one in the admin pages.', 'woo-fortnox-hub'),
                'default' => '',
                'id' => 'fortnox_' . $this->payout_id . '_client_id_prod',
            ];

            $settings[] = [
                'title' => __('OAuth Client Secret (production)', 'woo-fortnox-hub'),
                'type' => 'text',
                'description' => __('The production OAuth Client Secret. Create one in the admin pages.', 'woo-fortnox-hub'),
                'default' => '',
                'id' => 'fortnox_' . $this->payout_id . '_client_secret_prod',
            ];

            return $settings;
        }

        public function process_payout_document($start_date)
        {

            include_once dirname(__FILE__) . '/class-fortnox-clearhaus-api.php';
            $api = new Fortnox_Hub_Clearhaus_API($this);

            $args = 'payout.date:>=' . date('Y-m-d', strtotime($start_date)) . ' currency:SEK';

            $api_response = $api->get_payouts($args);

            WC_FH()->logger->add(print_r($api_response, true));

            $include_reversed_vat = wc_string_to_bool(get_option('fortnox_' . $this->payout_id . '_include_reversed_vat'));
            $outgoing_reversed_vat = get_option('fortnox_' . $this->payout_id . '_outgoing_reversed_vat', FN_OUTGOING_REVERSED_VAT);
            $incoming_calculated_vat = get_option('fortnox_' . $this->payout_id . '_incoming_calculated_vat', FN_INCOMING_CALCULATED_VAT);
            $document_type = get_option('fortnox_' . $this->payout_id . '_document_type', FN_PAYOUT_DOCTYPE);

            $processed_order_ids = [];

            foreach ($api_response['_embedded']['ch:settlements'] as $payout) {

                if (true === apply_filters('fortnox_hub_filter_clearhouse_payout', true, $payout)) {

                    $this->payouts_handler->start_document();

                    $sales = $payout['summary']['sales'] / 100;
                    if ($sales !== 0) {
                        $this->payouts_handler->create_document_row(
                            $this->balance_account,
                            abs($sales),
                            __('Clearhaus sales %s', 'woo-fortnox-hub'),
                            $sales < 0,
                        );
                    }

                    $credits = $payout['summary']['credits'] / 100;
                    if ($credits !== 0) {
                        $this->payouts_handler->create_document_row(
                            $this->balance_account,
                            abs($credits),
                            __('Clearhaus credits %s', 'woo-fortnox-hub'),
                            $credits < 0,
                        );
                    }

                    $refunds = $payout['summary']['refunds'] / 100;
                    if ($refunds !== 0) {
                        $this->payouts_handler->create_document_row(
                            $this->balance_account,
                            abs($refunds),
                            __('Clearhaus refunds %s', 'woo-fortnox-hub'),
                            $refunds < 0,
                        );
                    }

                    $chargebacks = $payout['summary']['chargebacks'] / 100;
                    if ($chargebacks !== 0) {
                        $this->payouts_handler->create_document_row(
                            $this->balance_account,
                            abs($chargebacks),
                            __('Clearhaus chargebacks %s', 'woo-fortnox-hub'),
                            $chargebacks < 0,
                        );
                    }

                    $fees = $payout['summary']['fees'] / 100;
                    if ($fees !== 0) {
                        $this->payouts_handler->create_document_row(
                            $this->fee_account,
                            abs($fees),
                            __('Clearhaus fees', 'woo-fortnox-hub'),
                            $fees < 0,
                        );
                    }

                    $other_postings = $payout['summary']['other_postings'] / 100;
                    if ($other_postings !== 0) {
                        $this->payouts_handler->create_document_row(
                            $this->balance_account,
                            abs($other_postings),
                            __('Clearhaus other_postings %s', 'woo-fortnox-hub'),
                            $other_postings < 0,
                        );
                    }

                    $net = $payout['summary']['net'] / 100;
                    if ($net !== 0) {
                        $this->payouts_handler->create_document_row(
                            $this->bank_account,
                            abs($net),
                            __('Clearhaus payout created %s', 'woo-fortnox-hub'),
                            $net > 0
                        );
                    }

                    $this->payouts_handler->create_document(
                        $payout['payout']['date'],
                        $payout['payout']['date'],
                        $payout['currency'],
                        $payout['payout']['reference_number']
                    );

                    $this->payouts_handler->maybe_send_document_to_fortnox($payout['payout']['reference_number']);

                }

            }

            return $processed_order_ids;
        }

    }

    new Fortnox_Hub_Payouts_Handler_Clearhaus();

}
