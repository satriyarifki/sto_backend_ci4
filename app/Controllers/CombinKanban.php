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
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $model = new IonAuth();
        $this->ionAuth = $model;
    }

    public function tmmin_combin_kanban()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan. Silahkan Login Ulang.'])->setStatusCode(401);
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

        if (empty($id_kanban)) {
            return $this->polybox_combin_kanban();
        }
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
            $model->updateToScanTagIfpd($data);
            $status = $model->getInternalScanStatus($manifestNumber);
          // Ambil data kanban (qty_kbn)
        $kanban = $model->get_qty_kanban($tag_ok->part_number);

        if ($kanban) {

            // Jalankan fungsi update dengan parameter yang dibutuhkan
            $model->updateToCycleItem(
                $tag_ok->part_number, 
                $manifestNumber, 
                $kanban->qty_kbn, // Sesuaikan dengan nama kolom di tabel ms_data_ifp

            );
        }
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

    public function tmmin_combin_kanban2()
    {

        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan. Silahkan Login Ulang.'])->setStatusCode(401);
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
        $tag_ok = $model->get_tag_ok_ifpd($id_kanban);

        if (!$tag_ok) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Kanban Tidak ditemukan/Belum Scanned In.'
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
            $status = $model->getInternalScanStatus($manifestNumber);

            if (preg_match('/^KBN(\d{10})\s+(\S{10})/', $id_label, $matches)) {
                $manifest = $matches[1];
                $part_no_normalized = $matches[2];
                $part_no = substr($part_no_normalized, 0, 5) . '-' . substr($part_no_normalized, 5);
            } else {
                $manifest = substr($id_label, 3, 10);
                $part_no = substr($id_label, 12, 10);
                $part_no = substr($part_no, 0, 5) . '-' . substr($part_no, 5);
            }

            $detail = $model->findDetailBarcodeTmmin($manifest, $tag_ok->qty, $part_no);

            // if ($detail === 'NOT_FOUND') {
            //     return $this->response->setJSON([
            //         'status'  => false,
            //         'message' => 'Data Tag Customer tidak ditemukan (Cek Barcode/Part No).',
            //         'dn'      => $id_label,
            //         'scanned' => $tag_ok->qty,
            //     ]);
            // }

            // if ($detail === 'FULL') {
            //     return $this->response->setJSON([
            //         'status'  => false,
            //         'message' => 'Part ini sudah selesai di-scan (Qty Penuh/Over).',
            //         'dn'      => $id_label,
            //         'scanned' => $tag_ok->qty,
            //     ]);
            // }



            if (strpos($normalizedKanban, $part_no_normalized) === false) {

                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Not Match',
                    'dn'      => $id_label,
                    'scanned' =>  $tag_ok->qty,
                    'total' => $detail['total_qty']
                ]);
            } else {


                $data = [
                    'id_kanban'     => $id_kanban,
                    'id_label'      => $id_label,
                    'part'          => $tag_ok->part_number,
                    'customer'      => 'TMMIN',
                    'user_created'  => $user->nik
                ];

                $result = $model->insertToCombinResult($data);
                $model->updateToScanTagIfpd($data);
                $response = [
                    'status' => true,
                    'message' => 'Data matched successfully.',
                    'manifest_number' => $manifestNumber,
                    'total' => $status['total'],
                    'scanned' => $status['scanned'],
                    'kanban_normalized' => $normalizedKanban,
                    'label_normalized' => $normalizedLabel
                ];
            }
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
        $user = $this->_validateBearerToken();
        if ($user instanceof \CodeIgniter\HTTP\Response) return $user;

        $id_kanban        = $this->request->getPost('id_kanban');
        $id_label         = $this->request->getPost('id_label');
        $qty_kanban_input = $this->request->getPost('qty_kanban');

        if (empty($id_kanban)) {
            return $this->polybox_combin_kanban();
        }

        $model = new M_combinkanban();

        $customer = match(true) {
            str_contains($id_label, 'HAXQ') => 'HMMI',
            str_contains($id_label, 'MKM')  => 'MKM',
            default                          => 'MMKI',
        };

        if ($model->isAlreadyVerified($id_kanban, $id_label)) {
            return $this->response->setJSON([
                'status'    => false,
                'message'   => 'Data sudah diverifikasi.',
                'id_kanban' => $id_kanban,
                'id_label'  => $id_label,
                'dn'        => $id_label,
                'scanned'   => 0,
                'total'     => 0,
                'user'      => $user,
            ]);
        }

        if ($customer === 'MMKI' && empty($qty_kanban_input)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Qty harus diinput untuk MMKI.',
                'dn'      => $id_label,
                'scanned' => 0,
                'total'   => 0,
            ]);
        }

        $qty_aktual = (int) ($qty_kanban_input ?? 0);

        [$tag_ok, $tag_ok_source] = $this->_resolveTagOk($id_kanban, $model);

        // normalizedKanban hanya dihitung sekali di sini
        $normalizedKanban = $tag_ok
            ? substr(str_replace(['-', ' '], '', $tag_ok->part_number), 0, 14)
            : null;

        return match($customer) {
            'MMKI'  => $this->_handleMmki($model, $user, $id_kanban, $id_label, $qty_aktual, $normalizedKanban, $tag_ok, $tag_ok_source),
            'HMMI'  => $this->_handleHmmi($model, $user, $id_kanban, $id_label, $qty_aktual, $normalizedKanban, $tag_ok, $tag_ok_source),
            'MKM'   => $this->_handleMkm($model,  $user, $id_kanban, $id_label, $qty_aktual, $normalizedKanban, $tag_ok, $tag_ok_source),
            default => $this->_error('Customer tidak dikenali.', 400),
        };
    }

    private function _handleMmki($model, $user, $id_kanban, $id_label, $qty_aktual, $normalizedKanban, $tag_ok, $tag_ok_source)
    {
        $normalized_id_label = substr($id_label, 0, 15);
        $detail = $model->findDetailBarcodeMmki($normalized_id_label, $qty_aktual, 'MMKI');

        if (!$detail) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Barcode Cust data not found.',
                'dn'      => $id_label,
                'scanned' => 0,
                'total'   => 0,
            ]);
        }

        $matnr = $detail['part_no'];

        if ($normalizedKanban !== null && $normalizedKanban !== $matnr) {
            return $this->response->setJSON([
                'status'            => false,
                'message'           => 'Not Match',
                'dn'                => $id_label,
                'scanned'           => 0,
                'total'             => 0,
                'kanban_normalized' => $normalizedKanban,
                'matnr'             => $matnr,
                'tag_ok_source'     => $tag_ok_source,
            ]);
        }

        $data = [
            'id_kanban'    => $id_kanban,
            'id_label'     => $id_label,
            'part'         => $tag_ok->part_number ?? $matnr,
            'qty'          => $qty_aktual,
            'customer'     => 'MMKI',
            'user_created' => $user->nik,
        ];

        // Transaction: insert + 2 update harus atomic
        $model->dbDnAdm->transStart();
        $model->insertToCombinResult($data);
        $model->updateToScanTagIfpd($data);
        $model->updateScanStatus(substr($id_label, 0, 10), $matnr, $qty_aktual);
        $model->dbDnAdm->transComplete();

        return $this->response->setJSON([
            'status'            => true,
            'message'           => 'Data matched.',
            'customer'          => 'MMKI',
            'kanban_normalized' => $normalizedKanban,
            'tag_ok_source'     => $tag_ok_source,
            'sap'               => $detail,
            'dn'                => $id_label,
            'scanned'           => $qty_aktual,
            'total'             => $detail['qty_total'] ?? 0,
        ]);
    }

    private function _handleHmmi($model, $user, $id_kanban, $id_label, $qty_aktual, $normalizedKanban, $tag_ok, $tag_ok_source)
    {
        $detail = $model->findDetailBarcodeHmmi($id_label, $qty_aktual, 'HMMI');

        if (!$detail) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data Tag Cust tidak ditemukan.',
                'dn'      => $id_label,
                'scanned' => $qty_aktual,
                'total'   => 0,
            ]);
        }

        $matnr = str_replace('-', '', $detail['part_no']);

        if ($normalizedKanban !== null && $normalizedKanban !== $matnr) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Not Match',
                'dn'      => $id_label,
                'scanned' => $qty_aktual,
                'total'   => $detail['qty_total'],
            ]);
        }

        $data = [
            'id_kanban'    => $id_kanban,
            'id_label'     => $id_label,
            'part'         => $tag_ok->part_number ?? $matnr,
            'qty'          => $qty_aktual,
            'customer'     => 'HMMI',
            'user_created' => $user->nik,
        ];

        $model->dbDnAdm->transStart();
        $model->insertToCombinResult($data);
        $model->updateToCycleItemHmmi($tag_ok->part_number ?? $matnr, $qty_aktual);
        $model->dbDnAdm->transComplete();

        return $this->response->setJSON([
            'status'            => true,
            'message'           => 'Data matched.',
            'customer'          => 'HMMI',
            'kanban_normalized' => $normalizedKanban,
            'tag_ok_source'     => $tag_ok_source,
            'sap'               => $detail,
            'dn'                => $id_label,
            'scanned'           => $qty_aktual,
            'total'             => $detail['qty_total'] ?? 0,
        ]);
    }

    private function _handleMkm($model, $user, $id_kanban, $id_label, $qty_aktual, $normalizedKanban, $tag_ok, $tag_ok_source)
    {
        $barcodeMkm       = substr($id_label, 0, 14);
        $normalizedPartNo = $tag_ok
            ? substr(str_replace(['-', ' '], '', $tag_ok->part_number), 0, 14)
            : null;

        $detail = $model->findDetailBarcodeMkm($barcodeMkm, $qty_aktual, $tag_ok->part_number ?? null);

        if ($normalizedKanban !== null && $normalizedPartNo !== null && $normalizedKanban !== $normalizedPartNo) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Not Match',
                'dn'      => $id_label,
                'scanned' => $qty_aktual,
                'total'   => $detail['total_qty'] ?? 0,
            ]);
        }

        $data = [
            'id_kanban'    => $id_kanban,
            'id_label'     => $id_label,
            'part'         => $tag_ok->part_number ?? '',
            'qty'          => $qty_aktual,
            'customer'     => 'MKM',
            'user_created' => $user->nik,
        ];

        $model->dbDnAdm->transStart();
        $model->insertToCombinResult($data);
        $model->dbDnAdm->transComplete();

        return $this->response->setJSON([
            'status'            => true,
            'message'           => 'Data matched.',
            'customer'          => 'MKM',
            'kanban_normalized' => $normalizedKanban,
            'tag_ok_source'     => $tag_ok_source,
            'sap'               => $detail ?? null,
            'dn'                => $id_label,
            'scanned'           => $qty_aktual,
            'total'             => $detail['qty_total'] ?? 0,
        ]);
    }
    public function adm_combin_kanban()
    {
        $user = $this->_validateBearerToken();
        if ($user instanceof \CodeIgniter\HTTP\Response) return $user;

        $id_kanban = $this->request->getPost('id_kanban');
        $id_label  = $this->request->getPost('id_label');

        if (empty($id_kanban)) {
            return $this->polybox_combin_kanban();
        }

        $model    = new M_combinkanban();
        $parentDn = $model->extractParentDn($id_label);

        if ($parentDn === null) {
            return $this->_error('Format barcode tidak valid.', 200, [
                'dn' => null, 'total' => 0, 'scanned' => 0,
            ]);
        }

        $combined = $model->getPartDnWithStatus($id_label, $parentDn);

        if ($combined === null || $combined['part_number'] === null) {
            return $this->_error('Data detail Label Customer tidak ditemukan di database, Silahkan Hubungi Delcon', 404, [
                'dn' => null, 'total' => 0, 'scanned' => 0,
            ]);
        }

        $total   = (int) $combined['total'];
        $scanned = (int) $combined['scanned'];

        if ($total === 0) {
            return $this->_error('Data Label Customer tidak ditemukan di cycle delivery', 404, [
                'dn' => $parentDn, 'total' => 0, 'scanned' => 0,
            ]);
        }

        if ($model->isAlreadyVerified($id_kanban, $id_label)) {
            return $this->response->setJSON([
                'status'    => false,
                'message'   => 'No action taken: data already verified.',
                'dn'        => $parentDn,
                'total'     => $total,
                'scanned'   => $scanned,
                'id_kanban' => $id_kanban,
                'id_label'  => $id_label,
                'user'      => $user,
            ]);
        }

        [$tag_ok, $tag_ok_source] = $this->_resolveTagOk($id_kanban, $model);

        if (!$tag_ok) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'The specified Kanban ID does not exist.',
                'dn'      => $parentDn,
                'total'   => $total,
                'scanned' => $scanned,
            ]);
        }

        $normalizedKanban = substr(str_replace(['-', ' '], '', $tag_ok->part_number), 0, 10);
        $normalizedLabel  = str_replace(['-', ' '], '', $combined['part_number']);

        if (!str_contains($normalizedLabel, $normalizedKanban)) {
            return $this->response->setJSON([
                'status'            => false,
                'message'           => 'Data verification failed: unmatched data.',
                'dn'                => $parentDn,
                'total'             => $total,
                'scanned'           => $scanned,
                'kanban_normalized' => $normalizedKanban,
                'label_normalized'  => $normalizedLabel,
                'tag_ok_source'     => $tag_ok_source,
            ]);
        }

        if ($tag_ok_source === 'get_tag_e_kanban') {
            $this->_triggerSapEkanban($tag_ok, $model);
        }

        $insertData = [
            'id_kanban'    => $id_kanban,
            'id_label'     => $id_label,
            'part'         => $tag_ok->part_number,
            'customer'     => 'ADM',
            'user_created' => $user->nik,
        ];

        $model->insertToCombinResult($insertData);
        $model->updateToScanTagIfpd($insertData);

        return $this->response->setJSON([
            'status'            => true,
            'message'           => 'Data matched successfully.',
            'dn'                => $parentDn,
            'total'             => $total,
            'scanned'           => $scanned + 1, // ← tidak ada DB round-trip
            'kanban_normalized' => $normalizedKanban,
            'label_normalized'  => $normalizedLabel,
            'tag_ok_source'     => $tag_ok_source,
        ]);
    }

    private function _resolveTagOk(string $id_kanban, M_combinkanban $model): array
    {
        $sources = [
            'get_tag_ok'        => fn() => $model->get_tag_ok($id_kanban),
            'get_tag_ok_wld'    => fn() => $model->get_tag_ok_wld($id_kanban),
            'get_tag_e_kanban'  => fn() => $model->get_tag_e_kanban($id_kanban),
            'get_tag_log_print' => fn() => $model->get_tag_log_print($id_kanban),
        ];

        foreach ($sources as $source => $fn) {
            $tag_ok = $fn();
            if ($tag_ok) {
                return [$tag_ok, $source];
            }
        }

        return [null, null];
    }

    private function _triggerSapEkanban(object $tag_ok, M_combinkanban $model): void
    {
        $M_curl = new M_curl();

        $this->SAP_PARAMS['function'] = 'Z_RFC_GET_KANBAN';
        $this->SAP_PARAMS['params']   = [
            'RPT'      => 'PRESS',
            'STATUS'   => '6',
            'PKKEY'    => $tag_ok->id_kanban,
            'LASTDATA' => 'X',
        ];

        $res = $M_curl->execute("POST", $this->SAP_PARAMS);

        if (isset($res['success']) && $res['success'] === true) {
            $this->SAP_PARAM['function'] = 'Z_RFC_WAIT_V2';
            $this->SAP_PARAM['params']   = [
                'RPT'    => 'WAIT',
                'STATUS' => '1',
                'PKKEY'  => $tag_ok->id_kanban,
            ];
            $M_curl->execute("POST", $this->SAP_PARAM);
            $model->update_status_kanban($tag_ok->id_kanban);
        }
    }
    public function polybox_combin_kanban()
    {
        $user = $this->_validateBearerToken();
        if ($user instanceof \CodeIgniter\HTTP\Response) return $user;

        $id_label = $this->request->getPost('id_label');
        $model    = new M_combinkanban();

        try {
            [$customer, $part_no, $parentDn, $normalized_id_label, $cachedResult] =
                $this->_resolveCustomerData($id_label, $model);
        } catch (\RuntimeException $e) {
            return $this->_error($e->getMessage(), $e->getCode() ?: 400);
        }
        if ($customer === 'ADM') {
            if ($cachedResult === null) {
                return $this->_error('Data Label Customer tidak ditemukan di cycle delivery', 404, [
                    'dn' => $parentDn, 'total' => 0, 'scanned' => 0,
                ]);
            }
           
        }

        if ($model->validation_polybox($part_no) === null) {
            return $this->_error('Wajib pakai Tag OK MAJ untuk item ini', 400, [
                'dn' => null, 'total' => 0, 'scanned' => 0,
            ]);
        }
      
        if ($model->isAlreadyVerifiedPolybox($id_label)) {
                return $this->response->setJSON([
                    'status'    => false,
                    'message'   => 'No action taken: data already verified.',
                   
                   
                    'id_kanban' => '',
                    'id_label'  => $id_label,
                    'user'      => $user,
                ]);
        }  
        $model->insertToCombinResult([
            'id_kanban'    => '',
            'id_label'     => $id_label,
            'part'         => $part_no,
            'customer'     => $customer,
            'user_created' => $user->nik,
        ]);

        $result = $this->_buildResult($customer, $model, $parentDn, $id_label, $part_no, $cachedResult);

        return $this->response->setJSON([
            'status'            => true,
            'message'           => 'Data matched successfully.',
            'customer'          => $customer,
            'total'             => $result['total']   ?? 0,
            'scanned'           => $result['scanned'] ?? 0,
            'kanban_normalized' => '',
            'label_normalized'  => $part_no,
        ]);
    }

    private function _validateBearerToken()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON([
                'status' => false, 'message' => 'Token tidak ditemukan. Silahkan Login Ulang.',
            ])->setStatusCode(401);
        }
        try {
            return (new \App\Libraries\JwtLib())->validate($matches[1]);
        } catch (\Exception) {
            return $this->response->setJSON([
                'status' => false, 'message' => 'Token tidak valid. Mohon untuk login kembali!',
            ])->setStatusCode(401);
        }
    }

    private function _resolveCustomerData(string $id_label, M_combinkanban $model): array
    {
        // ADM
        if (str_starts_with($id_label, 'DN')) {
            $parentDn = $model->extractParentDn($id_label);

            $combined = $model->getPartDnWithStatus($id_label, $parentDn);

            if ($combined === null || $combined['part_number'] === null) {
                throw new \RuntimeException('Data detail Label Customer tidak ditemukan di database', 404);
            }

            $cachedResult = [
                'total'   => (int) $combined['total'],
                'scanned' => (int) $combined['scanned'],
            ];

            if ($cachedResult['total'] === 0) {
                $cachedResult = null;
            }

            return ['ADM', $combined['part_number'], $parentDn, null, $cachedResult];
        }

        if (str_contains($id_label, 'HAXQ')) {
            return ['HMMI', '', null, null, null];
        }

        if (str_contains($id_label, 'MKM')) {
            $dn = $model->get_part_dn_upload_barcode($id_label);
            if ($dn === null) {
                throw new \RuntimeException('Data MKM tidak ditemukan', 404);
            }
            return ['MKM', $dn->part_no, null, null, null];
        }

        if (str_contains($id_label, 'KBN')) {
            return ['TMMIN', '', null, null, null];
        }

        $normalized = substr($id_label, 0, 15);
        $dn         = $model->get_part_dn_upload_barcode($normalized);
        if ($dn === null) {
            throw new \RuntimeException('Data MMKI tidak ditemukan', 404);
        }
        return ['MMKI', $dn->part_no, null, $normalized, null];
    }

    private function _buildResult(
        string $customer,
        M_combinkanban $model,
        ?string $parentDn,
        string $id_label,
        string $part_no,
        ?array $cachedResult
    ): array {
        return match ($customer) {
            'ADM'  => $cachedResult ?? ['total' => 0, 'scanned' => 0],

            'MMKI' => (function () use ($id_label, $part_no, $model) {
                $qty_aktual = (int) $this->request->getPost('qty_kanban');
                $po_no      = substr($id_label, 0, 10);
                $kwmeng     = $model->updateScanStatus($po_no, $part_no, $qty_aktual);
                return ['total' => $kwmeng ?? 0, 'scanned' => $qty_aktual];
            })(),

            default => ['total' => 0, 'scanned' => 0],
        };
    }

    private function _error(string $message, int $code = 400, array $extra = [])
    {
        return $this->response->setJSON(
            array_merge(['status' => false, 'message' => $message], $extra)
        )->setStatusCode($code);
    }

    public function history_kanban()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan. Silahkan Login Ulang.'])->setStatusCode(401);
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
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan. Silahkan Login Ulang'])->setStatusCode(401);
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
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan. Silahkan Login Ulang.'])->setStatusCode(401);
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

    public function adm_list_shipping()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak ditemukan. Silahkan Login Ulang.'])->setStatusCode(401);
        }

        $token = $matches[1];

        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($token);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid. Mohon untuk login kembali!'])->setStatusCode(401);
        }

        $user = $this->request->user;

        $cycle = $this->request->getPost('cycle');
        $date = $this->request->getPost('date');
        $plant = $this->request->getPost('customer');

        // $data['start_date'] = $start_date ? substr($start_date, 0, 10) : null;
        // $data['end_date'] = $end_date ? substr($end_date, 0, 10) : null;
        // $data['customer'] = $customer;
        $model = new M_combinkanban();
   
        $result = $model->get_item($cycle, $date, $plant);
        return $this->response->setJSON([
            'status' => true,
            'data'   => $result
        ]);
    }

    public function mieruka_shipping_customer()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
    
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response
                ->setJSON(['status' => false, 'message' => 'Token tidak ditemukan. Silahkan Login Ulang'])
                ->setStatusCode(401);
        }
    
        try {
            $jwt = new \App\Libraries\JwtLib();
            $user = $jwt->validate($matches[1]);
        } catch (\Exception $e) {
            return $this->response
                ->setJSON(['status' => false, 'message' => 'Token tidak valid'])
                ->setStatusCode(401);
        }
    
        $plant = strtoupper($this->request->getPost('customer'));
        $dateParam = $this->request->getPost('date');
        $cycle = $this->request->getPost('cycle');
    
        if (empty($dateParam) || $dateParam === 'null') {
            $date = date('Y-m-d');
        } else {
            $date = date('Y-m-d', strtotime($dateParam));
        }
    
        if ($plant === 'HMMI' || $plant === 'MKM') {
            $cycle = null;
        }
    
        if ($cycle === '' || $cycle === 'null') {
            $cycle = null;
        }
    
        if ($cycle === '11' || $cycle === '12') {
            $date = date('Y-m-d', strtotime('+1 day'));
        }
    
        $allowedPlants = ['HMMI', 'TMMIN', 'MKM', 'MMKI'];
    
        if (!in_array($plant, $allowedPlants)) {
            return $this->response->setJSON([
                'status' => false,
                'data'   => []
            ]);
        }   
    
        $model = new M_combinkanban();
        $result = $model->get_mieruka_barcode_by_plant($cycle, $date, $plant);
    
        return $this->response->setJSON([
            'status' => true,
            'data'   => $result
        ]);
    }
    public function hmmi_combin_preview()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Token tidak ditemukan. Silahkan Login Ulang'
            ])->setStatusCode(401);
        }
    
        try {
            $jwt = new \App\Libraries\JwtLib();
            $jwt->validate($matches[1]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Token tidak valid'
            ])->setStatusCode(401);
        }

        $id_label  = $this->request->getGet('id_label');
        $id_kanban = $this->request->getGet('id_kanban');
   
        if (empty($id_label)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Parameter tidak lengkap'
            ]);
        }

        $model = new M_combinkanban();
       
        if (strpos($id_label, 'HAXQ') !== false) {
            $customer = 'HMMI';
        } elseif (strpos($id_label, 'MKM') !== false) {
            $customer = 'MKM';
        } elseif (strpos($id_label, 'KBN') !== false) {
            $customer = 'TMMIN';
        } else {
            $customer = 'MMKI';
        }
        
        $labelForQuery = $id_label;

        if ($customer === 'MMKI') {
            preg_match_all('/\d/', $id_label, $digits);
            $numbersOnly = implode('', $digits[0]);
            $labelForQuery = substr($numbersOnly, 0, 15);
        }
      
        if ($customer === 'MKM') {
            $labelForQuery = substr($id_label, 0, 14);
        }   

        $barcodeDn = $model->getBarcodeDnByLabel($labelForQuery);
        if (!$barcodeDn) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Barcode DN/Label Customer tidak ditemukan, Silahkan Hubungi Delcon'
            ]);
        }
      
        $part_no    = $barcodeDn['part_no'];
        $qty_kanban = $barcodeDn['qty']; // Default value dari barcode DN

        if (empty($id_kanban)) {
            $isMaj = $model->validation_polybox($part_no);
            if ($isMaj == null) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Wajib pakai Tag OK MAJ untuk item ini',
                ]); 
            }
        }

        // KONDISI 1: JIKA CUSTOMER MMKI
        if ($customer == 'MMKI') {
            preg_match_all('/\d/', $id_label, $digits);
            $numbersOnly = implode('', $digits[0]);
            $len = strlen($numbersOnly);

            if ($len >= 3) {
                $thirdFromEnd = $numbersOnly[$len - 3];
                if ($thirdFromEnd === '0') {
                    $qty_kanban = (int) substr($numbersOnly, -2);
                } else {
                    $qty_kanban = (int) substr($numbersOnly, -3);
                }
            } else {
                $qty_kanban = (int) $numbersOnly;
            }
        }

        // KONDISI 2: JIKA CUSTOMER MKM (Menggunakan Resolve Tag OK)
        if ($customer === 'MKM') {
            if (empty($id_kanban)) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'ID Kanban wajib diisi untuk customer MKM',
                ]);
            }

            [$tag_ok, $tag_ok_source] = $this->_resolveTagOk($id_kanban, $model);

            if (!$tag_ok) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Tag OK belum scan in / tidak ditemukan di semua source',
                ]);
            }

            // // Ambil qty dari hasil resolve tag_ok (mendukung tipe Objek maupun Array)
            // $qty_kanban = is_array($tag_ok) ? $tag_ok['qty'] : $tag_ok->qty;
             $part_number = is_array($tag_ok) ? $tag_ok['part_number'] : $tag_ok->part_number;
        
            $kanban = $model->get_qty_kanban($part_number);
            if (!$kanban) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Qty Kanban tidak ditemukan di master data',
                ]);
            }
            $qty_kanban = $kanban->qty_kbn;
        }
         
        return $this->response->setJSON([
            'status'     => true,
            'qty_label'  => $barcodeDn['qty'],
            'qty_kanban' => $qty_kanban,
            'part_label' => $barcodeDn['part_no'],
            'customer'   => $customer
        ]);
    }
    public function insertAndScanAdm(array $data): bool
    {
        $this->dbDnAdm->transStart();

        // INSERT ke combin_result
        $this->dbDnAdm->table('combin_result')->insert([
            'customer'     => $data['customer'],
            'id_label'     => $data['id_label'],
            'id_kanban'    => $data['id_kanban'],
            'part'         => $data['part'],
            'user_created' => $data['user_created'],
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        // UPDATE adm_p5 — mark sebagai scanned & finished
        $this->dbDnAdm->table('adm_p5')
            ->where('barcode', $data['id_label'])
            ->set([
                'is_scan'     => 1,
                'is_finish'   => 1,
                'update_date' => date('Y-m-d H:i:s'),
            ])
            ->update();

        $this->dbDnAdm->transComplete();

        if (!$this->dbDnAdm->transStatus()) {
            return false;
        }

        $this->dbKanban->table('scan_tag_ok_ifpd')
            ->where('id_tag_ok', $data['id_kanban'])
            ->set([
                'scanned_out'   => 1,
                'scan_out_date' => date('Y-m-d H:i:s'),
                'scan_out_user' => $data['user_created'],
            ])
            ->update();

        return true;
    }
    public function adm_trip_all()
    {
        $user = $this->_validateBearerToken();
        if ($user instanceof \CodeIgniter\HTTP\Response) return $user;

        $date  = $this->request->getGet('date') ?: date('Y-m-d');
        $plant = $this->request->getGet('dn');
        $trip  = $this->request->getGet('trip')  ?? '';
        $route = $this->request->getGet('route') ?? '';

        // ✅ Hanya dn dan date yang wajib
        if (empty($plant)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Parameter dn wajib diisi.',
                'data'    => []
            ]);
        }

        $model  = new M_combinkanban();
        $output = $model->get_item_range_trip($trip, $date, $plant, $route);

        if (empty($output)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data tidak ditemukan untuk kriteria tersebut.',
                'data'    => []
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Success fetch data trip',
            'data'    => $output,
            'user'    => [
                'nik'  => $user->nik,
                'name' => $user->nama ?? ''
            ]
        ]);
    }
    public function mieruka_shipping()
    {
        try {
            $user = $this->_validateBearerToken();
            if ($user instanceof \CodeIgniter\HTTP\Response) return $user;

            $date  = $this->request->getGet('tanggal') ?: date('Y-m-d');
            $plant = $this->request->getGet('customer');
            $cycle = $this->request->getGet('cycle') ?? '';

            if (empty($plant)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => false,
                    'message' => 'Parameter customer harus diisi.',
                    'data'    => []
                ]);
            }

            if ($cycle == '11' || $cycle == '12') {
                $date = date("Y-m-d", strtotime("+1 day"));
            }

            $model  = new M_combinkanban();
            $output = new \stdClass();
            $output->data = [];

            $sapPlants = ['hmmi', 'tmmin', 'mkm', 'mmki'];
            if (in_array(strtolower($plant), $sapPlants)) {
            
                $output->data = $model->get_mieruka_sap($cycle, $date, $plant);
            } else {
                $output->data = $model->get_mieruka($cycle, $date, $plant);
            }
            //  WAJIB GANTI INI DI FRONT END ETA JD ETD
            if (!empty($output->data)) {
                $output->data = array_map(function($item) {
                    $item['ETA'] = $item['ETD'] ?? '--:--:--';
                    return $item;
                }, $output->data);
            }
            $output->total_item    = count($output->data);
            $output->total_act     = array_sum(array_column($output->data, 'ACT'));
            $output->time_delivery = !empty($output->data) ? [
                [
                    // nti wajib diagnti di androidnya jangan pake eta
                    'plan_eta' => $output->data[0]['ETA'] ?? '--:--:--',
                    'plan_etd' => $output->data[0]['ETD'] ?? '--:--:--',
                ]
            ] : [];

            $cycleInfo             = $model->get_total_cycle_delivery($plant, $date);
            $output->total_cycle   = $cycleInfo['total_cycle'] ?? 0;
            $output->cycle_summary = $cycleInfo['cycles'] ?? [];
            $output->sap_total_item = 0;
            $output->sap_total_act  = 0;

            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Success fetch Mieruka Shipping data',
                'data'    => $output,           // ✅ konsisten pakai 'data' bukan 'result'
                'user'    => [
                    'nik'  => $user->nik,
                    'name' => $user->nama ?? ''
                ]
            ]);

        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }
    }
    public function search_tag_ok()
    {
        $user = $this->_validateBearerToken();
        if ($user instanceof \CodeIgniter\HTTP\Response) return $user;

        $query    = $this->request->getPost('query') ?? $this->request->getGet('query');
        $customer = $this->request->getPost('customer') ?? $this->request->getGet('customer');

        if (empty($query)) {
            return $this->response->setJSON([
                'status' => true,
                'data'   => []
            ]);
        }

        try {
            $model  = new M_combinkanban();
            $result = $model->searchTagOk($query, $customer);

            // Pengurutan DESC (Descending / dari yang terbaru & Z-A)
            rsort($result, SORT_NATURAL | SORT_FLAG_CASE);

            return $this->response->setJSON([
                'status' => true,
                'data'   => array_values($result)
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ]);
        }
    }
}
