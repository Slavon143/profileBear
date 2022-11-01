<?php
namespace Wbs\Services\ApiService\Apis;

use Wbs\ShippingMethod;


class ConfigApi extends AbstractApi
{
    public function handleRequest()
    {
        if (@$_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            self::respond(405, 'The only supported method is POST.');
        }

        $this->post();
    }

    private function post()
    {
        if ($requestBody = file_get_contents('php://input'))
        if ($requestBody = json_decode($requestBody, true))
        if (array_key_exists('config', $requestBody)) {
            $method = new ShippingMethod(@$_GET['instance_id']);
            $method->config($requestBody['config']);
            self::respond(200);
        }

        self::respond(400, 'Empty or malformed request body.');
    }
}