<?php

namespace App\Models\Sto;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * Keadaan CETAK satu tag STO -- kolom `print_status`, `print_error`, dan
 * `printed_at` pada majsf_sto.sto_data.
 *
 * Kenapa dicatat di server, bukan di perangkat: tag yang gagal keluar dari
 * printer (kertas habis, antrean tertahan) tetap harus terlihat oleh admin
 * dan oleh perangkat lain. Kalau keadaannya hanya disimpan di HT yang
 * mencetak, tag itu hilang begitu aplikasinya dipasang ulang -- padahal
 * barisnya sudah terlanjur ada di sto_data.
 *
 *   draft   -> tag sudah dibuat, lembarannya BELUM keluar dari printer
 *   printed -> lembarannya sudah keluar
 *   error   -> percobaan cetak gagal; alasannya di `print_error`
 *
 * Model ini sengaja terpisah dari ScanModel supaya perubahan di sini tidak
 * bersinggungan dengan alur scan.
 *
 * Padanan CI3: application/models/sto/M_sto_print.php
 */
class PrintModel extends Model
{
    protected $DBGroup    = 'db_sto';
    protected $table      = 'sto_data';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = false;

    public const MAX_LIMIT     = 500;
    public const DEFAULT_LIMIT = 100;

    /** Status yang sah; nilai lain ditolak sebelum menyentuh database. */
    public const STATUS_SAH = ['draft', 'printed', 'error'];

    /** Batas jumlah id_tag dalam satu permintaan ubah status. */
    public const MAX_TAG = 100;

    /** Panjang kolom print_error. */
    public const MAX_PESAN = 255;

    public array $lastError = [];

