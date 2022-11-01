<?php

namespace WebpConverterVendor\MattPlugins\DeactivationModal\Model;

/**
 * Stores information about the additional value sent in the request reporting plugin deactivation.
 */
class FormValue
{
    /**
     * @var string Key of the additional value.
     */
    private $key;
    /**
     * @var callable A function that returns a text value.
     */
    private $value_callback;
    /**
     * @param string   $key            Key of the additional value.
     * @param callable $value_callback A function that returns a text value.
     */
    public function __construct(string $key, callable $value_callback)
    {
        $this->key = $key;
        $this->value_callback = $value_callback;
    }
    public function get_key() : string
    {
        return $this->key;
    }
    public function get_value_callback() : callable
    {
        return $this->value_callback;
    }
}
