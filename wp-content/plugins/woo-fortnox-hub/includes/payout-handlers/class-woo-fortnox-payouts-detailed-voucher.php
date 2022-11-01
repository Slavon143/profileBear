<?php

defined('ABSPATH') || exit;

require_once dirname(__FILE__) . '/class-woo-fortnox-payouts-voucher-common.php';

if (!class_exists('Fortnox_Hub_Payouts_Detailed_Voucher', false)) {

    class Fortnox_Hub_Payouts_Detailed_Voucher extends Fortnox_Hub_Payouts_Voucher_Common
    {

        public $document_type = 'voucher';
        public $series;

        public function __construct()
        {
            $this->voucher_series = get_option('fortnox_' . $this->payout_id . '_voucher_serie', 'A');
        }

        public function create_document_row($account, $amount, $desc, $is_debit = false)
        {
            $this->document_rows[] = array(
                "Account" => (int) $account,
                $is_debit ? "Debit" : "Credit" => $amount,
                "TransactionInformation" => WCFH_Util::clean_fortnox_text($desc, 100),
            );
        }

        public function create_document($payout_date, $creation_date, $currency, $external_reference)
        {
            $this->document = array(
                "TransactionDate" => $payout_date,
                "VoucherRows" => $this->document_rows,
                "VoucherSeries" => $this->voucher_series,
                "Description" => sprintf(__('Payout from %s (%s)', 'woo-fortnox-hub'), $this->payout_id, $external_reference),
            );
            WC_FH()->logger->add(sprintf('Created %s payout voucher', $this->payout_id));
            WC_FH()->logger->add(json_encode($this->document, JSON_INVALID_UTF8_IGNORE));
        }

    }

}
