<?php

namespace HomeNet\RouterosApi\Traits;

trait HasInterfaces
{
    public function interfaces()
    {
        $interfaces = $this->api->comm('/interface/print');

        return $interfaces;
    }

    public function trafficmonitor($interfacename, $duration)
    {
        $monitoringtraffic = $this->api->comm('/interface/monitor-traffic', [
            'interface' => $interfacename,
            'duration' => $duration,
        ]);

        return $monitoringtraffic;
    }
}
