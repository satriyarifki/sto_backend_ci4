<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class M_combinkanban extends Model
{
    protected $dbManifest;
    protected $dbKanban;
    protected $dbWelding;
    protected $dbDnAdm;

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->dbManifest = Database::connect('dbManifest');
        $this->dbKanban = Database::connect('kanbanInventory');
        $this->dbWelding = Database::connect('dbWelding');
        $this->dbDnAdm = Database::connect('dbAdm');
        $this->table = 'kanban_list';
    }

    public function get_tag_ok($id_tag_ok)
    {
        $builder = $this->dbKanban->table('ms_patan_tag_ok');
        $builder->where('id_tag_ok', $id_tag_ok);
        $query = $builder->get();
        return $query->getRow();
    }

    public function get_tag_ok_wld($id_tag_ok)
    {
        $builder = $this->dbWelding->table('welding_production_tag_ok');
        $builder->where('id_tag_ok', $id_tag_ok);
        $query = $builder->get();
        return $query->getRow();
    }

    public function get_tag_e_kanban($id_tag_ok)
    {
        $builder = $this->dbKanban->table('kanban_circulation');
        $builder->where('prod_order', $id_tag_ok);
        $query = $builder->get();
        return $query->getRow();
    }
    public function get_tag_log_print($id_tag_ok)
    {
        $builder = $this->dbKanban->table('log_print_tag_ok');
        $builder->where('id_label', $id_tag_ok);
        $query = $builder->get();
        return $query->getRow();
    }

    public function get_part_dn($id_label)
    {
        $builder = $this->dbDnAdm->table('adm_p5');
        $builder->where('barcode', $id_label);
        $query = $builder->get();
        return $query->getRow();
    }

    
    public function get_tag_ok_ifpd($id_tag_ok)
    {
        $builder = $this->dbKanban->table('scan_tag_ok_ifpd');
        $builder->where('id_tag_ok', $id_tag_ok);
        $query = $builder->get();
        return $query->getRow();
    }


    public function insertToCombinResult($data)
    {
        $builder = $this->dbDnAdm->table('combin_result');
        $insertResult = $builder->insert([
            'customer'      => $data['customer'],
            'id_label'      => $data['id_label'],
            'id_kanban'     => $data['id_kanban'],
            'part'          => $data['part'],
            'user_created'  => $data['user_created'],
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        if ($insertResult) {
            if ($data['customer'] === 'ADM') {
                // Update ke dbAdm.adm_p5
                $builderUpdate = $this->dbDnAdm->table('adm_p5');
                $builderUpdate->where('barcode', $data['id_label']);
                $builderUpdate->set([
                    'is_scan'   => 1,
                    'is_finish' => 1,
                    'update_date' => date('Y-m-d H:i:s'),
                ]);
                $builderUpdate->update();
    
            } elseif ($data['customer'] === 'TMMIN') {
                // Update ke dbManifest.kanban_list
                $builderUpdate = $this->dbManifest->table('kanban_list');
                $builderUpdate->where('kanban_id', $data['id_label']);
                $builderUpdate->set([
                    'is_internal_scan' => 'Y'
                ]);
                $builderUpdate->update();
            }
        }

        return $insertResult;
    }

    public function isAlreadyVerified($id_kanban, $id_label)
    {
        $builder = $this->dbDnAdm->table('combin_result');
        return $builder
            ->where('id_kanban', $id_kanban)
            ->where('id_label', $id_label)
            ->countAllResults() > 0;
    }


    public function get_all($data)
    {
        $builder = $this->db->table($this->table);
        $builder->select("*");
        $builder->where("no_tag", $data);
        $query = $builder->get();
        return $query->getResultArray();
    }

    public function update_status_kanban($id_kanban)
    {
        if ($id_kanban == null) {
            return false;
        }
    
        $builder = $this->dbKanban->table('kanban_circulation');
    
        $builder->where('id_kanban', $id_kanban);
        $builder->set([
            'status'        => '1',
            'user_updated'  => null,
            'updated_at'    => null,
            'prod_order'    => null
        ]);
    
        return $builder->update();
    }

    public function historyKanbanUser($start_date, $end_date, $customer, $nik, $quick = false)
    {
        // 1. Validasi dasar
        if ($customer == null && $nik == null) {
            return false;
        }
    
        $builder = $this->dbDnAdm->table('combin_result');
        $builder->select("*");
    
        // 2. Perbaikan Logika Customer
        // Jika Flutter mengirim 'Grup2', tampilkan semua (HMMI, MKM, MMKI)
        if ($customer == 'Grup2') {
            $builder->whereIn("customer", ['HMMI', 'MKM', 'MMKI']);
        } else {
            // Jika Flutter mengirim salah satu (misal: 'MKM'), filter spesifik
            $builder->where("customer", $customer);
        }
    
        // 3. Filter berdasarkan NIK User
        $builder->where("user_created", $nik);
    
        // 4. Logika Quick Scan (5 Terakhir) vs Filter Tanggal
        if ($quick == 'true' || $quick === true) { 
            // Pastikan order by ID terbaru untuk "5 Scan Terakhir"
            $builder->orderBy('id', 'DESC');
            $builder->limit(5);
        } else {
            if ($start_date && $end_date) {
                $builder->where("DATE(created_at) >=", $start_date);
                $builder->where("DATE(created_at) <=", $end_date);
            } else {
                $builder->where("DATE(created_at)", date('Y-m-d'));
            }
            $builder->orderBy('id', 'DESC'); // Tetap urutkan dari yang terbaru
        }
    
        $query = $builder->get();
        return $query->getResultArray();
    }
    public function dn_kanban($start_date, $end_date, $customer)
    {
        $startInt = (int) str_replace('-', '', $start_date);
        $endInt = (int) str_replace('-', '', $end_date);

        $builder = $this->dbKanban->table('cycle_delivery_sap');
        $builder->where('VDATU >=', $startInt);
        $builder->where('VDATU <=', $endInt);

        if (!empty($customer)) {
            $builder->where('CUSTOMER', $customer);
        }

        $parentData = $builder->get()->getResultArray();

        if (empty($parentData)) {
            return [];
        }

        $prefixes = [];
        foreach ($parentData as $row) {
            $prefixes[] = $row['BSTNK'];
        }
        $prefixes = array_unique($prefixes);

        $admBuilder = $this->dbDnAdm->table('adm_p5');

        $admBuilder->groupStart();
        foreach ($prefixes as $idx => $prefix) {
            if ($idx === 0) {
                $admBuilder->like('barcode', $prefix, 'after');
            } else {
                $admBuilder->orLike('barcode', $prefix, 'after');
            }
        }
        $admBuilder->groupEnd();

        $admResults = $admBuilder->get()->getResultArray();

        $admIndex = [];
        foreach ($admResults as $admRow) {
            foreach ($prefixes as $prefix) {
                if (strpos($admRow['barcode'], $prefix) === 0) {
                    $admIndex[$prefix][] = $admRow;
                    break;
                }
            }
        }

        foreach ($parentData as &$parentRow) {
            $bstnk = $parentRow['BSTNK'];
            $parentRow['kanban'] = $admIndex[$bstnk] ?? [];
        }

        return $parentData;
    }

    public function extractParentDn(string $childDn): ?string
    {
        $posA = strpos($childDn, 'A');
        return ($posA !== false) ? substr($childDn, 0, $posA + 1) : null;
    }

    public function getParentExistAndChildStatus(string $parentDn): array
    {
        $parentExists = $this->dbKanban
            ->table('cycle_delivery_sap')
            ->where('BSTNK', $parentDn)
            ->countAllResults() > 0;

        $result = $this->dbDnAdm->table('adm_p5')
            ->select([
                'COUNT(*) AS total',
                "SUM(CASE WHEN is_finish = '1' THEN 1 ELSE 0 END) AS scanned"
            ])
            ->like('barcode', $parentDn, 'after')
            ->get()
            ->getRowArray();

        return [
            'parentExists' => $parentExists,
            'total'        => (int) ($result['total'] ?? 0),
            'scanned'      => (int) ($result['scanned'] ?? 0)
        ];
    }

    public function extractManifestNumber(string $kanbanCode): ?string
    {
        if (strpos($kanbanCode, 'KBN') === 0 && strlen($kanbanCode) >= 13) {
            return substr($kanbanCode, 3, 10);
        }
        return null;
    }

    public function getInternalScanStatus(string $manifestNumber): array
    {
        $builder = $this->dbManifest->table('kanban_list');
        $builder->select([
            'COUNT(*) AS total',
            "SUM(CASE WHEN is_internal_scan = 'Y' THEN 1 ELSE 0 END) AS scanned"
        ]);
        $builder->where('manifest_number', $manifestNumber);

        $result = $builder->get()->getRowArray();

        if (!$result) {
            return [
                'total' => 0,
                'scanned' => 0
            ];
        }

        return $result;
    }

    public function findDoByPartAndSjman($part, $sjman)
    {
        return $this->dbKanban->table('do_sap')
            ->where('MATNR', $part)
            ->like('SJMAN', $sjman)
            ->select('VBELN, VBELV, MATNR')
            ->get()
            ->getResultArray();
    }

    public function findDetailBarcodeHmmi($id_label, $total_scan, $customer)
    {
        // 1. Pastikan total_scan adalah angka untuk keamanan SQL (type casting)
        $total_scan = (int)$total_scan;
    
        $builder = $this->dbKanban->table('barcode_dn');
    
        // 2. Ambil data awal untuk dicek keberadaannya
        $row = $builder
            ->select('part_no, qty_total')
            ->where('barcode', $id_label)
            ->where('customer', $customer)
            ->limit(1)
            ->get()
            ->getRowArray();
    
        if (!$row) {
            return null;
        }
    
        // 3. Update dengan logika penjumlahan (total_scan lama + total_scan baru)
        $builder
            ->where('barcode', $id_label)
            ->where('customer', $customer)
            ->limit(1)
            ->set('is_scan', 1)
            // Parameter false memastikan database melakukan kalkulasi matematika, bukan teks
            ->set('total_scan', "total_scan + {$total_scan}", false) 
            ->update();
    
        return $row;
    }

    public function findDetailBarcodeMmki($id_label, $total_scan, $customer)
    {
        // Pastikan total_scan adalah angka (Integer/Float)
        $total_scan = (int) $total_scan;
    
        $builder = $this->dbKanban->table('barcode_dn');
    
        // 1. Ambil data awal
        $row = $builder
            ->select('part_no, qty_total, cycle')
            ->where('barcode', $id_label)
            ->where('customer', $customer)
            ->limit(1)
            ->get()
            ->getRowArray();
    
        // Jika data tidak ditemukan, hentikan proses
        if (!$row) {
            return null;
        }
    
        // 2. Jalankan Update Increment
        $builder
            ->where('barcode', $id_label)
            ->where('customer', $customer)
            ->limit(1)
            ->set('is_scan', 1)
            // Parameter false di sini sangat krusial
            ->set('total_scan', "total_scan + $total_scan", false) 
            ->update();
    
        return $row;
    }

    public function updateScanStatus($bsntk, $matnr, $total_scan)
    {
        $query = "
            UPDATE mieruka_delivery 
            SET is_scan = 1, total_qty_scan = ? 
            WHERE BSTNK = ? AND MATNR = ?
        ";

        return $this->dbKanban->query($query, [$total_scan, $bsntk, $matnr]);
    }

    public function findDetailBarcodeMkm($barcodeMkm, $total_scan, $part_no)
    {
        // 1. Casting ke integer untuk memastikan keamanan operasi matematika di SQL
        $total_scan = (int)$total_scan;
    
        $builder = $this->dbKanban->table('barcode_dn');
    
        // 2. Ambil data untuk validasi keberadaan baris
        $row = $builder
            ->select('part_no, qty_total, cycle')
            ->where('barcode', $barcodeMkm)
            ->where('part_no', $part_no)
            ->limit(1)
            ->get()
            ->getRowArray();
    
        if (!$row) {
            return null;
        }
    
        // 3. Update dengan akumulasi total_scan (increment)
        $builder
            ->where('barcode', $barcodeMkm)
            ->where('part_no', $part_no)
            ->limit(1)
            ->set('is_scan', 1)
            // Parameter false agar SQL menjalankan "total_scan + X" sebagai angka, bukan string
            ->set('total_scan', "total_scan + {$total_scan}", false) 
            ->update();
    
        return $row;
    }
    public function findDetailBarcodeTmmin($manifest, $total_scan, $part_no)
    {
        $total_scan = (int)$total_scan;

        $builder = $this->dbKanban->table('barcode_dn');

        $row = $builder
            ->where('barcode', $manifest)
            ->like('part_no', $part_no)
            ->select('part_no, qty_total, total_scan, cycle')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$row) {
            return 'NOT_FOUND';
        }

        if ($row['total_scan'] >= $row['qty_total']) {
            return 'FULL';
        }

        $builder
            ->where('barcode', $manifest)
            ->like('part_no', $part_no)
            ->limit(1)
            ->set('is_scan', 1)
            ->set('total_scan', "total_scan + $total_scan", false)
            ->update();

        return $row;
    }
}