    /**
     * Ubah keadaan cetak beberapa tag sekaligus.
     *
     * Tag yang sudah dibatalkan sengaja TIDAK ikut diubah -- keadaan cetaknya
     * tidak lagi berarti, dan menandainya "printed" hanya akan menyesatkan
     * ringkasan.
     *
     * @param array<int, mixed> $idTags
     *
     * @return array{diubah: int, tidak_ditemukan: array<int, string>}|false
     */
    public function setStatus(array $idTags, string $status, ?string $pesan = null)
    {
        $this->lastError = [];

        $bersih = [];

        foreach ($idTags as $satu) {
            $satu = trim((string) $satu);

            if ($satu !== '' && ! in_array($satu, $bersih, true)) {
                $bersih[] = $satu;
            }
        }

        if ($bersih === []) {
            return ['diubah' => 0, 'tidak_ditemukan' => []];
        }

        $data = [
            'print_status' => $status,
            'print_error'  => ($status === 'error' && $pesan !== null && $pesan !== '')
                                 ? mb_substr($pesan, 0, self::MAX_PESAN)
                                 : null,
            // Waktu cetak hanya diisi saat benar-benar tercetak; draft/error
            // mengosongkannya lagi supaya tidak ada tag "tercetak tanpa keluar".
            'printed_at'   => $status === 'printed' ? date('Y-m-d H:i:s') : null,
        ];

        try {
            $ada = $this->db->table('sto_data')
                ->select('id_tag')
                ->whereIn('id_tag', $bersih)
                ->get();

            if ($ada === false) {
                $this->lastError = $this->db->error();

                return false;
            }

            $ditemukan = [];

            foreach ($ada->getResultArray() as $row) {
                $ditemukan[] = (string) $row['id_tag'];
            }

            if ($ditemukan === []) {
                return ['diubah' => 0, 'tidak_ditemukan' => $bersih];
            }

            $ok = $this->db->table('sto_data')
                ->whereIn('id_tag', $ditemukan)
                ->where('is_canceled !=', 1)
                ->update($data);

            if ($ok === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        return [
            'diubah'          => (int) $this->db->affectedRows(),
            'tidak_ditemukan' => array_values(array_diff($bersih, $ditemukan)),
        ];
    }

    /**
     * Riwayat cetak beserta identitas materialnya.
     *
     * Filter `nik` menyaring PEMBUAT tag (sto_data.created_by -> users.nik),
     * bukan pemindainya -- yang ditanyakan halaman riwayat cetak adalah "tag
     * apa saja yang saya cetak", termasuk yang masih draft.
     *
     * @param array<string, mixed> $filter
     *
     * @return array<int, array<string, mixed>>|false
     */
    public function getPrintHistory(array $filter)
    {
        $this->lastError = [];

        $limit = isset($filter['limit']) ? (int) $filter['limit'] : self::DEFAULT_LIMIT;

        if ($limit <= 0) {
            $limit = self::DEFAULT_LIMIT;
        }

        if ($limit > self::MAX_LIMIT) {
            $limit = self::MAX_LIMIT;
        }

        $builder = $this->db->table('sto_data d')
            ->select('d.id_tag, d.id_event, d.area, d.print_status, d.print_error,'
                . ' d.printed_at, d.is_canceled, d.cancel_reason, d.created_at,'
                . ' d.cancel_requested_at,'
                . ' pengaju.nik AS cancel_requested_nik,'
                . ' penyetuju.nik AS cancel_approved_nik,'
                . ' u.nik AS created_by_nik,'
                . ' e.event_name,'
                . ' m.id AS id_item, m.part_number, m.job_number,'
                . ' m.material_description, m.type, m.customer, m.model,'
                . ' m.plant, m.status_part', false)
            ->join('master_data m', 'm.id = d.id_item', 'left')
            ->join('users u', 'u.id = d.created_by', 'left')
            ->join('users pengaju', 'pengaju.id = d.cancel_requested_by', 'left')
            ->join('users penyetuju', 'penyetuju.id = d.cancel_approved_by', 'left')
            ->join('events e', 'e.id_event = d.id_event', 'left');

        if (! empty($filter['nik'])) {
            $builder->where('u.nik', $filter['nik']);
        }

        if (! empty($filter['status'])) {
            $builder->whereIn('d.print_status', $filter['status']);
        }

        if (! empty($filter['q'])) {
            // Pencarian bebas: nomor tag / part / job / nama material.
            $builder->groupStart()
                ->like('d.id_tag', $filter['q'])
                ->orLike('m.part_number', $filter['q'])
                ->orLike('m.job_number', $filter['q'])
                ->orLike('m.material_description', $filter['q'])
                ->groupEnd();
        }

        if (! empty($filter['area'])) {
            $builder->where('d.area', $filter['area']);
        }

        if (! empty($filter['id_event'])) {
            $builder->where('d.id_event', (int) $filter['id_event']);
        }

        if (! empty($filter['id_tag'])) {
            $builder->where('d.id_tag', $filter['id_tag']);
        }

        if (isset($filter['sertakan_batal']) && $filter['sertakan_batal'] === false) {
            $builder->where('d.is_canceled', 0);
        }

        try {
            $query = $builder
                ->orderBy('d.created_at', 'DESC')
                ->orderBy('d.id', 'DESC')
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
     * Ringkasan jumlah tag per keadaan cetak -- dipakai kartu ringkasan di
     * aplikasi supaya angkanya datang dari server, bukan hitungan lokal.
     *
     * @param array<string, mixed> $filter
     *
     * @return array<string, int>|false
     */
    public function getPrintSummary(array $filter)
    {
        $this->lastError = [];

        $builder = $this->db->table('sto_data d')
            ->select('d.print_status, d.is_canceled, COUNT(*) AS jumlah', false)
            ->join('users u', 'u.id = d.created_by', 'left');

        if (! empty($filter['nik'])) {
            $builder->where('u.nik', $filter['nik']);
        }

        if (! empty($filter['area'])) {
            $builder->where('d.area', $filter['area']);
        }

        if (! empty($filter['id_event'])) {
            $builder->where('d.id_event', (int) $filter['id_event']);
        }

        try {
            $query = $builder->groupBy(['d.print_status', 'd.is_canceled'])->get();

            if ($query === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        $hasil = [
            'total'          => 0,
            'draft'          => 0,
            'printed'        => 0,
            'error'          => 0,
            'diajukan_batal' => 0,
            'dibatalkan'     => 0,
        ];

        foreach ($query->getResultArray() as $row) {
            $jumlah = (int) $row['jumlah'];
            $hasil['total'] += $jumlah;

            $batal = (int) $row['is_canceled'];

            if ($batal === 1) {
                $hasil['dibatalkan'] += $jumlah;

                continue;
            }

            // is_canceled = 2 (konvensi endpoint cancel-request): pengajuan
            // masih menunggu keputusan admin - tag belum batal, tetapi juga
            // tidak boleh dianggap beres.
            if ($batal === 2) {
                $hasil['diajukan_batal'] += $jumlah;

                continue;
            }

            $status = (string) $row['print_status'];

            if (isset($hasil[$status])) {
                $hasil[$status] += $jumlah;
            }
        }

        return $hasil;
    }
}
