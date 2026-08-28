<?php

namespace App\Controllers;

use CodeIgniter\Controller;

use \IonAuth\Libraries\IonAuth;
use App\Models\M_combinkanban;
use App\Models\M_curl;

class CombinKanban extends BaseController
{
    protected $ionAuth;
    protected $data = [];

    public function __construct()
    {
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function tmmin_combin_kanban()
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

        $id_kanban = $this->request->getPost('id_kanban');
        $id_label = $this->request->getPost('id_label');

        $model = new M_combinkanban();

        $manifestNumber = $model->extractManifestNumber($id_label);

        if (!$manifestNumber) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid kanban code format.'
            ]);
        }

        $status = $model->getInternalScanStatus($manifestNumber);

        if ($model->isAlreadyVerified($id_kanban, $id_label)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'No action taken: data already verified.',
                'manifest_number' => $manifestNumber,
                'total' => $status['total'],
                'scanned' => $status['scanned'],
                'id_kanban' => $id_kanban,
                'id_label'  => $id_label,
                'user' => $user
            ]);
        }

        $tag_ok_source = null;
        $tag_ok = $model->get_tag_ok($id_kanban);

        if ($tag_ok) {
            $tag_ok_source = 'get_tag_ok';
        } else {
            $tag_ok = $model->get_tag_ok_wld($id_kanban);
            if ($tag_ok) {
                $tag_ok_source = 'get_tag_ok_wld';
            } else {
                $tag_ok = $model->get_tag_e_kanban($id_kanban);

                if ($tag_ok) {
                    $tag_ok_source = 'get_tag_e_kanban';
                }
            }
        }

        if (!$tag_ok) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'The specified Kanban ID does not exist.'
            ]);
        }

        $M_curl = new M_curl();
        $rawKanban = substr($tag_ok->part_number, 0, 14);

        $normalizedKanban = str_replace(['-', ' '], '', $rawKanban);
        $normalizedLabel = str_replace(' ', '', $id_label);

        if (strpos($normalizedLabel, $normalizedKanban) !== false) {

            if ($tag_ok_source === 'get_tag_e_kanban') {
                $this->SAP_PARAMS['function'] = 'Z_RFC_GET_KANBAN';
                $this->SAP_PARAMS['params'] = [
                    'RPT'       => 'PRESS',
                    'STATUS'    => '6',
                    'PKKEY'     => $tag_ok->id_kanban,
                    'LASTDATA'  => 'X'
                ];
                $res_inuse_raw = $M_curl->execute("POST", $this->SAP_PARAMS);

                if (isset($res_inuse_raw['success']) && $res_inuse_raw['success'] === true) {
                    $this->SAP_PARAM['function'] = 'Z_RFC_WAIT_V2';
                    $this->SAP_PARAM['params'] = [
                        'RPT'       => 'WAIT',
                        'STATUS'    => '1',
                        'PKKEY'     => $tag_ok->id_kanban
                    ];
                    $M_curl->execute("POST", $this->SAP_PARAM);
                    $model->update_status_kanban($tag_ok->id_kanban);
                }
            }

            $data = [
                'id_kanban'     => $id_kanban,
                'id_label'      => $id_label,
                'part'          => $tag_ok->part_number,
                'customer'      => 'TMMIN',
                'user_created'  => $user->nik
            ];

            $model->insertToCombinResult($data);

            $status = $model->getInternalScanStatus($manifestNumber);

            $response = [
                'status' => true,
                'message' => 'Data matched successfully.',
                'manifest_number' => $manifestNumber,
                'total' => $status['total'],
                'scanned' => $status['scanned'],
                'kanban_normalized' => $normalizedKanban,
                'label_normalized' => $normalizedLabel
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Data verification failed: unmatched data.',
                'manifest_number' => $manifestNumber,
                'total' => $status['total'],
                'scanned' => $status['scanned'],
                'kanban_normalized' => $normalizedKanban,
                'label_normalized' => $normalizedLabel
            ];
        }

        return $this->response->setJSON($response);
    }

    public function hmmi_combin_kanban()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan.'])->setStatusCode(401);
        }

        try {
            $jwt = new \App\Libraries\JwtLib();
            $jwt->validate($matches[1]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid.'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $id_kanban = $this->request->getPost('id_kanban');
        $id_label  = $this->request->getPost('id_label');

        $model = new M_combinkanban();

        if ($model->isAlreadyVerified($id_kanban, $id_label)) {
            return $this->response->setJSON([
                'status'     => false,
                'message'    => 'No action taken: data already verified.',
                'id_kanban'  => $id_kanban,
                'id_label'   => $id_label
            ]);
        }

        $tag_ok = $model->get_tag_ok($id_kanban);

        if (!$tag_ok) {
            $tag_ok = $model->get_tag_ok_wld($id_kanban);
        }

        if (!$tag_ok) {
            $tag_ok = $model->get_tag_e_kanban($id_kanban);
        }

        if (!$tag_ok) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data kanban tidak ditemukan.'
            ]);
        }

        $normalizedKanban = substr(str_replace(['-', ' '], '', $tag_ok->part_number), 0, 14);
        $normalizedLabel  = str_replace(' ', '', $id_label);

        if (strpos($id_label, 'HAXQ') !== false) {

            if (strpos($normalizedLabel, $normalizedKanban) !== false) {

                $data = [
                    'id_kanban'    => $id_kanban,
                    'id_label'     => $id_label,
                    'part'         => $tag_ok->part_number,
                    'customer'     => 'HMMI',
                    'user_created' => $user->nik
                ];

                $model->insertToCombinResult($data);

                return $this->response->setJSON([
                    'status'             => true,
                    'message'            => 'Data matched successfully.',
                    'kanban_normalized'  => $normalizedKanban,
                    'label_normalized'   => $normalizedLabel
                ]);
            }

            return $this->response->setJSON([
                'status'             => false,
                'message'            => 'Data verification failed: unmatched data.',
                'kanban_normalized'  => $normalizedKanban,
                'label_normalized'   => $normalizedLabel
            ]);
        }

        if (strpos($id_label, 'MKM') !== false) {

            $data = [
                'id_kanban'    => $id_kanban,
                'id_label'     => $id_label,
                'part'         => $tag_ok->part_number,
                'customer'     => 'MKM',
                'user_created' => $user->nik
            ];

            $model->insertToCombinResult($data);

            return $this->response->setJSON([
                'status'             => true,
                'message'            => 'Data matched successfully.',
                'kanban_normalized'  => $normalizedKanban,
                'label_normalized'   => $normalizedLabel
            ]);
        }

        return $this->response->setJSON([
            'status'  => false,
            'message' => 'Label tidak mengandung kode customer yang valid.'
        ]);
    }

    public function adm_combin_kanban()
    {
        // var_dump($id_kanban);
        $authHeader = $this->request->getHeaderLine('Authorization');
        
        
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan.'])->setStatusCode(401);
        }
        
        $token = $matches[1];

        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($token);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid. Mohon untuk login kembali!'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $id_kanban = $this->request->getPost('id_kanban');
        $id_label = $this->request->getPost('id_label');


        $model = new M_combinkanban();

        $parentDn = $model->extractParentDn($id_label);

        if ($parentDn === null) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Format barcode tidak valid.',
                'dn' => null,
                'total' => 0,
                'scanned' => 0,
            ]);
        }

        $result = $model->getParentExistAndChildStatus($parentDn);

        if ($model->isAlreadyVerified($id_kanban, $id_label)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'No action taken: data already verified.',
                'dn'      => $parentDn,
                'total'   => $result['total'],
                'scanned' => $result['scanned'],
                'id_kanban' => $id_kanban,
                'id_label'  => $id_label,
                'user' => $user
            ]);
        }

        
        $dn = $model->get_part_dn($id_label);
        
        $tag_ok_source = null;

        $tag_ok = $model->get_tag_ok($id_kanban);
        
        if ($tag_ok) {
            $tag_ok_source = 'get_tag_ok';

        } else {
            $tag_ok = $model->get_tag_ok_wld($id_kanban);
            if ($tag_ok) {
                $tag_ok_source = 'get_tag_ok_wld';

            } else {
                $tag_ok = $model->get_tag_e_kanban($id_kanban);

                if ($tag_ok) {
                    $tag_ok_source = 'get_tag_e_kanban';

                }
            }
        }
        

        if (!$tag_ok) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'The specified Kanban ID does not exist.',
                'dn'      => $parentDn,
                'total'   => $result['total'],
                'scanned' => $result['scanned'],
            ]);
        }

        $M_curl = new M_curl();
        // return $this->response->setJSON(['status' => false, 'message' => 'Test.', 'DN' => $dn])->setStatusCode(406);
        $normalizedKanban = str_replace(['-', ' '], '', $tag_ok->part_number);
        $normalizedLabel = str_replace(['-', ' '], '', $dn->part_number);

        if (strpos($normalizedLabel, $normalizedKanban) !== false) {

            if ($tag_ok_source === 'get_tag_e_kanban') {
                $this->SAP_PARAMS['function'] = 'Z_RFC_GET_KANBAN';
                $this->SAP_PARAMS['params'] = [
                    'RPT'       => 'PRESS',
                    'STATUS'    => '6',
                    'PKKEY'     => $tag_ok->id_kanban,
                    'LASTDATA'  => 'X'
                ];
                $res_inuse_raw = $M_curl->execute("POST", $this->SAP_PARAMS);
                // $res_inuse = json_decode($res_inuse_raw, true);

                if (isset($res_inuse_raw['success']) && $res_inuse_raw['success'] === true) {
                    $this->SAP_PARAM['function'] = 'Z_RFC_WAIT_V2';
                    $this->SAP_PARAM['params'] = [
                        'RPT'       => 'WAIT',
                        'STATUS'    => '1',
                        'PKKEY'     => $tag_ok->id_kanban
                    ];
                    $M_curl->execute("POST", $this->SAP_PARAM);
                    $model->update_status_kanban($tag_ok->id_kanban);
                }
            }
            
            $data = [
                'id_kanban'     => $id_kanban,
                'id_label'      => $id_label,
                'part'          => $tag_ok->part_number,
                'customer'      => 'ADM',
                'user_created'  => $user->nik
            ];

            $model->insertToCombinResult($data);    
            $result = $model->getParentExistAndChildStatus($parentDn);
            $response = [
                'status'             => true,
                'message'            => 'Data matched successfully.',
                'dn'                 => $parentDn,
                'total'              => $result['total'],
                'scanned'            => $result['scanned'],
                'kanban_normalized'  => $normalizedKanban,
                'label_normalized'   => $normalizedLabel,
                'tag_ok_source'      => $tag_ok_source
            ];
        } else {
            $response = [
                'status'            => false,
                'message'           => 'Data verification failed: unmatched data.',
                'dn'                => $parentDn,
                'total'             => $result['total'],
                'scanned'           => $result['scanned'],
                'kanban_normalized' => $normalizedKanban,
                'label_normalized'  => $normalizedLabel,
                'tag_ok_source'     => $tag_ok_source,
            ];
        }

        return $this->response->setJSON($response);
    }

    public function history_kanban()
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
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid. Mohon untuk login kembali!'])->setStatusCode(401);
        }

        $user = $this->request->user;
        $customer = $this->request->getPost('customer');
        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');
        $quick = $this->request->getPost('quick') === 'true';

        $start_date = $start_date ? substr($start_date, 0, 10) : null;
        $end_date = $end_date ? substr($end_date, 0, 10) : null;

        $model = new M_combinkanban();
        $result = $model->historyKanbanUser($start_date, $end_date, $customer, $user->nik, $quick);
        return $this->response->setJSON([
            'status' => true,
            'data'   => $result
        ]);
    }

    public function dn_require()
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
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid. Mohon untuk login kembali!'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');
        $customer = $this->request->getPost('customer');
        
        // $data['start_date'] = $start_date ? substr($start_date, 0, 10) : null;
        // $data['end_date'] = $end_date ? substr($end_date, 0, 10) : null;
        // $data['customer'] = $customer;
        $model = new M_combinkanban();
        $result = $model->dn_kanban($start_date, $end_date, $customer);
        return $this->response->setJSON([
            'status' => true,
            'data'   => $result
        ]);
    }

    public function dummy_api()
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
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid. Mohon untuk login kembali!'])->setStatusCode(401);
        }

        $user = $this->request->user;
        $period = $this->request->getPost('period');
        $noPo = $this->request->getPost('noPo');
        $matnr = $this->request->getPost('matnr');
        $date_from = $this->request->getPost('date_from');
        $date_to = $this->request->getPost('date_to');
        $qty = $this->request->getPost('qty');

        $M_curl = new M_curl();

        $this->SAP_PARAMS['function'] = 'Z_QC';
        $this->SAP_PARAMS['params'] = [
            'RPT'           => 'UPDT_SCH',
            'P_EBELN'       => $noPo,
            'P_MATNR'       => $matnr,
            'P_DATE_FROM'   => $date_from,
            'P_DATE_TO'     => $date_to,
            'P_QTY'         => $qty,
            'P_TYPE'        => 'A'
        ];
        
        $M_curl->execute("POST", $this->SAP_PARAMS);

        return $this->response->setJSON([
            'status' => true,
            'data' => $schedulePO,
        ]);
    }
}
