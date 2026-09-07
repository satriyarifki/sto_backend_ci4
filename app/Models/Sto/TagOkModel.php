<?php

namespace App\Models\Sto;

use App\Libraries\TagOkLookup;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * Tag OK (majsf_sto.tag_ok_data) - tag hasil produksi yang sudah dinyatakan
 * OK, dipakai sebagai satuan hitung saat STO.
 *
 * Alurnya dua langkah, dan sengaja dipisah:
 *  1. SIAPKAN - petugas memindai tag OK di lapangan lalu menyetujuinya;
 *     `scan_open` menjadi 1. Artinya "tag ini ada dan siap dihitung".
 *  2. HITUNG  - penghitung memindai tag yang sama lalu mengisi qty fisiknya;
 *     `scan_open` kembali 0 beserta `qty_scan`, `scanned_by`, `scanned_at`.
 *
 * Pemisahan itu yang membuat selisih bisa ditelusuri: tag yang disiapkan tapi
 * tidak pernah dihitung tetap terlihat (scan_open = 1), begitu pula tag yang
 * dihitung tanpa pernah disiapkan tidak mungkin terjadi.
 *
 * JANGAN tertukar dengan App\Models\Inventory\TagOkModel -- yang itu menyentuh
 * majsf_inventory.table_sto_tag_ok untuk endpoint warisan cancel-tag-ok.
 *
 * Padanan CI3: application/models/sto/M_sto_tagok.php
 */
class TagOkModel extends Model
{
    protected $DBGroup    = 'db_sto';
    protected $table      = 'tag_ok_data';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = false;

    public const MAX_LIMIT     = 500;
    public const DEFAULT_LIMIT = 100;

    /** Batas qty hasil hitung; kolomnya INT. */
    public const MAX_QTY = 2147483647;

    /** Panjang maksimal kolom tag_ok_data.area. */
    public const MAX_AREA = 50;

    /**
     * Kode error MySQL untuk pelanggaran UNIQUE key.
     * Dipakai siapkan() untuk membedakan tabrakan uq_tag_event (tag sudah
     * disiapkan) dari kegagalan tulis yang sebenarnya.
     */
    public const ERR_DUPLICATE = 1062;

    /**
     * Kolom yang disalin apa adanya dari majsf_inventory.v_print_tag_ok_all
     * ke majsf_sto.tag_ok_data saat penyiapan.
     *
     * Disalin, bukan dibaca lewat JOIN lintas-database, supaya hasil STO tetap
     * mencerminkan keadaan tag pada saat dihitung -- data produksi di
     * majsf_inventory bisa berubah setelahnya.
     *
     * `create_date` sengaja TIDAK ikut: kolom created_at pada tag_ok_data
     * berarti "kapan disiapkan", bukan kapan tag dicetak produksi.
     */
    public const KOLOM_SALIN = [
        'process',
        'date',
        'shift',
        'line',
        'part_number',
        'job_number',
        'qty_kbn',
        'status',
        'project',
        'customer',
        'user_create',
    ];

    public array $lastError = [];

