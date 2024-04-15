<?php

namespace HomeNet\RouterosApi\Traits;

use Exception;

trait HasPing
{
    public function ping($host, $count)
    {
        $ping = $this->comm('/ping', [
            'address' => "$host",
            'count' => "$count",
        ]);
        if (isset($ping['!trap'])) {
            throw new Exception('Error');
        }

        return $ping;
    }
}
