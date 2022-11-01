<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Invoice', false)) {

    class Fortnox_Hub_Payouts_Invoice extends Fortnox_Hub_Payouts
    {

        public $document_type = 'invoice';
        public $account_description = [];
        public $account_price = [];

        public function __construct()
        {

        }

        public function payout_type_specific_check()
        {
            $customer_number = get_option('fortnox_' . $this->payout_id . '_customer_number', false);
            if (!$customer_number) {
                Fortnox_Notice::add(sprintf(__('In order to process %s payouts, a customer number must be present in the settings', 'woo-fortnox-hub'), $this->payout_id), 'error');
                WC_FH()->logger->add(sprintf('Customer number for %s invoices is not configured', $this->payout_id));
                return false;
            }
            return true;
        }

        public function create_document_row($account, $price, $desc, $is_debit = false)
        {
            $account = (string) $account;
            $price = $is_debit ? -$price : $price;

            $this->account_description[$account] = WCFH_Util::clean_fortnox_text($desc, 50);
            $this->account_price[$account] = array_key_exists($account, $this->account_price) ? $this->account_price[$account] += $price : $this->account_price[$account] = $price;

        }

        public function create_document($payout_date, $creation_date, $currency, $external_reference)
        {

            foreach ($this->account_description as $account => $account_description) {
                $this->document_rows[] = [
                    "AccountNumber" => $account,
                    "DeliveredQuantity" => 1,
                    "Price" => $this->account_price[$account],
                    "Description" => $account_description,
                ];
            }

            $this->document = array(
                "CustomerNumber" => get_option('fortnox_' . $this->payout_id . '_customer_number', false),
                "DueDate" => $payout_date,
                "InvoiceDate" => $creation_date,
                "InvoiceRows" => $this->document_rows,
                "Currency" => $currency,
                "ExternalInvoiceReference1" => $external_reference,
            );

            WC_FH()->logger->add(sprintf('Created %s payout invoice', $this->payout_id));
            WC_FH()->logger->add(json_encode($this->document, JSON_INVALID_UTF8_IGNORE));

        }

        public function maybe_send_document_to_fortnox($id)
        {

            if (empty($this->document_rows)) {
                return;
            }

            $fortnox_invoice = WCFH_Util::check_if_invoice_already_created($id);

            if (!$fortnox_invoice) {
                $invoice_created = WC_FH()->fortnox->create_invoice($this->document);
                WC_FH()->logger->add(sprintf('maybe_send_document_to_fortnox: Fortnox invoice %s updated from "%s" payout (%s)', $invoice_created['DocumentNumber'], $this->payout_id, $id));
                WC_FH()->logger->add(json_encode($this->document, JSON_INVALID_UTF8_IGNORE));
                Fortnox_Notice::add(sprintf(__('Successfully created invoice %s from %s payouts', 'woo-fortnox-hub'), $fortnox_invoice['DocumentNumber'], $this->payout_id), 'success');
            } elseif (!rest_sanitize_boolean($fortnox_invoice['Booked']) && !rest_sanitize_boolean($fortnox_invoice['Cancelled']) && !$fortnox_invoice['FinalPayDate']) {
                $invoice_created = WC_FH()->fortnox->update_invoice($fortnox_invoice['DocumentNumber'], $this->document);
                WC_FH()->logger->add(sprintf('maybe_send_document_to_fortnox: Fortnox invoice %s updated by "%s" payout (%s)', $invoice_created['DocumentNumber'], $this->payout_id, $id));
                WC_FH()->logger->add(json_encode($this->document, JSON_INVALID_UTF8_IGNORE));
                Fortnox_Notice::add(sprintf(__('Successfully updated invoice %s from %s payouts.', 'woo-fortnox-hub'), $fortnox_invoice['DocumentNumber'], $this->payout_id), 'success');
            } else {
                WC_FH()->logger->add(sprintf('Fortnox invoice %s already created by "%s" payout (%s)', $fortnox_invoice['DocumentNumber'], $this->payout_id, $id));
            }

        }

    }

}
