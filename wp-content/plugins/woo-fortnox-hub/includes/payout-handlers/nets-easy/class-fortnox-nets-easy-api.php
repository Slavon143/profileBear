<?php

defined('ABSPATH') || exit;

if (!class_exists('Fortnox_Hub_Nets_Easy_API', false)) {

    class Fortnox_Hub_Nets_Easy_API
    {

        private $base_url;
        private $live_secret_key;
        private $test_secret_key;
        private $testmode;

        public function __construct()
        {
            $this->testmode = 'yes' === get_option('test_mode');
            $this->base_url = $this->testmode ? DIBS_API_TEST_ENDPOINT : DIBS_API_LIVE_ENDPOINT;
            $this->secret_key = $this->testmode ? get_option('dibs_test_key', '') : get_option('dibs_live_key', '');
        }

        public function get_payouts($params = array())
        {
            return $this->execute('GET', 'report/v1/payouts', $params);
        }

        private function execute($request_type, $path, $request = array())
        {

            $url = $this->base_url . $path;

            $args = array(
                'method' => $request_type,
                'headers' => array(
                    'Authorization' => $this->secret_key,
                ),
            );

            if (is_array($request) && !empty($request)) {
                if ('GET' == $request_type || 'DELETE' == $request_type) {
                    $url .= '?' . preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($request, '', '&'));
                } else {
                    $json_body = json_encode($request, JSON_INVALID_UTF8_IGNORE);
                    if (!$json_body) {
                        $json_error = json_last_error();
                        throw new Fortnox_API_Exception('JSON conversion failed when connecting to Nets_Easy', $json_error, null, $url);
                    }
                    $args['body'] = $json_body;
                }
            }

            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                $code = $response->get_error_code();
                $error = $response->get_error_message($code);
                throw new Fortnox_API_Exception(sprintf('Got error %s with message %s when connecting to Nets_Easy', $code, $error), 0, null, $url);
            }

            $data = wp_remote_retrieve_body($response);

            if (($http_code = wp_remote_retrieve_response_code($response)) > 299) {
                throw new Fortnox_API_Exception('Error when connecting to Nets_Easy', $http_code, null, $url, json_encode($args, JSON_INVALID_UTF8_IGNORE), $data);
            }

            return json_decode($data);
        }

    }

}
