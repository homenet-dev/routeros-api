<?php

namespace HomeNet\RouterosApi\Traits;

trait HasConnectedDevice
{
    public function connectedDevices()
    {
        $data = [];
        $data['.proplist'] = '.id,address,mac-address,status,host-name,comment';
        $data['?status'] = 'bound';

        $connectedLease = $this->api->comm('/ip/dhcp-server/lease/print', $data);
        // dd($connectedLease);

        if (isset($connectedLease['!trap'])) {
            return [
                'error' => true,
                'messages' => 'Terjadi kesalahan LD-001',
            ];
        }

        $chooseDataLease = [];
        $chooseDataLease['leases-id'] = [];
        $chooseDataLease['mac-address'] = [];
        $chooseDataLease['saved'] = [];
        $chooseDataLease['name'] = [];
        $chooseDataLease['id_jenis_perangkat'] = [];
        $chooseDataLease['nama_perangkat'] = [];

        foreach ($connectedLease as $device) {
            $chooseDataLease['leases-id'][] = $device['.id'];
            $chooseDataLease['mac-address'][] = $device['mac-address'];

            // getting device name from comment or hostname
            if (! isset($device['comment'])) {
                $chooseDataLease['saved'][] = false;
                if (isset($device['host-name'])) {
                    $chooseDataLease['name'][] = $device['host-name'];
                } else {
                    $chooseDataLease['name'][] = $device['address'];
                }
            } else {
                if (substr($device['comment'], 0, 3) == 'jc-') {
                    $deviceName = substr($device['comment'], 3);
                    $chooseDataLease['name'][] = $deviceName;
                    $chooseDataLease['saved'][] = true;
                } else {
                    if (! isset($device['host-name'])) {
                        $device['host-name'] = $device['address'];
                        $chooseDataLease['name'][] = $device['host-name'];
                    } else {
                        if ($device['host-name'] != null || $device['host-name'] != '') {
                            $chooseDataLease['name'][] = $device['host-name'];
                        } else {
                            $chooseDataLease['name'][] = $device['address'];
                        }
                    }

                    // $chooseDataLease['name'][] = $device['address'];

                    $chooseDataLease['saved'][] = false;
                }
            }
        }

        //getting blocked user
        unset($data);

        $data = [];
        $data['.proplist'] = '.id,src-mac-address,disabled,invalid,time';
        $data['?comment'] = 'jinom-block-user';
        $data['?disabled'] = 'false';

        $APIBlockedUser = $this->api->comm('/ip/firewall/filter/print', $data);

        $blockedUser = [];
        foreach ($APIBlockedUser as $block) {
            // dd($block);
            $macAddress = $block['src-mac-address'];
            if (in_array($macAddress, $chooseDataLease['mac-address'])) {
                $block['device-name'] = $chooseDataLease['name'][array_search($macAddress, $chooseDataLease['mac-address'])];
            } else {
                $block['device-name'] = '-';
            }
            $blockedUser[] = $block;
        }

        $blockedMacAddress = [];
        foreach ($blockedUser as $device) {
            if ($device['disabled'] == 'false' && $device['invalid'] == 'false') {
                $blockedMacAddress[] = $device['src-mac-address'];
            }
        }

        unset($data);
        $data = [];
        $data['.proplist'] = '.id,target,name,rate';
        $data['?parent'] = 'saved-jinom';
        $APIPriorityUser = $this->api->comm('/queue/simple/print', $data);

        if (isset($APIPriorityUser['!trap'])) {
            return [
                'error' => true,
                'messages' => 'Terjadi kesalahan LD-003',
            ];
        }

        $data2['?complete'] = 'yes';
        $arp = $this->api->comm('/ip/arp/print', $data2);
        // dd($arp);
        if (isset($arp['!trap'])) {
            return [
                'error' => true,
                'messages' => 'Terjadi kesalahan LD-004',
            ];
        }

        //get connected users
        $connectedUser = [];
        foreach ($connectedLease as $device) {
            if ($device['status'] == 'bound' && (in_array($device['address'], array_column($arp, 'address')))) {
                $indexArp = array_search($device['address'], array_column($arp, 'address'));

                if ($indexArp) {
                    if ($arp[$indexArp]['complete'] == 'false') {
                        continue;
                    }
                }

                $macAddress = $device['mac-address'];
                // check if device blocked
                if (in_array($device['mac-address'], $blockedMacAddress)) {
                    continue;
                    $device['blocked-status'] = true;
                } else {
                    $device['blocked-status'] = false;
                }
                $device['connect-status'] = true;

                $device['comment'] = $chooseDataLease['name'][array_search($macAddress, $chooseDataLease['mac-address'])];
                $device['saved'] = $chooseDataLease['saved'][array_search($macAddress, $chooseDataLease['mac-address'])];
                // dd($device);

                $device['id_queue'] = null;

                foreach ($APIPriorityUser as $queue) {
                    // dd($queue);
                    if ($queue['target'] == $device['address'].'/24' || $queue['target'] == $device['address'].'/32') {
                        $rate = explode('/', $queue['rate']);
                        $upload = $rate[0];
                        $download = $rate[1];

                        $upload = (float) $upload / 1000000;
                        $download = (float) $download / 1000000;
                        // dd($upload);

                        $upload = number_format($upload, 2);
                        $download = number_format($download, 2);
                        $downloadUnit = 'Mbps';
                        $uploadUnit = 'Mbps';

                        $device['download'] = $download;
                        $device['upload'] = $upload;
                        $device['id_queue'] = $queue['.id'];
                    }
                }

                if (! isset($device['download'])) {
                    $device['download'] = '-';
                    $downloadUnit = '-';
                }

                if (! isset($device['upload'])) {
                    $device['upload'] = '-';
                    $uploadUnit = '-';
                }

                $device['downloadUnit'] = $downloadUnit;
                $device['uploadUnit'] = $uploadUnit;

                $connectedUser[] = $device;
            }
        }
        $dashboardData['connected_user'] = strval(count($connectedUser));

        $response = [
            'error' => false,
            'messages' => 'success',
            'data' => ['list' => $connectedUser],
        ];

        return $response;
    }
}
