<?php

namespace HomeNet\RouterosApi\Traits;

trait HasInterfaces
{
    public function interfaces()
    {
        $interfaces = $this->comm('/interface/print');

        return $interfaces;
    }

    public function trafficmonitor($interfaceName, $duration)
    {
        $monitoringtraffic = $this->comm('/interface/monitor-traffic', [
            'interface' => $interfaceName,
            'duration' => $duration,
        ]);

        return $monitoringtraffic;
    }

    public function avgTrafficMonitor($interfaceName, $duration)
    {

        $trafficMonitor = $this->comm('/interface/monitor-traffic', [
            'interface' => $interfaceName,
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
