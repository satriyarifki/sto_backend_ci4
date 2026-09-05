<?php

namespace App\Controllers\Api\Sto;

use App\Models\Sto\ChatModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * PESAN (chat) operator <-> admin.
 *
 *   GET  /api/sto/chat-threads   daftar percakapan
 *   GET  /api/sto/chat-messages  isi satu percakapan
 *   POST /api/sto/chat-send      kirim pesan
 *   POST /api/sto/chat-read      tandai sudah dibaca
 *   POST /api/sto/chat-mute      bisukan akun (HANYA admin)
 *
 * Padanan CI3: Sto::chat_threads_get(), chat_messages_get(), chat_send_post(),
 * chat_read_post(), chat_mute_post()
 */
class Chat extends BaseSto
{
    private ChatModel $chat;

    /**
     * GET /api/sto/chat-threads?nik=
     *
     * Daftar percakapan beserta pesan terakhir dan jumlah belum dibaca.
     *
     * Operator hanya melihat dua: utasnya sendiri dan pengumuman. Admin
     * melihat seluruh utas operator - semua admin membaca kotak masuk yang
     * sama, karena di lapangan yang dibutuhkan operator adalah jawabannya
     * datang, bukan jawabannya datang dari admin tertentu.
     */
    public function threads(): ResponseInterface
    {
        $this->chat = new ChatModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $nik   = (string) $user['nik'];
        $admin = strtolower((string) $user['role']) === self::ROLE_ADMIN;

        $aktif = $this->chat->utasAktif();

        if ($aktif === false) {
            return $this->gagal('Gagal membaca daftar pesan', 500);
        }

        $terakhir = [];

        foreach ($aktif as $row) {
            $terakhir[$row['thread']] = $row;
        }

        // Utas yang boleh dilihat user ini.
        $threads = [ChatModel::UTAS_BROADCAST];

        if ($admin) {
            foreach ($aktif as $row) {
                if ($row['thread'] !== ChatModel::UTAS_BROADCAST) {
                    $threads[] = $row['thread'];
                }
            }
        } else {
            $threads[] = $nik;
        }

        $threads = array_values(array_unique($threads));

        $belum = $this->chat->belumDibaca($nik, $threads);

        $data  = [];
        $total = 0;

        foreach ($threads as $thread) {
            $row    = $terakhir[$thread] ?? null;
            $jumlah = isset($belum[$thread]) ? (int) $belum[$thread] : 0;
            $total += $jumlah;

            $data[] = [
                'thread'       => $thread,
                'broadcast'    => $thread === ChatModel::UTAS_BROADCAST ? 1 : 0,
                'last_id'      => $row === null ? 0 : (int) $row['last_id'],
                'last_body'    => $row === null ? '' : (string) $row['last_body'],
                'last_from'    => $row === null ? '' : (string) $row['last_from'],
                'last_at'      => $row === null ? '' : (string) $row['last_at'],
                'belum_dibaca' => $jumlah,
            ];
        }

        return $this->ok([
            'status'       => 'success',
            'message'      => 'Daftar percakapan',
            'total_row'    => count($data),
            'belum_dibaca' => $total,
            'data'         => $data,
        ]);
    }

