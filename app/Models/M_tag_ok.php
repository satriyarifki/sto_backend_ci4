<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class M_tag_ok extends Model
{
    protected $dbKanban;

    // Sumber data hasil scan (view di majsf_inventory)
    protected $view_tag_ok = 'v_print_tag_ok_all';
    // Tabel penampung hasil scan STO
    protected $table_sto_tag_ok = 'table_sto_tag_ok';

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->dbKanban = Database::connect('kanbanInventory');
    }

    /**
     * Ambil detail TAG OK dari view berdasarkan id_tag_ok hasil scan.
     */
    public function getTagOk($id_tag_ok)
    {
        $builder = $this->dbKanban->table($this->view_tag_ok);
        $builder->select('
            process,
            id_tag_ok,
            date,
            shift,
            line,
            part_number,
            job_number,
            qty_kbn,
            status,
            project,
            customer,
            create_date,
            user_create
        ');
        $builder->where('id_tag_ok', $id_tag_ok);

        return $builder->get()->getRowArray();
    }

    /**
     * Cek apakah tag sudah pernah discan pada event yang sama.
     */
    public function isScanned($id_tag_ok, $id_event = null)
    {
        $builder = $this->dbKanban->table($this->table_sto_tag_ok);
        $builder->where('id_tag_ok', $id_tag_ok);

        if (!empty($id_event)) {
            $builder->where('id_event', $id_event);
        }

        return $builder->get()->getRowArray();
    }

    /**
     * Simpan hasil scan TAG OK.
     */
    public function insertTagOk(array $data)
    {
        $inserted = $this->dbKanban->table($this->table_sto_tag_ok)->insert($data);

        if (!$inserted) {
            return false;
        }

        return $this->dbKanban->insertID();
    }

    /**
     * Ambil satu baris hasil scan berdasarkan primary key.
     */
    public function getScanById($id)
    {
        return $this->dbKanban->table($this->table_sto_tag_ok)
            ->where('id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * History scan berdasarkan NIK (scan_by).
     * Filter opsional: area, tanggal (range scan_at), limit.
     */
    public function getHistoryByNik($nik, $filter = [])
    {
        $builder = $this->dbKanban->table($this->table_sto_tag_ok);
        $builder->select('
            id,
            id_tag_ok,
            process,
            date,
            shift,
            line,
            part_number,
            job_number,
            qty_kbn,
            status,
            project,
            customer,
            area,
            scan_at,
            scan_by,
            id_event,
            created_at
        ');
        $builder->where('scan_by', $nik);

        if (!empty($filter['area'])) {
            $builder->where('area', $filter['area']);
        }
        if (!empty($filter['id_event'])) {
            $builder->where('id_event', $filter['id_event']);
        }
        if (!empty($filter['start_date'])) {
            $builder->where('scan_at >=', $filter['start_date'] . ' 00:00:00');
        }
        if (!empty($filter['end_date'])) {
            $builder->where('scan_at <=', $filter['end_date'] . ' 23:59:59');
        }

        $builder->orderBy('scan_at', 'DESC');

        $limit = isset($filter['limit']) ? (int) $filter['limit'] : 0;
        if ($limit > 0) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Ringkasan history: total tag & total qty per NIK.
     */
    public function getHistorySummaryByNik($nik, $filter = [])
    {
        $builder = $this->dbKanban->table($this->table_sto_tag_ok);
        $builder->select('COUNT(id) AS total_tag, IFNULL(SUM(qty_kbn), 0) AS total_qty', false);
        $builder->where('scan_by', $nik);

        if (!empty($filter['area'])) {
            $builder->where('area', $filter['area']);
        }
        if (!empty($filter['id_event'])) {
            $builder->where('id_event', $filter['id_event']);
        }
        if (!empty($filter['start_date'])) {
            $builder->where('scan_at >=', $filter['start_date'] . ' 00:00:00');
        }
        if (!empty($filter['end_date'])) {
            $builder->where('scan_at <=', $filter['end_date'] . ' 23:59:59');
        }

        return $builder->get()->getRowArray();
    }

    /**
     * Hapus hasil scan (koreksi salah scan) milik NIK terkait.
     */
    public function deleteScan($id, $nik)
    {
        return $this->dbKanban->table($this->table_sto_tag_ok)
            ->where('id', $id)
            ->where('scan_by', $nik)
            ->delete();
    }
}
