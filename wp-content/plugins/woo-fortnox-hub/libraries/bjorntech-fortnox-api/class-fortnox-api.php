<?php

/**
 * Constant that contains the fortnox endpoint and the integrations client secret
 *
 */

defined('ABSPATH') || exit;

/**
 * Fortnox_API
 */

if (!class_exists('Fortnox_API', false)) {
    class Fortnox_API
    {

        const SERVICE_URL = 'bjorntech.net/v1';

        /**
         * Refresh token used to get a valid authorization token
         */

        public function __construct($service_url = false)
        {
            $this->service_url = trailingslashit($service_url ? $service_url : self::SERVICE_URL);
        }

        public function get_service_url()
        {
            return 'fortnox.' . $this->service_url;
        }

        public function get_access_token()
        {
            global $woocommerce;

            $access_token = '';
            $refresh_token = get_option('fortnox_refresh_token');

            $time_now = time();

            if ($refresh_token && false === ($access_token = get_site_transient('fortnox_accesstoken'))) {

                $body = array(
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refresh_token,
                    'plugin_version' => WC_FH()->plugin_version,
                    'wc_version' => $woocommerce->version,
                    'price_type' => get_option('woocommerce_prices_include_tax'),
                    'invoice_push' => "yes" == get_option('fortnox_check_invoices_automatically'),
                    'product_push' => "yes" == get_option('fortnox_sync_from_fortnox_automatically'),
                );

                $args = array(
                    'headers' => array(
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'X-AuthorizationCode' => get_option('fortnox_authorization_code') ? get_option('fortnox_authorization_code') : '',
                    ),
                    'timeout' => 20,
                    'body' => $body,
                );

                $url = 'https://' . $this->get_service_url() . 'token';

                $response = wp_remote_post($url, $args);

                if (is_wp_error($response)) {

                    $code = $response->get_error_code();

                    $error = $response->get_error_message($code);

                    throw new Fortnox_API_Exception($error, 0, null, $url, $body);
                } else {

                    $response_body = json_decode(wp_remote_retrieve_body($response));
                    $http_code = wp_remote_retrieve_response_code($response);

                    if (200 != $http_code) {

                        $error_message = isset($response_body->error) ? $response_body->error : 'Unknown error message';
                        WC_FH()->logger->add(sprintf('Error %s when asking for access token from service: %s', $http_code, $error_message));
                        throw new Fortnox_API_Exception($error_message, $http_code, null, $url, $body, $response);
                    }

                    update_option('fortnox_valid_to', $response_body->valid_to);
                    update_option('fortnox_gmt_offset', $response_body->gmt_offset * MINUTE_IN_SECONDS);
                    update_option('fortnox_refresh_token', $response_body->refresh_token);

                    //set_site_transient('fortnox_accesstoken', $response_body->access_token, $response_body->expires_in);
                    $expires_in = $response_body->expires_in;
                    $expires_in = ($expires_in && $expires_in > 1800) ? $expires_in - 600 : $expires_in;

                    set_site_transient('fortnox_accesstoken', $response_body->access_token, $expires_in);

                    WC_FH()->logger->add(sprintf('Got access "%s" expiring in %s seconds', $response_body->access_token, $expires_in));

                    $access_token = $response_body->access_token;
                }
            }

            return $access_token;
        }

        public function ratelimiter()
        {

            $current = microtime(true);
            $time_passed = $current - (float) get_site_transient('fortnox_api_limiter', $current);
            set_site_transient('fortnox_api_limiter', $current);

            if ($time_passed < 250000) {
                usleep(250000 - $time_passed);
            }
        }

        /**
         * Function to make a "raw" call to Fortnox
         *
         * @return the response from Fortnox
         */
        public function apiCall($method, $entity, $body = null, $assoc = true)
        {

            $this->ratelimiter();

            $url = 'https://' . $this->get_service_url() . $entity;

            if (!get_option('fortnox_refresh_token')) {
                Fortnox_Notice::clear();
                throw new Fortnox_API_Exception('Not connected to Fortnox Hub - please go to the Connection section in Fortnox Hub and click Connect', 0, null, $url, null, null);
            }

            $request_body = $body;
            if ($method == 'POST' || $method == 'PUT') {
                $request_body = json_encode($body, JSON_INVALID_UTF8_IGNORE);
                $json_last_error = json_last_error();
                if ($json_last_error !== JSON_ERROR_NONE) {
                    WC_FH()->logger->add(sprintf('apiCall: JSON error %s', $json_last_error));
                    WC_FH()->logger->add(print_r($body, true));
                    throw new Fortnox_API_Exception('Invalid data when encoding JSON, contact BjornTech support at hello@bjorntech.com', 500, null, $url);
                }
            }

            $args = array(
                'headers' => array_merge(
                    array(
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->get_access_token(),
                        'X-AuthorizationCode' => get_option('fortnox_authorization_code') ? get_option('fortnox_authorization_code') : '',
                    )
                ),
                'body' => $request_body,
                'method' => $method,
                'timeout' => 20,
            );

            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {

                $code = $response->get_error_code();
                $message = $response->get_error_message($code);

                WC_FH()->logger->add(sprintf('Error %s - %s', $code, $message));

                throw new Fortnox_API_Exception($message ? $message : 'Unknown error', 0, null, $url, json_encode($args, JSON_INVALID_UTF8_IGNORE), null);
            } else {

                $json_response = json_decode($response['body'], true);

                $http_code = wp_remote_retrieve_response_code($response);

                if ($http_code >= 200 && $http_code < 300) {

                    if ($assoc === true) {
                        if ($json_response == null) {
                            return $response['body'];
                        } else {
                            return $json_response;
                        }
                    } else {
                        return json_decode($response['body'], false);
                    }
                } elseif (429 == $http_code) {

                    WC_FH()->logger->add('Unexpectedly hitting the Fortnox API rate limit , waiting 30 seconds to repair');
                    sleep(30);
                    return $this->apiCall($method, $entity, $body, $assoc);
                } elseif (403 == $http_code) {

                    throw new Fortnox_API_Exception(__('Authorization failure, contact BjornTech support at hello@bjorntech.com', 'woo-fortnox-hub'), 403, null, $url, json_encode($args, JSON_INVALID_UTF8_IGNORE));
                } elseif (is_array($json_response) && array_key_exists('ErrorInformation', $json_response)) {

                    $error_information = $json_response['ErrorInformation'];
                    $error_code = array_key_exists('Code', $error_information) ? $error_information['Code'] : $error_information['code'];
                    $message = array_key_exists('Message', $error_information) ? $error_information['Message'] : $error_information['message'];
                    $message = sprintf('(%s): %s', $error_code, $message);

                    throw new Fortnox_API_Exception($message, $http_code, null, $url, json_encode($args, JSON_INVALID_UTF8_IGNORE), print_r($response['response'], true));
                } else {

                    throw new Fortnox_API_Exception(sprintf(__('Error %s when communicating with Fortnox', 'woo-fortnox-hub'), $http_code), $http_code, null, $url, json_encode($args, JSON_INVALID_UTF8_IGNORE), print_r($response['body'], true));
                }
            }
        }

        /**
         * Get pricelist
         *
         * @return Prices
         */
        public function get_pricelists($pricelist = '')
        {
            $response = $this->apiCall('GET', '3/pricelists/' . urlencode($pricelist));
            return $response;
        }

        /**
         * Get price from alternate pricelist
         *
         * @return Prices
         */
        public function get_prices($article, $pricelist)
        {
            $response = $this->apiCall('GET', '3/prices/' . urlencode($pricelist) . '/' . urlencode($article) . '/0');
            return $response['Price'];
        }

        /**
         * Create price in pricelist
         *
         * @return Prices
         */
        public function create_price($price)
        {
            $response = $this->apiCall('POST', '3/prices', $price);
            return $response;
        }

        /**
         * Update price in pricelist
         *
         * @return Prices
         */
        public function update_price($pricelist, $article_id, $api_request_data)
        {
            $response = $this->apiCall(
                'PUT',
                '3/prices/' . urlencode($pricelist) . '/' . urlencode($article_id) . '/0',
                $api_request_data
            );
            return $response;
        }

        public function getAllCustomers($only_active = true, $last_modified = false, $filters = false)
        {
            $last_modified !== false ? $modified_field = '&lastmodified=' . $last_modified : $modified_field = "";
            $only_active === true ? $active_field = '&filter=active' : $active_field = "";

            $i = 1;
            $returnarray = array();
            do {
                $jsonarray = $this->apiCall('GET', '3/customers/?page=' . $i . '&sortby=customernumbernumber&sortorder=ascending' . $active_field . $modified_field);
                if ($filters !== false) {
                    foreach ($jsonarray['Customers'] as $customer) {
                        foreach ($filters as $filter) {
                            if ($filter['value'] == $customer[$filter['field']]) {
                                array_push($returnarray, $customer);
                                break;
                            }
                        }
                    }
                } else {
                    $returnarray = array_merge($jsonarray['Customers'], $returnarray);
                }
                $totalpages = $jsonarray["MetaInformation"]["@TotalPages"];
            } while ($i++ < $totalpages);

            return $returnarray;
        }

        public function getCustomer($customer_id)
        {
            $response = $this->apiCall(
                'GET',
                '3/customers/' . $customer_id
            );
            return $response['Customer'];
        }

        public function addCustomer($customer)
        {
            $api_request_data['Customer'] = $customer;
            $response = $this->apiCall(
                'POST',
                '3/customers',
                $api_request_data
            );
            return $response['Customer'];
        }

        public function updateCustomer($customer_id, $customer)
        {
            $api_request_data['Customer'] = $customer;
            $response = $this->apiCall(
                'PUT',
                '3/customers/' . $customer_id,
                $api_request_data
            );
            return $response['Customer'];
        }

        public function create_order($order)
        {
            $api_request_data['Order'] = $order;
            $response = $this->apiCall(
                'POST',
                '3/orders',
                $api_request_data
            );
            return $response['Order'];
        }

        public function create_voucher($voucher)
        {
            $api_request_data['Voucher'] = $voucher;
            $response = $this->apiCall(
                'POST',
                '3/vouchers/',
                $api_request_data
            );
            return $response['Voucher'];
        }

        public function get_voucher($voucher_series, $voucher_number)
        {
            $response = $this->apiCall(
                'GET',
                '3/vouchers/' . $voucher_series . '/' . $voucher_number
            );
            return $response['Voucher'];
        }

        public function update_order($order_id, $order)
        {
            $api_request_data['Order'] = $order;
            $response = $this->apiCall(
                'PUT',
                '3/orders/' . $order_id,
                $api_request_data
            );
            return $response['Order'];
        }

        public function updateInvoice($id, $data)
        {
            $api_request_data['Invoice'] = $data;
            $response = $this->apiCall(
                'PUT',
                '3/invoices/' . $id,
                $api_request_data
            );
            return $response['Invoice'];
        }

        public function cancel_order($order_id)
        {
            return $this->apiCall(
                'PUT',
                '3/orders/' . $order_id . '/cancel'
            );
        }

        public function cancel_invoice($id)
        {
            return $this->apiCall(
                'PUT',
                '3/invoices/' . $id . '/cancel'
            );
        }

        public function set_order_warehouseready($id){
            $response = $this->apiCall(
                'PUT',
                '3/orders/' . $id . '/warehouseready'
            );
            return $response['Order'];
        }

        public function set_invoice_warehouseready($id){
            $response = $this->apiCall(
                'PUT',
                '3/invoices/' . $id . '/warehouseready'
            );
            return $response['Invoice'];
        }

        public function finish_order($order_id)
        {
            $response = $this->apiCall(
                'PUT',
                '3/orders/' . $order_id . '/createinvoice'
            );
            return $response['Order'];
        }

        public function getOrderPDF($order_id)
        {
            return $this->apiCall(
                'GET',
                '3/orders/' . $order_id . '/preview'
            );
        }

        public function getInvoicePDF($invoice_id)
        {
            return $this->apiCall(
                'GET',
                '3/invoices/' . $invoice_id . '/preview'
            );
        }

        public function warehouseOrder($order_id)
        {
            $this->apiCall(
                'GET',
                '3/orders/' . $order_id . '/warehouseready'
            );
        }

        public function get_article($article_id)
        {
            $response = $this->apiCall(
                'GET',
                '3/articles/' . urlencode($article_id)
            );
            return $response['Article'];
        }

        public function get_units()
        {
            $response = $this->apiCall(
                'GET',
                '3/units'
            );
            return $response['Units'];
        }

        public function get_unit($unit_id)
        {
            $response = $this->apiCall(
                'GET',
                '3/units/' . urlencode($unit_id)
            );
            return $response['Unit'];
        }

        public function create_unit($unit)
        {
            $api_request_data['Unit'] = $unit;
            $response = $this->apiCall(
                'POST',
                '3/units',
                $api_request_data
            );
            return $response['Unit'];
        }

        public function get_voucher_series()
        {
            $response = $this->apiCall(
                'GET',
                '3/voucherseries/'
            );
            return $response['VoucherSeriesCollection'];
        }

        public function get_financial_years($financial_year = null, $date = null)
        {
            $params = '';

            if ($financial_year) {
                $params .= $financial_year . '/';
            }

            if ($date) {
                $params .= '?date=' . $date;
            }

            $response = $this->apiCall(
                'GET',
                '3/financialyears/' . $params
            );
            return $response['FinancialYears'];
        }

        public function inbound_delivery($data, $type = false, $id = false)
        {
            return $this->apiCall(
                'PUT',
                'api/warehouse/documentdeliveries/custom/inbound-v1' . ($type ? '/' . $type . ($id ? '/' . $id : '') : ''),
                $data
            );
        }

        public function outbound_delivery($data, $type = false, $id = false)
        {
            return $this->apiCall(
                'PUT',
                'api/warehouse/documentdeliveries/custom/outbound-v1' . ($type ? '/' . $type . ($id ? '/' . $id : '') : ''),
                $data
            );
        }

        public function get_article_by_manufacturer_article_number($key)
        {
            return $this->apiCall(
                'GET',
                '3/articles/?ManufacturerArticleNumber=' . urlencode($key)
            );
        }

        public function get_article_by_description($key)
        {
            return $this->apiCall(
                'GET',
                '3/articles/?description=' . urlencode($key)
            );
        }

        public function get_cost_centers()
        {
            $response = $this->apiCall(
                'GET',
                '3/costcenters'
            );
            return $response['CostCenters'];
        }

        public function get_print_templates($type = '')
        {
            $filter = $type ? '?type=' . urlencode($type) : '';
            $response = $this->apiCall(
                'GET',
                '3/printtemplates' . $filter
            );
            return $response['PrintTemplates'];
        }

        public function get_projects()
        {
            $response = $this->apiCall(
                'GET',
                '3/projects'
            );
            return $response['Projects'];
        }

        public function update_article($article_id, $article)
        {
            $api_request_data['Article'] = $article;
            $response = $this->apiCall(
                'PUT',
                '3/articles/' . urlencode($article_id),
                $api_request_data
            );
            return $response['Article'];
        }

        public function create_article($article)
        {
            $api_request_data['Article'] = $article;
            $response = $this->apiCall(
                'POST',
                '3/articles/',
                $api_request_data
            );
            return $response['Article'];
        }

        public function get_all_articles($only_active = true, $last_modified = false, $filters = false)
        {
            $last_modified ? $modified_field = '&lastmodified=' . $last_modified : $modified_field = "";
            $only_active === true ? $active_field = '&filter=active' : $active_field = "";

            $i = 1;
            $returnarray = array();
            do {
                $jsonarray = $this->apiCall('GET', '3/articles/?page=' . $i . '&sortby=articlenumber&sortorder=ascending' . $active_field . $modified_field);
                if ($filters !== false) {
                    foreach ($jsonarray['Articles'] as $article) {
                        foreach ($filters as $filter) {
                            if ($filter['value'] == $article[$filter['field']]) {
                                array_push($returnarray, $article);
                                break;
                            }
                        }
                    }
                } else {
                    $returnarray = array_merge($jsonarray['Articles'], $returnarray);
                }
                $totalpages = $jsonarray["MetaInformation"]["@TotalPages"];
            } while ($i++ < $totalpages);

            return $returnarray;
        }

        public function getAllAccounts()
        {
            $i = 1;
            $returnarray = array();
            do {
                $jsonarray = $this->apiCall('GET', '3/accounts/?page=' . $i);
                $returnarray = array_merge($jsonarray['Accounts'], $returnarray);
                $totalpages = $jsonarray["MetaInformation"]["@TotalPages"];
            } while ($i++ < $totalpages);
            return $returnarray;
        }

        public function get_all_invoices($last_modified = null)
        {
            $modified_field = $last_modified !== null ? $modified_field = '&lastmodified=' . $last_modified : $modified_field = "";
            $i = 1;
            $returnarray = array();
            do {
                $jsonarray = $this->apiCall('GET', '3/invoices/?sortby=documentnumber&sortorder=ascending&page=' . $i . $modified_field);
                $returnarray = array_merge($returnarray, $jsonarray['Invoices']);
                $totalpages = $jsonarray["MetaInformation"]["@TotalPages"];
            } while ($i++ < $totalpages);
            return $returnarray;
        }

        public function getOrder($order_id)
        {
            $result = $this->apiCall(
                'GET',
                '3/orders/' . $order_id
            );
            return $result['Order'];
        }

        public function getOrdersByExternalInvoiceReference1($search_for)
        {
            $result = $this->apiCall('GET', '3/orders/?externalinvoicereference1=' . $search_for);
            return $result;
        }

        public function getInvoicesByExternalInvoiceReference1($search_for)
        {
            $result = $this->apiCall('GET', '3/invoices/?externalinvoicereference1=' . $search_for);
            return $result;
        }

        public function get_invoice($invoice_id)
        {
            $result = $this->apiCall(
                'GET',
                '3/invoices/' . $invoice_id
            );
            return $result['Invoice'];
        }

        public function get_order($order_id)
        {
            $result = $this->apiCall(
                'GET',
                '3/orders/' . $order_id
            );
            return $result['Order'];
        }

        public function get_terms_of_payments($id = '')
        {
            $result = $this->apiCall(
                'GET',
                '3/termsofpayments/' . urlencode($id)
            );
            return $result['TermsOfPayments'];
        }

        public function get_modes_of_payments($id = '')
        {
            $result = $this->apiCall(
                'GET',
                '3/modesofpayments/' . urlencode($id)
            );
            return $result['ModesOfPayments'];
        }

        public function get_terms_of_deliveries($id = '')
        {
            $result = $this->apiCall(
                'GET',
                '3/termsofdeliveries/' . urlencode($id)
            );
            return $result['TermsOfDeliveries'];
        }

        public function get_way_of_deliveries($id = '')
        {
            $result = $this->apiCall(
                'GET',
                '3/wayofdeliveries/' . urlencode($id)
            );
            return $result['WayOfDeliveries'];
        }

        public function create_invoice($invoice)
        {
            $api_request_data['Invoice'] = $invoice;
            $result = $this->apiCall(
                'POST',
                '3/invoices/',
                $api_request_data
            );
            return $result['Invoice'];
        }

        public function update_invoice($invoice_id, $invoice)
        {
            $api_request_data['Invoice'] = $invoice;
            $result = $this->apiCall(
                'PUT',
                '3/invoices/' . urlencode($invoice_id),
                $api_request_data
            );
            return $result['Invoice'];
        }

        public function external_print_invoice($invoice_id)
        {
            $this->apiCall(
                'PUT',
                '3/invoices/' . $invoice_id . '/externalprint'
            );
        }

        public function book_invoice($invoice_id)
        {
            $this->apiCall(
                'PUT',
                '3/invoices/' . $invoice_id . '/bookkeep'
            );
        }

        public function credit_invoice($invoice_id)
        {
            $this->apiCall(
                'PUT',
                '3/invoices/' . $invoice_id . '/credit'
            );
        }

        public function email_invoice($invoice_id)
        {
            $this->apiCall(
                'GET',
                '3/invoices/' . $invoice_id . '/email'
            );
        }

        public function getInvoicePaymentsByInvoiceNumber($search_for)
        {
            $response = $this->apiCall('GET', '3/invoicepayments/?invoicenumber=' . (string) $search_for);
            if ($response["MetaInformation"]["@TotalResources"] == 0) {
                return false;
            } else {
                return $response['InvoicePayments'];
            }
        }

        public function delete_invoice_payment($payment_number)
        {
            $this->apiCall(
                'DELETE',
                '3/invoicepayments/' . (string) $payment_number
            );
        }

        public function createInvoicePayment($invoicepayment)
        {
            $api_request_data['InvoicePayment'] = $invoicepayment;
            $response = $this->apiCall(
                'POST',
                '3/invoicepayments/',
                $api_request_data
            );
            return $response['InvoicePayment'];
        }

        public function getInvoicePayments($search_for)
        {
            $response = $this->apiCall('GET', '3/invoicepayments/' . (string) $search_for);
            if ($response["MetaInformation"]["@TotalResources"] == 0) {
                return false;
            } else {
                return $response['InvoicePayments'];
            }
        }

        public function bookkeepInvoicePayment($payment_number)
        {
            $response = $this->apiCall('PUT', '3/invoicepayments/' . (string) $payment_number . '/bookkeep');
            return $response;
        }

        public function get_first_customer_by_email($email)
        {
            return ($customers = $this->get_customers_by_email($email)) ? $customers[0] : $customers;
        }

        public function get_customers_by_email($email)
        {
            if ($email) {
                $response = $this->apiCall('GET', '3/customers/?email=' . urlencode($email));
                if ($response["MetaInformation"]["@TotalResources"] != 0) {
                    return $response['Customers'];
                }
            }
            return false;
        }

        public function get_first_customer_by_organisation_number($organisation_number)
        {
            $customers = $this->get_customers_by_organisation_number($organisation_number);
            return $customers ? $customers[0] : $customers;
        }

        public function get_customers_by_organisation_number($organisation_number)
        {
            if ($organisation_number) {
                $response = $this->apiCall('GET', '3/customers/?organisationnumber=' . $organisation_number);
                if ($response["MetaInformation"]["@TotalResources"] != 0) {
                    return $response['Customers'];
                }
            }
            return false;
        }

        public function get_customers_by_name($name)
        {
            $response = $this->apiCall('GET', '3/customers/?name=' . $name);
            if ($response["MetaInformation"]["@TotalResources"] == 0) {
                return false;
            } else {
                return $response['Customers'];
            }
        }

        public function get_company_information()
        {
            return $this->apiCall('GET', '3/settings/company');
        }
    }

    require_once dirname(__FILE__) . '/class-fortnox-class.php';
    require_once dirname(__FILE__) . '/class-fortnox-voucher-row.php';
    require_once dirname(__FILE__) . '/class-fortnox-voucher.php';

}
