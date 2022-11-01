<?php

namespace WebpConverterVendor\MattPlugins\DeactivationModal\Model;

/**
 * Stores information containing request data reporting plugin deactivation.
 */
class RequestData
{
    /**
     * @var string
     */
    private $plugin_slug;
    /**
     * @var string|null
     */
    private $reason_key = null;
    /**
     * @var string|null
     */
    private $additional_info = null;
    /**
     * @var array
     */
    private $additional_data = [];
    /**
     * @param string $plugin_slug .
     */
    public function __construct(string $plugin_slug)
    {
        $this->plugin_slug = $plugin_slug;
    }
    public function get_plugin_slug() : string
    {
        return $this->plugin_slug;
    }
    public function set_reason_key(string $reason_key = null) : self
    {
        $this->reason_key = $reason_key;
        return $this;
    }
    /**
     * @return string|null
     */
    public function get_reason_key()
    {
        return $this->reason_key;
    }
    public function set_additional_info(string $additional_info = null) : self
    {
        $this->additional_info = $additional_info;
        return $this;
    }
    /**
     * @return string|null
     */
    public function get_additional_info()
    {
        return $this->additional_info;
    }
    public function set_additional_data(array $additional_data) : self
    {
        $this->additional_data = $additional_data;
        return $this;
    }
    public function set_additional_data_item(string $data_key, string $data_value) : self
    {
        $this->additional_data[$data_key] = $data_value;
        return $this;
    }
    public function get_additional_data() : array
    {
        return $this->additional_data;
    }
}
