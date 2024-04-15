<?php

namespace HomeNet\RouterosApi\Traits;

use Exception;

trait HasHotspot
{
    public function hotspotServers()
    {
        //print data hotspot keseluruhan
        $hotspots = $this->comm('/ip/hotspot/print');

        return $hotspots;
    }

    public function isIsolationHotspotEnable()
    {
        $data = [];
        $data['.proplist'] = '.id,name,disabled';

        //print data hotspot
        $hotspotsApi = $this->comm('/ip/hotspot/print', $data);
        if (isset($hotspotApi['!trap'])) {
            throw new Exception('Gagal print hotspot server');
        }
        // $hotspotsApi = [];

        //cek ambil data hotspot sesuai nama dan status disable
        foreach ($hotspotsApi as $value) {
            if ($value['name'] == 'hotspot-pembayaran') {
                if ($value['disabled'] == 'true') {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    public function setHotspotIsolation(bool $enabled)
    {
        $hotspotdata = [];
        $hotspotdata['.proplist'] = '.id,name,disabled';

        //print data hotspot
        $hotspotsdataget = $this->comm('/ip/hotspot/print', $hotspotdata);
        if (isset($hotspotsdataget['!trap'])) {
            throw new Exception('Gagal print data hotspot');
        }
        // $hotspotsdataget = [];

        //ambil data hotspot dengan nama sesuai dan enable/disable iolation hotspot
        foreach ($hotspotsdataget as $value) {
            if ($value['name'] == 'hotspot-pembayaran') {
                if ($value['disabled'] = $enabled ? 'false' : 'true') {
                    $this->comm('/ip/hotspot/set', $value);

                    return 'Berhasil mengubah status isolation';
                } else {
                    return 'Gagal mengubah status isolation';
                }
            }
        }

        return 'Tidak ditemukan data hotspot, Gagal mengubah status';
    }
}
