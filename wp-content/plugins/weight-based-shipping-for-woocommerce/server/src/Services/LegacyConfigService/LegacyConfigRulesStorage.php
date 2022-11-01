<?php
namespace Wbs\Services\LegacyConfigService;


class LegacyConfigRulesStorage
{
    /**
     * @param string $ruleOptionNamePrefix
     * @param string $ruleOptionNameSuffix
     */
    public function __construct($ruleOptionNamePrefix, $ruleOptionNameSuffix)
    {
        $this->prefix = (string)$ruleOptionNamePrefix;
        $this->suffix = (string)$ruleOptionNameSuffix;
    }

    /**
     * @param callable $callback function(string $ruleId): bool|mixed (bool to return immediately, mixed to collect in the result list)
     * @return mixed[]|bool
     */
    public function walk($callback)
    {
        $rules = array();

        $optionNamePattern = $this->optionNamePattern();

        foreach (array_keys(wp_load_alloptions()) as $option) {

            $matches = array();
            if (preg_match($optionNamePattern, $option, $matches)) {

                $id = $matches[1];

                $result = call_user_func($callback, $id);
                if (is_bool($result)) {
                    return $result;
                }

                $rules[$id] = $result;
            }
        }

        return $rules;
    }

    /**
     * @param mixed $ruleId
     * @return mixed
     */
    public function load($ruleId)
    {
        return self::object2Array(get_option($this->optionName($ruleId), null));
    }

    /**
     * @param mixed $ruleId
     * @return void
     */
    public function delete($ruleId)
    {
        delete_option($this->optionName($ruleId));
    }


    /** @var string */
    private $prefix;

    /** @var string */
    private $suffix;


    private function optionNamePattern()
    {
        return '/^' . preg_quote($this->prefix, '/') . '(.*)' . preg_quote($this->suffix, '/') . '$/';
    }

    /**
     * @param string $forRuleId
     * @return string
     */
    private function optionName($forRuleId)
    {
        return "{$this->prefix}{$forRuleId}{$this->suffix}";
    }

    /**
     * Converts objects to arrays recursively
     *
     * @param mixed $value
     * @return mixed $value
     */
    static private function object2Array($value)
    {
        if ($value instanceof \__PHP_Incomplete_Class) {

            $array = array();
            foreach ((array)$value as $property => $value) {
                $array[@end(explode("\0", $property))] = $value;
            }

            unset($array['__PHP_Incomplete_Class_Name']);

            $value = $array;
        }

        if (is_object($value)) {

            $array = array();

            $o = new \ReflectionObject($value);
            foreach ($o->getProperties() as $property) {
                $property->setAccessible(true);
                $array[$property->getName()] = $property->getValue($value);
            }

            $value = $array;
        }

        if (is_array($value)) {
            foreach ($value as &$v) {
                $v = self::object2Array($v);
            }
        }

        return $value;
    }
}