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

    public function avgTrafficMonitor($interfacename, $duration)
    {

        $trafficMonitor = $this->api->comm('/interface/monitor-traffic', [
            'interface' => $interfacename,
            'duration' => $duration,
        ]);

        $num = count($trafficMonitor);
        $totalrxbits = 0;
        $totaltxbits = 0;

        $avgrx = 0;
        $avgtx = 0;

        for ($i = 0; $i < $num; $i++) {
            $totalrxbits += (float) $trafficMonitor[$i]['rx-bits-per-second'];
            $totaltxbits += (float) $trafficMonitor[$i]['tx-bits-per-second'];
        }

        if ($num > 0) {
            $avgrx = $totalrxbits / $num;
            $avgtx = $totaltxbits / $num;
        }

        return [
            'rx' => $avgrx,
            'tx' => $avgtx,
        ];
    }
}
