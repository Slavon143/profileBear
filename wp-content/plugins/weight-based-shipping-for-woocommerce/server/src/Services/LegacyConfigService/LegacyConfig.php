<?php
namespace Wbs\Services\LegacyConfigService;

use WbsVendors\Dgm\SimpleProperties\SimpleProperties;


/**
 * @property-read string $version
 * @property-read array $rules
 */
class LegacyConfig extends SimpleProperties
{
    public function __construct($version, array $rules)
    {
        $this->version = $version;
        $this->rules = $rules;
    }

    public function toArray()
    {
        return array(
            'version' => $this->version,
            'rules' => $this->rules,
        );
    }

    protected $version;
    protected $rules;
}   