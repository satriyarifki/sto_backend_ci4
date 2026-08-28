<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\M_manifest;
use App\Models\M_kanban;

class InsertManifestCron extends BaseCommand
{
    protected $group       = 'cron';
    protected $name        = 'insert:manifest';
    protected $description = 'Mengambil data e-manifest dan menyimpan ke DB';

    public function run(array $params)
    {
        date_default_timezone_set('Asia/Jakarta');
        
        $manifestModel = new M_manifest();
        $kanbanModel   = new M_kanban();

        $loginUrl = 'https://apihub.toyota.co.id/api/api/exec/tmmin.edcl/api/emanifest-api/0.1.0/api/v1/auth/login';

        $loginData = [
            'username' => 'T034-1.Mekar01',
            'password' => 'Toyota@1234',
        ];

        $loginOptions = [
            'http' => [
                'header'  => "Content-Type: application/json\r\nx-apihub-key: 06334a28-22a0-4832-8aa0-6f56f472c6b0",
                'method'  => 'POST',
                'content' => json_encode($loginData),
            ],
        ];

        $loginContext = stream_context_create($loginOptions);
        $loginResult = file_get_contents($loginUrl, false, $loginContext);
        $loginResponse = json_decode($loginResult, true);

        if (!isset($loginResponse['data']['accessToken'])) {
            die("Gagal login ke API. Pesan: " . ($loginResponse['message'] ?? 'Unknown'));
        }

        $accessToken = $loginResponse['data']['accessToken'];

        $yesterday = date('Ymd', strtotime('-1 day'));
        $currentDateFormatted = date('Y-m-d', strtotime('-1 day'));

        // $yesterday = "20250701";
        // $currentDateFormatted = "2025-07-01";

        $ordDate = $yesterday;
        $currentDate = $currentDateFormatted;

        $inserted = 0;
        $failed   = 0;

        $orderUrl = 'https://apihub.toyota.co.id/api/api/exec/tmmin.edcl/api/emanifest-api/0.1.0/api/v1/Order/daily-order';
        $orderTypes = ['1', '2'];
        $receivingPlants = ['1', '5'];

        foreach ($orderTypes as $orderType) {
            foreach ($receivingPlants as $receivingPlant) {
                $page = 1;

                do {
                    $query = http_build_query([
                        'OrdDate' => $ordDate,
                        'SupplierCode' => 'T034',
                        'SupplierPlantCd' => '1',
                        'RcvPlantCode' => $receivingPlant,
                        'OrderType' => $orderType,
                        'PageNumber' => $page,
                        'PageSize' => 10
                    ]);

                    $fullUrl = $orderUrl . '?' . $query;

                    $options = [
                        'http' => [
                            'header' => [
                                "Authorization: Bearer $accessToken",
                                "x-apihub-key: 06334a28-22a0-4832-8aa0-6f56f472c6b0"
                            ],
                            'method' => 'GET',
                        ],
                    ];

                    $context = stream_context_create($options);
                    $result = file_get_contents($fullUrl, false, $context);
                    $data = json_decode($result, true);

                    // Jika message adalah "Data tidak ditemukan", langsung keluar dari do-while
                    if (isset($data['message']) && $data['message'] === 'Data tidak ditemukan') {
                        break;
                    }

                    // Kalau data kosong juga berhenti
                    if (empty($data['data'])) {
                        break;
                    }

                    foreach ($data['data'] as $item) {
                        $exists = $manifestModel->where('manifest_number', $item['manifestNo'])
                                                ->where('item_number', $item['itemNo'])
                                                ->where('order_type', $orderType)
                                                ->where('receiving_plant', $receivingPlant)
                                                ->first();

                        if ($exists) {
                            $failed++;
                            continue;
                        }

                        $dataInsert = [
                            'manifest_number'       => $item['manifestNo'] ?? null,
                            'order_number'          => $item['orderNo'] ?? null,
                            'part_number'           => $item['partNo'] ?? null,
                            'part_name'             => $item['partName'] ?? null,
                            'dock_code'             => $item['dockCd'] ?? null,
                            'supplier_name'         => $item['supplierName'] ?? null,
                            'orderMode'             => $item['orderMode'] ?? null,
                            'kanban_no'             => $item['kanbanNo'] ?? null,
                            'plane_code'            => $item['pLaneCd'] ?? null,
                            'plane_sequence'        => $item['pLaneSeq'] ?? null,
                            'plane_date'            => $item['pLaneDt'] ?? null,
                            'order_quantity'        => $item['orderQty'] ?? 0,
                            'item_number'           => $item['itemNo'] ?? 0,
                            'company_code'          => $item['companyCd'] ?? null,
                            'qtyPerContainer'       => $item['qtyPerContainer'] ?? 0,
                            'orderPlanQtyLot'       => $item['orderPlanQtyLot'] ?? 0,
                            'order_date'            => $item['orderReleaseDt'] ?? null,
                            'shipping_date'         => $item['shippingDt'] ?? null,
                            'shipping_dock'         => $item['shippingDock'] ?? null,
                            'arrival_date'          => $item['arrivalDt'] ?? null,
                            'supplier_code'         => $item['supplierCode'] ?? null,
                            'rcv_plant_code'        => $item['rcvPlantCd'] ?? null,
                            'carFamilyCd'           => $item['carFamilyCd'] ?? null,
                            'reExportCd'            => $item['reExportCd'] ?? null,
                            'kanbanPrintAddress'    => $item['kanbanPrintAddress'] ?? null,
                            'orderReleaseDt'        => $item['orderReleaseDt'] ?? null,
                            'buildOutFlag'          => $item['buildOutFlag'] ?? null,
                            'buildOutQty'           => $item['buildOutQty'] ?? null,
                            'aicoCeptFlag'          => $item['aicoCeptFlag'] ?? null,
                            'subRouteCd'            => $item['subRouteCd'] ?? null,
                            'subRouteSeq'           => $item['subRouteSeq'] ?? null,
                            'subRouteDt'            => $item['subRouteDt'] ?? null,
                            'fstXDocCd'             => $item['fstXDocCd'] ?? null,
                            'fstXPlantCd'           => $item['fstXPlantCd'] ?? null,
                            'fstXShippingDockCd'    => $item['fstXShippingDockCd'] ?? null,
                            'fstXDockArrivalDt'     => $item['fstXDockArrivalDt'] ?? null,
                            'fstXDockDepartDt'      => $item['fstXDockDepartDt'] ?? null,
                            'fstXDockRouteGrpCd'    => $item['fstXDockRouteGrpCd'] ?? null,
                            'fstXDockSeqNo'         => $item['fstXDockSeqNo'] ?? null,
                            'fstXRouteDt'           => $item['fstXRouteDt'] ?? null,
                            'sndXDocCd'             => $item['sndXDocCd'] ?? null,
                            'sndXPlantCd'           => $item['sndXPlantCd'] ?? null,
                            'sndXShippingDockCd'    => $item['sndXShippingDockCd'] ?? null,
                            'sndXDockArrivalDt'     => $item['sndXDockArrivalDt'] ?? null,
                            'sndXDockDepartDt'      => $item['sndXDockDepartDt'] ?? null,
                            'sndXDockRouteGrpCd'    => $item['sndXDockRouteGrpCd'] ?? null,
                            'sndXDockSeqNo'         => $item['sndXDockSeqNo'] ?? null,
                            'trdXDocCd'             => $item['trdXDocCd'] ?? null,
                            'trdXPlantCd'           => $item['trdXPlantCd'] ?? null,
                            'trdXShippingDockCd'    => $item['trdXShippingDockCd'] ?? null,
                            'trdXDockArrivalDt'     => $item['trdXDockArrivalDt'] ?? null,
                            'trdXDockDepartDt'      => $item['trdXDockDepartDt'] ?? null,
                            'trdXDockRouteGrpCd'    => $item['trdXDockRouteGrpCd'] ?? null,
                            'trdXDockSeqNo'         => $item['trdXDockSeqNo'] ?? null,
                            'trdXRouteDt'           => $item['trdXRouteDt'] ?? null,
                            'manifestPrintPointCd'  => $item['manifestPrintPointCd'] ?? null,
                            'manifestPrintDatePlant'=> $item['manifestPrintDatePlant'] ?? null,
                            'manifestPrintDateLocal'=> $item['manifestPrintDateLocal'] ?? null,
                            'kanbanPrintPointCd'    => $item['kanbanPrintPointCd'] ?? null,
                            'kanbanPrintDatePlant'  => $item['kanbanPrintDatePlant'] ?? null,
                            'kanbanPrintDateLocal'  => $item['kanbanPrintDateLocal'] ?? null,
                            'conveyanceRoute'       => $item['conveyanceRoute'] ?? null,
                            'ferryPortArrivalDt'    => $item['ferryPortArrivalDt'] ?? null,
                            'ferryPortDepartDt'     => $item['ferryPortDepartDt'] ?? null,
                            'printFlag'             => $item['printFlag'] ?? 0,
                            'printDate'             => $item['printDate'] ?? null,
                            'receiveFlag'           => $item['receiveFlag'] ?? 0,
                            'receiveDt'             => $item['receiveDt'] ?? null,
                            'usageFlag'             => $item['usageFlag'] ?? 0,
                            'usageDt'               => $item['usageDt'] ?? null,
                            'deletionFlag'          => $item['deletionFlag'] ?? null,
                            'deletionDt'            => $item['deletionDt'] ?? null,
                            'dropStatusFlag'        => $item['dropStatusFlag'] ?? 0,
                            'dropStatusDt'          => $item['dropStatusDt'] ?? null,
                            'sapFlag'               => $item['sapFlag'] ?? 0,
                            'sapDate'               => $item['sapDate'] ?? null,
                            'totalPage'             => $item['totalPage'] ?? null,
                            'ekbsFlag'              => $item['ekbsFlag'] ?? 0,
                            'ekbsDt'                => $item['ekbsDt'] ?? null,
                            'patDpFlag'             => $item['patDpFlag'] ?? null,
                            'patPlFlag'             => $item['patPlFlag'] ?? null,
                            'receivePlantName'      => $item['receivePlantName'] ?? null,
                            'packageType'           => $item['packageType'] ?? null,
                            'supplierInfo1'         => $item['supplierInfo1'] ?? null,
                            'supplierInfo2'         => $item['supplierInfo2'] ?? null,
                            'supplierInfo3'         => $item['supplierInfo3'] ?? null,
                            'printCd'               => $item['printCd'] ?? null,
                            'importirInfo'          => $item['importirInfo'] ?? null,
                            'importirInfo2'         => $item['importirInfo2'] ?? null,
                            'importirInfo3'         => $item['importirInfo3'] ?? null,
                            'importirInfo4'         => $item['importirInfo4'] ?? null,
                            'partBarcode'           => $item['partBarcode'] ?? null,
                            'modDstCd'              => $item['modDstCd'] ?? null,
                            'caseNo'                => $item['caseNo'] ?? null,
                            'plnPckDt'              => $item['plnPckDt'] ?? null,
                            'boxNo'                 => $item['boxNo'] ?? null,
                            'contSno'               => $item['contSno'] ?? null,
                            'processType'           => $item['processType'] ?? null,
                            'lotModNo'              => $item['lotModNo'] ?? null,
                            'cfCd'                  => $item['cfCd'] ?? null,
                            'receivedQty'           => $item['receivedQty'] ?? 0,
                            'dockType'              => $item['dockType'] ?? null,
                            'fetch_date'            => $currentDate,
                            'order_type'            => $orderType,
                            'receiving_plant'       => $receivingPlant,
                        ]; 

                        if ($manifestModel->insert($dataInsert)) {
                            $inserted++;

                            if (!empty($item['kanbans'])) {
                                foreach ($item['kanbans'] as $kanban) {
                                    $kanbanData = [
                                        'manifest_number' => $kanban['manifestNo'] ?? null,
                                        'item_number'     => $kanban['itemNo'] ?? 0,
                                        'seq_number'      => $kanban['seqNo'] ?? 0,
                                        'kanban_code'     => $kanban['kanbanCode'] ?? null,
                                        'kanban_id'       => $kanban['kanbanId'] ?? null,
                                    ];
                                    $kanbanModel->insert($kanbanData);
                                }
                            }
                        } else {
                            $failed++;
                        }
                    }

                    $page++;
                } while (true);
            }
        }
        CLI::write("Insert selesai. Berhasil: {$inserted} | Gagal: {$failed}", 'green');
    }
}
