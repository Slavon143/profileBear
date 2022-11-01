<?php
namespace Wbs\Services\LegacyConfigService;

use WbsVendors\Dgm\Arrays\Arrays;


class LegacyConfigRulesOrderStorage
{
    public function __construct($storageKeyName)
    {
        $this->storageKeyName = $storageKeyName;
    }

    public function sort(array $profiles)
    {
        return Arrays::map($this->sortIds(array_keys($profiles)), function($id) use($profiles) {
            return $profiles[$id];
        });
    }

    public function sortIds(array $profileIds)
    {
        $profilesWithDefinedOrder = array();
        $profilesWithNoDefineOrder = array();

        $weights = $this->getProfilesSortWeights();
        foreach ($profileIds as $profileId) {
            if (isset($weights[$profileId])) {
                $profilesWithDefinedOrder[$weights[$profileId]] = $profileId;
            } else {
                $profilesWithNoDefineOrder[] = $profileId;
            }
        }

        ksort($profilesWithDefinedOrder);

        $sortedProfiles = array_merge($profilesWithDefinedOrder, $profilesWithNoDefineOrder);

        return $sortedProfiles;
    }


    private $storageKeyName;

    private function getProfilesSortWeights()
    {
        return get_option($this->storageKeyName, array());
    }
}