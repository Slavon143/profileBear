<?php

defined('ABSPATH') || exit;

require_once dirname(__FILE__) . '/class-woo-fortnox-payouts-voucher-common.php';

if (!class_exists('Fortnox_Hub_Payouts_Voucher', false)) {

    class Fortnox_Hub_Payouts_Voucher extends Fortnox_Hub_Payouts_Voucher_Common
    {

        public $document_type = 'voucher';
        public $series;
        private $transactions = [];

        public function __construct()
        {
            $this->voucher_series = get_option('fortnox_' . $this->payout_id . '_voucher_serie', 'A');
        }

        public function create_document_row($account, $amount, $desc, $is_debit = false)
        {

            $account = (string) $account;
            $this->account_transaction_information[$account] = WCFH_Util::clean_fortnox_text($desc, 50);

            if ($is_debit) {
                $this->account_debit[$account] = array_key_exists($account, $this->account_debit) ? $this->account_debit[$account] += $amount : $this->account_debit[$account] = $amount;
            } else {
                $this->account_credit[$account] = array_key_exists($account, $this->account_credit) ? $this->account_credit[$account] += $amount : $this->account_credit[$account] = $amount;
            }

        }

        public function create_document($payout_date, $creation_date, $currency, $external_reference)
        {

            foreach ($this->account_transaction_information as $account => $transaction_information) {
                if (array_key_exists($account, $this->account_debit)) {
                    $this->document_rows[] = [
                        "Account" => $account,
                        "Debit" => $this->account_debit[$account],
                        "TransactionInformation" => $transaction_information,
                    ];
                }
                if (array_key_exists($account, $this->account_credit)) {
                    $this->document_rows[] = [
                        "Account" => $account,
                        "Credit" => $this->account_credit[$account],
                        "TransactionInformation" => $transaction_information,
                    ];
                }
            }

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
