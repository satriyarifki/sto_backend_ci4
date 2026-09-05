<?php

namespace App\Controllers;

use \App\Models\M_tag_ok;
use \App\Models\M_sto;

class Tag_ok extends BaseController
{
    protected $model;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->model = new M_tag_ok();
    }

    /**
     * STEP 1 - Scan.
     * Frontend kirim id_tag_ok, backend balikan detail dari view v_print_tag_ok_all.
     *
     * Method : POST/GET
     * Param  : id_tag_ok
     */
    public function get_tag_ok()
    {
        $id_tag_ok = $this->request->getPost('id_tag_ok') ?? $this->request->getGet('id_tag_ok');

        if (empty($id_tag_ok)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Parameter id_tag_ok wajib diisi.',
                'data'    => null,
            ])->setStatusCode(400);
        }

        $id_tag_ok = trim($id_tag_ok);
        $data      = $this->model->getTagOk($id_tag_ok);

        if (!$data) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'TAG OK tidak ditemukan.',
                'data'    => null,
            ])->setStatusCode(404);
        }

        $id_event = $this->currentEventId();
        $scanned  = $this->model->isScanned($id_tag_ok, $id_event);

        $data['id_event']     = $id_event;
        $data['is_scanned']   = $scanned ? true : false;
        $data['scanned_info'] = $scanned ? [
            'area'    => $scanned['area'],
            'scan_at' => $scanned['scan_at'],
            'scan_by' => $scanned['scan_by'],
        ] : null;

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data ditemukan.',
            'data'    => $data,
        ]);
    }

    /**
     * STEP 2 - Simpan hasil scan ke table_sto_tag_ok.
     *
     * Method : POST
     * Param  : id_tag_ok, area, scan_at (opsional), scan_by
     */
    public function store_tag_ok()
    {
        $id_tag_ok = trim((string) $this->request->getPost('id_tag_ok'));
        $area      = trim((string) $this->request->getPost('area'));
        $scan_by   = trim((string) $this->request->getPost('scan_by'));
        $scan_at   = $this->request->getPost('scan_at');

        if ($id_tag_ok === '' || $area === '' || $scan_by === '') {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'id_tag_ok, area, dan scan_by wajib diisi.',
                'data'    => null,
            ])->setStatusCode(400);
        }

        $scan_at = !empty($scan_at)
            ? date('Y-m-d H:i:s', strtotime($scan_at))
            : date('Y-m-d H:i:s');

        $tag = $this->model->getTagOk($id_tag_ok);

        if (!$tag) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'TAG OK tidak ditemukan, data tidak dapat disimpan.',
                'data'    => null,
            ])->setStatusCode(404);
        }

        $id_event = $this->currentEventId();

        if ($this->model->isScanned($id_tag_ok, $id_event)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'TAG OK sudah pernah discan.',
                'data'    => null,
            ])->setStatusCode(409);
        }

        $payload = [
            'id_tag_ok'   => $tag['id_tag_ok'],
            'process'     => $tag['process'],
            'date'        => $tag['date'],
            'shift'       => $tag['shift'],
            'line'        => $tag['line'],
            'part_number' => $tag['part_number'],
            'job_number'  => $tag['job_number'],
            'qty_kbn'     => $tag['qty_kbn'],
            'status'      => $tag['status'],
            'project'     => $tag['project'],
            'customer'    => $tag['customer'],
            'user_create' => $tag['user_create'],
            'area'        => $area,
            'scan_at'     => $scan_at,
            'scan_by'     => $scan_by,
            'id_event'    => $id_event,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        $insertId = $this->model->insertTagOk($payload);

        if (!$insertId) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal menyimpan data scan.',
                'data'    => null,
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data berhasil disimpan.',
            'data'    => $this->model->getScanById($insertId),
        ])->setStatusCode(201);
    }

    /**
     * STEP 3 - History scan berdasarkan NIK.
     *
     * Method : POST
     * Param  : nik, area (opsional), start_date, end_date, limit
     */
    public function history()
    {
        $nik = trim((string) ($this->request->getPost('nik') ?? $this->request->getGet('nik')));

        if ($nik === '') {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Parameter nik wajib diisi.',
                'data'    => [],
            ])->setStatusCode(400);
        }

        $filter = [
            'area'       => $this->request->getPost('area') ?? $this->request->getGet('area'),
            'start_date' => $this->request->getPost('start_date') ?? $this->request->getGet('start_date'),
            'end_date'   => $this->request->getPost('end_date') ?? $this->request->getGet('end_date'),
            'limit'      => $this->request->getPost('limit') ?? $this->request->getGet('limit'),
        ];

        $result  = $this->model->getHistoryByNik($nik, $filter);
        $summary = $this->model->getHistorySummaryByNik($nik, $filter);

        return $this->response->setJSON([
            'status'  => true,
            'message' => empty($result) ? 'Data tidak tersedia.' : 'Data ditemukan.',
            'summary' => [
                'total_tag' => (int) ($summary['total_tag'] ?? 0),
                'total_qty' => (int) ($summary['total_qty'] ?? 0),
            ],
            'data'    => $result,
        ]);
    }

    /**
     * Hapus hasil scan (koreksi salah scan).
     *
     * Method : POST
     * Param  : id, nik
     */
    public function delete_scan()
    {
        $id  = (int) $this->request->getPost('id');
        $nik = trim((string) $this->request->getPost('nik'));

        if ($id <= 0 || $nik === '') {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Parameter id dan nik wajib diisi.',
            ])->setStatusCode(400);
        }

        $row = $this->model->getScanById($id);

        if (!$row || $row['scan_by'] !== $nik) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data tidak ditemukan atau bukan milik NIK terkait.',
            ])->setStatusCode(404);
        }

        $this->model->deleteScan($id, $nik);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data berhasil dihapus.',
        ]);
    }

    /**
     * Event STO yang sedang aktif (tabel sto_events di DB default).
     */
    private function currentEventId()
    {
        try {
            return (new M_sto())->getCurrentEventId();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