    /**
     * GET /api/sto/chat-messages?nik=&thread=&after_id=&limit=
     *
     * Isi satu percakapan. `after_id` mengambil yang lebih baru saja - itu
     * yang dipakai saat layar percakapan menyegarkan dirinya.
     */
    public function messages(): ResponseInterface
    {
        $this->chat = new ChatModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $nik    = (string) $user['nik'];
        $admin  = strtolower((string) $user['role']) === self::ROLE_ADMIN;
        $thread = $this->cleanString($this->q('thread'));

        if ($thread === '') {
            $thread = $nik;
        }

        $tolakUtas = $this->bolehBukaUtas($nik, $admin, $thread);
        if ($tolakUtas !== null) {
            return $tolakUtas;
        }

        $rows = $this->chat->pesan(
            $thread,
            (int) $this->cleanString($this->q('after_id')),
            (int) $this->cleanString($this->q('limit'))
        );

        if ($rows === false) {
            return $this->gagal('Gagal membaca pesan', 500);
        }

        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'id'         => (int) $row['id'],
                'thread'     => (string) $row['thread'],
                'from_nik'   => (string) $row['from_nik'],
                'body'       => (string) $row['body'],
                'broadcast'  => (int) $row['is_broadcast'],
                'created_at' => (string) $row['created_at'],
            ];
        }

        return $this->ok([
            'status'    => 'success',
            'message'   => 'Isi percakapan',
            'thread'    => $thread,
            'total_row' => count($data),
            'data'      => $data,
        ]);
    }

    /**
     * POST /api/sto/chat-send
     * Body: {"nik":"M.9276","thread":"M.9276","body":"..."}
     *       thread "BROADCAST" hanya boleh diisi admin.
     */
    public function send(): ResponseInterface
    {
        $this->chat = new ChatModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $nik   = (string) $user['nik'];
        $admin = strtolower((string) $user['role']) === self::ROLE_ADMIN;
        $body  = trim((string) $this->in('body'));

        if ($body === '') {
            return $this->gagal('Pesan kosong tidak dikirim', 400);
        }

        if (strlen($body) > ChatModel::MAX_PANJANG) {
            return $this->gagal(
                'Pesan terlalu panjang (maksimal ' . ChatModel::MAX_PANJANG . ' huruf)',
                400
            );
        }

        $thread = $this->cleanString($this->in('thread'));

        if ($thread === '') {
            $thread = $nik;
        }

        $broadcast = $thread === ChatModel::UTAS_BROADCAST;

        if ($broadcast && ! $admin) {
            return $this->gagal('Hanya admin yang boleh mengirim pengumuman', 403);
        }

        if (! $broadcast) {
            $tolakUtas = $this->bolehBukaUtas($nik, $admin, $thread);
            if ($tolakUtas !== null) {
                return $tolakUtas;
            }
        }

        $muted = $user['chat_muted_until'] ?? null;
        $alasan = $this->chat->alasanTolak($nik, $body, $muted);

        if ($alasan !== null) {
            // 429: penolakannya sementara, bukan permintaan yang salah.
            return $this->gagal($alasan, 429);
        }

        $id = $this->chat->kirim($thread, $nik, $body, $broadcast);

        if ($id === false) {
            return $this->gagal('Gagal mengirim pesan', 500);
        }

        // Pesan sendiri langsung dianggap terbaca - kalau tidak, pengirimnya
        // melihat badge "belum dibaca" atas tulisannya sendiri.
        $this->chat->tandaiDibaca($nik, $thread, $id);

        return $this->ok([
            'status'  => 'success',
            'message' => $broadcast ? 'Pengumuman terkirim' : 'Pesan terkirim',
            'data'    => [
                'id'         => $id,
                'thread'     => $thread,
                'from_nik'   => $nik,
                'body'       => $body,
                'broadcast'  => $broadcast ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * POST /api/sto/chat-read
     * Body: {"nik":"M.9276","thread":"M.9276","last_id":42}
     */
    public function read(): ResponseInterface
    {
        $this->chat = new ChatModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $nik    = (string) $user['nik'];
        $admin  = strtolower((string) $user['role']) === self::ROLE_ADMIN;
        $thread = $this->cleanString($this->in('thread'));

        if ($thread === '') {
            $thread = $nik;
        }

        $tolakUtas = $this->bolehBukaUtas($nik, $admin, $thread);
        if ($tolakUtas !== null) {
            return $tolakUtas;
        }

        $ok = $this->chat->tandaiDibaca(
            $nik,
            $thread,
            (int) $this->cleanString($this->in('last_id'))
        );

        if ($ok === false) {
            return $this->gagal('Gagal menandai pesan dibaca', 500);
        }

        return $this->ok([
            'status'  => 'success',
            'message' => 'Ditandai sudah dibaca',
        ]);
    }

    /**
     * POST /api/sto/chat-mute
     * Body: {"nik":"F.9964","nik_user":"M.9276","menit":60}
     *
     * `menit` 0 melepas pembisuan. Penjagaan terakhir bila ada yang memakai
     * kotak pesan untuk hal di luar pekerjaan.
     */
    public function mute(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $sasaran = $this->cleanString($this->in('nik_user'));

        if ($sasaran === '') {
            return $this->gagal('Parameter "nik_user" wajib diisi', 400);
        }

        $menit  = (int) $this->cleanString($this->in('menit'));
        $sampai = $menit > 0 ? date('Y-m-d H:i:s', time() + ($menit * 60)) : null;

        $db = db_connect('db_sto');
        $db->table('users')
            ->where('nik', $sasaran)
            ->update(['chat_muted_until' => $sampai]);

        if ($db->affectedRows() === 0) {
            return $this->gagal(
                'NIK "' . $sasaran . '" tidak ditemukan atau keadaannya sudah sama',
                404
            );
        }

        return $this->ok([
            'status'  => 'success',
            'message' => $sampai === null ? 'Pembisuan dilepas' : 'Dibisukan sampai ' . $sampai,
            'data'    => [
                'nik'              => $sasaran,
                'chat_muted_until' => $sampai === null ? '' : $sampai,
            ],
        ]);
    }

    /**
     * Utas yang boleh dibuka: pengumuman untuk semua, utas sendiri untuk
     * pemiliknya, utas siapa pun untuk admin.
     *
     * @return ResponseInterface|null null bila boleh
     */
    private function bolehBukaUtas(string $nik, bool $admin, string $thread): ?ResponseInterface
    {
        if ($thread === ChatModel::UTAS_BROADCAST || $admin || $thread === $nik) {
            return null;
        }

        return $this->gagal('Percakapan ini bukan milik Anda', 403);
    }
}
