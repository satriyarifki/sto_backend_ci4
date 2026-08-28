<?php

namespace App\Controllers;

use CodeIgniter\Controller;

use \IonAuth\Libraries\IonAuth;
use \App\Models\M_manifest;
use \App\Models\M_kanban;
use \App\Models\M_buffer_manifest;
use App\Libraries\MY_TCPDF AS TCPDF;
use OpenSpout\Reader\Common\Creator\ReaderFactory;
use OpenSpout\Reader\XLSX\Options;

class Emanifest extends BaseController
{
    protected $ionAuth;
    protected $data = [];

    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function dailyInsertOrder()
    {
        $json = $this->request->getJSON();

        if (!is_array($json)) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Format data harus berupa array',
                ]);
        }

        $orderModel = new M_manifest();
        $kanbanModel = new M_kanban();

        $inserted = 0;
        $failed = 0;

        foreach ($json as $item) {
            $exists = $orderModel->where('manifest_number', $item->manifestNo)
                                 ->where('item_number', $item->itemNo)
                                 ->first();
        
            if ($exists) {
                $failed++;
                continue;
            }
        
            $data = [
                'manifest_number' => $item->manifestNo ?? null,
                'order_number'    => $item->orderNo ?? null,
                'part_number'     => $item->partNo ?? null,
                'part_name'       => $item->partName ?? null,
                'dock_code'       => $item->dockCd ?? null,
                'supplier_name'   => $item->supplierName ?? null,
                'kanban_no'       => $item->kanbanNo ?? null,
                'plane_code'      => $item->pLaneCd ?? null,
                'plane_sequence'  => $item->pLaneSeq ?? null,
                'plane_date'      => $item->pLaneDt ?? null,
                'order_quantity'  => $item->orderQty ?? 0,
                'item_number'     => $item->itemNo ?? 0,
                'order_date'      => $item->orderReleaseDt ?? null,
                'shipping_date'   => $item->shippingDt ?? null,
                'arrival_date'    => $item->arrivalDt ?? null,
                'supplier_code'   => $item->supplierCode ?? null,
                'rcv_plant_code'  => $item->rcvPlantCd ?? null,
                'fetch_date'      => isset($item->formattedDate) ? date('Y-m-d', strtotime($item->formattedDate)) : null,
                'order_type'      => $item->order_type ?? null,
                'receiving_plant' => $item->receiving_plant ?? null,
            ];
        
            if ($orderModel->insert($data)) {
                $inserted++;

                if (!empty($item->kanbans)) {
                    foreach ($item->kanbans as $kanban) {
                        $kanbanData = [
                            'manifest_number'   => $kanban->manifestNo ?? null,
                            'item_number'       => $kanban->itemNo ?? 0,
                            'seq_number'        => $kanban->seqNo ?? 0,
                            'kanban_code'       => $kanban->kanbanCode ?? null,
                            'kanban_id'         => $kanban->kanbanId ?? null,
                        ];
        
                        $kanbanModel->insert($kanbanData);
                    }
                }
            } else {
                $failed++;
            }
        }
        

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Proses insert selesai.',
            'inserted' => $inserted,
            'failed'   => $failed,
        ]);
    }


    public function get_data_internal_manifest()
    {

        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan.'])->setStatusCode(401);
        }

        $token = $matches[1];

        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($token);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid.'])->setStatusCode(401);
        }

        $user = $this->request->user;
        $date = $this->request->getPost('date');
        $order_type = $this->request->getPost('orderType');
        $receiving_plant = $this->request->getPost('receiving_plant');

        $model = new M_manifest();
        $data = $model->getInternalManifest($date, $order_type, $receiving_plant);

        if (empty($data)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Data tidak ditemukan. Lakukan sync data terlebih dahulu.',
                'user'    => $user,
                'data' => []
            ]);
        }
    
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data ditemukan.',
            'user'    => $user,
            'data' => $data
        ]);
    }

    public function get_qty_kanban_manifest()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan.'])->setStatusCode(401);
        }

        $token = $matches[1];

        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($token);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid.'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $no_manifest = $this->request->getPost('no_manifest');

        $model = new M_kanban();

        $count_total = $model->getKanbanManifest($no_manifest, true);
        $count_confirm = $model->getKanbanScan($no_manifest, true);
        // $data = $model->getKanbanManifest($no_manifest);
    
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data ditemukan.',
            'data_total' => $count_total,
            'data_confirm' => $count_confirm
        ]);
    }

    public function get_id_skid()
    {

        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan.'])->setStatusCode(401);
        }

        $token = $matches[1];

        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($token);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid.'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $no_manifest = $this->request->getPost('no_manifest');

        $model_manifest = new M_buffer_manifest();

        $generate_id = $model_manifest->getSkidId($no_manifest);
        // $data = $model->getKanbanManifest($no_manifest);
    
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data ID Generate',
            'generate_id'  => $generate_id
        ]);
    }

    public function confirm_kanban()
    {

        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan.'])->setStatusCode(401);
        }

        $token = $matches[1];

        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($token);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid.'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $kanbanId = $this->request->getPost('kanbanId');
        $noManifest = $this->request->getPost('noManifest');
        $no_skid = $this->request->getPost('no_Skid');

        $model = new M_Kanban();

        $updateResult = $model->updateIsScan($kanbanId, $noManifest, $no_skid, $user);
        

        if ($updateResult['status']) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $updateResult['message']
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $updateResult['message']
            ]);
        }
    }

    public function confirm_manifest()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan.'])->setStatusCode(401);
        }

        $token = $matches[1];

        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($token);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid.'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $noManifest = $this->request->getPost('noManifest');
        $noSkid = $this->request->getPost('noSkid');
        $kanbanId = $this->request->getPost('kanbanId');

        $model = new M_Kanban();
        $result = $model->queryConfirm($noManifest, $noSkid, $kanbanId);

        return $this->response->setJSON($result);
    }

    public function confirm_flag()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan.'])->setStatusCode(401);
        }

        $token = $matches[1];

        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($token);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid.'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $noManifest = $this->request->getPost('noManifest');
        $no_skid = $this->request->getPost('noSkid');

        $model = new M_Kanban();

        $updateResult = $model->updateIsConfirm($noManifest, $no_skid);

        if ($updateResult['status']) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $updateResult['message']
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $updateResult['message']
            ]);
        }
    }

    public function cancel_flag()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan.'])->setStatusCode(401);
        }

        $token = $matches[1];

        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($token);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid.'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $noManifest = $this->request->getPost('noManifest');
        $no_skid = $this->request->getPost('noSkid');
        $kanbanId = $this->request->getPost('kanbanId');

        $model = new M_Kanban();

        $updateResult = $model->updateIsCancel($noManifest, $no_skid, $kanbanId);

        if ($updateResult['status']) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $updateResult['message']
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $updateResult['message']
            ]);
        }
    }

    public function list_skid()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan.'])->setStatusCode(401);
        }

        $token = $matches[1];

        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($token);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid.'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $noManifest = $this->request->getGet('noManifest');

        $model = new M_Kanban();
        $result = $model->ListSkid($noManifest);
        return $this->response->setJSON([
            'status'=> 'success',
            'data'  => $result
        ]);
    }
}
