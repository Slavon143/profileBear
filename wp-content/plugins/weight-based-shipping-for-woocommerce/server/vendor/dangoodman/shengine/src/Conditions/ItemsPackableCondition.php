<?php
namespace WbsVendors\Dgm\Shengine\Conditions;

use BoxPacking\Packer;
use Dgm\Shengine\Conditions\Common\AbstractCondition;


class ItemsPackableCondition extends \WbsVendors\Dgm\Shengine\Conditions\Common\AbstractCondition
{
    public function __construct(\WbsVendors\BoxPacking\Packer $packer, $box)
    {
        $this->packer = $packer;
        $this->box = $box;
    }

    public function isSatisfiedBy($boxes)
    {
        return $this->packer->canPack($this->box, $boxes);
    }

    private $packer;
    private $box;
}