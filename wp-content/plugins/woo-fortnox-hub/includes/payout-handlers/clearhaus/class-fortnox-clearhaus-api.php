<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Clearhaus_API', false)) {

    define('CLEARHOUSE_API_TEST_ENDPOINT', 'https://merchant.test.clearhaus.com');
    define('CLEARHOUSE_API_LIVE_ENDPOINT', 'https://merchant.clearhaus.com');

    class Fortnox_Hub_Clearhaus_API
    {

        private $base_url;
        private $client_id;
        private $client_secret;

        public function __construct($handler)
        {
            $this->payout_id = $handler->payout_id;
            $mode = wc_string_to_bool(get_option('fortnox_' . $this->payout_id . '_test_mode'));
            $this->base_url = $mode ? CLEARHOUSE_API_TEST_ENDPOINT : CLEARHOUSE_API_LIVE_ENDPOINT;
            $this->client_id = get_option('fortnox_' . $this->payout_id . '_client_id' . ($mode ? '_test' : '_prod'));
            $this->client_secret = get_option('fortnox_' . $this->payout_id . '_client_secret' . ($mode ? '_test' : '_prod'));
        }

        public function get_payouts($params = array())
        {
            return $this->execute('GET', '/settlements?query=' . $params);
        }

        private function get_access_token()
        {

            $access_token = get_site_transient('fortnox_clearhaus_access_token');

            if (false === $access_token) {

                $body = [
                    'grant_type' => 'client_credentials',
                    'audience' => $this->base_url,
                ];

                $args = [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret),
                    ],
                    'timeout' => 20,
                    'body' => $body,
                ];

                $url = $this->base_url . '/oauth/token';

                $response = wp_safe_remote_post($url, $args);

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

                    set_site_transient('fortnox_clearhaus_access_token', $response_body->access_token, $response_body->expires_in);
                    WC_FH()->logger->add(sprintf('Got access "%s" expiring in %s seconds', $response_body->access_token, $response_body->expires_in));
                    $access_token = $response_body->access_token;

                }

            }

            return $access_token;

        }

        private function execute($request_type, $path, $request = array())
        {

            $url = $this->base_url . $path;

            $args = array(
                'method' => $request_type,
                'headers' => array(
                    'Authorization' => $this->get_access_token(),
                ),
            );

            if (is_array($request) && !empty($request)) {
                if ('GET' == $request_type || 'DELETE' == $request_type) {
                    $url .= '?' . preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($request, '', '&'));
                } else {
                    $json_body = json_encode($request, JSON_INVALID_UTF8_IGNORE);
                    if (!$json_body) {
                        $json_error = json_last_error();
                        throw new Fortnox_API_Exception('JSON conversion failed when connecting to Clearhaus', $json_error, null, $url);
                    }
                    $args['body'] = $json_body;
                }
            }

            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                $code = $response->get_error_code();
                $error = $response->get_error_message($code);
                throw new Fortnox_API_Exception(sprintf('Got error %s with message %s when connecting to Clearhaus', $code, $error), 0, null, $url);
            }

            $data = wp_remote_retrieve_body($response);

            if (($http_code = wp_remote_retrieve_response_code($response)) > 299) {
                throw new Fortnox_API_Exception('Error when connecting to Clearhaus', $http_code, null, $url, json_encode($args, JSON_INVALID_UTF8_IGNORE), $data);
            }

            return json_decode($data, true);
        }

    }

}
