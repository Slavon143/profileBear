<?php

namespace WebpConverterVendor\MattPlugins\DeactivationModal\Model;

use WebpConverterVendor\MattPlugins\DeactivationModal\Exception\DuplicatedFormOptionKeyException;
use WebpConverterVendor\MattPlugins\DeactivationModal\Exception\UnknownFormOptionKeyException;
/**
 * Manages the list of deactivation reason in the form.
 */
class FormOptions
{
    /**
     * @var FormOption[]
     */
    private $options = [];
    /**
     * @param FormOption $new_option .
     *
     * @throws DuplicatedFormOptionKeyException
     */
    public function set_option(FormOption $new_option) : self
    {
        foreach ($this->options as $option) {
            if ($option->get_key() === $new_option->get_key()) {
                throw new DuplicatedFormOptionKeyException($new_option->get_key());
            }
        }
        $this->options[] = $new_option;
        return $this;
    }
    /**
     * @param string $option_key .
     *
     * @throws UnknownFormOptionKeyException
     */
    public function delete_option(string $option_key) : self
    {
        foreach ($this->options as $option_index => $option) {
            if ($option->get_key() === $option_key) {
                unset($this->options[$option_index]);
                return $this;
            }
        }
        throw new UnknownFormOptionKeyException($option_key);
    }
    /**
     * @param string   $option_key      .
     * @param callable $update_callback Example: "function ( FormOption $option ) { }".
     *
     * @throws UnknownFormOptionKeyException
     */
    public function update_option(string $option_key, callable $update_callback) : self
    {
        foreach ($this->options as $option) {
            if ($option->get_key() === $option_key) {
                \call_user_func($update_callback, $option);
                return $this;
            }
        }
        throw new UnknownFormOptionKeyException($option_key);
    }
    /**
     * @return FormOption[]
     */
    public function get_options() : array
    {
        $options = $this->options;
        \usort($options, function (FormOption $option_a, FormOption $option_b) {
            return $option_a->get_priority() <=> $option_b->get_priority();
        });
        return $options;
    }
}
