<?php
namespace Wbs\Services;

use Wbs\Services\LegacyConfigService\LegacyConfig;
use Wbs\Services\LegacyConfigService\LegacyConfigRulesOrderStorage;
use Wbs\Services\LegacyConfigService\LegacyConfigRulesStorage;


class LegacyConfigService
{
    public function __construct()
    {
        $this->rules = new LegacyConfigRulesStorage('woowbs_', '_settings');
        $this->order = new LegacyConfigRulesOrderStorage("woowbs_rules_order");
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->rules->walk('__return_true') === true;
    }

    /**
     * @return LegacyConfig
     */
    public function get()
    {
        $rules = $this->rules->walk(array($this->rules, 'load'));
        $rules = array_values($this->order->sort($rules));

        $config = new LegacyConfig(get_option('woowbs_version'), $rules);

        return $config;
    }

    /**
     * @return void
     */
    public function delete()
    {
        $this->rules->walk(array($this->rules, 'delete'));
    }


    private $rules;
    private $order;
}
