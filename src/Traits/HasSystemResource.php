<?php

namespace HomeNet\RouterosApi\Traits;

use Exception;

trait HasSystemResource
{
    public function uptime()
    {
        // Ambil data uptime
        $datauptime = [];
        $datauptime['.proplist'] = 'uptime';

        // Print exception jika error
        $uptime = $this->comm('/system/resource/print', $datauptime);
        if (isset($uptime['!trap'])) {
            throw new Exception('Gagal print data uptime!');
        }

        return $uptime[0]['uptime'];
    }
}
