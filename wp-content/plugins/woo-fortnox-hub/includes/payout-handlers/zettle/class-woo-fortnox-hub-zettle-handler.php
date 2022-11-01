<?php

/**
 * Creating Supplier invoices from a Izettle payments
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Zettle_Handler', false)) {

    class Fortnox_Hub_Zettle_Handler
    {

        private $customer_number;

        public function __construct()
        {

            add_action('init', array($this, 'schedule_fortnox_sync_izettle'), 99);
            add_filter('fortnox_get_sections', array($this, 'add_settings_section'), 230);
            add_filter('woocommerce_get_settings_fortnox_hub', array($this, 'get_settings'), 100, 2);
            add_filter('woocommerce_save_settings_fortnox_hub_izettle', array($this, 'save_settings_section'));
            add_filter('fortnox_payment_gateways', array($this, 'terms_of_payments_options'));
            add_action('wp_ajax_fortnox_sync_izettle', array($this, 'ajax_sync_izettle'));
            add_action('woocommerce_settings_fortnox_izettle_options', array($this, 'show_izettle_button'), 10);
            add_action('fortnox_izettle_sync', array($this, 'fortnox_izettle_sync'));

        }

        public function schedule_fortnox_sync_izettle()
        {

            if ('yes' == get_option('fortnox_create_izettle_payout_invoices')) {
                if (false === as_next_scheduled_action('fortnox_izettle_sync')) {
                    as_schedule_cron_action(strtotime('tomorrow 7 am'), '0 7 * * *', 'fortnox_izettle_sync');
                }
            } else {
                if (false !== as_next_scheduled_action('fortnox_izettle_sync')) {
                    as_unschedule_all_actions('fortnox_izettle_sync');
                }
            }

        }

        public function ajax_sync_izettle()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            $days_or_date = sanitize_key($_POST['sync_days']);

            WC_FH()->logger->add(sprintf('ajax_sync_izettle: Sync from "%s" requested', $days_or_date));

            if (is_numeric($days_or_date) || date_create_from_format('Y-m-d', $days_or_date)) {

                as_schedule_single_action(as_get_datetime_object()->getTimestamp(), 'fortnox_izettle_sync', array($days_or_date));

                if (is_numeric($days_or_date)) {

                    $start_date = date('Y-m-d', current_time('timestamp') - (DAY_IN_SECONDS * $days_or_date));
                    $response = array(
                        'result' => 'success',
                        'message' => sprintf(__('Zettle payouts from %s are being processed.', 'woo-fortnox-hub'), $start_date),
                    );

                } else {

                    $response = array(
                        'result' => 'success',
                        'message' => sprintf(__('Zettle payouts for %s are being processed.', 'woo-fortnox-hub'), $days_or_date),
                    );

                }

            } else {

                $response = array(
                    'result' => 'error',
                    'message' => sprintf(__('"%s" is not a valid date format or a numeric value.', 'woo-fortnox-hub'), $days_or_date),
                );

            }

            wp_send_json($response);

        }

        public function terms_of_payments_options($choice)
        {
            $izettle_array = array(
                'izettle_card' => new Fortnox_Hub_Mock_Payment_Gateway(__('Zettle card', 'woo-fortnox-hub')),
                'izettle_cash' => new Fortnox_Hub_Mock_Payment_Gateway(__('Zettle cash', 'woo-fortnox-hub')),
                'izettle_swish' => new Fortnox_Hub_Mock_Payment_Gateway(__('Zettle swish', 'woo-fortnox-hub')),
            );
            return (array_merge($choice, $izettle_array));
        }

        public function show_izettle_button()
        {
            echo '<div id=fortnox_titledesc_sync_izettle>';
            echo '<tr valign="top">';
            echo '<th scope="row" class="titledesc">';
            echo '<label for="fortnox_sync_izettle">' . __('Manual sync', 'woo-fortnox-hub') . '<span class="woocommerce-help-tip" data-tip="' . __('Sync WooCommerce products to Fortnox', 'woo-fortnox-hub') . '"></span></label>';
            echo '</th>';
            echo '<td class="forminp forminp-button">';
            echo '<button name="fortnox_sync_izettle" id="fortnox_sync_izettle" class="button">' . __('Syncronize', 'woo-fortnox-hub') . '</button>';
            echo '</td>';
            echo '</tr>';
            echo '</div>';
        }

        public function add_settings_section($sections)
        {
            if (!array_key_exists('izettle', $sections)) {
                $sections = array_merge($sections, array('izettle' => __('Zettle', 'woo-fortnox-hub')));
            }
            return $sections;
        }

        public function save_settings_section($true)
        {
            return $true;
        }

        public function get_settings($settings, $current_section)
        {
            if ('izettle' === $current_section) {

                $account_selection = apply_filters('fortnox_get_account_selection', array());

                $settings = array(
                    array(
                        'title' => __('Zettle options', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'fortnox_izettle_options',
                    ),
                    array(
                        'title' => __('Create invoice to match Zettle card payouts', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'default' => '',
                        'id' => 'fortnox_create_izettle_payout_invoices',
                    ),
                    array(
                        'title' => __('Fortnox customer number for Zettle-invoice', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'default' => '',
                        'id' => 'fortnox_izettle_customer_number',
                    ),
                    array(
                        'title' => __('Fee account for Zettle-sales', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => 6570,
                        'options' => $account_selection,
                        'id' => 'fortnox_izettle_fee_account',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_izettle_options',
                    ),
                );

            }
            return $settings;
        }

        protected function get_transaction_id($purchases, $transaction_uuid)
        {

            foreach ($purchases->purchases as $purchase) {
                foreach ($purchase->payments as $payment) {
                    if ($payment->uuid == $transaction_uuid) {
                        return $purchase->purchaseNumber;
                    }
                }
            }

            return 'n/a';

        }

        public function get_account_number($type)
        {
            if (in_array($type, array('CARD_PAYMENT_FEE', 'CARD_PAYMENT_FEE_REFUND', 'INVOICE_PAYMENT_FEE', 'PAYMENT_FEE', 'BANK_ACCOUNT_VERIFICATION', 'CASHBACK'))) {
                return get_option('fortnox_izettle_fee_account');
            }
            return get_option('fortnox_payment_account_izettle_card');
        }

        public function valid_transactions()
        {
            return array(
                'CARD_PAYMENT' => __('card payment', 'woo-fortnox-hub'),
                'CARD_PAYMENT_FEE' => __('card payment fee', 'woo-fortnox-hub'),
                'CARD_REFUND' => __('card refund', 'woo-fortnox-hub'),
                'CARD_PAYMENT_FEE_REFUND' => __('card payment fee refund', 'woo-fortnox-hub'),
                'INVOICE_PAYMENT' => __('invoice payment', 'woo-fortnox-hub'),
                'INVOICE_PAYMENT_FEE' => __('invoice payment fee', 'woo-fortnox-hub'),
                'PAYMENT' => __('external payment', 'woo-fortnox-hub'),
                'PAYMENT_FEE' => __('external payment fee', 'woo-fortnox-hub'),
                'BANK_ACCOUNT_VERIFICATION' => __('bank account verification', 'woo-fortnox-hub'),
                'CASHBACK' => __('adjustment of card payment fee', 'woo-fortnox-hub'),
                'VOUCHER_ACTIVATION' => __('voucher activation', 'woo-fortnox-hub'),
                'FAILED_PAYOUT' => __('failed payout', 'woo-fortnox-hub'),
                'EMONEY_TRANSFER' => __('emoney transfer', 'woo-fortnox-hub'),
                'FROZEN_FUNDS' => __('frozen funds', 'woo-fortnox-hub'),
                'FEE_DISCOUNT_REVOCATION' => __('fee discount revocation', 'woo-fortnox-hub'),
                'ADVANCE' => __('advance', 'woo-fortnox-hub'),
                'ADVANCE_DOWNPAYMENT' => __('advance downpayment', 'woo-fortnox-hub'),
                'ADVANCE_FEE_DOWNPAYMENT' => __('advance fee downpayment', 'woo-fortnox-hub'),
                'ADJUSTMENT' => __('adjustment', 'woo-fortnox-hub'),
                'PAYOUT' => __('payout', 'woo-fortnox-hub'),
            );
        }

        public function format_transaction_type($type)
        {
            $transactions = $this->valid_transactions();
            if (array_key_exists($type, $transactions)) {
                return $transactions[$type];
            };
            return '';
        }

        private function get_day_offset($date_to_process)
        {
            $payout_info_data = izettle_api()->get_payout_info();
            WC_FH()->logger->add(sprintf('process_izettle_payout: payout info %s', json_encode($payout_info_data, JSON_INVALID_UTF8_IGNORE)));

            if (isset($payout_info_data->data) && $payout_info_data->data->periodicity == 'WEEKLY') {
                $day_offset = DAY_IN_SECONDS * 7;
            } else {
                $day_offset = DAY_IN_SECONDS * (('1' == date('N', $date_to_process)) ? 3 : 1);
            }
            return $day_offset;
        }

        private function process_izettle_payout($date_to_process)
        {

            try {

                $date_to_process = is_numeric($date_to_process) ? $date_to_process : strtotime($date_to_process);

                $payout_date_start = date('Y-m-d\T00:00:01', $date_to_process);

                $args = array(
                    'accountTypeGroup' => 'LIQUID',
                    'includeTransactionType' => 'PAYOUT',
                    'start' => $payout_date_start,
                    'end' => date('Y-m-d\T23:59:59', $date_to_process),
                );

                $payout_transactions = izettle_api()->get_liquid_transactions($args);

                if (empty($payout_transactions->data)) {
                    WC_FH()->logger->add(sprintf('No Zettle payout data for %s', date('Y-m-d', $date_to_process)));
                    return;
                }

                $payout_args = array(
                    'at' => date('Y-m-d\T23:59:59', $date_to_process)
                );

                $payout_info = izettle_api()->get_payout_info($payout_args);

                if (empty($payout_info->data)) {
                    WC_FH()->logger->add(sprintf('No Zettle payout data for %s to get currency', date('Y-m-d', $date_to_process)));
                    return;
                }

                $payout_transaction = reset($payout_transactions->data);

                WC_FH()->logger->add(sprintf('process_izettle_payout: payout transaction %s', json_encode($payout_transaction, JSON_INVALID_UTF8_IGNORE)));

                $transactions = izettle_api()->get_liquid_transactions(array(
                    'accountTypeGroup' => 'LIQUID',
                    'includeTransactionType' => array(
                        array_keys($this->valid_transactions()),
                    ),
                    'start' => date('Y-m-d\T00:00:01', $date_to_process - (DAY_IN_SECONDS * 100)),
                    'end' => $payout_transaction->timestamp,
                ));

                WC_FH()->logger->add(sprintf('process_izettle_payout: payout transactions %s', json_encode($transactions, JSON_INVALID_UTF8_IGNORE)));

                $purchases = izettle_api()->get_purchases(array(
                    'startDate' => date('Y-m-d\T00:00:01', $date_to_process - (DAY_IN_SECONDS * 100)),
                    'endDate' => $payout_transaction->timestamp,
                ));

                WC_FH()->logger->add(sprintf('process_izettle_payout: purchases %s', json_encode($purchases, JSON_INVALID_UTF8_IGNORE)));

                $invoice_rows = array();
                foreach ($transactions->data as $transaction) {

                    if ($transaction->originatorTransactionType === 'PAYOUT') {
                        break;
                    }

                    $invoice_rows[] = array(
                        "AccountNumber" => $this->get_account_number($transaction->originatorTransactionType),
                        "DeliveredQuantity" => "1",
                        "Price" => $transaction->amount / 100,
                        "Description" => sprintf(__('Zettle %s #%s', 'woo-fortnox-hub'), $this->format_transaction_type($transaction->originatorTransactionType), $this->get_transaction_id($purchases, $transaction->originatingTransactionUuid)),
                    );

                }

                $invoice = array(
                    "CustomerNumber" => $this->customer_number,
                    "DueDate" => date('Y-m-d', $date_to_process),
                    "InvoiceDate" => date('Y-m-d', $date_to_process),
                    "InvoiceRows" => $invoice_rows,
                    "Currency" => strtoupper($payout_info->data->currencyId),
                    "ExternalInvoiceReference1" => $payout_transaction->originatingTransactionUuid,
                    "TermsOfPayment" => '',
                );

                $fortnox_invoice = WCFH_Util::check_if_invoice_already_created($payout_transaction->originatingTransactionUuid, true);

                if (!$fortnox_invoice) {
                    $invoice_created = WC_FH()->fortnox->create_invoice($invoice);
                    WC_FH()->logger->add(sprintf('process_izettle_payout: Zettle payout %s created Fortnox invoice %s', $payout_transaction->originatingTransactionUuid, $invoice_created['DocumentNumber']));
                    Fortnox_Notice::add(sprintf(__('Successfully created Fortnox Invoice %s from Zettle payouts for %s', 'woo-fortnox-hub'), $invoice_created['DocumentNumber'], date('Y-m-d', $date_to_process)), 'success');
                    return;
                }

                if (!rest_sanitize_boolean($fortnox_invoice['Booked']) && !rest_sanitize_boolean($fortnox_invoice['Cancelled']) && !$fortnox_invoice['FinalPayDate']) {
                    $invoice_created = WC_FH()->fortnox->update_invoice($fortnox_invoice['DocumentNumber'], $invoice);
                    WC_FH()->logger->add(sprintf('process_izettle_payout: Zettle payout %s updated Fortnox invoice %s', $payout_transaction->originatingTransactionUuid, $invoice_created['DocumentNumber']));
                    Fortnox_Notice::add(sprintf(__('Successfully updated Fortnox Invoice %s from Zettle payouts for %s', 'woo-fortnox-hub'), $invoice_created['DocumentNumber'], date('Y-m-d', $date_to_process)), 'success');
                    return;
                }

                if (rest_sanitize_boolean($fortnox_invoice['Cancelled'])) {
                    WC_FH()->logger->add(sprintf('process_izettle_payout: Zettle payout %s already booked on Fortnox Invoice %s that has been cancelled', $payout_transaction->originatingTransactionUuid, $fortnox_invoice['DocumentNumber']));
                    return;
                }

                if (rest_sanitize_boolean($fortnox_invoice['Booked']) || $fortnox_invoice['FinalPayDate']) {
                    WC_FH()->logger->add(sprintf('process_izettle_payout: Zettle payout %s already booked on Fortnox invoice %s', $payout_transaction->originatingTransactionUuid, $fortnox_invoice['DocumentNumber']));
                    return;
                }

                WC_FH()->logger->add(sprintf('process_izettle_payout: Zettle payout %s already created Fortnox Invoice %s, no further processing will be made', $payout_transaction->originatingTransactionUuid, $fortnox_invoice['DocumentNumber']));

            } catch (Fortnox_API_Exception $e) {

                Fortnox_Notice::add(sprintf(__('Something went wrong when processing Zettle payouts for %s', 'woo-fortnox-hub'), date('Y-m-d', $date_to_process)), 'error');
                $e->write_to_logs();

            }
        }

        public function fortnox_izettle_sync($days_or_date = 1)
        {

            $this->customer_number = get_option('fortnox_izettle_customer_number');

            if (!$this->customer_number) {
                Fortnox_Notice::add(sprintf(__('In order to process payouts, a (Fortnox) customer number must be present in the %s settings', 'woo-fortnox-hub'), 'Zettle'), 'error');
                WC_FH()->logger->add('fortnox_izettle_sync: Customer number for Zettle invoices must be configured');
                return;
            }

            if (is_numeric($days_or_date)) {

                WC_FH()->logger->add(sprintf('fortnox_izettle_sync: Starting to process Zettle payouts %s day back', $days_or_date));

                for ($days = $days_or_date; $days > 0; $days--) {
                    $this->process_izettle_payout(current_time('timestamp') - (DAY_IN_SECONDS * $days));
                }

            } else {

                WC_FH()->logger->add(sprintf('fortnox_izettle_sync: Starting to process Zettle payouts for %s', $days_or_date));
                $this->process_izettle_payout($days_or_date);

            }

        }

    }

    class Fortnox_Hub_Mock_Payment_Gateway
    {

        private $description;
        public $enabled = 'yes';

        public function __construct($description)
        {
            $this->description = $description;
        }

        public function is_available()
        {
            return true;
        }

        public function get_title()
        {
            return $this->description;
        }
    }

    new Fortnox_Hub_Zettle_Handler();

}
