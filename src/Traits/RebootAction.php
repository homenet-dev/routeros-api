<?php

namespace HomeNet\RouterosApi\Traits;

use Exception;

trait RebootAction
{
    public function reboot()
    {
        $reboot = $this->api->comm('/system/reboot');

        if (isset($reboot['!trap'])) {
            throw new Exception($reboot['!trap']);
        }

        return $reboot;
    }
}
