<?php

namespace WebpConverterVendor\MattPlugins\DeactivationModal\Model;

use WebpConverterVendor\MattPlugins\DeactivationModal\Exception\DuplicatedFormValueKeyException;
/**
 * It manages the list of additional information sent in the request reporting plugin deactivation.
 */
class FormValues
{
    /**
     * @var FormValue[]
     */
    private $values = [];
    /**
     * @param FormValue $new_value .
     *
     * @throws DuplicatedFormValueKeyException
     */
    public function set_value(FormValue $new_value) : self
    {
        foreach ($this->values as $value) {
            if ($value->get_key() === $new_value->get_key()) {
                throw new DuplicatedFormValueKeyException($new_value->get_key());
            }
        }
        $this->values[] = $new_value;
        return $this;
    }
    /**
     * @return FormValue[]
     */
    public function get_values() : array
    {
        return $this->values;
    }
}
