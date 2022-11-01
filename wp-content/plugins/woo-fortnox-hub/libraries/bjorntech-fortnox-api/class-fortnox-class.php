<?php

defined('ABSPATH') || exit;
define('API_BLANK', 'API_BLANK');

if (!class_exists('Fortnox_API_Class', false)) {

    class Fortnox_API_Class extends Fortnox_API implements JsonSerializable
    {

        protected $id = '';
        protected $version = '3';
        protected $resource = '';
        protected $resource_url = '';
        protected $data = [];
        protected $new_data = [];

        public function __construct($data = false)
        {
            if (false !== $data) {
                if (is_array($data)) {
                    $this->data = $data;
                } else {
                    $this->id = $data;
                    $this->get();
                }
            }
        }

        /**
         * Get key data if exists
         *
         * @param string $key
         * @return mixed
         */
        private function get_data($key)
        {
            return array_key_exists($key, $this->new_data) ? $new_data[$key] : (array_key_exists($key, $this->data) ? $data[$key] : null);
        }

        /**
         * Set data, if false set to API_BLANK
         *
         * @param string $key
         * @param mixed $new_data
         * @return void
         */
        private function update_data($key, $new_data)
        {
            $new_data[$key] = null === $new_data ? API_BLANK : $new_data;
        }

        private function get_resource_url()
        {
            $version = $this->version;
            $resource_url = $this->resource_url;
            $id = $this->id;
            return "$version/$resource_url/$id";
        }

        public function save()
        {
            if ('' === $this->id) {
                $this->post();
            } else {
                $this->put();
            }
        }

        private function get()
        {
            $response = $this->apiCall(
                'GET',
                $this->get_resource_url(),
            );
            $this->data = $response[$resource];
            $this->new_data = [];
        }

        private function post()
        {
            $response = $this->apiCall(
                'POST',
                $this->get_resource_url(),
                [$resource => $this->new_data]
            );
            $this->data = $response[$resource];
            $this->new_data = [];
        }

        private function put()
        {
            $response = $this->apiCall(
                'PUT',
                $this->get_resource_url(),
                [$resource => $this->new_data]
            );
            $this->data = $response[$resource];
            $this->new_data = [];
        }

        private function delete()
        {
            $this->apiCall(
                'DELETE',
                $this->get_resource_url(),
            );
            $this->new_data = [];
            $this->data = [];
            $this->id = '';
        }

        public function jsonSerialize()
        {
            $readyonly_keys = $this->get_readyonly_keys();
            return [
                $resource => $data,
            ];
        }

    }

}
