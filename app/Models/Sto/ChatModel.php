<?php

namespace App\Models\Sto;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * Pesan dua arah antara operator dan admin (majsf_sto.chat_messages).
 *
 * Percakapannya sengaja dangkal - satu operator = satu utas, dan SEMUA admin
 * membaca utas yang sama. Di lapangan operator tidak peduli admin mana yang
 * menjawab; yang ia butuhkan jawabannya datang. Utas khusus 'BROADCAST' dipakai
 * pengumuman: hanya admin yang boleh menulis, semua orang membaca.
 *
 * Penjagaan spam ada di sini, bukan di aplikasi: aturan di aplikasi bisa
 * dilewati dengan memanggil API langsung, dan mengubahnya berarti memasang
 * ulang APK ke puluhan handheld.
 *
 * Padanan CI3: application/models/sto/M_sto_chat.php
 */
class ChatModel extends Model
{
    protected $DBGroup    = 'db_sto';
    protected $table      = 'chat_messages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = false;

    /** Utas pengumuman - bukan NIK siapa pun, jadi tidak mungkin bentrok. */
    public const UTAS_BROADCAST = 'BROADCAST';

    public const MAX_PANJANG   = 1000;
    public const MAX_LIMIT     = 200;
    public const DEFAULT_LIMIT = 50;

    /** Penjagaan spam. */
    public const MAX_PER_MENIT  = 10;
    public const JEDA_DETIK     = 2;
    public const JENDELA_KEMBAR = 300; // pesan sama persis dalam 5 menit ditolak

    public array $lastError = [];

    // ------------------------------------------------------------- kirim

