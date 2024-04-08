<?php

namespace HomeNet\RouterosApi\Traits;

trait HasSavedDevice
{
    public function savedDevices()
    {
        $saved = [];

        $commandData = [];
        $commandData['.proplist'] = '.id,target,name,rate';
        $commandData['?parent'] = 'saved-jinom';
        $queue = $this->api->comm('/queue/simple/print', $commandData);
        if (isset($queue['!trap'])) {
            return [
                'error' => true,
                'messages' => 'Terjadi kesalahan RS-001',
            ];
        }

        // get data leases
        $data['.proplist'] = '.id,address,mac-address,host-name,comment';
        $lease = $this->api->comm('/ip/dhcp-server/lease/print', $data);
        if (isset($lease['!trap'])) {
            return [
                'error' => true,
                'messages' => 'Terjadi kesalahan RS-002',
            ];
        }
        // jadikan collection
        $lease = collect($lease);

        foreach ($queue as $key => $value) {
            if ($value['name'] != 'dhcp-jinom') {
                // if (strpos($value['name'], "jc-priority") !== false) {
                $priority = strpos($value['name'], 'priority-jinom-') === false ? false : true;

                // get device name
                $device_name = str_replace('priority-jinom-', '', $value['name']);
                $device_name = str_replace('jc-', '', $device_name);

                // get id leases
                $leaseData = $lease->where('address', substr($value['target'], 0, -3))->first();

                if ($leaseData == null) {
                    $idLeases = '-';
                } else {
                    $idLeases = $leaseData['.id'];
                }

                if ($leaseData) {
                    $saved[] = [
                        'target' => substr($value['target'], 0, -3),
                        'device-name' => $device_name,
                        'mac-address' => isset($leaseData['mac-address']) ? $leaseData['mac-address'] : '',
                        'idLeases' => $idLeases,
                        'idQueue' => $value['.id'],
                        'saved' => true,
                        'is_priority' => $priority,
                    ];
                }
            }
            // }
        }

        return [
            'error' => false,
            'messages' => 'success',
            'data' => ['list' => $saved],
        ];
    }
}
