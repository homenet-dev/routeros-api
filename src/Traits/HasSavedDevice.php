<?php

namespace HomeNet\RouterosApi\Traits;

use Exception;

trait HasSavedDevice
{
    public function savedDevices()
    {
        $saved = [];

        $commandData = [];
        $commandData['.proplist'] = '.id,target,name,rate';
        $commandData['?parent'] = 'saved-jinom';
        $queue = $this->comm('/queue/simple/print', $commandData);
        if (isset($queue['!trap'])) {
            throw new Exception('Terjadi kesalahan RS-001');
        }

        // get data leases
        $data['.proplist'] = '.id,address,mac-address,host-name,comment';
        $lease = $this->comm('/ip/dhcp-server/lease/print', $data);
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

        return $saved;
    }

    public function isDeviceRegistered($deviceName)
    {
        $inputLower = strtolower($deviceName);
        $data = [
            '.proplist' => 'comment,host-name',
        ];

        $registerDeviceData = $this->comm('/ip/dhcp-server/lease/print', $data);

        if (isset($registerDeviceData['!trap'])) {
            throw new Exception('Gagal mengambil data registered device');
        }

        foreach ($registerDeviceData as $lease) {
            if (isset($lease['comment'])) {
                $commentLower = strtolower($lease['comment']);
                if (strpos($commentLower, $inputLower) !== false) {
                    return true;
                }
            }

            if (isset($lease['host-name'])) {
                $hostNameLower = strtolower($lease['host-name']);
                if (strpos($hostNameLower, $inputLower) !== false) {
                    return true;
                }
            }
        }

        throw new Exception('Device tidak ditemukan');
    }

