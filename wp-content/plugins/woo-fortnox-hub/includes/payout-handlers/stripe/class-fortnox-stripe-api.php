<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Stripe_API', false)) {

    class Fortnox_Hub_Stripe_API
    {

        private $stripe_base_url;
        private $secret_key;
        private $options;

        public function __construct()
        {
            $this->stripe_base_url = 'https://api.stripe.com';

            if (function_exists('wc_stripe_get_secret_key')) {
                $this->secret_key = wc_stripe_get_secret_key();
            } else {
                $options = get_option('woocommerce_stripe_settings');
                $testmode = (isset($options['testmode']) && 'yes' === $options['testmode']) ? true : false;
                $test_secret_key = isset($options['test_secret_key']) ? $options['test_secret_key'] : '';
                $live_secret_key = isset($options['secret_key']) ? $options['secret_key'] : '';
                $this->secret_key = apply_filters('fortnox_hub_stripe_secret_key', $testmode ? $test_secret_key : $live_secret_key);
            }

        }

        public function get_payouts($params = array())
        {
            return $this->execute('GET', '/v1/payouts', $params);
        }

        public function get_balance_transactions($params = array())
        {
            return $this->execute('GET', '/v1/balance_transactions', $params);
        }

        private function execute($request_type, $path, $request = array())
        {

            $url = $this->stripe_base_url . $path;

            $args = array(
                'method' => $request_type,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->secret_key,
                ),
            );

            if (is_array($request) && !empty($request)) {
                if ('GET' == $request_type || 'DELETE' == $request_type) {
                    $url .= '?' . preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($request, '', '&'));
                } else {
                    $json_body = json_encode($request, JSON_INVALID_UTF8_IGNORE);
                    if (!$json_body) {
                        $json_error = json_last_error();
                        throw new Fortnox_API_Exception('JSON conversion failed when connecting to Stripe', $json_error, null, $url);
                    }
                    $args['body'] = $json_body;
                }
            }

            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                $code = $response->get_error_code();
                $error = $response->get_error_message($code);
                throw new Fortnox_API_Exception(sprintf('Got error %s with message %s when connecting to Stripe', $code, $error), 0, null, $url);
            }

            $data = wp_remote_retrieve_body($response);

            if (($http_code = wp_remote_retrieve_response_code($response)) > 299) {
                throw new Fortnox_API_Exception('Error when connecting to Stripe', $http_code, null, $url, json_encode($args, JSON_INVALID_UTF8_IGNORE), $data);
            }

            return json_decode($data);
        }

    }

}
