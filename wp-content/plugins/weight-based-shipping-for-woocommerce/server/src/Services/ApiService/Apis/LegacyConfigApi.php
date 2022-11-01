<?php
namespace Wbs\Services\ApiService\Apis;

use Wbs\Services\LegacyConfigService;


class LegacyConfigApi extends AbstractApi
{
    public function __construct(LegacyConfigService $config)
    {
        $this->config = $config;
    }

    public function handleRequest()
    {
        static $suppotedMethods = array('GET', 'DELETE');

        $method = @$_SERVER['REQUEST_METHOD'];
        if (!in_array($method, $suppotedMethods, true)) {
            $suppotedMethodsString = join(',', $suppotedMethods);
            header("Allow: {$suppotedMethodsString}");
            self::respond(405, "Supported methods: {$suppotedMethodsString}, request '{$method}'.");
        }

        $method = strtolower($method);
        $this->$method();
    }


    /** @var LegacyConfigService */
    private $config;

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function get()
    {
        $config = $this->config->get();
        if (!$config->rules) {
            self::respond(404, "Legacy config not found");
        }

        self::respond(200, json_encode($config->toArray()));
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function delete()
    {
        $this->config->delete();
        self::respond(200);
    }
}