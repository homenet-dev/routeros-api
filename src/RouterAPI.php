<?php

namespace HomeNet\RouterosApi;

use Exception;
use HomeNet\RouterosApi\Traits\HasInterfaces;

class RouterAPI
{
    use HasInterfaces;

    private $api;

    public function __construct(string $host, string $username, string $password)
    {
        $this->api = new BaseRouterAPI();

        $connect = $this->api->connect($host, $username, $password);
        if (! $connect) {
            throw new Exception("can't connect to host");
        }
    }

    public static function make(string $host, string $username, string $password)
    {
        return new static($host, $username, $password);
    }

    public function comm($comm, $arr = [])
    {
        return $this->api->comm($comm, $arr);
    }
}
