<?php
namespace Wbs\Services;

use InvalidArgumentException;
use Wbs\Plugin;
use Wbs\Services\ApiService\ApiEndpoint;
use Wbs\Services\ApiService\Apis\ConfigApi;
use Wbs\Services\ApiService\Apis\LegacyConfigApi;
use WbsVendors\Dgm\PluginServices\IService;
use WbsVendors\Dgm\PluginServices\IServiceReady;


class ApiService implements IService, IServiceReady
{
    /**
     * @param callable $legacyConfigServiceFactory  function(): LegacyConfigService
     */
    public function __construct($legacyConfigServiceFactory)
    {
        $this->legacyConfigServiceFactory = $legacyConfigServiceFactory;
    }

    public function ready()
    {
        return is_admin() && defined('DOING_AJAX') && DOING_AJAX;
    }

    public function install()
    {
        $legacyConfigServiceFactory = $this->legacyConfigServiceFactory;

        foreach (self::endpoints() as $endpoint) {
            add_action("wp_ajax_{$endpoint->action}", function() use($endpoint, $legacyConfigServiceFactory) {
                $api = $endpoint->createHandler($legacyConfigServiceFactory);
                $api->handleRequest();
            });
        }
    }

    /**
     * @param string $apiClass
     * @return ApiEndpoint
     * @throws InvalidArgumentException If an unknown $apiClass provided.
     */
    static public function endpoint($apiClass)
    {
        $endpoints = self::endpoints();

        if (!isset($endpoints[$apiClass])) {
            throw new InvalidArgumentException("No endpoints found for api class '{$apiClass}'.");
        }

        return $endpoints[$apiClass];
    }

    /**
     * @return ApiEndpoint[]
     */
    static private function endpoints()
    {
        static $endpoints;

        if (!isset($endpoints)) {
            $endpoints = array(
                ConfigApi::className() => new ApiEndpoint(
                    Plugin::ID . '_config',
                    function() { return new ConfigApi(); }
                ),
                LegacyConfigApi::className() => new ApiEndpoint(
                    Plugin::ID . '_legacy_config',
                    function($legacyConfigServiceFactory) { return new LegacyConfigApi($legacyConfigServiceFactory()); }
                ),
            );
        }

        return $endpoints;
    }


    /** @var callable */
    private $legacyConfigServiceFactory;
}