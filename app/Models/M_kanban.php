<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class M_kanban extends Model
{
    protected $table      = 'kanban_list';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'manifest_number', 'item_number', 'seq_number', 'kanban_code', 'kanban_id', 'is_scan', 'date_scan', 'prepared', 'is_confirm', 'skid_number', 'date_confirm'
    ];

    protected $dbGroup = 'dbManifest';

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::connect('dbManifest'); 
        $this->table = 'kanban_list';
    }

    public function getKanbanManifest($no_manifest, $countOnly = false)
    {
        $builder = $this->builder();
        $builder->where('manifest_number', $no_manifest);
    
        if ($countOnly) {
            return $builder->countAllResults();
        } else {
            return $builder->get()->getResult();
        }
    }

    public function getKanbanScan($no_manifest, $countOnly = false)
    {
        $builder = $this->builder();
        $builder->where('manifest_number', $no_manifest);
        $builder->where('is_scan', 'Y');
    
        if ($countOnly) {
            return $builder->countAllResults();
        } else {
            return $builder->get()->getResult();
        }
    }

    public function updateIsScan($kanbanId, $noManifest, $noSkid, $user)
    {
        date_default_timezone_set('Asia/Jakarta');

        $existingData = $this->builder()
            ->where('kanban_id', $kanbanId)
            ->where('manifest_number', $noManifest)
            ->get()
            ->getRowArray();

        if (!$existingData) {
            return ['status' => false, 'message' => 'Kanban ID tidak ditemukan.'];
        }

        if ($existingData['is_scan'] == 'Y') {
            return ['status' => false, 'message' => 'Kanban sudah dikonfirmasi.'];
        }

        $update = $this->set([
                'is_scan'   => 'Y', 
                'skid_number'  => $noSkid,
                'prepared'     => $user->nik,
                'date_scan' => date('Y-m-d')
            ])
            ->where('kanban_id', $kanbanId)
            ->update();

        if ($update) {
            return ['status' => true, 'message' => 'Status kanban berhasil diperbarui.'];
        } else {
            return ['status' => false, 'message' => 'Gagal memperbarui status kanban.'];
        }
    }


    public function queryConfirm($noManifest, $noSkid, $kanbanId = null)
    {
        $builder = $this->builder()
            ->where('manifest_number', $noManifest)
            ->where('skid_number', $noSkid);

        if (!is_null($kanbanId)) {
            $builder->where('kanban_id', $kanbanId);
        }

        $results = $builder->get()->getResultArray();

        if (empty($results)) {
            return [];
        }

        $formattedResults = [];

        foreach ($results as $data) {
            $formattedResults[] = [
                'supplierCode' => 'T034',
                'supplierPlant' => '1', 
                'skidNo' => $data['skid_number'] ?? '',
                'manifestNo' => $data['manifest_number'] ?? '',
                'itemNo' => (int)($data['item_number'] ?? 0),
                'seqNo' => (int)($data['seq_number'] ?? 0),
                'kanbanId' => $data['kanban_id'] ?? '',
            ];
        }

        return $formattedResults;
    }


    public function updateIsConfirm($noManifest, $noSkid)
    {
        date_default_timezone_set('Asia/Jakarta');

        $existingData = $this->builder()
            ->where('skid_number', $noSkid)
            ->where('manifest_number', $noManifest)
            ->get()
            ->getRowArray();

        if (!$existingData) {
            return ['status' => false, 'message' => 'Manifest tidak ditemukan.'];
        }

        if ($existingData['is_confirm'] == 'Y') {
            return ['status' => false, 'message' => 'Manifest sudah dikonfirmasi.'];
        }

        $update = $this->set([
                'is_confirm'   => 'Y', 
                'date_confirm' => date('Y-m-d')
            ])
            ->where('skid_number', $noSkid)
            ->update();

        if ($update) {
            return ['status' => true, 'message' => 'Status Manifest berhasil diperbarui.'];
        } else {
            return ['status' => false, 'message' => 'Gagal memperbarui status Manifest.'];
        }
    }

    public function updateIsCancel($noManifest, $noSkid, $kanbanId = null)
    {
        date_default_timezone_set('Asia/Jakarta');

        $builder = $this->builder()
            ->where('skid_number', $noSkid)
            ->where('manifest_number', $noManifest);

        if (!is_null($kanbanId)) {
            $builder->where('kanban_id', $kanbanId);
        }

        $existingData = $builder->get()->getRowArray();

        if (!$existingData) {
            return ['status' => false, 'message' => 'Manifest tidak ditemukan.'];
        }

        if ($existingData['is_confirm'] == 'N') {
            return ['status' => false, 'message' => 'Manifest sudah dicancel.'];
        }

        $updateBuilder = $this->set([
                'is_scan'       => 'N', 
                'is_confirm'    => 'N', 
                'date_confirm'  => NULL
            ])
            ->where('skid_number', $noSkid)
            ->where('manifest_number', $noManifest);

        if (!is_null($kanbanId)) {
            $updateBuilder->where('kanban_id', $kanbanId);
        }

        $update = $updateBuilder->update();

        if ($update) {
            return ['status' => true, 'message' => 'Status Manifest berhasil diperbarui.'];
        } else {
            return ['status' => false, 'message' => 'Gagal memperbarui status Manifest.'];
        }
    }


    public function ListSkid($noManifest)
    {
        $results = $this->builder()
            ->select('skid_number, kanban_code, is_confirm, kanban_id, prepared')
            ->where('manifest_number', $noManifest)
            ->where('is_scan', 'Y')
            ->orderBy('skid_number')
            ->get()
            ->getResultArray();

        if (empty($results)) {
            return [];
        }

        $grouped = [];

        foreach ($results as $row) {
            $skid = $row['skid_number'];
            if (!isset($grouped[$skid])) {
                $grouped[$skid] = [
                    'skid_number' => $skid,
                    'kanbans' => []
                ];
            }
            $grouped[$skid]['kanbans'][] = [
                'kanban_code'   => $row['kanban_code'],
                'kanban_id'     => $row['kanban_id'],
                'prepared'      => $row['prepared'],
                'is_confirm'    => $row['is_confirm']
            ];
        }
        return array_values($grouped);
    }

}
