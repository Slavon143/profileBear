<?php
namespace Wbs;

use WbsVendors\Dgm\SimpleProperties\SimpleProperties;


/**
 * @property-read string $root
 * @property-read string $assets
 * @property-read string $tplFile
 */
class PluginPaths extends SimpleProperties
{
    public function __construct($root)
    {
        $this->root = rtrim($root, '/\\');
        $this->assets = defined('WBS_DEV') ? "{$this->root}/../client/build" : "{$this->root}/..";
        $this->tplFile = "{$this->root}/tpl/main.php";
    }

    public function getAssetUrl($asset = null)
    {
        return plugins_url($asset, $this->assets.'/.');
    }

    protected $root;
    protected $assets;
    protected $tplFile;
}