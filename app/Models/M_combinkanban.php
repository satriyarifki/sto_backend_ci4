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
           $this->dbRun = Database::connect('dbRun');
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
    public function get_part_dn_upload_barcode($id_label)
    {
        $builder = $this->dbKanban->table('barcode_dn');
        $builder->where('barcode', $id_label);
        $query = $builder->get();
        return $query->getRow();
    }  
    public function validation_polybox($part_number)
    {
        $builder = $this->dbRun->table('stocks');
        
        $builder->where('MATNR', $part_number);
        $builder->groupStart()
                ->like('ZROUTING', 'MGL')
                ->like('MTART', 'IFPD')
                ->groupEnd();

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
    $this->dbDnAdm->transStart();

    $this->dbDnAdm->table('combin_result')->insert([
        'customer'     => $data['customer'],
        'id_label'     => $data['id_label'],
        'id_kanban'    => $data['id_kanban'],
        'part'         => $data['part'],
        'user_created' => $data['user_created'],
        'created_at'   => date('Y-m-d H:i:s'),
    ]);

    if ($data['customer'] === 'ADM') {
        $this->dbDnAdm->table('adm_p5')
            ->where('barcode', $data['id_label'])
            ->set([
                'is_scan'     => 1,
                'is_finish'   => 1,
                'update_date' => date('Y-m-d H:i:s'),
            ])
            ->update();

    } elseif ($data['customer'] === 'TMMIN') {
        $this->dbManifest->table('kanban_list')
            ->where('kanban_id', $data['id_label'])
            ->set(['is_internal_scan' => 'Y'])
            ->update();
    }

    $this->dbDnAdm->transComplete();

    return $this->dbDnAdm->transStatus();
}

    public function updateToScanTagIfpd($data)
    {
        $builder = $this->dbKanban->table('scan_tag_ok_ifpd');
    
        $builder->where('id_tag_ok', $data['id_kanban']);
        $builder->set([
            'scanned_out'   => 1,
            'scan_out_date' => date('Y-m-d H:i:s'),
            'scan_out_user' => $data['user_created']
        ]);
    
        return $builder->update();
    }
    // public function updateToCycleItem($part_number, $manifest_number, $qty_tambahan)
    // {
    //     // STEP 1: Ambil VBELN berdasarkan manifest number
    //     $delivery = $this->dbKanban->table('cycle_delivery_sap')
    //         ->select('VBELN')
    //         ->where('BSTNK', $manifest_number)
    //         ->get()
    //         ->getRow();
    
    //     if ($delivery) {
    //         $vbeln = $delivery->VBELN;
    
    //         // STEP 2: Update hanya 1 baris (yang total_scannya masih kurang dari KWMENG)
    //         $builder = $this->dbKanban->table('mieruka_delivery');
    //         $builder->where('VBELN', $vbeln);
    //         $builder->like('MATNR', $part_number);
            
    //         // FILTER: Hanya ambil yang total_scan belum mencapai target KWMENG
    //         // Kita gunakan COALESCE agar jika total_scan masih NULL, dianggap 0
    //         $builder->where('COALESCE(total_qty_scan, 0) < KWMENG', null, false);
    
    //         $builder->set('is_scan', 1);
    //         $builder->set('scanned_date', date('Y-m-d H:i:s'));
    //         $builder->set('total_qty_scan', "COALESCE(total_qty_scan, 0) + $qty_tambahan", false);
    
    //         // LIMIT 1: Sangat penting agar jika ada banyak baris yang cocok, 
    //         // hanya satu baris saja yang terupdate (biasanya baris pertama yang ditemukan)
    //         $builder->limit(1);
    
    //         return $builder->update();
    //     }
    
    //     return false;
    // }

    public function updateToCycleItem($part_number, $manifest_number, $qty_tambahan)
    {
        $delivery = $this->dbKanban->table('cycle_delivery_sap')
            ->select('VBELN')
            ->where('BSTNK', $manifest_number)
            ->get()
            ->getRow();
    
        if ($delivery) {
            $vbeln = $delivery->VBELN;
    
            $builder = $this->dbKanban->table('mieruka_delivery');
            $builder->where('VBELN', $vbeln);
            $builder->like('MATNR', $part_number);
            $builder->where('COALESCE(total_qty_scan, 0) < KWMENG', null, false);
    
            $builder->set('is_scan', 1);
            $builder->set('scanned_date', date('Y-m-d H:i:s'));
            $builder->set('total_qty_scan', "COALESCE(total_qty_scan, 0) + $qty_tambahan", false);
    
            $builder->limit(1);
    
            $update = $builder->update();
    
            if ($update) {
                $current_hour = date('H:00');
    
                $this->dbRun->query("UPDATE majsf_rundown.stocks_shipping_preparation 
                    SET 
                        qty_reduced_scan = qty_reduced_scan + $qty_tambahan,
                        total_calc_assumpt_scan_ship = init_stok + qty_added_scan - (qty_reduced_scan + $qty_tambahan),
                        updated_at = CURRENT_TIMESTAMP()
                    WHERE part_number = '$part_number'
                    AND hour_time = '$current_hour'");
            }
    
            return $update;
        }
    
        return false;
    }

    public function updateToCycleItemHmmi($part_number, $qty_tambahan)
    {
        $today = date('Ymd'); 
    
        $builder = $this->dbKanban->table('mieruka_delivery');
        
        $builder->where('VDATU', $today);
        $builder->where('MATNR', $part_number);
        
        // --- TAMBAHAN: ABAIKAN CYCLE 0 ---
        // Menggunakan != 0 atau > 0 tergantung tipe data di DB (string/int)
        $builder->where('CYCLE !=', '0'); 
        // ---------------------------------
    
        $builder->where('COALESCE(total_qty_scan, 0) < KWMENG', null, false);
    
        // Urutkan dari yang terkecil (Cycle 1, 2, 3...)
        $builder->orderBy('CYCLE', 'ASC'); 
    
        $builder->set('is_scan', 1);
        $builder->set('scanned_date', date('Y-m-d H:i:s'));
        
        $qty = (float)$qty_tambahan;
        $builder->set('total_qty_scan', "COALESCE(total_qty_scan, 0) + $qty", false);
    
        $builder->limit(1);
    
        return $builder->update();
    }
    public function get_qty_kanban($part_number)
    {
      
        $builder = $this->dbKanban->table('ms_data_ifp');
        $builder->where('part_number', $part_number);
        $query = $builder->get();
        return $query->getRow();
    }

    public function isAlreadyVerified($id_kanban, $id_label)
    {
        $builder = $this->dbDnAdm->table('combin_result');
        return $builder
            ->where('id_kanban', $id_kanban)
            ->where('id_label', $id_label)
            ->countAllResults() > 0;
    }

    public function isAlreadyVerifiedPolybox($id_label)
    {
        $builder = $this->dbDnAdm->table('combin_result');
        return $builder
        
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

    public function getParentExistAndChildStatus(string $parentDn): ?array
    {
        // $parentExists = $this->dbKanban
        //     ->table('cycle_delivery_sap')
        //     ->where('BSTNK', $parentDn)
        //     ->countAllResults();
    
        // if ($parentExists === 0) {
        //     return null;
        // }
    
        $result = $this->dbDnAdm
            ->table('adm_p5')
            ->select("
                COUNT(*) AS total,
                SUM(CASE WHEN is_finish = '1' THEN 1 ELSE 0 END) AS scanned
            ")
            ->like('barcode', $parentDn, 'after')
            ->get()
            ->getRowArray();
    
        return [
            'total'   => (int) $result['total'],
            'scanned' => (int) $result['scanned']
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
        $total_scan = (int)$total_scan;
    
        $builder = $this->dbKanban->table('barcode_dn');
    
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
        $builder
            ->where('barcode', $id_label)
            ->where('customer', $customer)
            ->limit(1)
            ->set('is_scan', 1)
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

    // public function updateScanStatus($bsntk, $matnr, $total_scan)
    // {
     
    //     // 1. Update dulu
    //     $updateQuery = "
    //         UPDATE mieruka_delivery 
    //         SET is_scan = 1, total_qty_scan = ? 
    //         WHERE BSTNK = ? AND MATNR = ?
    //     ";
    //     $this->dbKanban->query($updateQuery, [$total_scan, $bsntk, $matnr]);

 
    //     $selectQuery = "
    //         SELECT KWMENG 
    //         FROM mieruka_delivery 
    //         WHERE BSTNK = ? AND MATNR = ?
    //         LIMIT 1
    //     ";
    //     $result = $this->dbKanban->query($selectQuery, [$bsntk, $matnr]);
    //     $row    = $result->getRowArray();

    //     return $row ? (int) $row['KWMENG'] : null;
    // }
    public function updateScanStatus(string $bstnk, string $matnr, int $total_scan): ?int
    {
        $row = $this->dbKanban->query(
            "SELECT KWMENG FROM mieruka_delivery WHERE BSTNK = ? AND MATNR = ? LIMIT 1",
            [$bstnk, $matnr]
        )->getRowArray();

        if (!$row) return null;

        $this->dbKanban->query(
            "UPDATE mieruka_delivery SET is_scan = 1, total_qty_scan = ? WHERE BSTNK = ? AND MATNR = ?",
            [$total_scan, $bstnk, $matnr]
        );

        return (int) $row['KWMENG'];
    }
    public function getPartDnWithStatus(string $id_label, string $parentDn): ?array
    {
        $sql = "
            SELECT
                detail.part_number,
                agg.total,
                agg.scanned
            FROM adm_p5 AS detail
            LEFT JOIN (
                SELECT
                    COUNT(*)                                             AS total,
                    SUM(CASE WHEN is_finish = '1' THEN 1 ELSE 0 END)   AS scanned
                FROM adm_p5
                WHERE barcode LIKE ?
            ) AS agg ON 1 = 1
            WHERE detail.barcode = ?
            LIMIT 1
        ";

        $row = $this->dbDnAdm->query($sql, [
            $parentDn . '%', 
            $id_label,       
        ])->getRowArray();

        return $row ?: null;
    }
    public function findDetailBarcodeMkm($barcodeMkm, $total_scan, $part_no)
    {
        $total_scan = (int)$total_scan;
    
        $builder = $this->dbKanban->table('barcode_dn');
    
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

    public function get_item($cycle, $date, $plant)
    {
        if ($plant === 'DN5') {
            $customer = 'ADM PLANT 5';
        } elseif ($plant === 'DN4') {
            $customer = 'ADM PLANT 4';
        } else {
            return [];
        }
    
        $builder = $this->dbDnAdm->table('adm_p5 AS main');
      
        return $builder
            ->select("
                main.job_number,
                main.part_number,
                main.qty,
                sap.etd,
                sap.eta,
                main.delivery_date,
                SUM(main.is_scan = '1') AS scanned,
                COUNT(*) AS total,
                sap.BSTNK,
                sap.ACT,
                MAX(main.is_scan = '1') AS FINISH
            ")
            ->join(
                'majsf_inventory.cycle_delivery_sap AS sap',
                "sap.CYCLE = main.cycle
                 AND sap.VDATU = DATE_FORMAT(main.delivery_date,'%Y%m%d')
                 AND main.barcode LIKE CONCAT(sap.BSTNK,'%')",
                'left'
            )
            ->where([
                'main.cycle' => $cycle,
                'main.delivery_date' => $date,
                'main.plan' => $plant,
          
            ])
            ->groupBy('main.job_number')
            ->orderBy('FINISH', 'ASC')
            ->orderBy('main.job_number', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    public function get_mieruka_barcode_by_plant($cycle, $date, $plant)
    {
        $extraWhere = '';
    
        if ($plant === 'MMKI' && $cycle !== null) {
            $extraWhere = ' AND cycle = ' . $this->dbKanban->escape($cycle);
        }
     
        $sql = "
            SELECT 
                part_no,
                SUM(qty_total) AS qty_all_part_no,
                SUM(COALESCE(total_scan, 0)) AS total_scan,
                SUM(is_scan = 1) AS scanned,
                CASE WHEN SUM(is_scan = 1) > 0 THEN 1 ELSE 0 END AS FINISH
            FROM majsf_inventory.barcode_dn
            WHERE delivery_date = " . $this->dbKanban->escape($date) . "
              AND customer = " . $this->dbKanban->escape($plant) . "
              $extraWhere
            GROUP BY part_no
            ORDER BY FINISH ASC
        ";
    
        $query = $this->dbKanban->query($sql);
    
        return $query->getResultArray();
    }
    public function getBarcodeDnByLabel($id_label)
    {
        return $this->dbKanban->table('barcode_dn')
            ->select('barcode, part_no, qty')
            ->like('barcode', $id_label, 'after')
   
            ->limit(1)
            ->get()
            ->getRowArray();
    }

    public function getTagOkIfpByKanban($id_kanban)
    {
        $builder = $this->dbKanban->table('scan_tag_ok_ifpd');
        $builder->select('id_tag_ok, part_number, qty');
        $builder->where('id_tag_ok', $id_kanban);
        return $builder->get()->getRowArray();
    }
    public function getAdmLabelData(string $parentDn, string $id_label): ?array
    {
        $result = $this->dbDnAdm
            ->table('adm_p5')
            ->select("
                COUNT(*) AS total,
                SUM(CASE WHEN is_finish = '1' THEN 1 ELSE 0 END) AS scanned,
                MAX(CASE WHEN barcode = ? THEN part_number ELSE NULL END) AS part_number,
                MAX(CASE WHEN barcode = ? AND is_finish = '1' THEN 1 ELSE 0 END) AS already_verified
            ")
            ->like('barcode', $parentDn, 'after')
            ->get()
            ->getRowArray();
        // Jika tidak ada baris sama sekali (parentDn tidak ditemukan)
        if ($result === null || (int)$result['total'] === 0) {
            return null;
        }

        return [
            'total'            => (int) $result['total'],
            'scanned'          => (int) $result['scanned'],
            'part_number'      => $result['part_number'] ?: null,
            'already_verified' => (bool) $result['already_verified'],
        ];
    }
   public function get_item_range_trip($trip, $date, $plant, $route): array
{
    try {
        // ✅ Bangun kondisi WHERE secara dinamis
        $where  = "WHERE main.order_date = ? AND main.plan = ?";
        $params = [$date, $plant];

        if (!empty($trip)) {
            $where  .= " AND main.trip = ?";
            $params[] = $trip;
        }

        if (!empty($route)) {
            $where  .= " AND main.route = ?";
            $params[] = $route;
        }

        $query = $this->dbDnAdm->query("
            SELECT 
                main.job_number,
                MAX(main.barcode)       AS barcode,
                MAX(main.part_number)   AS part_number,
                MAX(main.qty)           AS qty,
                main.trip,
                main.route, 
                MAX(main.part_category) AS part_category,
                CASE 
                    WHEN UPPER(MAX(main.part_category)) LIKE '%BIG%' 
                        THEN 'PALLET' 
                    ELSE 'POLYBOX' 
                END AS jenis,
                MAX(main.shop_code)     AS shop_code,
                MAX(main.etd)           AS etd,
                MAX(main.eta)           AS eta,
                main.delivery_date,
                CAST(SUM(
                    CASE WHEN main.is_scan = '1' THEN 1 ELSE 0 END
                ) AS UNSIGNED) AS scanned,
                CAST(COUNT(*) AS UNSIGNED) AS total,
                CASE 
                    WHEN COUNT(*) = SUM(
                        CASE WHEN main.is_scan = '1' THEN 1 ELSE 0 END
                    ) THEN 1 
                    ELSE 0 
                END AS FINISH
            FROM adm_p5 main
            {$where}
            GROUP BY 
                main.job_number,
                main.delivery_date,
                main.trip,
                main.route
            ORDER BY 
                FINISH ASC,
                main.trip ASC,
                main.job_number ASC
        ", $params);

        return $query->getResultArray();

    } catch (\Throwable $e) {
        dd($e->getMessage());
    }
}
   
public function get_mieruka_sap(string $cycle, string $date, string $customer): array
{
    $formattedDate = date('Ymd', strtotime($date));

    $builder = $this->dbKanban->table('mieruka_delivery md');

    // ✅ false = jangan escape — biarkan ekspresi SQL apa adanya
    $builder->select("
        md.*,
        CASE WHEN md.is_scan = 1 THEN 1 ELSE 0 END AS FINISH,
        SUM(CASE WHEN md.is_scan = '1' THEN 1 ELSE 0 END) AS scanned,
        COUNT(md.is_scan) AS total,
        SUM(md.KWMENG) AS qty_all_part_no
    ", false);

    $builder->where('md.VDATU', $formattedDate);
    $builder->where('md.CUSTOMER', strtoupper($customer));

    if (!empty($cycle) && $cycle !== 'null') {
        $builder->where('md.CYCLE', $cycle);
    }

    $builder->groupBy(['md.MATNR', 'md.CYCLE']);
    $builder->orderBy('FINISH', 'ASC');

    return $builder->get()->getResultArray();
}
public function get_total_cycle_delivery(string $customer, ?string $date = null): array
{
    if (empty($customer)) {
        return ['total_cycle' => 0, 'cycles' => []];
    }

    $formattedDate = date('Ymd', strtotime($date ?: date('Y-m-d')));

    // ✅ Hapus prefix majsf_inventory. — sudah pakai koneksi dbKanban
    $builder = $this->dbKanban->table('mieruka_delivery md');

    $builder->select("
        md.CYCLE,
        COUNT(DISTINCT md.MATNR) AS totalData,
        SUM(CASE WHEN md.is_scan = 1 THEN 1 ELSE 0 END) AS totalFinish,
        MIN(md.ETD) AS etd
    ");

    $builder->where('md.VDATU', $formattedDate);
    // ✅ uppercase agar cocok dengan nilai di DB
    $builder->where('md.CUSTOMER', strtoupper($customer));

    $builder->groupBy('md.CYCLE');
    $builder->orderBy('CAST(md.CYCLE AS UNSIGNED)', 'ASC');

    $rows = $builder->get()->getResultArray();

    $cycles = [];
    foreach ($rows as $row) {
        $cycles[(int)$row['CYCLE']] = [
            'totalData'   => (int)$row['totalData'],
            'totalFinish' => (int)$row['totalFinish'],
            'etd'         => $row['etd'],
        ];
    }

    return [
        'total_cycle' => count($cycles),
        'cycles'      => $cycles,
    ];
}
    public function searchTagOk(string $query, ?string $customer = null): array
    {
        if (empty($query)) return [];

        $results = [];
        $fourteenDaysAgo = date('Y-m-d H:i:s', strtotime('-14 days'));

        // 0. Pre-fetch ID Kanban yang SUDAH DISCAN dari combin_result
        $scannedMap = [];
        try {
            $scannedRows = $this->dbDnAdm->table('combin_result')
                ->select('id_kanban')
                ->where('id_kanban !=', '')
                ->get()
                ->getResultArray();
            foreach ($scannedRows as $sRow) {
                if (!empty($sRow['id_kanban'])) {
                    $scannedMap[trim($sRow['id_kanban'])] = true;
                }
            }
        } catch (\Throwable $e) {}

        // Helper fungsi untuk memasukkan ID unik & memfilter yang sudah discan
        $addResult = function($id) use (&$results, &$scannedMap) {
            $idStr = trim((string)$id);
            if (!empty($idStr) && !isset($scannedMap[$idStr]) && !in_array($idStr, $results)) {
                $results[] = $idStr;
            }
        };

        // 1. Cari di ms_patan_tag_ok (MAJ...) - Urut DESC (Terbaru)
        try {
            $builder = $this->dbKanban->table('ms_patan_tag_ok')->select('id_tag_ok')->like('id_tag_ok', $query);
            foreach (['created_at', 'created_date', 'date'] as $col) {
                if ($this->dbKanban->fieldExists($col, 'ms_patan_tag_ok')) {
                    $builder->where("$col >=", $fourteenDaysAgo)->orderBy($col, 'DESC');
                    break;
                }
            }
            $r1 = $builder->limit(10)->get()->getResultArray();
            foreach ($r1 as $row) {
                $addResult($row['id_tag_ok'] ?? '');
            }
        } catch (\Throwable $e) {}

        // 2. Cari di welding_production_tag_ok (MAJWLD...) - Urut DESC (Terbaru)
        try {
            $builder = $this->dbWelding->table('welding_production_tag_ok')->select('id_tag_ok')->like('id_tag_ok', $query);
            foreach (['created_at', 'created_date', 'date', 'update_date'] as $col) {
                if ($this->dbWelding->fieldExists($col, 'welding_production_tag_ok')) {
                    $builder->where("$col >=", $fourteenDaysAgo)->orderBy($col, 'DESC');
                    break;
                }
            }
            $r2 = $builder->limit(10)->get()->getResultArray();
            foreach ($r2 as $row) {
                $addResult($row['id_tag_ok'] ?? '');
            }
        } catch (\Throwable $e) {}

        // 3. Cari di kanban_circulation - Urut DESC (Terbaru)
        try {
            $builder = $this->dbKanban->table('kanban_circulation')->select('prod_order AS id_tag_ok')->like('prod_order', $query);
            foreach (['updated_at', 'created_at', 'date'] as $col) {
                if ($this->dbKanban->fieldExists($col, 'kanban_circulation')) {
                    $builder->where("$col >=", $fourteenDaysAgo)->orderBy($col, 'DESC');
                    break;
                }
            }
            $r3 = $builder->limit(10)->get()->getResultArray();
            foreach ($r3 as $row) {
                $addResult($row['id_tag_ok'] ?? '');
            }
        } catch (\Throwable $e) {}

        // 4. Cari di log_print_tag_ok - Urut DESC (Terbaru)
        try {
            $builder = $this->dbKanban->table('log_print_tag_ok')->select('id_label AS id_tag_ok')->like('id_label', $query);
            $builder->orderBy('id_label', 'DESC');
            $r4 = $builder->limit(10)->get()->getResultArray();
            foreach ($r4 as $row) {
                $addResult($row['id_tag_ok'] ?? '');
            }
        } catch (\Throwable $e) {}

        // 5. Cari di scan_tag_ok_ifpd - Urut DESC (Terbaru)
        try {
            $builder = $this->dbKanban->table('scan_tag_ok_ifpd')->select('id_tag_ok')->like('id_tag_ok', $query);
            foreach (['scan_out_date', 'created_at', 'date'] as $col) {
                if ($this->dbKanban->fieldExists($col, 'scan_tag_ok_ifpd')) {
                    $builder->where("$col >=", $fourteenDaysAgo)->orderBy($col, 'DESC');
                    break;
                }
            }
            $r5 = $builder->limit(10)->get()->getResultArray();
            foreach ($r5 as $row) {
                $addResult($row['id_tag_ok'] ?? '');
            }
        } catch (\Throwable $e) {}

        // Mengurutkan hasil akhir secara DESC (terbaru / Z-A)
        rsort($results, SORT_NATURAL | SORT_FLAG_CASE);

        return array_slice($results, 0, 20);
    }
}