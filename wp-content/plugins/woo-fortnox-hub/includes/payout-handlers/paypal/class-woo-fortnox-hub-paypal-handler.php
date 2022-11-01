<?php

/**
 * Creating Supplier invoices from a PayPal payments
 *
 * @package   BjornTech_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2020 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_PayPal_Handler', false)) {

    class Fortnox_Hub_PayPal_API
    {

        private $api_username;
        private $api_password;
        private $api_signature;
        private $api_endpoint;

        protected $request_handler;

        public function __construct($settings)
        {

            $this->api_username = $settings['api_username'];
            $this->api_password = $settings['api_password'];
            $this->api_signature = $settings['api_signature'];
            $this->api_endpoint = ('yes' == $settings['testmode']) ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';

        }

        /**
         * Get refund request args.
         *
         * @param  WC_Order $order Order object.
         * @param  float    $amount Refund amount.
         * @param  string   $reason Refund reason.
         * @return array
         */
        public function create_transaction_detail_request($order)
        {
            $request = array(
                'VERSION' => '84.0',
                'SIGNATURE' => $this->api_signature,
                'USER' => $this->api_username,
                'PWD' => $this->api_password,
                'METHOD' => 'GetTransactionDetails',
                'TRANSACTIONID' => $order->get_transaction_id(),
            );
            return $request;
        }

        public function get_transaction_details($order)
        {
            $params = array(
                'headers' => array(
                    'Content-type: application/x-www-form-urlencoded',
                ),
                'method' => 'POST',
                'body' => $this->create_transaction_detail_request($order),
                'timeout' => 70,
                'user-agent' => 'WooCommerce/' . WC()->version,
                'httpversion' => '1.1',
            );

            $raw_response = wp_safe_remote_post(
                $this->api_endpoint,
                $params
            );

            if (empty($raw_response['body'])) {
                throw new Fortnox_Exception('Empty Paypal response');
            } elseif (is_wp_error($raw_response)) {
                $code = $raw_response->get_error_code();
                $error = $raw_response->get_error_message($code);
                throw new Fortnox_Exception(sprintf('Got error %s with message %s when connecting to Paypal', $code, $error), 0, null, $this->api_endpoint);
            }

            parse_str($raw_response['body'], $response);

            return (object) $response;
        }

        public function get_balance_history()
        {

        }

        public function get_payouts()
        {

        }

    }

    class Fortnox_PayPal_Handler
    {

        protected $paypal;
        protected $settings;

        public function __construct()
        {
            $this->settings = get_option('woocommerce_paypal_settings');

            if ($this->settings && array_key_exists('enabled', $this->settings) && 'yes' == $this->settings['enabled']) {

                add_action('init', array($this, 'schedule_fortnox_sync_paypal'));
                add_filter('fortnox_get_sections', array($this, 'add_settings_section'), 210);
                add_filter('woocommerce_get_settings_fortnox_hub', array($this, 'get_settings'), 80, 2);
                add_filter('woocommerce_save_settings_fortnox_hub_paypal', array($this, 'save_settings_section'));
                add_action('fortnox_process_changed_invoices_action_all', array($this, 'set_paypal_customer_invoice_to_paid'), 600, 3);
                add_action('wp_ajax_fortnox_sync_paypal', array($this, 'ajax_sync_paypal'));
                add_action('woocommerce_settings_fortnox_paypal_options', array($this, 'show_paypal_button'), 10);
                add_action('fortnox_paypal_sync', array($this, 'fortnox_paypal_sync'));
            }

        }

        public function schedule_fortnox_sync_paypal()
        {

            if ('yes' == get_option('fortnox_create_paypal_invoices')) {
                if (false === as_next_scheduled_action('fortnox_paypal_sync')) {
                    as_schedule_cron_action(strtotime('tomorrow 7 am'), '0 7 * * *', 'fortnox_paypal_sync');
                }
            } else {
                if (false !== as_next_scheduled_action('fortnox_paypal_sync')) {
                    as_unschedule_all_actions('fortnox_paypal_sync');
                }
            }

        }

        public function show_paypal_button()
        {
            echo '<div id=fortnox_titledesc_sync_paypal>';
            echo '<tr valign="top">';
            echo '<th scope="row" class="titledesc">';
            echo '<label for="fortnox_sync_paypal">' . __('Manual sync', 'woo-fortnox-hub') . '<span class="woocommerce-help-tip" data-tip="' . __('Sync WooCommerce products to Fortnox', 'woo-fortnox-hub') . '"></span></label>';
            echo '</th>';
            echo '<td class="forminp forminp-button">';
            echo '<button name="fortnox_sync_paypal" id="fortnox_sync_paypal" class="button">' . __('Syncronize', 'woo-fortnox-hub') . '</button>';
            echo '</td>';
            echo '</tr>';
            echo '</div>';
        }

        public function add_syncdate_to_queue($days_to_scan = 1)
        {
            $start_date = date('Y-m-d', current_time('timestamp') - (DAY_IN_SECONDS * $days_to_scan));
            as_schedule_single_action(as_get_datetime_object()->getTimestamp(), 'fortnox_paypal_sync', array($days_to_scan));
            return $start_date;
        }

        public function ajax_sync_paypal()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            $sync_date = $this->add_syncdate_to_queue($_POST['sync_days']);

            $response = array(
                'result' => 'success',
                'message' => sprintf(__('Paypal payouts from %s are being processed.', 'woo-fortnox-hub'), $sync_date),
            );

            wp_send_json($response);
        }

        public function add_settings_section($sections)
        {
            if (!array_key_exists('paypal', $sections)) {
                $sections = array_merge($sections, array('paypal' => __('PayPal', 'woo-fortnox-hub')));
            }
            return $sections;
        }

        public function save_settings_section($true)
        {
            return $true;
        }

        public function get_settings($settings, $current_section)
        {
            if ('paypal' === $current_section) {
                $account_selection = apply_filters('fortnox_get_account_selection', array());
                $payment_account = get_option('fortnox_payment_account_paypal');
                $settings = array(
                    array(
                        'title' => __('PayPal options', 'woo-fortnox-hub'),
                        'type' => 'title',
                        'desc' => '',
                        'id' => 'fortnox_paypal_options',
                    ),
                    array(
                        'title' => __('Automatic payout invoices', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'default' => '',
                        'id' => 'fortnox_create_paypal_invoices',
                    ),
                    array(
                        'title' => __('Fortnox customer number for PayPal-invoice', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'default' => '',
                        'id' => 'fortnox_paypal_customer_number',
                    ),
                    array(
                        'title' => __('Paypal sales', 'woo-fortnox-hub'),
                        'type' => 'infotext',
                        'desc' => sprintf(__('Change by selecting another payment method at the payments settings link.', 'woo-fortnox-hub')),
                        'text' => ' ' . (!empty($account_selection) && array_key_exists($payment_account, $account_selection)) ? $account_selection[$payment_account] : __('Select mode of payment in the "Payment" section', 'woo-fortnox-hub'),
                    ),
                    array(
                        'title' => __('Fee for PayPal-sales', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => 6570,
                        'options' => $account_selection,
                        'id' => 'fortnox_paypal_fee_account',
                    ),
                    array(
                        'type' => 'sectionend',
                        'id' => 'fortnox_paypal_options',
                    ),
                );
            }
            return $settings;
        }

        public function set_paypal_customer_invoice_to_paid($fn_invoice, $order, $order_id)
        {

            if (!method_exists($order, 'get_payment_method') || 'paypal' != strtolower($order->get_payment_method())) {
                return;
            }

            $this->paypal = new Fortnox_Hub_PayPal_API($this->settings);

            $fn_invoice_number = $fn_invoice['DocumentNumber'];
            $mode_of_payment = get_option('fortnox_mode_of_payment_paypal');
            $invoice_payments = WC_FH()->fortnox->getInvoicePaymentsByInvoiceNumber($fn_invoice_number);
            $automatic_payment = get_option('fortnox_automatic_payment_paypal');

            WC_FH()->logger->add(sprintf('set_paypal_customer_invoice_to_paid (%s): Processing PayPal payment for Fortnox Invoice number %s using %s as payment mode', $order_id, $fn_invoice_number, $mode_of_payment));

            if ($mode_of_payment && !$invoice_payments && (($fn_invoice['Sent'] && 'sent' == $automatic_payment) || ($fn_invoice['Booked'] && 'booked' == $automatic_payment) || ('created' == $automatic_payment))) {

                WC_FH()->logger->add(sprintf('set_paypal_customer_invoice_to_paid (%s): Creating PayPal payment for Fortnox Invoice number %s', $order_id, $fn_invoice_number));

                $payment_date_datetime = $order->get_date_paid();
                $payment_date = $payment_date_datetime->date('Y-m-d');
                $invoice_date_datetime = new DateTime($fn_invoice['InvoiceDate']);
                if ($payment_date_datetime < $invoice_date_datetime) {
                    $payment_date = $fn_invoice['InvoiceDate'];
                    WC_FH()->logger->add(sprintf('Changed to payment date %s', $payment_date));
                }
                if ($payment_date_datetime < $invoice_date_datetime) {
                    $payment_date = $fn_invoice['InvoiceDate'];
                    WC_FH()->logger->add(sprintf('Changed to payment date %s', $payment_date));
                }

                $transaction_details = $this->paypal->get_transaction_details($order);

                WC_FH()->logger->add(sprintf('set_paypal_customer_invoice_to_paid (%s): %s', $order_id, json_encode($transaction_details, JSON_INVALID_UTF8_IGNORE)));

                if ('Completed' == $transaction_details->PAYMENTSTATUS) {
                    $exchange_rate = ('SEK' == $transaction_details->CURRENCYCODE) ? 1 : $transaction_details->EXCHANGERATE;
                    $payment_request = array(
                        'InvoiceNumber' => $fn_invoice_number,
                        'Amount' => $fn_invoice['Total'] * $exchange_rate,
                        'AmountCurrency' => $fn_invoice['Total'],
                        'CurrencyRate' => $exchange_rate,
                        'PaymentDate' => $payment_date,
                        'ModeOfPayment' => get_option('fortnox_mode_of_payment_paypal'),
                        'ModeOfPaymentAccount' => get_option('fortnox_payment_account_paypal'),
                    );

                    $payment_response = WC_FH()->fortnox->createInvoicePayment($payment_request);
                    WC_FH()->logger->add(sprintf('Created Fortnox payment %s', $order_id, $payment_response['Number']));
                    $bookkeep_response = WC_FH()->fortnox->bookkeepInvoicePayment($payment_response['Number']);
                    WC_FH()->logger->add(sprintf('set_paypal_customer_invoice_to_paid (%s): Fortnox Invoice %s booked', $order_id, $fn_invoice['DocumentNumber']));
                } else {
                    WC_FH()->logger->add(sprintf('set_paypal_customer_invoice_to_paid (%s): Paypal payment has status %s, not booked', $order_id, $transaction_details->PAYMENTSTATUS));
                }

            }

        }

        public function fortnox_paypal_sync($days_to_scan = false)
        {
            try {

                $this->paypal = new Fortnox_Hub_PayPal_API($this->settings);

                if (false === $days_to_scan) {
                    $start_date = date('Y-m-d', current_time('timestamp') - DAY_IN_SECONDS);
                }

                if ($customer_number = get_option('fortnox_paypal_customer_number', false)) {

                    $sync_type = 'PayPal';

                    for ($days = $days_to_scan; $days > 0; $days--) {

                        $start_date = date('Y-m-d', current_time('timestamp') - (DAY_IN_SECONDS * $days));

                        if ($days == 1) {
                            $args = array(
                                'created' => array(
                                    'gte' => strtotime(date('m/d/Y 00:00:01', current_time('timestamp') - DAY_IN_SECONDS)),
                                ),
                            );
                        } else {
                            $args = array(
                                'created' => array(
                                    'gte' => strtotime(date('m/d/Y 00:00:01', strtotime($start_date))),
                                    'lte' => strtotime(date('m/d/Y 23:59:59', strtotime($start_date))),
                                ),
                            );
                        }

                        $payouts = $this->paypal->get_payouts($args);

                        foreach ($payouts->data as $payout) {

                            $invoice_rows = array();

                            if (!($fortnox_invoice = WCFH_Util::check_if_invoice_already_created($payout->id))) {
                                WC_FH()->logger->add(sprintf('Processing payout %s', $payout->id));

                                $balance_transactions = $this->paypal->get_balance_history(array('payout' => $payout->id));

                                foreach ($balance_transactions->data as $balance_transaction) {
                                    if ('charge' == $balance_transaction->type) {
                                        $invoice_rows[] = array(
                                            "AccountNumber" => get_option('fortnox_paypal_balance_account', 1540),
                                            "DeliveredQuantity" => "1",
                                            "Price" => $balance_transaction->amount / 100,
                                            "Description" => $balance_transaction->description,
                                        );

                                        if (!empty($balance_transaction->fee_details)) {
                                            foreach ($balance_transaction->fee_details as $fee_detail) {
                                                $invoice_rows[] = array(
                                                    "AccountNumber" => get_option('fortnox_paypal_fee_account', 6570),
                                                    "DeliveredQuantity" => "1",
                                                    "Price" => -$fee_detail->amount / 100,
                                                    "Description" => $fee_detail->description,
                                                );
                                            }
                                        }
                                    }
                                }

                                if (!empty($invoice_rows)) {
                                    $invoice = array(
                                        "CustomerNumber" => $customer_number,
                                        "DueDate" => date('Y-m-d', $payout->arrival_date),
                                        "InvoiceDate" => date('Y-m-d', $payout->created),
                                        "InvoiceRows" => $invoice_rows,
                                        "Currency" => strtoupper($payout->currency),
                                        "ExternalInvoiceReference1" => $payout->id,
                                        "TermsOfPayment" => '',
                                    );

                                    $invoice_created = WC_FH()->fortnox->create_invoice($invoice);
                                    WC_FH()->logger->add(sprintf('PayPal payout %s created Fortnox invoice %s', $payout->id, $invoice_created['DocumentNumber']));
                                }

                            } elseif ($fortnox_invoice) {
                                WC_FH()->logger->add(sprintf('PayPal payout %s has already been creating Fortnox invoice %s', $payout->id, $fortnox_invoice['DocumentNumber']));
                            } else {
                                WC_FH()->logger->add(sprintf('PayPal payout %s has status %s and will not be processed', $payout->id, $payout->status));
                            }
                        }
                    }

                    if ($invoice_created['DocumentNumber']) {
                        Fortnox_Notice::add(sprintf(__('Successfully created invoice(s) from %s payouts', 'woo-fortnox-hub'), $sync_type), 'success');
                    }

                } else {
                    Fortnox_Notice::add(sprintf(__('In order to process payouts, a customer number must be present in the %s settings', 'woo-fortnox-hub'), 'PayPal'), 'error');
                    WC_FH()->logger->add('Customer number for Paypal invoices must be configured');
                }

            } catch (Throwable $t) {
                Fortnox_Notice::add(sprintf(__('Something went wrong when processing PayPal payouts', 'woo-fortnox-hub')), 'error');
                WC_FH()->logger->add(print_r($t, true));
            }
        }

    }

    new Fortnox_PayPal_Handler();
}
