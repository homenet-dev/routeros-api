<?php

namespace HomeNet\RouterosApi\Traits;

use Exception;

trait HasPing
{
    public function ping($host, $count)
    {
        $ping = $this->api->comm("/ping", array(
            "address" => "$host",
            "count" => "$count",
        ));
        if (isset($ping['!trap'])) {
            throw new Exception('Error');
        }
        $num = count($ping);

        //struktur response
        for ($i = 0; $i < $num; $i++) {
            $hot = $ping[$i]['host'];
            // $size = $ping[$i]['size'];
            $ttl = $ping[$i]['ttl'];
            $time = $ping[$i]['time'];
            $packet_loss = $ping[$i]['packet-loss'];
            $avg = $ping[$i]['avg-rtt'];

            $text = "Ping from $hot, TTL= $ttl, time= $time, pl= $packet_loss, avg= $avg";
        }
        return ($text);
    }
}

       // Ambil data uptime
        // $datauptime = [];
        // $datauptime['.proplist'] = 'uptime';

        // Print exception jika error 
        // $ping = $this->api->comm('/ping/print');
        // if (isset($uptime['!trap'])) {
        //     throw new Exception('Gagal print data uptime!');
        // }

        // foreach ($uptime as $timedata) {
        //     $uptimeString = $timedata['uptime'];
        //     $formattedUptime = $this->convertUptime($uptimeString);
        //     return $formattedUptime;
        // }
        //  $ping;

        // $PING = $this->api->comm("/ping", array(
        //     "address" => "www.google.com",
        //     "count" => "5",
        //     "routing-table" => "$rm",
        // ));
        // $response = $PING['0']['avg-rtt'] ." ms";
        // echo ltrim($response, 0);
