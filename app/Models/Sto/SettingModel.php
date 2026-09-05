<?php

namespace App\Models\Sto;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * Setelan printer bersama, tabel majsf_sto.printer_settings.
 *
 * Kenapa di server dan bukan di perangkat: jarak kertas, jarak antar tag, dan
 * tarik-mundur harus SAMA di semua handheld. Selama disimpan lokal, tiap HT
 * punya angka sendiri dan hasil cetak antar operator berbeda-beda - tidak ada
 * yang bisa memastikan mana yang benar. Sekarang admin menyetelnya sekali,
 * semua handheld mengikuti.
 *
 * Bentuknya sengaja nama-nilai (bukan satu kolom per setelan) supaya menambah
 * setelan baru tidak perlu mengubah skema tabel.
 *
 * Catatan: 'tarik_awal_baris' pernah ada di sini lalu dibuang. Perintah
 * mundur ESC e tidak dijalankan printer handheld MPOS - huruf 'e'-nya malah
 * ikut tercetak di kepala tag. Jangan dihidupkan lagi tanpa printer yang
 * terbukti mendukungnya.
 *
 * Padanan CI3: application/models/sto/M_sto_setting.php
 */
class SettingModel extends Model
{
    protected $DBGroup    = 'db_sto';
    protected $table      = 'printer_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = false;

    /** Nama setelan yang dikenali; selain ini ditolak. */
    public const NAMA_SAH = [
        'gap_antar_tag_dots',
        'feed_akhir_dots',
        'paper_size',
    ];

    /** Batas nilai per setelan: [min, max]. paper_size ditangani khusus. */
    public const BATAS = [
        'gap_antar_tag_dots' => [0, 255],
        'feed_akhir_dots'    => [0, 255],
    ];

    /** Nilai bawaan bila admin belum pernah menyetel apa pun. */
    public const BAWAAN = [
        'gap_antar_tag_dots' => '96',
        'feed_akhir_dots'    => '0',
        'paper_size'         => 'mm58',
    ];

    public const MAX_PANJANG = 100;

    public array $lastError = [];

    /**
     * Seluruh setelan sebagai array nama => nilai.
     *
     * Setelan yang belum pernah disimpan diisi nilai bawaannya, jadi
     * pemanggil tidak perlu menebak-nebak apa yang hilang.
     *
     * @return array{nilai: array<string, string>, rinci: array<string, array<string, string>>}|false
     */
    public function getAll()
    {
        $this->lastError = [];

        try {
            $query = $this->db->table('printer_settings')
                ->select('nama, nilai, keterangan, updated_at')
                ->get();

            if ($query === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        $hasil = self::BAWAAN;
        $rinci = [];

        foreach ($query->getResultArray() as $row) {
            $nama = (string) $row['nama'];

            if (! in_array($nama, self::NAMA_SAH, true)) {
                continue; // baris asing tidak ikut dikirim
            }

            $hasil[$nama] = (string) $row['nilai'];
            $rinci[$nama] = [
                'nilai'      => (string) $row['nilai'],
                'keterangan' => $row['keterangan'] === null ? '' : (string) $row['keterangan'],
                'updated_at' => $row['updated_at'] === null ? '' : (string) $row['updated_at'],
            ];
        }

        return ['nilai' => $hasil, 'rinci' => $rinci];
    }

    /**
     * Memeriksa satu setelan sebelum disimpan.
     *
     * @return string|false pesan kesalahan, atau false bila nilainya sah
     */
    public function periksa(string $nama, string $nilai)
    {
        if (! in_array($nama, self::NAMA_SAH, true)) {
            return 'setelan "' . $nama . '" tidak dikenal, pilihannya: '
                   . implode(', ', self::NAMA_SAH);
        }

        if ($nama === 'paper_size') {
            if (! in_array($nilai, ['mm58', 'mm80'], true)) {
                return 'paper_size harus mm58 atau mm80';
            }

            return false;
        }

        if (! ctype_digit($nilai)) {
            return $nama . ' harus bilangan bulat >= 0';
        }

        [$min, $max] = self::BATAS[$nama];
        $angka       = (int) $nilai;

        if ($angka < $min || $angka > $max) {
            return $nama . ' harus antara ' . $min . ' dan ' . $max;
        }

        return false;
    }

    /**
     * Menyimpan beberapa setelan sekaligus.
     *
     * Satu transaksi: setelan cetak saling berkaitan (gap dan feed menentukan
     * posisi potong yang sama), jadi separuh tersimpan lebih membingungkan
     * daripada tidak tersimpan sama sekali.
     *
     * @param array<string, string> $pasangan nama => nilai (sudah lolos periksa())
     *
     * @return int|false jumlah setelan yang tersimpan
     */
    public function simpan(array $pasangan, ?int $adminId)
    {
        $this->lastError = [];

        if ($pasangan === []) {
            return 0;
        }

        $this->db->transBegin();

        // Satu baris per nama dijaga oleh UNIQUE(nama), jadi ON DUPLICATE KEY
        // cukup -- tidak perlu SELECT dulu untuk tahu barisnya sudah ada.
        $sql = 'INSERT INTO printer_settings (nama, nilai, updated_by, updated_at)'
               . ' VALUES (?, ?, ?, ?)'
               . ' ON DUPLICATE KEY UPDATE'
               . ' nilai = VALUES(nilai),'
               . ' updated_by = VALUES(updated_by),'
               . ' updated_at = VALUES(updated_at)';

        try {
            foreach ($pasangan as $nama => $nilai) {
                $ok = $this->db->query($sql, [
                    $nama,
                    mb_substr((string) $nilai, 0, self::MAX_PANJANG),
                    $adminId,
                    date('Y-m-d H:i:s'),
                ]);

                if ($ok === false) {
                    $this->lastError = $this->db->error();
                    $this->db->transRollback();

                    return false;
                }
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];
            $this->db->transRollback();

            return false;
        }

        $this->db->transCommit();

        return count($pasangan);
    }
}
