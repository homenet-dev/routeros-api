<?php

namespace HomeNet\RouterosApi\Traits;

trait HasInterfaces
{
    public function interfaces()
    {
        $interfaces = $this->api->comm('/interface/print');

        return $interfaces;
    }
}