    /**
     * Memeriksa penjagaan spam untuk satu pengirim.
     *
     * @return string|null alasan penolakan, atau null bila boleh mengirim
     */
    public function alasanTolak(string $nik, string $body, ?string $mutedUntil = null): ?string
    {
        if ($mutedUntil !== null && $mutedUntil !== '') {
            if (strtotime($mutedUntil) > time()) {
                return 'Akun ini sedang dibisukan admin sampai '
                       . date('d/m/Y H:i', strtotime($mutedUntil));
            }
        }

        try {
            // Berapa pesan dalam satu menit terakhir.
            $sejak  = date('Y-m-d H:i:s', time() - 60);
            $jumlah = $this->db->table('chat_messages')
                ->where('from_nik', $nik)
                ->where('created_at >=', $sejak)
                ->countAllResults();

            if ($jumlah >= self::MAX_PER_MENIT) {
                return 'Terlalu banyak pesan dalam semenit terakhir. Tunggu '
                       . 'sebentar sebelum mengirim lagi.';
            }

            // Pesan terakhir: dipakai untuk jeda minimum sekaligus tolak kembar.
            $query = $this->db->table('chat_messages')
                ->select('body, created_at')
                ->where('from_nik', $nik)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get();

            if ($query === false) {
                $this->lastError = $this->db->error();

                return null; // gangguan baca jangan sampai memblokir pesan
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return null;
        }

        $akhir = $query->getRowArray();

        if ($akhir === null) {
            return null;
        }

        $jarak = time() - strtotime($akhir['created_at']);

        if ($jarak < self::JEDA_DETIK) {
            return 'Terlalu cepat. Beri jeda sedikit antar pesan.';
        }

        if ($jarak < self::JENDELA_KEMBAR && $akhir['body'] === $body) {
            return 'Pesan yang sama baru saja dikirim.';
        }

        return null;
    }

    /**
     * Menyimpan satu pesan.
     *
     * @return int|false id pesan yang tersimpan
     */
    public function kirim(string $thread, string $fromNik, string $body, bool $broadcast = false)
    {
        $this->lastError = [];

        try {
            $ok = $this->db->table('chat_messages')->insert([
                'thread'       => $thread,
                'from_nik'     => $fromNik,
                'body'         => $body,
                'is_broadcast' => $broadcast ? 1 : 0,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);

            if ($ok === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        return (int) $this->db->insertID();
    }

    // ------------------------------------------------------------- baca

    /**
     * Pesan pada satu utas.
     *
     * $afterId > 0 hanya mengambil yang lebih baru - itu yang dipakai layar
     * percakapan saat menyegarkan, supaya tidak menarik ulang seluruh utas
     * setiap beberapa detik.
     *
     * @return array<int, array<string, mixed>>|false
     */
    public function pesan(string $thread, int $afterId = 0, int $limit = self::DEFAULT_LIMIT)
    {
        $this->lastError = [];

        if ($limit <= 0) {
            $limit = self::DEFAULT_LIMIT;
        }

        if ($limit > self::MAX_LIMIT) {
            $limit = self::MAX_LIMIT;
        }

        $builder = $this->db->table('chat_messages')->where('thread', $thread);

        if ($afterId > 0) {
            $builder->where('id >', $afterId)->orderBy('id', 'ASC');
        } else {
            // Tanpa after_id: ambil yang terbaru, lalu dibalik supaya urutan
            // yang dikirim ke aplikasi tetap lama -> baru.
            $builder->orderBy('id', 'DESC');
        }

        try {
            $query = $builder->limit($limit)->get();

            if ($query === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        $rows = $query->getResultArray();

        if ($afterId <= 0) {
            $rows = array_reverse($rows);
        }

        return $rows;
    }

    /**
     * Daftar utas yang ada beserta pesan terakhirnya.
     *
     * @return array<int, array<string, mixed>>|false
     */
    public function utasAktif(int $limit = self::MAX_LIMIT)
    {
        $this->lastError = [];

        $sql = 'SELECT m.thread, m.id AS last_id, m.body AS last_body, '
             . 'm.from_nik AS last_from, m.created_at AS last_at '
             . 'FROM chat_messages m '
             . 'JOIN (SELECT thread, MAX(id) AS id FROM chat_messages '
             . '      GROUP BY thread) t ON t.id = m.id '
             . 'ORDER BY m.id DESC LIMIT ' . $limit;

        try {
            $query = $this->db->query($sql);

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
     * Batas baca terakhir user per utas: array(thread => last_read_id).
     *
     * @return array<string, int>
     */
    public function batasBaca(string $nik): array
    {
        $this->lastError = [];

        try {
            $query = $this->db->table('chat_reads')
                ->select('thread, last_read_id')
                ->where('nik', $nik)
                ->get();

            if ($query === false) {
                $this->lastError = $this->db->error();

                return [];
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return [];
        }

        $hasil = [];

        foreach ($query->getResultArray() as $row) {
            $hasil[$row['thread']] = (int) $row['last_read_id'];
        }

        return $hasil;
    }

    /**
     * Jumlah pesan belum dibaca per utas, tidak menghitung pesan sendiri.
     *
     * @param array<int, string> $threads utas yang ingin dihitung
     *
     * @return array<string, int>
     */
    public function belumDibaca(string $nik, array $threads): array
    {
        $this->lastError = [];

        if ($threads === []) {
            return [];
        }

        $batas = $this->batasBaca($nik);
        $hasil = [];

        foreach ($threads as $thread) {
            $sejak = $batas[$thread] ?? 0;

            $hasil[$thread] = (int) $this->db->table('chat_messages')
                ->where('thread', $thread)
                ->where('id >', $sejak)
                ->where('from_nik !=', $nik)
                ->countAllResults();
        }

        return $hasil;
    }

    /** Menandai utas sudah dibaca sampai id tertentu. */
    public function tandaiDibaca(string $nik, string $thread, int $lastId): bool
    {
        $this->lastError = [];

        // Mundur tidak diizinkan: penanda baca hanya boleh maju, supaya
        // perangkat yang tertinggal tidak membuat pesan terbaca jadi
        // "belum dibaca" lagi di perangkat lain.
        $sql = 'INSERT INTO chat_reads (nik, thread, last_read_id, updated_at) '
             . 'VALUES (?, ?, ?, ?) '
             . 'ON DUPLICATE KEY UPDATE '
             . 'last_read_id = GREATEST(last_read_id, VALUES(last_read_id)), '
             . 'updated_at = VALUES(updated_at)';

        try {
            $ok = $this->db->query($sql, [$nik, $thread, $lastId, date('Y-m-d H:i:s')]);

            if ($ok === false) {
                $this->lastError = $this->db->error();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];

            return false;
        }

        return true;
    }
}
