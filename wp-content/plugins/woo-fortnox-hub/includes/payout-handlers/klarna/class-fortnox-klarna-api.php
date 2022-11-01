<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Klarna_API', false)) {

    class Fortnox_Hub_Klarna_API
    {

        public $settings = array();

        public function __construct()
        {
            if (class_exists('KCO', false)) {
                $this->settings = get_option('woocommerce_kco_settings');
            } else {
                $this->settings = get_option('woocommerce_klarna_payments_settings');
            }
        }

        public function get_credentials()
        {
            $base_location = wc_get_base_location();

            if (class_exists('KCO', false)) {
                if ('US' === $base_location['country']) {
                    $country_string = 'us';
                } else {
                    $country_string = 'eu';
                }
            } else {
                $country_string = strtolower(WC()->countries->get_base_country());
            }

            $test_string = 'yes' === $this->settings['testmode'] ? 'test_' : '';

            $credentials = array(
                'merchant_id' => $this->settings[$test_string . 'merchant_id_' . $country_string],
                'shared_secret' => $this->settings[$test_string . 'shared_secret_' . $country_string],
            );

            return $credentials;
        }

        public function get_api_url_base()
        {
            $base_location = wc_get_base_location();
            $country_string = 'US' === $base_location['country'] ? '-na' : '';
            $test_string = 'yes' === $this->settings['testmode'] ? '.playground' : '';

            return 'https://api' . $country_string . $test_string . '.klarna.com/';
        }

        public function get_request_headers()
        {
            $request_headers = array(
                'Authorization' => 'Basic ' . base64_encode($this->get_merchant_id() . ':' . $this->get_shared_secret()),
                'Content-Type' => 'application/json',
            );

            return $request_headers;
        }

        public function get_merchant_id()
        {
            $credentials = $this->get_credentials();

            return $credentials['merchant_id'];
        }

        public function get_shared_secret()
        {
            $credentials = $this->get_credentials();

            return $credentials['shared_secret'];
        }

        public function get_user_agent()
        {
            $user_agent = apply_filters('http_headers_useragent', 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')) . ' - WooCommerce: ' . WC()->version;

            return $user_agent;
        }

        public function get_payouts($params = array())
        {
            return $this->execute('GET', 'settlements/v1/payouts', $params);
        }

        public function get_transactions($params = array())
        {
            return $this->execute('GET', 'settlements/v1/transactions', $params);
        }

        private function execute($request_type, $path, $request = array())
        {

            $request_form_data = '';
            $params = '';
            $url = $this->get_api_url_base() . $path;

            $args = array(
                'headers' => $this->get_request_headers(),
                'user-agent' => $this->get_user_agent(),
                'timeout' => 10,
            );

            if (is_array($request) && !empty($request)) {
                if ('GET' == $request_type || 'DELETE' == $request_type) {
                    $url .= '?' . preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($request, '', '&'));
                } else {
                    $json_body = json_encode($request, JSON_INVALID_UTF8_IGNORE);
                    if (!$json_body) {
                        $json_error = json_last_error();
                        throw new Fortnox_API_Exception('JSON conversion failed when connecting to Klarna', $json_error, null, $url);
                    }
                    $args['body'] = $json_body;
                }
            }

            $response = wp_safe_remote_request($url, $args);

            if (is_wp_error($response)) {
                $code = $response->get_error_code();
                $error = $response->get_error_message($code);
                throw new Fortnox_API_Exception(sprintf('Got error %s with message %s when connecting to Klarna', $code, $error), 0, null, $url);
            }

            $data = wp_remote_retrieve_body($response);

            if (($http_code = wp_remote_retrieve_response_code($response)) > 299) {
                throw new Fortnox_API_Exception('Error when connecting to Klarna', $http_code, null, $url, json_encode($args, JSON_INVALID_UTF8_IGNORE), $data);
            }

            return json_decode($data);

        }

    }

}
