<?php

namespace HomeNet\RouterosApi;

use Exception;
use HomeNet\RouterosApi\Traits\HasConnectedDevice;
use HomeNet\RouterosApi\Traits\HasHotspot;
use HomeNet\RouterosApi\Traits\HasInterfaces;
use HomeNet\RouterosApi\Traits\HasPing;
use HomeNet\RouterosApi\Traits\HasPriority;
use HomeNet\RouterosApi\Traits\HasSavedDevice;
use HomeNet\RouterosApi\Traits\HasSystemResource;
use HomeNet\RouterosApi\Traits\RebootAction;

class RouterAPI
{
    use HasConnectedDevice;
    use HasHotspot;
    use HasInterfaces;
    use HasPing;
    use HasPriority;
    use HasSavedDevice;
    use HasSystemResource;
    use RebootAction;

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