    /**
     * Satu tag OK berdasarkan kodenya. null bila tidak ada.
     *
     * @return array<string, mixed>|null|false
     */
    public function getById(string $idTagOk, ?int $idEvent = null)
    {
        $this->lastError = [];

        $builder = $this->db->table('tag_ok_data')->where('id_tag_ok', $idTagOk);

        if ($idEvent !== null) {
            $builder->where('id_event', $idEvent);
        }

        try {
            $query = $builder->orderBy('id', 'DESC')->limit(1)->get();

            if ($query === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        return $query->getRowArray();
    }
    /**
     * Detail satu tag OK dari data produksi.
     *
     * Tidak lagi menembak view v_print_tag_ok_all: view itu ber-UNION dan
     * MySQL 5.7 menerapkan filter id_tag_ok baru setelah seluruh isi view
     * dimaterialisasi (~507rb baris, 9,1 detik per tag). TagOkLookup menulis
     * query yang sama dengan filternya didorong ke tiap cabang UNION.
     *
     * @return array<string, mixed>|null|false
     */
    public function getPrepareById(string $idTagOk, ?int $idEvent = null)
    {
        $this->lastError = [];

        try {
            return TagOkLookup::byId($this->db, $idTagOk);
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }
    }

    /**
     * Menyalin satu tag OK dari view produksi ke tag_ok_data -- langkah
     * SIAPKAN yang sesungguhnya, yaitu saat barisnya lahir.
     *
     * buka() di bawah hanya meng-UPDATE baris yang sudah ada, jadi tanpa
     * method ini tag yang belum pernah disiapkan tidak punya baris untuk
     * dibuka sama sekali.
     *
     * Tabrakan uq_tag_event (id_tag_ok + id_event) TIDAK dianggap kegagalan
     * tulis: itu berarti tag sudah disiapkan lebih dulu, dan pemanggil perlu
     * membedakannya lewat lastError['code'] === self::ERR_DUPLICATE.
     *
     * `created_at` sengaja TIDAK ditulis di sini: kolomnya ber-DEFAULT
     * CURRENT_TIMESTAMP dan jam server MySQL sudah GMT+7, jadi biar MySQL
     * sendiri yang mengisinya. `scan_at` dan `opened_at` hanya diisi bila
     * kosong -- keduanya boleh datang dari lapangan (mis. hasil scan luring
     * yang baru terkirim belakangan).
     *
     * @param array<string, mixed> $data baris siap simpan
     *
     * @return int|false id baris baru
     */
    public function siapkan(array $data)
    {
        $this->lastError = [];

        unset($data['created_at']);

        foreach (['scan_at', 'opened_at'] as $kolom) {
            if (! isset($data[$kolom]) || trim((string) $data[$kolom]) === '') {
                $data[$kolom] = date('Y-m-d H:i:s');
            }
        }

        try {
            $ok = $this->db->table('tag_ok_data')->insert($data);

            if ($ok === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            // Driver melempar sebelum sempat mengisi db->error() pada sebagian
            // kasus, jadi kode diambil dari exception-nya.
            $err             = $this->db->error();
            $this->lastError = ($err['code'] ?? 0) !== 0
                ? $err
                : ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        return (int) $this->db->insertID();
    }

    /**
     * Menandai tag OK siap dihitung.
     *
     * @return int|false jumlah baris yang berubah
     */
    public function buka(string $idTagOk, string $nik, ?int $idEvent = null)
    {
        $this->lastError = [];

        $data = [
            'scan_open' => 1,
            'opened_by' => $nik,
            'opened_at' => date('Y-m-d H:i:s'),
        ];

        if ($idEvent !== null) {
            $data['id_event'] = $idEvent;
        }

        try {
            $ok = $this->db->table('tag_ok_data')
                ->where('id_tag_ok', $idTagOk)
                ->where('is_canceled', 0)
                ->update($data);

            if ($ok === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        return (int) $this->db->affectedRows();
    }

    /**
     * Mencatat hasil hitung fisik lalu menutup tag.
     *
     * Hanya tag yang sedang terbuka yang boleh dihitung - itu sebabnya
     * `scan_open = 1` ikut jadi syarat WHERE, bukan sekadar diperiksa di
     * controller: dua handheld bisa memindai tag yang sama bersamaan.
     *
     * @return int|false jumlah baris yang berubah
     */
    public function hitung(string $idTagOk, string $nik, int $qty, ?int $idEvent = null)
    {
        $this->lastError = [];

        $data = [
            'scan_open'  => 0,
            'qty_scan'   => $qty,
            'scanned_by' => $nik,
            'scanned_at' => date('Y-m-d H:i:s'),
        ];

        if ($idEvent !== null) {
            $data['id_event'] = $idEvent;
        }

        try {
            $ok = $this->db->table('tag_ok_data')
                ->where('id_tag_ok', $idTagOk)
                ->where('scan_open', 1)
                ->where('is_canceled', 0)
                ->update($data);

            if ($ok === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        return (int) $this->db->affectedRows();
    }

    /**
     * Mengubah keadaan pembatalan tag OK.
     *
     * $nilai: 2 = pengajuan menunggu keputusan, 1 = dibatalkan,
     *         0 = kembali normal (pengajuan ditolak).
     *
     * $dari dipakai sebagai syarat keadaan awal supaya keputusan ganda tidak
     * saling menimpa - misalnya dua admin menyetujui pengajuan yang sama.
     *
     * @return int|false jumlah baris yang berubah
     */
    public function batal(string $idTagOk, string $nik, ?string $alasan, int $nilai, ?int $dari = null)
    {
        $this->lastError = [];

        $data = [
            'is_canceled'   => $nilai,
            'cancel_reason' => $nilai === 0 ? null : $alasan,
            'canceled_by'   => $nilai === 0 ? null : $nik,
            'canceled_at'   => $nilai === 0 ? null : date('Y-m-d H:i:s'),
        ];

        $builder = $this->db->table('tag_ok_data')->where('id_tag_ok', $idTagOk);

        if ($dari !== null) {
            $builder->where('is_canceled', $dari);
        }

        try {
            $ok = $builder->update($data);

            if ($ok === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        return (int) $this->db->affectedRows();
    }

    /**
     * Daftar tag OK dengan penyaring.
     *
     * @param array<string, mixed> $f open (0/1/null), area, id_event, q, nik, limit
     *
     * @return array<int, array<string, mixed>>|false
     */
    public function getList(array $f)
    {
        $this->lastError = [];

        $builder = $this->db->table('tag_ok_data');

        if (isset($f['batal']) && $f['batal'] !== null && $f['batal'] !== '') {
            $builder->where('is_canceled', (int) $f['batal']);
        }

        if (isset($f['open']) && $f['open'] !== null) {
            $builder->where('scan_open', (int) $f['open']);
        }

        if (! empty($f['area'])) {
            $builder->where('area', $f['area']);
        }

        if (! empty($f['id_event'])) {
            $builder->where('id_event', (int) $f['id_event']);
        }

        if (! empty($f['nik'])) {
            // NIK bisa muncul sebagai penyiap maupun penghitung.
            $builder->groupStart()
                ->where('opened_by', $f['nik'])
                ->orWhere('scanned_by', $f['nik'])
                ->groupEnd();
        }

        if (! empty($f['q'])) {
            $builder->groupStart()
                ->like('id_tag_ok', $f['q'])
                ->orLike('part_number', $f['q'])
                ->orLike('job_number', $f['q'])
                ->groupEnd();
        }

        $limit = isset($f['limit']) ? (int) $f['limit'] : self::DEFAULT_LIMIT;

        if ($limit <= 0) {
            $limit = self::DEFAULT_LIMIT;
        }

        if ($limit > self::MAX_LIMIT) {
            $limit = self::MAX_LIMIT;
        }

        try {
            $query = $builder
                ->orderBy('COALESCE(scanned_at, opened_at, scan_at)', 'DESC', false)
                ->limit($limit)
                ->get();

            if ($query === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        return $query->getResultArray();
    }

    /**
     * Ringkasan jumlah tag OK per keadaan.
     *
     * @param array<string, mixed> $f
     *
     * @return array<string, int>|false
     */
    public function getSummary(array $f)
    {
        $this->lastError = [];

        $builder = $this->db->table('tag_ok_data')
            ->select('scan_open, is_canceled, COUNT(*) AS jumlah, SUM(qty_scan) AS total_qty', false);

        if (! empty($f['area'])) {
            $builder->where('area', $f['area']);
        }

        if (! empty($f['id_event'])) {
            $builder->where('id_event', (int) $f['id_event']);
        }

        try {
            $query = $builder->groupBy(['scan_open', 'is_canceled'])->get();

            if ($query === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        $hasil = [
            'terbuka'   => 0,
            'tertutup'  => 0,
            'batal'     => 0,
            'pengajuan' => 0,
            'total_qty' => 0,
        ];

        foreach ($query->getResultArray() as $row) {
            $jumlah = (int) $row['jumlah'];
            $batal  = (int) $row['is_canceled'];

            if ($batal === 1) {
                // Tag batal tidak ikut angka terbuka/tertutup maupun qty -
                // memasukkannya membuat total hitung terlihat lebih besar
                // dari yang sebenarnya tercatat.
                $hasil['batal'] += $jumlah;

                continue;
            }

            if ($batal === 2) {
                $hasil['pengajuan'] += $jumlah;
            }

            if ((int) $row['scan_open'] === 1) {
                $hasil['terbuka'] += $jumlah;
            } else {
                $hasil['tertutup'] += $jumlah;
            }

            $hasil['total_qty'] += (int) $row['total_qty'];
        }

        return $hasil;
    }
}
