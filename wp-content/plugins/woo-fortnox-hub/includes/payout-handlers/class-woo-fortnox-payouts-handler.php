<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Payouts_Handler', false)) {

    class Fortnox_Hub_Payouts_Handler
    {

        public $payout_id;
        public $payment_method;
        public $payout_name;
        public $payouts_handler;
        public $document_type;
        public $supports = [];
        public $fee_vat = [];
        public $bank_account;
        public $fee_account;

        public function __construct()
        {

            add_action('init', array($this, 'schedule_sync'), 99);
            add_filter('fortnox_get_sections', array($this, 'add_settings_section'), 200);
            add_filter('woocommerce_get_settings_fortnox_hub', array($this, 'get_settings'), 70, 2);
            add_action('wp_ajax_fortnox_sync_' . $this->payout_id, array($this, 'ajax_sync_payments'));
            add_action('woocommerce_settings_fortnox_' . $this->payout_id . '_settings', array($this, 'show_sync_button'), 10);

            add_action('fortnox_process_' . $this->payout_id . '_payouts', array($this, 'process_payouts'));
            add_action('fortnox_process_' . $this->payout_id . '_payouts_manually', array($this, 'process_payouts'));

            add_action('fortnox_book_and_pay_' . $this->payout_id . '_invoice', array($this, 'book_and_pay_invoice'));

            $payment_account = get_option('fortnox_payment_account_' . $this->payment_method);
            $this->balance_account = get_option('fortnox_' . $this->payout_id . '_balance_account', $payment_account);

            $this->document_type = get_option('fortnox_' . $this->payout_id . '_document_type', FN_PAYOUT_DOCTYPE);
            $this->bank_account = get_option('fortnox_' . $this->payout_id . '_bank_account', FN_PAYOUT_BANK_ACCOUNT);
            $this->fee_account = get_option('fortnox_' . $this->payout_id . '_fee_account', FN_PAYOUT_FEE_ACCOUNT);

            $this->payouts_handler = $this->create_payout_object();

        }

        public function payment_gw_credentials_settings()
        {
            return [];
        }

        public function payout_gw_specific_settings()
        {
            return [];
        }

        public function payment_gw_account_settings($account_selection)
        {
            return [];
        }

        public function create_payout_object()
        {

            $payout_id = str_replace('-', '_', $this->payout_id);
            $document_type = str_replace('_', '-', $this->document_type);
            require_once dirname(__FILE__) . "/$this->payout_id/class-woo-fortnox-hub-payouts-$document_type-$this->payout_id.php";

            $connection_class = 'Fortnox_Hub_Payouts_' . $this->document_type . '_' . $payout_id;
            return new $connection_class();

        }

        public function schedule_sync()
        {

            if ('yes' == get_option('fortnox_create_' . $this->payout_id . '_payouts_automatically')) {
                if (false === as_next_scheduled_action('fortnox_process_' . $this->payout_id . '_payouts')) {
                    as_schedule_cron_action(strtotime('tomorrow 7 am'), '0 7 * * *', 'fortnox_process_' . $this->payout_id . '_payouts');
                }
            } else {
                if (false !== as_next_scheduled_action('fortnox_process_' . $this->payout_id . '_payouts')) {
                    as_unschedule_all_actions('fortnox_process_' . $this->payout_id . '_payouts');
                }
            }
        }

        public function get_settings($settings, $current_section)
        {
            if ($this->payout_id === $current_section) {

                $document_type = get_option('fortnox_' . $this->payout_id . '_document_type', FN_PAYOUT_DOCTYPE);

                $include_reversed_vat = wc_string_to_bool(get_option('fortnox_' . $this->payout_id . '_include_reversed_vat'));

                $account_selection = apply_filters('fortnox_get_account_selection', array(), false !== strpos('invoice', $document_type));

                $payout_types = array_intersect_key([
                    'detailed_invoice' => __(' Detailed Invoice (all transactions on invoice)', 'woo-fortnox-hub'),
                    'invoice' => __('Invoice', 'woo-fortnox-hub'),
                    'detailed_voucher' => __('Detailed Voucher (all transactions on voucher)', 'woo-fortnox-hub'),
                    'voucher' => __('Voucher', 'woo-fortnox-hub'),
                ], array_fill_keys($this->supports, 'foo'));

                $settings[] = [
                    'title' => sprintf(__('%s payout processing settings', 'woo-fortnox-hub'), $this->payout_name),
                    'type' => 'title',
                    'desc' => '<div class=fortnox_infobox>' . sprintf(__('In this section you can configure the plugin to generate an Fortnox Invoice or Voucher in order to automate the booking of payouts from %s</BR></BR>Click "Syncronize" and enter the number of days back or a date in the format of YYYY-MM-DD to start searching for payouts and generate an Invoice or Voucher for each payout date starting with the selected date or selected days back.</BR></BR>If you check "Generate automatically" the plugin will automatically start the processing for todays date at 07.00 every morning.</BR></BR>The plugin can also automatically book (in relevant cases) and set the invoices (created by your orders) generating the payout to paid.', 'woo-fortnox-hub'), $this->payout_name, $this->payout_id) . '</div>',
                    'id' => 'fortnox_' . $this->payout_id . '_settings',
                ];

                $settings[] = [
                    'title' => __('Type of document', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => FN_PAYOUT_DOCTYPE,
                    'options' => $payout_types,
                    'id' => 'fortnox_' . $this->payout_id . '_document_type',
                ];

                $settings[] = [
                    'title' => __('Generate automatically', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'default' => '',
                    'id' => 'fortnox_create_' . $this->payout_id . '_payouts_automatically',
                ];

                if ('invoice' === $document_type || 'detailed_invoice' === $document_type) {

                    $settings[] = [
                        'title' => __('Fortnox customer number used on Invoice', 'woo-fortnox-hub'),
                        'type' => 'number',
                        'default' => '',
                        'id' => 'fortnox_' . $this->payout_id . '_customer_number',
                    ];

                }

                $type = str_replace('detailed_', '', $document_type);

                if (false !== strpos($this->document_type, 'invoice')) {

                    $accounting_method = WCFH_Util::get_accounting_method();
                    if ('ACCRUAL' === $accounting_method) {
                        $settings[] = [
                            'title' => __('Book invoices', 'woo-fortnox-hub'),
                            'type' => 'checkbox',
                            'desc' => sprintf(__('Book invoices included in a payout when the payout %s has been created successfully.', 'woo-fortnox-hub'), $type),
                            'default' => '',
                            'id' => 'fortnox_' . $this->payout_id . '_book_included_invoice',
                        ];
                    }

                    $settings[] = [
                        'title' => __('Set invoices to paid', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => sprintf(__('Set invoices included in a payout to paid when the payout %s has been created successfully.', 'woo-fortnox-hub'), $type),
                        'default' => '',
                        'id' => 'fortnox_' . $this->payout_id . '_set_included_invoice_to_paid',
                    ];

                }

                if ('voucher' === $document_type) {
                    $voucher_series = is_array($voucher_series = apply_filters('fortnox_get_voucher_series', [])) ? $voucher_series : [];

                    $series = [];
                    foreach ($voucher_series as $voucher_serie) {
                        $series[$voucher_serie['Code']] = sprintf('%s - %s', $voucher_serie['Code'], $voucher_serie['Description']);
                    }
                    $settings[] = [
                        'title' => __('Voucher series', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => 'A',
                        'options' => $series,
                        'id' => 'fortnox_' . $this->payout_id . '_voucher_serie',
                    ];
                }

                $settings = array_merge($settings, $this->payout_gw_specific_settings());

                $settings[] = [
                    'title' => __('Balance account', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => get_option('fortnox_payment_account_' . $this->payment_method),
                    'options' => $account_selection,
                    'id' => 'fortnox_' . $this->payout_id . '_balance_account',
                ];

                $settings[] = [
                    'title' => __('Fee account', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => FN_PAYOUT_FEE_ACCOUNT,
                    'options' => $account_selection,
                    'id' => 'fortnox_' . $this->payout_id . '_fee_account',
                ];

                if (in_array('incoming', $this->fee_vat)) {
                    $settings[] = [
                        'title' => __('Fee VAT account', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => FN_PAYOUT_FEE_VAT,
                        'options' => $account_selection,
                        'id' => 'fortnox_' . $this->payout_id . '_fee_vat_account',
                    ];
                }

                $settings = array_merge($settings, $this->payment_gw_account_settings($account_selection));

                if ('voucher' === $document_type) {

                    $settings[] = [
                        'title' => __('Bank account', 'woo-fortnox-hub'),
                        'type' => 'select',
                        'default' => FN_PAYOUT_BANK_ACCOUNT,
                        'options' => $account_selection,
                        'id' => 'fortnox_' . $this->payout_id . '_bank_account',
                    ];

                    if (in_array('reversed', $this->fee_vat)) {

                        $settings[] = [
                            'title' => __('Include reversed VAT.', 'woo-fortnox-hub'),
                            'type' => 'checkbox',
                            'desc' => sprintf(__('Calculate and include reversed VAT on the voucher.', 'woo-fortnox-hub')),
                            'default' => '',
                            'id' => 'fortnox_' . $this->payout_id . '_include_reversed_vat',
                        ];

                        if ($include_reversed_vat) {

                            $settings[] = [
                                'title' => __('Outgoing reversed VAT', 'woo-fortnox-hub'),
                                'type' => 'select',
                                'default' => FN_OUTGOING_REVERSED_VAT,
                                'options' => $account_selection,
                                'id' => 'fortnox_' . $this->payout_id . '_outgoing_reversed_vat',
                            ];

                            $settings[] = [
                                'title' => __('Incoming calculated VAT', 'woo-fortnox-hub'),
                                'type' => 'select',
                                'default' => FN_INCOMING_CALCULATED_VAT,
                                'options' => $account_selection,
                                'id' => 'fortnox_' . $this->payout_id . '_incoming_calculated_vat',
                            ];

                        }

                    }
                }

                $advanced_settings = [];

                if ('voucher' === $document_type || 'detailed_voucher' === $document_type) {

                    $voucher_history = get_option('fortnox_payout_voucher_history_' . $this->payout_id, array());

                    $voucher_options = array();
                    foreach ($voucher_history as $voucher) {
                        $voucher_options[$voucher] = $voucher;
                    }

                    $advanced_settings[] = [
                        'title' => __('Processed payouts', 'woo-fortnox-hub'),
                        'type' => 'multiselect',
                        'class' => 'wc-enhanced-select',
                        'css' => 'width: 400px;',
                        'desc' => __('Remove the payout that you do need to process again. Be careful...removing the wrong id could cause double bookings, ', 'woo-fortnox-hub'),
                        'options' => $voucher_options,
                        'id' => 'fortnox_payout_voucher_history_' . $this->payout_id,
                    ];

                }

                if ($advanced_settings) {
                    $settings[] = [
                        'title' => __('Show advanced settings', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'default' => '',
                        'id' => 'fortnox_' . $this->payout_id . '_show_advanced_settings',
                    ];
                }

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_' . $this->payout_id . '_settings',
                ];

                $gw_credentials_settings = $this->payment_gw_credentials_settings();

                if ($gw_credentials_settings) {

                    $settings[] = [
                        'title' => sprintf(__('%s payout credentials', 'woo-fortnox-hub'), $this->payout_name),
                        'type' => 'title',
                        'id' => 'fortnox_' . $this->payout_id . '_credentials',
                    ];

                    $settings = array_merge($settings, $gw_credentials_settings);

                    $settings[] = [
                        'type' => 'sectionend',
                        'id' => 'fortnox_' . $this->payout_id . '_credentials',
                    ];

                }

                if (wc_string_to_bool(get_option('fortnox_' . $this->payout_id . '_show_advanced_settings'))) {

                    $settings[] = [
                        'title' => sprintf(__('%s advanced settings', 'woo-fortnox-hub'), $this->payout_name),
                        'type' => 'title',
                        'id' => 'fortnox_' . $this->payout_id . '_advanced',
                    ];

                    $settings = array_merge($settings, $advanced_settings);

                    $settings[] = [
                        'type' => 'sectionend',
                        'id' => 'fortnox_' . $this->payout_id . '_advanced',
                    ];

                }

            }

            return $settings;

        }

        public function add_settings_section($sections)
        {
            if (!array_key_exists($this->payout_id, $sections)) {
                $sections = array_merge($sections, array($this->payout_id => $this->payout_name));
            }
            return $sections;
        }

        public function show_sync_button()
        {
            echo '<div id=fortnox_titledesc_sync_payouts>';
            echo '<tr valign="top">';
            echo '<th scope="row" class="titledesc">';
            echo '<label for="fortnox_sync_' . $this->payout_id . '">' . __('Manual sync', 'woo-fortnox-hub') . '<span class="woocommerce-help-tip" data-tip="' . sprintf(__('Create %s payout invoices', 'woo-fortnox-hub'), $this->payout_id) . '"></span></label>';
            echo '</th>';
            echo '<td class="forminp forminp-button">';
            echo '<button class="fortnox_sync_payouts button" id="fortnox_sync_' . $this->payout_id . '">' . __('Syncronize', 'woo-fortnox-hub') . '</button>';
            echo '</td>';
            echo '</tr>';
            echo '</div>';
        }

        public function format_date($start_date)
        {
            return is_numeric($start_date) ? date('Y-m-d', current_time('timestamp') - (DAY_IN_SECONDS * $start_date)) : $start_date;
        }

        public function ajax_sync_payments()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-fortnox-hub')) {
                wp_die();
            }

            $days_or_date = sanitize_key($_POST['sync_days']);

            if (is_numeric($days_or_date) || date_create_from_format('Y-m-d', $days_or_date)) {

                $start_date = $this->format_date($days_or_date);
                as_schedule_single_action(as_get_datetime_object()->getTimestamp(), 'fortnox_process_' . $this->payout_id . '_payouts_manually', array($start_date));
                WC_FH()->logger->add(sprintf('ajax_sync_payments: Manual processing of "%s" payouts from %s queued', $this->payout_id, $start_date));

                $response = array(
                    'result' => 'success',
                    'message' => sprintf(__('%s payouts from %s have been added to the processing queue.', 'woo-fortnox-hub'), $this->payout_name, $start_date),
                );

            } else {

                $response = array(
                    'result' => 'error',
                    'message' => sprintf(__('"%s" is not a valid date format or a numeric value.', 'woo-fortnox-hub'), $days_or_date),
                );
                WC_FH()->logger->add(sprintf('ajax_sync_payments: Manual processing of "%s" payouts had %s as non vaild parameter', $this->payout_id, $days_or_date));

            }

            wp_send_json($response);
        }

        public function process_payouts($start_date = false)
        {

            try {

                WC_FH()->logger->add(sprintf('process_payouts: Starting processing of "%s" payouts starting from %s', $this->payout_id, $start_date));

                if (!$this->payouts_handler->payout_type_specific_check()) {
                    return;
                }

                if (false === $start_date) {
                    $start_date = date('Y-m-d', current_time('timestamp') - (DAY_IN_SECONDS));
                }

                $processed_order_ids = $this->process_payout_document($start_date);

                foreach ($processed_order_ids as $processed_order_id) {
                    as_enqueue_async_action('fortnox_book_and_pay_' . $this->payout_id . '_invoice', array($processed_order_id));
                }

            } catch (Fortnox_API_Exception $e) {
                Fortnox_Notice::add(sprintf(__('Something went wrong when processing %s payouts', 'woo-fortnox-hub'), $this->payout_name), 'error');
                $e->write_to_logs();
            }

            WC_FH()->logger->add(sprintf('process_payouts: Finished processing "%s" payouts starting from %s', $this->payout_id, $start_date));

        }

        public function book_and_pay_invoice($order_id)
        {

            try {

                WC_FH()->logger->add(sprintf('book_and_pay_invoice: Starting book and pay process for order id %s', $order_id));

                $order = WCFH_Util::get_order_by_order_number($order_id);
                if (!$order) {
                    WC_FH()->logger->add(sprintf('book_and_pay_invoice: No valid wc order found for order id %s', $order_id));
                    return;
                }

                // Get order id in case the order id was a order number
                $order_id = $order->get_id();

                // Get the Fortnox Invoice created by the order
                $fn_invoice_number = WCFH_Util::get_fortnox_invoice_number($order_id);
                if (!$fn_invoice_number) {
                    WC_FH()->logger->add(sprintf('book_and_pay_invoice: No valid fortnox invoice found for order id %s', $order_id));
                    return;
                }

                $fn_invoice = WC_FH()->fortnox->get_invoice($fn_invoice_number);

                if ('ACCRUAL' === WCFH_Util::get_accounting_method() && wc_string_to_bool(get_option('fortnox_' . $this->payout_id . '_book_included_invoice'))) {
                    $this->bookkeep_invoice($order, $order_id, $fn_invoice);
                }

                if (wc_string_to_bool(get_option('fortnox_' . $this->payout_id . '_set_included_invoice_to_paid'))) {
                    $this->set_invoice_to_paid($order, $order_id, $fn_invoice);
                }

            } catch (Fortnox_API_Exception $e) {

                $e->write_to_logs();
                Fortnox_Notice::add(sprintf(__("Fortnox Hub: %s when processing order %s", 'woo-fortnox-hub'), $e->getMessage(), $order_id));

            }

        }

        public function bookkeep_invoice($order, $order_id, $fn_invoice)
        {

            if ('ACCRUAL' !== $fn_invoice['AccountingMethod']) {
                return;
            }

            if (rest_sanitize_boolean($fn_invoice['Booked'])) {
                WC_FH()->logger->add(sprintf('bookkeep_invoice (%s): Fortnox Invoice %s already booked', $order_id, $fn_invoice['DocumentNumber']));
                return;
            }

            WC_FH()->fortnox->book_invoice($fn_invoice['DocumentNumber']);
            WC_FH()->logger->add(sprintf('bookkeep_invoice (%s): Booked Fortnox Invoice %s', $order_id, $fn_invoice['DocumentNumber']));

        }

        public function set_invoice_to_paid($order, $order_id, $fn_invoice)
        {

            // Never handle refund orders
            if ($order->get_parent_id()) {
                return;
            }

            // Check if the Fortnox order is already set to paid
            if ($this->invoice_is_paid($order_id, $fn_invoice)) {
                return;
            }

            $payment_date_datetime = $order->get_date_paid();
            $payment_date = $payment_date_datetime->date('Y-m-d');
            $payment_method = WCFH_Util::get_payment_method($order, 'set_invoice_to_paid');
            $mode_of_payment = get_option('fortnox_mode_of_payment_' . $payment_method);
            $invoice_date_datetime = new DateTime($fn_invoice['InvoiceDate']);
            if ($payment_date_datetime < $invoice_date_datetime) {
                $payment_date = $fn_invoice['InvoiceDate'];
                WC_FH()->logger->add(sprintf('Changed to payment date %s', $payment_date));
            }
            $payment_request = array(
                'InvoiceNumber' => $fn_invoice['DocumentNumber'],
                'Amount' => $fn_invoice['Total'] * $fn_invoice['CurrencyRate'] * $fn_invoice['CurrencyUnit'],
                'AmountCurrency' => $fn_invoice['Total'],
                'CurrencyRate' => $fn_invoice['CurrencyRate'],
                'PaymentDate' => $payment_date,
                'ModeOfPayment' => $mode_of_payment,
                'ModeOfPaymentAccount' => get_option('fortnox_payment_account_' . $payment_method),
            );
            $payment_response = WC_FH()->fortnox->createInvoicePayment($payment_request);
            WC_FH()->logger->add(sprintf('set_invoice_to_paid: Created Fortnox invoice payment %s', $payment_response['Number']));
            $bookkeep_response = WC_FH()->fortnox->bookkeepInvoicePayment($payment_response['Number']);
            WC_FH()->logger->add(sprintf('set_invoice_to_paid: Fortnox invoice payment %s booked', $fn_invoice['DocumentNumber']));

        }

        private function invoice_is_paid($order_id, $fn_invoice)
        {

            if (strlen($fn_invoice['FinalPayDate']) === 10) {
                WC_FH()->logger->add(sprintf('invoice_is_paid (%s): Fortnox Invoice %s is paid', $order_id, $fn_invoice['DocumentNumber']));
                return true;
            }

            $was_paid = false;
            $invoice_payments = WC_FH()->fortnox->getInvoicePaymentsByInvoiceNumber($fn_invoice['DocumentNumber']);

            if ($invoice_payments) {

                $delete_file_payments = wc_string_to_bool(get_option('fortnox_delete_invoice_file_payments'));

                foreach ($invoice_payments as $invoice_payment) {

                    if ($invoice_payment['Source'] === 'direct') {

                        if (!rest_sanitize_boolean($invoice_payment['Booked'])) {
                            $bookkeep_response = WC_FH()->fortnox->bookkeepInvoicePayment($invoice_payment['Number']);
                            WC_FH()->logger->add(sprintf('invoice_is_paid (%s): Invoice payment %s was already created and is now booked on Invoice %s', $order_id, $invoice_payment['Number'], $fn_invoice['DocumentNumber']));
                        }
                        $was_paid = true;

                    } elseif ($delete_file_payments && $invoice_payment['Source'] === 'file' && !rest_sanitize_boolean($invoice_payment['Booked'])) {

                        WC_FH()->fortnox->delete_invoice_payment($invoice_payment['Number']);

                    }

                }

            }

            return $was_paid;

        }

    }

}
