<?php
namespace Wbs\Services\ApiService\Apis;

use WbsVendors\Dgm\ClassNameAware\ClassNameAware;
use Wbs\Services\ApiService\IApi;


abstract class AbstractApi extends ClassNameAware implements IApi
{
    static protected function respond($code, $message = null)
    {
        if ($code == 200 && !isset($message)) {
            $message = 'OK';
        }

        wp_die(
            $message,
            null, array('response' => $code)
        );
    }
}