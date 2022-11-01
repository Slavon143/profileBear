<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Voucher_Common', false)) {

    class Fortnox_Hub_Payouts_Voucher_Common extends Fortnox_Hub_Payouts
    {

        public function maybe_send_document_to_fortnox($id)
        {
            if (empty($this->document_rows)) {
                return;
            }

            $voucher_history = get_option('fortnox_payout_voucher_history_' . $this->payout_id, array());

            if (!empty($voucher_history) && in_array($id, $voucher_history)) {

                $voucher_meta = get_site_transient('fortnox_payout_voucher_payout_' . $this->payout_id . '_' . $id);

                if (empty($voucher_meta)) {
                    WC_FH()->logger->add(sprintf('Fortnox voucher already created by %s payout %s', $this->payout_id, $id));
                    return;
                }

                $series = $voucher_meta['series'];
                $number = $voucher_meta['number'];

                try {

                    $existing_voucher = WC_FH()->fortnox->get_voucher($series, $number);
                    WC_FH()->logger->add(sprintf('Fortnox voucher %s%s already created by %s payout %s', $series, $number, $this->payout_id, $id));
                    return;

                } catch (Fortnox_API_Exception $e) {

                    if (404 == $e->getCode()) {
                        WC_FH()->logger->add(sprintf('maybe_send_document_to_fortnox: Previosly created voucher %s%s created from %s payout (%s) is missing in Fortnox and will be recreated', $series, $number, $this->payout_id, $id));
                        unset($voucher_history[$id]);
                    } else {
                        throw new $e($e->getMessage(), $e->getCode(), $e);
                    }

                }

            }

            $created_voucher = WC_FH()->fortnox->create_voucher($this->document);

            WC_FH()->logger->add(sprintf('maybe_send_document_to_fortnox: Fortnox voucher %s%s created from "%s" payout (%s)', $this->voucher_series, $created_voucher['VoucherNumber'], $this->payout_id, $id));

            Fortnox_Notice::add(sprintf(__('Successfully created voucher %s%s from %s payouts', 'woo-fortnox-hub'), $this->voucher_series, $created_voucher['VoucherNumber'], $this->payout_id), 'success');

            set_site_transient('fortnox_payout_voucher_payout_' . $this->payout_id . '_' . $id, array(
                'series' => $this->voucher_series,
                'number' => $created_voucher['VoucherNumber'],
            ), MONTH_IN_SECONDS);

            $voucher_history[] = $id;
            update_option('fortnox_payout_voucher_history_' . $this->payout_id, $voucher_history);

            WC_FH()->logger->add(print_r($voucher_history, true));

        }

    }

}
