<?php
namespace Wbs\Services\ApiService;

use WbsVendors\Dgm\SimpleProperties\SimpleProperties;

/**
 * @property-read string $action
 */
class ApiEndpoint extends SimpleProperties
{
    /**
     * @param string $action
     * @param callable $handlerFactory  function(...$args): IApi
     */
    public function __construct($action, $handlerFactory)
    {
        $this->action = $action;
        $this->handlerFactory = $handlerFactory;
    }

    public function url(array $parameters = array())
    {
        $parameters['action'] = $this->action;

        $parameters = array_filter(
            $parameters,
            function($v) { return isset($v); }
        );

        $query = http_build_query($parameters, '', '&');

        $url = admin_url("admin-ajax.php?{$query}");

        return $url;
    }

    public function createHandler()
    {
        return call_user_func_array($this->handlerFactory, func_get_args());
    }

    protected $action;
    private $handlerFactory;
}