    public function registerDevice(
        string $idLease,
        string $deviceName,
        string $bandwidth = '25M'
    ) {
        $queue = $this->comm('/queue/simple/print', ['?name' => "jc-$deviceName"]);

        if (count($queue) > 0) {
            throw new Exception('Nama sudah terdaftar. Mohon masukkan nama lain');
        } else {

            $leases = $this->comm('/ip/dhcp-server/lease/print', [
                '?.id' => $idLease,
            ]);

            if (array_key_exists('!trap', $leases)) {
                $response['error'] = true;
                $response['messages'] = $leases['!trap'][0]['message'];
            } else {
                if (count($leases) > 0) {
                    $deviceAddress = $leases[0]['address'];
                    $ipAddress = $deviceAddress;
                    unset($commandData);
                    $commandData = [];
                    $commandData['.id'] = $idLease;

                    $APIMakeStatic = $this->comm('/ip/dhcp-server/lease/make-static', $commandData);

                    if (array_key_exists('!trap', $APIMakeStatic)) {
                        throw new Exception($APIMakeStatic['!trap'][0]['message']);
                    } else {

                        // make new
                        unset($commandData);
                        $commandData['.proplist'] = 'address';
                        $commandData['?.id'] = $idLease;

                        unset($commandData);
                        $commandData = [];
                        $commandData['comment'] = 'jc-'.$deviceName;
                        $commandData['address'] = $ipAddress;
                        $commandData['.id'] = $idLease;

                        $APISetComment = $this->comm('/ip/dhcp-server/lease/set', $commandData);

                        if (array_key_exists('!trap', $APISetComment)) {
                            throw new Exception($APISetComment['!trap'][0]['message']);
                        } else {
                            $ipAddressFix = $ipAddress.'/32';

                            unset($commandData);
                            $commandData['?target'] = $ipAddressFix;
                            $commandData['count-only'] = '';

                            $deviceQueue = $this->comm('/queue/simple/print', $commandData);
                            if ($deviceQueue < 1) {
                                // create new simple queue
                                unset($commandData);
                                $commandData['name'] = "jc-$deviceName";
                                $commandData['target'] = $ipAddress;
                                $commandData['parent'] = 'saved-jinom';
                                $commandData['packet-marks'] = 'others-jinom-packet';
                                $commandData['max-limit'] = "$bandwidth/$bandwidth";
                                $commandData['priority'] = '6/6';
                                $APIAddQueue = $this->comm('/queue/simple/add', $commandData);
                                if (isset($APIAddQueue['!trap'])) {
                                    throw new Exception($APIAddQueue['!trap'][0]['message']);
                                }
                            } else {
                                // update simple queue
                                unset($commandData);
                                $commandData['.proplist'] = '.id,name';
                                $commandData['?target'] = $ipAddressFix;

                                $deviceQueue = $this->comm('/queue/simple/print', $commandData);
                                $deviceQueue = $deviceQueue[0];

                                $queueId = $deviceQueue['.id'];
                                $oldDevicename = $deviceQueue['name'];

                                if (strpos($oldDevicename, 'priority-jinom') !== false) {
                                    $newDeviceName = 'priority-jinom-'.$deviceName;
                                    $priority = '5/5';
                                } else {
                                    $newDeviceName = "jc-$deviceName";
                                    $priority = '6/6';
                                }

                                unset($commandData);
                                $commandData['name'] = $newDeviceName;
                                $commandData['target'] = $ipAddress;
                                $commandData['parent'] = 'saved-jinom';
                                $commandData['packet-marks'] = 'others-jinom-packet';
                                $commandData['max-limit'] = "$bandwidth/$bandwidth";
                                $commandData['priority'] = $priority;
                                $commandData['.id'] = $queueId;

                                $APISetQueue = $this->comm('/queue/simple/set', $commandData);
                                if (array_key_exists('!trap', $APISetQueue)) {
                                    throw new Exception($APISetQueue['!trap'][0]['message']);
                                }
                            }
                        }
                        $this->reOrderSimpleQueue();

                        //create dns filtering for device

                        $commandData = [];
                        $commandData['.proplist'] = '.id,address,list';
                        $commandData['?disabled'] = 'false';
                        $APIAddressList = $this->comm('/ip/firewall/address-list/print', $commandData);

                        $addressList = [];
                        $addressList['id'] = [];
                        $addressList['list'] = [];
                        $addressList['address'] = [];

                        foreach ($APIAddressList as $address) {
                            if (substr($address['list'], 0, 3) == 'jc-') {
                                $addressList['id'][] = $address['.id'];
                                $addressList['list'][] = $address['list'];
                                $addressList['address'][] = $address['address'];
                            }
                        }

                        // get all dns filter nat setting
                        unset($commandData);
                        $commandData = [];
                        $commandData['.proplist'] = 'comment';
                        // $commandData["?disabled"]   = "false";
                        $APINatList = $this->comm('/ip/firewall/nat/print', $commandData);

                        $jinomDNSList = [];
                        foreach ($APINatList as $nat) {
                            if (isset($nat['comment'])) {
                                $natName = $nat['comment'];
                                if (substr($natName, 0, 3) == 'jc-' && (strpos($natName, '-dns-') !== false)) {
                                    $jinomDNSList[] = $natName;
                                }
                            }
                        }

                        // prepare variabel for setting dns filter
                        $dnsFilterName = 'jc-low-dns';
                        $port = '531';

                        // check if device is on address list
                        if (in_array($ipAddress, $addressList['address'])) {
                            // update DNS Level
                            $dnsFilterIndex = array_search($ipAddress, $addressList['address']);
                            if ($dnsFilterIndex !== false) {
                                if ($dnsFilterName != $addressList['list'][$dnsFilterIndex]) {

                                    $id = $addressList['id'][$dnsFilterIndex];

                                    unset($commandData);
                                    $commandData = [];
                                    $commandData['list'] = $dnsFilterName;
                                    $commandData['numbers'] = $id;
                                    $APIAddressList = $this->comm('/ip/firewall/address-list/set', $commandData);
                                }
                            }
                        } else {
                            // add DNS Level
                            unset($commandData);
                            $commandData = [];
                            $commandData['list'] = $dnsFilterName;
                            $commandData['address'] = $ipAddress;
                            $commandData['disabled'] = 'no';
                            $APIAddressList = $this->comm('/ip/firewall/address-list/add', $commandData);
                        }

                        $dnsFilterNameSG = $dnsFilterName.'-sg';
                        $dnsFilterNameID = $dnsFilterName.'-id';

                        // if (!in_array($dnsFilterNameSG, $jinomDNSList)) {
                        //     unset($commandData);
                        //     $commandData = array();
                        //     $commandData["chain"]               = "dstnat";
                        //     $commandData["protocol"]            = "udp";
                        //     $commandData["dst-port"]            = "53";
                        //     $commandData["src-address-list"]    = $dnsFilterName;
                        //     $commandData["action"]              = "dst-nat";
                        //     $commandData["to-addresses"]        = "167.71.194.55";
                        //     $commandData["to-ports"]            = $port;
                        //     $commandData["disabled"]            = "yes";
                        //     $commandData["comment"]             = $dnsFilterNameSG;
                        //     $APIAddressList  = $this->comm("/ip/firewall/nat/add", $commandData);
                        // }

                        if (! in_array($dnsFilterNameID, $jinomDNSList)) {
                            unset($commandData);
                            $commandData = [];
                            $commandData['chain'] = 'dstnat';
                            $commandData['protocol'] = 'udp';
                            $commandData['dst-port'] = '53';
                            $commandData['src-address-list'] = $dnsFilterName;
                            $commandData['action'] = 'dst-nat';
                            $commandData['to-addresses'] = '103.122.65.37';
                            $commandData['to-ports'] = $port;
                            $commandData['disabled'] = 'no';
                            $commandData['comment'] = $dnsFilterNameID;
                            $APIAddressList = $this->comm('/ip/firewall/nat/add', $commandData);
                        }
                    }
                } else {
                    throw new Exception('Device tidak ditemukan');
                }
            }
        }

        return true;
    }

    /**
     * Fungsi untuk mengurutkan simple queue
     */
    public function reOrderSimpleQueue(): void
    {
        $allSimpleQueue = $this->api->comm('/queue/simple/print');
        // dd($allSimpleQueue);
        foreach ($allSimpleQueue as $key => $lease) {
            if (substr($lease['name'], 0, 3) == 'jc-' || substr($lease['name'], 0, 15) == 'priority-jinom-') {
                unset($commandData);
                $commandData['numbers'] = $lease['.id'];
                $commandData['destination'] = $allSimpleQueue[0]['.id'];
                $this->api->comm('/queue/simple/move', $commandData);
            }
        }

        $allSimpleQueue = $this->api->comm('/queue/simple/print');

        foreach ($allSimpleQueue as $key => $lease) {
            if ($lease['name'] == 'saved-jinom') {
                unset($commandData);
                $commandData['numbers'] = $lease['.id'];
                $commandData['destination'] = $allSimpleQueue[0]['.id'];
                $this->api->comm('/queue/simple/move', $commandData);
            }
        }
    }
}
