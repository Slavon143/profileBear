<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Svea_API', false)) {

    define('SVEA_API_TEST_ENDPOINT', 'https://paymentadminapistage.svea.com');
    define('SVEA_API_LIVE_ENDPOINT', 'https://paymentadminapi.svea.com');

    class Fortnox_Hub_Svea_API
    {

        private $base_url;
        private $secret_word;
        private $merchant_id;

        public function __construct()
        {
            $mode = wc_string_to_bool(get_option('fortnox_' . $this->payout_id . '_test_mode'));
            $this->base_url = $mode ? SVEA_API_TEST_ENDPOINT : SVEA_API_LIVE_ENDPOINT;
            $this->secret_word = get_option('fortnox_' . $this->payout_id . '_secret_word' . $mode ? '_test' : '_prod');
            $this->merchant_id = get_option('fortnox_' . $this->payout_id . '_merchant_id' . $mode ? '_test' : '_prod');

        }

        public function get_payouts($date)
        {
            return $this->execute('GET', `/api/v2/reports?date=$date&includeWithholding=false`);
        }

        private function get_authorization($request_body, $timestamp)
        {
            return base64_encode($this->merchant_id . ':' . hash('sha512', json_encode($request_body) . $this->secret_word . $timestamp));
        }

        private function execute($request_type, $path, $request = array())
        {

            $url = $this->base_url . $path;

            $args = array(
                'method' => $request_type,
            );

            if (is_array($request) && !empty($request)) {
                if ('GET' == $request_type || 'DELETE' == $request_type) {
                    $url .= '?' . preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($request, '', '&'));
                } else {
                    $json_body = json_encode($request, JSON_INVALID_UTF8_IGNORE);
                    if (!$json_body) {
                        $json_error = json_last_error();
                        throw new Fortnox_API_Exception('JSON conversion failed when connecting to Svea', $json_error, null, $url);
                    }
                    $args['body'] = $json_body;
                }
            }

            $timestamp = date('Y-m-d H:i:s');
            $args['headers']['Timestamp'] = $timestamp;
            $args['headers']['Authorization'] = $this->get_authorization(isset($args['body']) ? $args['body'] : '', $timestamp);

            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                $code = $response->get_error_code();
                $error = $response->get_error_message($code);
                throw new Fortnox_API_Exception(sprintf('Got error %s with message %s when connecting to Svea', $code, $error), 0, null, $url);
            }

            $data = wp_remote_retrieve_body($response);

            if (($http_code = wp_remote_retrieve_response_code($response)) > 299) {
                throw new Fortnox_API_Exception('Error when connecting to Svea', $http_code, null, $url, json_encode($args, JSON_INVALID_UTF8_IGNORE), $data);
            }

            return json_decode($data);
        }

    }

}
