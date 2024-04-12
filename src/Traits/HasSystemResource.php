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
        $uptime = $this->api->comm('/system/resource/print', $datauptime);
        if (isset($uptime['!trap'])) {
            throw new Exception('Gagal print data uptime!');
        }

        // TODO: Lebih baik gunakan return $uptimep[0] saja
        foreach ($uptime as $timedata) {
            $uptimeString = $timedata['uptime'];

            return $uptimeString;
        }
    }
}
