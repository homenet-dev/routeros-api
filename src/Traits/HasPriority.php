<?php

namespace HomeNet\RouterosApi\Traits;

use Exception;

trait HasPriority
{
    public function priorityPrint()
    {
        $datapriority = [];
        $datapriority['.proplist'] = '.id,name,priority,target';

        $lease = $this->api->comm('/queue/simple/print', $datapriority);

        if (isset($lease['!trap'])) {
            throw new Exception('Gagal mendapatkan data queue');
        }

        return $lease;
    }

    public function priorityDevice($target)
    {
        $datapriority = [];
        $datapriority['.proplist'] = '.id,name,priority,target';

        $lease = $this->api->comm('/queue/simple/print', $datapriority);

        if (isset($lease['!trap'])) {
            throw new Exception('Gagal mendapatkan data queue');
        }

        $updated = false;

        foreach ($lease as $value) {
            if ($value['target'] == $target) {
                if (strpos($value['name'], 'jc-') === 0) {
                    $device_name = str_replace('jc-', 'priority-jinom-', $value['name']);
                    $value['priority'] = '5/5';
                    $this->api->comm('/queue/simple/set', [
                        '.id' => $value['.id'],
                        'name' => $device_name,
                        'priority' => $value['priority'],
                    ]);
                    $updated = true;
                }
            }
        }
        if ($updated) {
            return 'Berhasil mengubah priority';
        } else {
            return 'Gagal mengubah priority';
        }
    }

    public function unpriorityDevice($target)
    {
        $dataunpriority = [];
        $dataunpriority['.proplist'] = '.id,name,priority,target';

        $leases = $this->api->comm('/queue/simple/print', $dataunpriority);

        if (isset($leases['!trap'])) {
            throw new Exception('Gagal mendapatkan data queue');
        }

        $updated = false;

        foreach ($leases as $value) {
            if ($value['target'] == $target) {
                if (strpos($value['name'], 'priority-jinom-') === 0) {
                    $device_name = str_replace('priority-jinom-', 'jc-', $value['name']);
                    $value['priority'] = '6/6';

                    $this->api->comm('/queue/simple/set', [
                        '.id' => $value['.id'],
                        'name' => $device_name,
                        'priority' => $value['priority'],
                    ]);
                    $updated = true;
                }
            }
        }
        if ($updated) {
            return 'Berhasil mengubah priority';
        } else {
            return 'Gagal mengubah priority';
        }
    }
}
