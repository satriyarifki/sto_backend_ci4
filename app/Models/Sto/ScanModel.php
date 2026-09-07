<?php

namespace App\Models\Sto;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * majsf_sto.sto_data -- print tag, scan, riwayat, dan summary.
 *
 * Tim A menulis ke kolom (nik_a, qty_a, updated_a),
 * tim B menulis ke kolom (nik_b, qty_b, updated_b).
 *
 * Penanda "sudah pernah discan" memakai `updated_a` / `updated_b` IS NOT NULL,
 * BUKAN `qty > 0`, karena hasil hitung 0 itu sah dan tetap harus dianggap
 * sudah discan.
 *
 * Identitas material (part_number, job_number, material_description, type)
 * TIDAK disimpan di sto_data -- diambil lewat JOIN ke master_data memakai
 * kolom `id_item`.
 */
class ScanModel extends Model
{
    protected $DBGroup    = 'db_sto';
    protected $table      = 'sto_data';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'id_tag', 'id_event', 'id_item', 'area',
        'nik_a', 'qty_a', 'updated_a',
        'nik_b', 'qty_b', 'updated_b',
        'is_canceled', 'created_by', 'created_at',
    ];

    protected $useTimestamps = false;

    /** Batas aman jumlah baris riwayat per request. */
    public const MAX_LIMIT     = 500;
    public const DEFAULT_LIMIT = 100;

    /** Batas atas qty, mengikuti tipe kolom INT UNSIGNED. */
    public const MAX_QTY = 4294967295;

    /** Batas jumlah kandidat item yang dikembalikan saat pencarian ganda. */
    public const MAX_KANDIDAT = 50;

    /** Jumlah id per query, supaya klausa IN (...) tidak kebablasan panjangnya. */
    public const CHUNK_SIZE = 500;

    /**
     * Batas daftar part. Lebih longgar dari MAX_LIMIT biasa karena
     * master_data memang dimaksudkan diambil sekaligus untuk dicache di
     * perangkat -- satu area terbesar saja sudah 1.962 baris, semua area 6.508.
     */
    public const PART_DEFAULT_LIMIT = 1000;
    public const PART_MAX_LIMIT     = 10000;

    /** Kode error MySQL untuk pelanggaran UNIQUE key. */
    public const ERR_DUPLICATE = 1062;

    public array $lastError = [];

    // =================================================================
    // TAG
    // =================================================================

    /**
     * Builder SELECT + JOIN yang dipakai bersama oleh getTag(),
     * getTagsByIdTags(), dan getCancelRequests().
     *
     * LEFT JOIN (bukan INNER) dipakai sebagai pengaman: FK menjamin
     * pasangannya ada, tapi kalau sampai tidak ada, barisnya tetap kebaca
     * dan tidak hilang diam-diam. Dua join terakhir menerjemahkan id
     * pengaju/penyetuju pembatalan jadi NIK, supaya layar approval tidak
     * perlu query tambahan.
     */
    private function builderTagLengkap()
    {
        return $this->db->table('sto_data s')
            ->select('s.*, m.part_number, m.job_number, m.material_description, m.`type`, '
                . 'm.status_part, m.customer, m.plant, m.process, m.category, m.model, '
                . 'ureq.nik AS cancel_requested_by_nik, uapp.nik AS cancel_approved_by_nik', false)
            ->join('master_data m', 's.id_item = m.id', 'left')
            ->join('users ureq', 's.cancel_requested_by = ureq.id', 'left')
            ->join('users uapp', 's.cancel_approved_by = uapp.id', 'left');
    }

    /**
     * Ambil satu baris tag berdasarkan id_tag.
     *
     * LEFT JOIN (bukan INNER) dipakai sebagai pengaman: FK menjamin
     * pasangannya ada, tapi kalau sampai tidak ada, barisnya tetap kebaca
     * dan tidak hilang diam-diam.
     *
     * @return array|null|false
     */
    public function getTag(string $idTag)
    {
        try {
            $row = $this->builderTagLengkap()
                ->where('s.id_tag', $idTag)
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::getTag gagal: ' . $e->getMessage());

            return false;
        }

        return $row ?: null;
    }

    /**
     * Simpan hasil scan ke kolom milik tim yang bersangkutan.
     *
     * @param string $tim 'A' atau 'B' (sudah divalidasi controller)
     */
    public function saveScan(string $idTag, string $tim, int $qty, string $nik): bool
    {
        $suffix = $tim === 'A' ? 'a' : 'b';

        $data = [
            'nik_' . $suffix     => $nik,
            'qty_' . $suffix     => $qty,
            'updated_' . $suffix => date('Y-m-d H:i:s'),
        ];

        $this->db->transBegin();

        try {
            $this->db->table('sto_data')->where('id_tag', $idTag)->update($data);

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'ScanModel::saveScan gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return true;
    }

    /**
     * Riwayat scan.
     *
     * sto_data menyimpan hasil tim A dan tim B pada satu baris, jadi riwayat
     * disusun dengan UNION ALL: satu baris sto_data bisa menghasilkan dua
     * entri riwayat.
     *
     * Identitas material di-JOIN dari master_data lewat id_item, dilakukan
     * di query LUAR supaya join-nya cukup sekali walau cabang UNION-nya dua.
     *
     * Semua filter opsional. Filter didorong ke dalam masing-masing cabang
     * UNION supaya index kolom terpakai.
     *
     * @param array $f [tim, nik, area, id_tag, start_date, end_date, limit]
     *
     * @return array|false
     */
    public function getScanHistory(array $f)
    {
        $cols = 'id, id_tag, id_event, id_item, area, is_canceled';

        $branches = [];
        $binds    = [];

        foreach (['A', 'B'] as $tim) {
            if ($f['tim'] !== null && $f['tim'] !== $tim) {
                continue;
            }

            $s     = strtolower($tim); // a / b
            $where = ['updated_' . $s . ' IS NOT NULL'];

            if ($f['nik'] !== null) {
                $where[] = 'nik_' . $s . ' = ?';
                $binds[] = $f['nik'];
            }
            if ($f['id_tag'] !== null) {
                $where[] = 'id_tag = ?';
                $binds[] = $f['id_tag'];
            }
            if ($f['area'] !== null) {
                $where[] = 'area = ?';
                $binds[] = $f['area'];
            }
            if ($f['start_date'] !== null) {
                $where[] = 'updated_' . $s . ' >= ?';
                $binds[] = $f['start_date'];
            }
            if ($f['end_date'] !== null) {
                $where[] = 'updated_' . $s . ' <= ?';
                $binds[] = $f['end_date'];
            }

            $branches[] = 'SELECT ' . $cols . ", '" . $tim . "' AS tim, "
                . 'nik_' . $s . ' AS nik, '
                . 'qty_' . $s . ' AS qty, '
                . 'updated_' . $s . ' AS scanned_at '
                . 'FROM sto_data '
                . 'WHERE ' . implode(' AND ', $where);
        }

        // Guard: kalau sampai tidak ada cabang sama sekali, jangan jalankan query.
        if ($branches === []) {
            return [];
        }

        $limit = (int) $f['limit'];
        if ($limit <= 0) {
            $limit = self::DEFAULT_LIMIT;
        }
        if ($limit > self::MAX_LIMIT) {
            $limit = self::MAX_LIMIT;
        }

        $sql = 'SELECT h.*, m.part_number, m.job_number, m.material_description, m.`type` '
            . 'FROM (' . implode(' UNION ALL ', $branches) . ') AS h '
            . 'LEFT JOIN master_data m ON m.id = h.id_item ';

        // Pencarian bebas: nomor tag / part / job / nama material. Ditaruh di
        // luar UNION supaya kolom master_data ikut tercari, dan bind-nya
        // menyusul bind cabang UNION - urutannya sama dengan urutan tanda '?'.
        if (isset($f['q']) && $f['q'] !== null && $f['q'] !== '') {
            $suka = '%' . $f['q'] . '%';
            $sql .= 'WHERE (h.id_tag LIKE ? OR m.part_number LIKE ? '
                  . 'OR m.job_number LIKE ? OR m.material_description LIKE ?) ';
            $binds[] = $suka;
            $binds[] = $suka;
            $binds[] = $suka;
            $binds[] = $suka;
        }

        $sql .= 'ORDER BY h.scanned_at DESC, h.id DESC '
            . 'LIMIT ' . $limit; // integer hasil cast, aman disisipkan langsung

        try {
            $result = $this->db->query($sql, $binds)->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::getScanHistory gagal: ' . $e->getMessage());

            return false;
        }

        $rows = [];

        foreach ($result as $row) {
            $rows[] = [
                'id'                   => (int) $row['id'],
                'id_tag'               => (string) $row['id_tag'],
                'id_event'             => $row['id_event'] === null ? null : (int) $row['id_event'],
                'id_item'              => (int) $row['id_item'],
                'area'                 => (string) $row['area'],
                'job_number'           => $row['job_number'] === null ? '' : (string) $row['job_number'],
                'part_number'          => $row['part_number'] === null ? '' : (string) $row['part_number'],
                'material_description' => $row['material_description'] === null ? '' : (string) $row['material_description'],
                'type'                 => $row['type'] === null ? '' : (string) $row['type'],
                'tim'                  => (string) $row['tim'],
                'nik'                  => $row['nik'] === null ? '' : (string) $row['nik'],
                'qty'                  => (int) $row['qty'],
                'scanned_at'           => (string) $row['scanned_at'],
                'is_canceled'          => (int) $row['is_canceled'],
            ];
        }

        return $rows;
    }

    // =================================================================
    // PRINT TAG
    // =================================================================

    /**
     * @return array|null|false
     */
    public function getEvent(int $idEvent)
    {
        try {
            $row = $this->db->table('events')
                ->select('id_event, event_name, start_date, end_date, status')
                ->where('id_event', $idEvent)
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::getEvent gagal: ' . $e->getMessage());

            return false;
        }

        return $row ?: null;
    }

    /**
     * Daftar event yang sedang berjalan (status = 1).
     * Dipakai print-tag saat pemanggil tidak menyebut id_event.
     *
     * @return array|false
     */
    public function getActiveEvents()
    {
        try {
            $result = $this->db->table('events')
                ->select('id_event, event_name, start_date, end_date, status')
                ->where('status', 1)
                ->orderBy('id_event', 'ASC')
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::getActiveEvents gagal: ' . $e->getMessage());

            return false;
        }

        $rows = [];

        // Di-cast supaya bentuknya sama dgn endpoint lain: angka tetap angka.
        foreach ($result as $row) {
            $rows[] = [
                'id_event'   => (int) $row['id_event'],
                'event_name' => (string) $row['event_name'],
                'start_date' => (string) $row['start_date'],
                'end_date'   => $row['end_date'] === null ? '' : (string) $row['end_date'],
                'status'     => (int) $row['status'],
            ];
        }

        return $rows;
    }

    /**
     * Cari item di master_data.
     *
     * `area` + `part_number` maupun `area` + `job_number` TIDAK unik di
     * master_data, jadi hasilnya bisa lebih dari satu. Controller yang
     * memutuskan: 1 hasil -> langsung dibuat tag, >1 -> minta pemanggil
     * mempertegas lewat id_item.
     *
     * @return array|false
     */
    public function findMasterItems(string $area, ?string $partNumber, ?string $jobNumber, ?int $idItem)
    {
        try {
            $builder = $this->db->table('master_data')
                ->select('id, area, job_number, part_number, material_description, '
                    . '`type`, status_part, customer, plant, process, category, model', false)
                ->where('area', $area);

            if ($idItem !== null) {
                $builder->where('id', $idItem);
            }
            if ($partNumber !== null) {
                $builder->where('part_number', $partNumber);
            }
            if ($jobNumber !== null) {
                $builder->where('job_number', $jobNumber);
            }

            return $builder->orderBy('part_number', 'ASC')
                ->orderBy('job_number', 'ASC')
                ->limit(self::MAX_KANDIDAT)
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::findMasterItems gagal: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Buat satu baris sto_data (tag baru) dan kembalikan id_tag-nya.
     *
     * Format id_tag: STO[YYMMDD]-[id_event][id sto_data]
     * contoh: event 5, baris sto_data id 1, tanggal 2026-09-03 -> STO260903-51
     *
     * `id sto_data` baru diketahui SETELAH INSERT, sedangkan id_tag NOT NULL
     * dan UNIQUE. Jadi barisnya dimasukkan dulu dengan id_tag sementara,
     * lalu di-update di dalam transaksi yang sama -- id_tag sementara tidak
     * pernah ikut ter-commit.
     *
     * @return array|false ['id' => .., 'id_tag' => ..]
     */
    public function createTag(int $idEvent, int $idItem, string $area, ?int $createdBy)
    {
        $this->lastError = [];
        $this->db->transBegin();

        try {
            $placeholder = 'TMP-' . uniqid('', true);

            $this->db->table('sto_data')->insert([
                'id_tag'     => $placeholder,
                'id_event'   => $idEvent,
                'id_item'    => $idItem,
                'area'       => $area,
                'created_by' => $createdBy,
                // created_at diisi MySQL (DEFAULT CURRENT_TIMESTAMP)
            ]);

            $newId = (int) $this->db->insertID();

            if ($this->db->transStatus() === false || $newId <= 0) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }

            $idTag = 'STO' . date('ymd') . '-' . $idEvent . $newId;

            $this->db->table('sto_data')->where('id', $newId)->update(['id_tag' => $idTag]);

            if ($this->db->transStatus() === false) {
                // Antara lain kalau id_tag hasil gabungan ternyata bentrok
                // (mis. event 51 + id 1 vs event 5 + id 11 sama-sama "511").
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = $this->db->error();
            $this->db->transRollback();
            log_message('error', 'ScanModel::createTag gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return ['id' => $newId, 'id_tag' => $idTag];
    }

    /**
     * Daftar part dari master_data.
     *
     * `areas` boleh kosong (semua area), satu area, atau banyak area.
     * Pencocokan area TIDAK perlu dibuat case-insensitive secara manual:
     * kolomnya bercollation utf8mb4_general_ci, jadi 'ifrm' sudah cocok
     * dengan 'IFRM' di level database.
     *
     * @param array $f [areas (array), q (string|null), limit (int)]
     *
     * @return array|false
     */
    public function getPartList(array $f)
    {
        $limit = (int) $f['limit'];
        if ($limit <= 0) {
            $limit = self::PART_DEFAULT_LIMIT;
        }
        if ($limit > self::PART_MAX_LIMIT) {
            $limit = self::PART_MAX_LIMIT;
        }

        try {
            $builder = $this->db->table('master_data')
                ->select('id, area, job_number, part_number, material_description, '
                    . '`type`, status_part, customer, plant, process, category, model', false);

            if ($f['areas'] !== []) {
                $builder->whereIn('area', $f['areas']);
            }

            if ($f['q'] !== null) {
                // Digroup supaya OR-nya tidak bocor keluar dari filter area.
                $builder->groupStart()
                    ->like('part_number', $f['q'], 'both')
                    ->orLike('job_number', $f['q'], 'both')
                    ->orLike('material_description', $f['q'], 'both')
                    ->groupEnd();
            }

            $result = $builder->orderBy('area', 'ASC')
                ->orderBy('part_number', 'ASC')
                ->orderBy('job_number', 'ASC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::getPartList gagal: ' . $e->getMessage());

            return false;
        }

        $str  = static fn ($v): string => $v === null ? '' : (string) $v;
        $rows = [];

        foreach ($result as $row) {
            $rows[] = [
                'id_item'              => (int) $row['id'],
                'area'                 => (string) $row['area'],
                'job_number'           => (string) $row['job_number'],
                'part_number'          => (string) $row['part_number'],
                'material_description' => (string) $row['material_description'],
                'type'                 => (string) $row['type'],
                'status_part'          => (string) $row['status_part'],
                'customer'             => (string) $row['customer'],
                'plant'                => $str($row['plant']),
                'process'              => $str($row['process']),
                'category'             => $str($row['category']),
                'model'                => $str($row['model']),
            ];
        }

        return $rows;
    }

    // =================================================================
    // CANCEL TAG  (soft cancel)
    // =================================================================

    /**
     * Ambil status is_canceled dari sekumpulan id_tag.
     *
     * @return array|false ['<id_tag>' => is_canceled(int), ...]
     */
    public function getTagsCancelStatus(array $idTags)
    {
        if ($idTags === []) {
            return [];
        }

        $found = [];

        try {
            foreach (array_chunk($idTags, self::CHUNK_SIZE) as $chunk) {
                if ($chunk === []) {
                    continue;
                }

                $rows = $this->db->table('sto_data')
                    ->select('id_tag, is_canceled')
                    ->whereIn('id_tag', $chunk)
                    ->get()
                    ->getResultArray();

                foreach ($rows as $row) {
                    $found[(string) $row['id_tag']] = (int) $row['is_canceled'];
                }
            }
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::getTagsCancelStatus gagal: ' . $e->getMessage());

            return false;
        }

        return $found;
    }

    /**
     * Set is_canceled = 1 pada daftar id_tag.
     *
     * Barisnya TIDAK dihapus -- hanya ditandai, supaya jejak hasil hitung
     * tim A / tim B tetap tersimpan dan bisa diaudit.
     *
     * @return int|false jumlah baris yang berubah
     */
    public function cancelTags(array $idTags)
    {
        if ($idTags === []) {
            return 0; // tidak ada yang perlu dibatalkan, bukan sebuah error
        }

        $affected = 0;
        $this->db->transBegin();

        try {
            foreach (array_chunk($idTags, self::CHUNK_SIZE) as $chunk) {
                // Guard: JANGAN PERNAH jalankan UPDATE tanpa WHERE.
                if ($chunk === []) {
                    continue;
                }

                // != 1: tag yang pembatalannya sedang diajukan (2) ikut
                // dibatalkan langsung, tag yang sudah batal (1) dilewati.
                $this->db->table('sto_data')
                    ->whereIn('id_tag', $chunk)
                    ->where('is_canceled !=', 1)
                    ->update(['is_canceled' => 1]);

                $affected += (int) $this->db->affectedRows();
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'ScanModel::cancelTags gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return $affected;
    }

    // =================================================================
    // SUMMARY
    // =================================================================

    /**
     * Rekap per area, difilter `created_at`.
     * Baris yang sudah dibatalkan (is_canceled = 1) TIDAK dihitung.
     *
     * @return array|false
     */
    public function getSummaryByArea(string $start, string $end)
    {
        try {
            $result = $this->db->table('sto_data')
                ->select('area, COUNT(*) AS total_tag, '
                    . 'SUM(updated_a IS NOT NULL) AS total_scanned_a, '
                    . 'SUM(updated_b IS NOT NULL) AS total_scanned_b, '
                    . 'SUM(qty_a) AS total_qty_a, '
                    . 'SUM(qty_b) AS total_qty_b', false)
                // != 1, bukan = 0: tag yang pembatalannya baru DIAJUKAN (2)
                // tetap dihitung sampai admin benar-benar menyetujui.
                ->where('is_canceled !=', 1)
                ->where('created_at >=', $start)
                ->where('created_at <=', $end)
                ->groupBy('area')
                ->orderBy('area', 'ASC')
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::getSummaryByArea gagal: ' . $e->getMessage());

            return false;
        }

        $rows = [];

        foreach ($result as $row) {
            $qa = (int) $row['total_qty_a'];
            $qb = (int) $row['total_qty_b'];

            $rows[] = [
                'area'            => (string) $row['area'],
                'total_tag'       => (int) $row['total_tag'],
                'total_scanned_a' => (int) $row['total_scanned_a'],
                'total_scanned_b' => (int) $row['total_scanned_b'],
                'total_qty_a'     => $qa,
                'total_qty_b'     => $qb,
                'selisih'         => $qa - $qb,
            ];
        }

        return $rows;
    }

    /**
     * Rekap per item master_data, difilter `created_at`.
     * Baris yang sudah dibatalkan (is_canceled = 1) TIDAK dihitung.
     *
     * Digroup per `id_item` + `area`: satu baris master_data = satu kombinasi
     * (area, job_number, part_number) yang sudah dijamin unik di sana.
     *
     * @return array|false
     */
    public function getSummaryByPart(string $start, string $end)
    {
        try {
            $result = $this->db->table('sto_data s')
                ->select('s.id_item, s.area, m.job_number, m.part_number, '
                    . 'm.material_description, m.`type`, '
                    . 'COUNT(*) AS total_tag, '
                    . 'SUM(s.updated_a IS NOT NULL) AS total_scanned_a, '
                    . 'SUM(s.updated_b IS NOT NULL) AS total_scanned_b, '
                    . 'SUM(s.qty_a) AS total_qty_a, '
                    . 'SUM(s.qty_b) AS total_qty_b', false)
                ->join('master_data m', 's.id_item = m.id', 'left')
                // != 1, bukan = 0: lihat penjelasan di getSummaryByArea().
                ->where('s.is_canceled !=', 1)
                ->where('s.created_at >=', $start)
                ->where('s.created_at <=', $end)
                ->groupBy('s.id_item, s.area')
                ->orderBy('m.part_number', 'ASC')
                ->orderBy('m.job_number', 'ASC')
                ->orderBy('s.area', 'ASC')
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::getSummaryByPart gagal: ' . $e->getMessage());

            return false;
        }

        $rows = [];

        foreach ($result as $row) {
            $qa = (int) $row['total_qty_a'];
            $qb = (int) $row['total_qty_b'];

            $rows[] = [
                'id_item'              => (int) $row['id_item'],
                'area'                 => (string) $row['area'],
                'job_number'           => $row['job_number'] === null ? '' : (string) $row['job_number'],
                'part_number'          => $row['part_number'] === null ? '' : (string) $row['part_number'],
                'material_description' => $row['material_description'] === null ? '' : (string) $row['material_description'],
                'type'                 => $row['type'] === null ? '' : (string) $row['type'],
                'total_tag'            => (int) $row['total_tag'],
                'total_scanned_a'      => (int) $row['total_scanned_a'],
                'total_scanned_b'      => (int) $row['total_scanned_b'],
                'total_qty_a'          => $qa,
                'total_qty_b'          => $qb,
                'selisih'              => $qa - $qb,
            ];
        }

        return $rows;
    }

    // =================================================================
    // PRINT TAG BULK
    // =================================================================

    /**
     * Buat sekaligus $qty baris sto_data untuk item yang sama.
     *
     * BEST EFFORT, bukan all-or-nothing: tiap baris punya transaksinya
     * sendiri. Kalau baris ke-3 gagal, dua yang sudah jadi TETAP tersimpan
     * dan sisanya tetap dicoba. Yang gagal dilaporkan balik lengkap dengan
     * nomor urutnya, jadi pemanggil tahu persis berapa yang jadi.
     *
     * Transaksi per baris tetap diperlukan karena satu tag butuh dua
     * langkah: INSERT dulu (untuk dapat id AUTO_INCREMENT), baru UPDATE
     * id_tag. Kalau langkah kedua gagal, baris placeholder-nya harus ikut
     * hilang -- jangan sampai ada baris ber-id_tag "TMP-..." yang nyangkut.
     *
     * @return array{dibuat: array, gagal: array}
     */
    public function createTagsBulk(int $idEvent, int $idItem, string $area, ?int $createdBy, int $qty): array
    {
        $prefix = 'STO' . date('ymd') . '-' . $idEvent;
        $dibuat = [];
        $gagal  = [];

        // Sengaja TIDAK memakai transStatus() sebagai penanda gagal per baris:
        // transBegin() mereset transFailure tapi tidak mereset transStatus,
        // jadi sekali satu baris gagal, seluruh baris sesudahnya ikut terbaca
        // gagal walaupun query-nya mulus. Hasil tiap query dicek langsung.
        for ($urutan = 1; $urutan <= $qty; $urutan++) {
            $this->db->transBegin();

            try {
                $ok = $this->db->table('sto_data')->insert([
                    'id_tag'     => 'TMP-' . uniqid('', true),
                    'id_event'   => $idEvent,
                    'id_item'    => $idItem,
                    'area'       => $area,
                    'created_by' => $createdBy,
                    // created_at diisi MySQL (DEFAULT CURRENT_TIMESTAMP)
                ]);

                $newId = (int) $this->db->insertID();

                if ($ok === false || $newId <= 0) {
                    $gagal[] = $this->catatGagalBulk($urutan);
                    $this->db->transRollback();

                    continue;
                }

                $idTag = $prefix . $newId;

                $ok = $this->db->table('sto_data')->where('id', $newId)->update(['id_tag' => $idTag]);

                if ($ok === false) {
                    $gagal[] = $this->catatGagalBulk($urutan);
                    $this->db->transRollback();

                    continue;
                }
            } catch (DatabaseException $e) {
                $gagal[] = $this->catatGagalBulk($urutan, $e->getMessage());
                $this->db->transRollback();

                continue;
            }

            $this->db->transCommit();
            $dibuat[] = ['id' => $newId, 'id_tag' => $idTag];
        }

        return ['dibuat' => $dibuat, 'gagal' => $gagal];
    }

    /**
     * Rangkum satu kegagalan pada print-tag-bulk.
     *
     * Pesan teknis dari driver dicatat ke log, bukan dikirim ke pemanggil --
     * yang dikembalikan cuma keterangan singkat yang aman dibaca aplikasi.
     */
    private function catatGagalBulk(int $urutan, ?string $detail = null): array
    {
        $err = $this->db->error();
        $this->lastError = $err;

        log_message(
            'error',
            'print-tag-bulk gagal pada urutan ke-' . $urutan . ': '
            . ($detail ?? ($err['message'] ?? 'penyebab tidak diketahui'))
        );

        $pesan = 'Gagal menyimpan tag';

        if (isset($err['code']) && (int) $err['code'] === self::ERR_DUPLICATE) {
            $pesan = 'id_tag hasil bentukan bentrok dengan tag yang sudah ada';
        }

        return ['urutan' => $urutan, 'message' => $pesan];
    }

    /**
     * Ambil banyak tag sekaligus berdasarkan daftar id_tag.
     * Dipakai print-tag-bulk supaya tidak perlu N kali getTag().
     *
     * @return array|false
     */
    public function getTagsByIdTags(array $idTags)
    {
        if ($idTags === []) {
            return [];
        }

        $rows = [];

        try {
            foreach (array_chunk($idTags, self::CHUNK_SIZE) as $chunk) {
                if ($chunk === []) {
                    continue;
                }

                $hasil = $this->builderTagLengkap()
                    ->whereIn('s.id_tag', $chunk)
                    ->orderBy('s.id', 'ASC')
                    ->get()
                    ->getResultArray();

                foreach ($hasil as $row) {
                    $rows[] = $row;
                }
            }
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::getTagsByIdTags gagal: ' . $e->getMessage());

            return false;
        }

        return $rows;
    }

    // =================================================================
    // PENGAJUAN PEMBATALAN
    // =================================================================

    /**
     * Ajukan pembatalan: is_canceled 0 -> 2, beserta alasan & pengajunya.
     *
     * WHERE-nya menyertakan `is_canceled = 0` supaya tidak menimpa tag yang
     * sudah dibatalkan (1) atau sudah diajukan orang lain (2) -- controller
     * sudah mengecek lebih dulu, ini lapis pengaman kalau ada dua request
     * berbarengan.
     *
     * @return int|false jumlah baris berubah
     */
    public function requestCancel(string $idTag, string $reason, int $requestedBy)
    {
        return $this->ubahStatusPembatalan($idTag, 0, [
            'is_canceled'         => 2,
            'cancel_reason'       => $reason,
            'cancel_requested_by' => $requestedBy,
            'cancel_requested_at' => date('Y-m-d H:i:s'),
            'cancel_approved_by'  => null,
        ], 'requestCancel');
    }

    /**
     * Setujui pengajuan: is_canceled 2 -> 1.
     *
     * @return int|false
     */
    public function approveCancel(string $idTag, int $approvedBy)
    {
        return $this->ubahStatusPembatalan($idTag, 2, [
            'is_canceled'        => 1,
            'cancel_approved_by' => $approvedBy,
        ], 'approveCancel');
    }

    /**
     * Tolak pengajuan: is_canceled 2 -> 0, jejak pengajuan dibersihkan
     * supaya tag benar-benar kembali ke keadaan normal.
     *
     * Penolakan TIDAK meninggalkan riwayat di tabel ini -- hanya tercatat
     * di log aplikasi.
     *
     * @return int|false
     */
    public function rejectCancel(string $idTag)
    {
        return $this->ubahStatusPembatalan($idTag, 2, [
            'is_canceled'         => 0,
            'cancel_reason'       => null,
            'cancel_requested_by' => null,
            'cancel_requested_at' => null,
            'cancel_approved_by'  => null,
        ], 'rejectCancel');
    }

    /**
     * Badan bersama request/approve/reject -- ketiganya cuma beda status
     * awal yang disyaratkan dan kolom yang ditulis.
     *
     * @return int|false
     */
    private function ubahStatusPembatalan(string $idTag, int $dariStatus, array $data, string $asal)
    {
        $this->db->transBegin();

        try {
            $this->db->table('sto_data')
                ->where('id_tag', $idTag)
                ->where('is_canceled', $dariStatus)
                ->update($data);

            $affected = (int) $this->db->affectedRows();

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'ScanModel::' . $asal . ' gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return $affected;
    }

    /**
     * Daftar pengajuan pembatalan yang masih menunggu keputusan.
     *
     * @param array $f [area, limit]
     *
     * @return array|false
     */
    public function getCancelRequests(array $f)
    {
        $limit = (int) $f['limit'];
        if ($limit <= 0) {
            $limit = self::DEFAULT_LIMIT;
        }
        if ($limit > self::MAX_LIMIT) {
            $limit = self::MAX_LIMIT;
        }

        try {
            $builder = $this->builderTagLengkap()->where('s.is_canceled', 2);

            if ($f['area'] !== null) {
                $builder->where('s.area', $f['area']);
            }

            return $builder->orderBy('s.cancel_requested_at', 'ASC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'ScanModel::getCancelRequests gagal: ' . $e->getMessage());

            return false;
        }
    }
}
