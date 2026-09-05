<?php

namespace App\Controllers\Api\Sto;

use App\Models\Sto\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use DateTime;

/**
 * Induk seluruh controller STO: helper input, validasi, dan gerbang admin.
 *
 * Beda utama dengan versi CI3:
 *  - CI4 tidak menggabungkan body JSON ke dalam POST. Method in() di sini
 *    yang menyatukannya, jadi endpoint tetap menerima JSON maupun
 *    x-www-form-urlencoded persis seperti sebelumnya.
 *  - Tidak ada `translate_uri_dashes`. URL berdash didaftarkan eksplisit
 *    di app/Config/Routes.php.
 */
abstract class BaseSto extends Controller
{
    use ResponseTrait;

    /** Role yang boleh mengakses CRUD device & event (dibandingkan lowercase). */
    protected const ROLE_ADMIN = 'admin';

    /** Batas maksimal id dalam satu request cancel. */
    protected const MAX_IDS = 5000;

    /** Batas panjang kolom pada majsf_sto.users. */
    protected const MAX_NIK  = 50;
    protected const MAX_ROLE = 50;

    /** Batas jumlah area yang boleh dikirim saat register. */
    protected const MAX_AREA = 100;

    /** Batas jumlah tag yang boleh dicetak dalam satu print-tag-bulk. */
    protected const MAX_BULK = 5;

    /** Panjang maksimal alasan pengajuan pembatalan (kolom VARCHAR(255)). */
    protected const MAX_REASON = 255;

    /** Cache body request supaya tidak di-parse berulang. */
    private ?array $payload = null;

    protected ?UserModel $users = null;

    // =================================================================
    // INPUT
    // =================================================================

    /**
     * Isi body request, apapun bentuknya.
     *
     * CI4 memisahkan body JSON dari $_POST, jadi keduanya disatukan di sini.
     * JSON diprioritaskan; kalau body bukan JSON, dipakai data form.
     */
    protected function body(): array
    {
        if ($this->payload === null) {
            $json = null;

            if (strpos((string) $this->request->getHeaderLine('Content-Type'), 'json') !== false) {
                $json = $this->request->getJSON(true);
            }

            if (is_array($json)) {
                $this->payload = $json;
            } else {
                $post          = $this->request->getPost();
                $this->payload = is_array($post) ? $post : [];
            }
        }

        return $this->payload;
    }

    /**
     * Satu field dari body request.
     *
     * @return mixed null bila tidak dikirim
     */
    protected function in(string $key)
    {
        $body = $this->body();

        return array_key_exists($key, $body) ? $body[$key] : null;
    }

    /**
     * Satu field dari query string.
     *
     * @return mixed null bila tidak dikirim
     */
    protected function q(string $key)
    {
        return $this->request->getGet($key);
    }

    // =================================================================
    // RESPONSE
    // =================================================================

    protected function ok(array $payload, int $code = 200): ResponseInterface
    {
        return $this->respond($payload, $code);
    }

    protected function gagal(string $message, int $code): ResponseInterface
    {
        return $this->respond(['status' => 'failed', 'message' => $message], $code);
    }

    /**
     * Balasan validasi: seluruh kesalahan dikumpulkan sekaligus,
     * bukan dilaporkan satu per satu.
     */
    protected function gagalValidasi(array $errors): ResponseInterface
    {
        return $this->respond([
            'status'  => 'failed',
            'message' => 'Input tidak valid',
            'errors'  => $errors,
        ], 400);
    }

    // =================================================================
    // VALIDASI DASAR
    // =================================================================

    /**
     * Rapikan input jadi string biasa. Nilai non-scalar (array/object/null)
     * diperlakukan sebagai kosong.
     *
     * @param mixed $value
     */
    protected function cleanString($value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }

    /**
     * Validasi id numerik opsional (id_item, id_event, dsb).
     *
     * @param mixed $value
     *
     * @return int|null|false null bila tidak dikirim, false bila tidak valid
     */
    protected function parseId($value)
    {
        $id = $this->cleanString($value);

        if ($id === '') {
            return null;
        }

        if (! ctype_digit($id) || (int) $id <= 0) {
            return false;
        }

        return (int) $id;
    }

    /**
     * Validasi kolom `tim` (ENUM 'A','B').
     *
     * NULL = tidak dikirim. Wajib/opsional diputuskan pemanggil.
     *
     * @param mixed $value
     *
     * @return string|null|false
     */
    protected function parseTim($value)
    {
        $tim = strtoupper($this->cleanString($value));

        if ($tim === '') {
            return null;
        }

        if ($tim !== 'A' && $tim !== 'B') {
            return false;
        }

        return $tim;
    }

    /**
     * Validasi kolom `status` pada events (TINYINT 0/1).
     *
     * @param mixed $value
     *
     * @return int|null|false
     */
    protected function parseStatus($value)
    {
        $v = $this->cleanString($value);

        if ($v === '') {
            return null;
        }

        if ($v !== '0' && $v !== '1') {
            return false;
        }

        return (int) $v;
    }

    /**
     * Validasi qty. Kolom qty_a / qty_b bertipe INT UNSIGNED, jadi hanya
     * bilangan bulat 0 s/d 4294967295 yang diterima.
     *
     * qty 0 SAH -- itu hasil hitung "kosong", bukan input kosong.
     *
     * @param mixed $value
     *
     * @return int|false
     */
    protected function parseQty($value, int $maxQty)
    {
        $qty = $this->cleanString($value);

        if ($qty === '' || ! ctype_digit($qty)) {
            return false;
        }

        // Bandingkan sbg string dulu supaya angka kelewat besar tidak
        // terlanjur dibulatkan float saat di-cast.
        $maks = (string) $maxQty;

        if (strlen($qty) > strlen($maks) || (strlen($qty) === strlen($maks) && $qty > $maks)) {
            return false;
        }

        return (int) $qty;
    }

    /**
     * Validasi tanggal murni (kolom DATE, tanpa jam).
     * Kosong dianggap NULL -- sah untuk end_date yang memang nullable.
     *
     * @param mixed $value
     *
     * @return string|null|false
     */
    protected function parseDateOnly($value)
    {
        if ($value === null) {
            return null;
        }

        $v = $this->cleanString($value);

        if ($v === '') {
            return null;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $v);

        if ($dt === false || $dt->format('Y-m-d') !== $v) {
            return false;
        }

        return $v;
    }

    /**
     * Ubah input tanggal jadi string 'Y-m-d H:i:s' yang pasti valid.
     *
     * @param mixed $value
     * @param bool  $isEnd kalau true, tanggal tanpa jam dilebarkan ke 23:59:59
     *
     * @return string|false
     */
    protected function parseDatetime($value, bool $isEnd)
    {
        $v = $this->cleanString($value);

        if ($v === '') {
            return false;
        }

        // Format lengkap: 2026-08-29 13:45:00
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $v);
        if ($dt !== false && $dt->format('Y-m-d H:i:s') === $v) {
            return $v;
        }

        // Format tanggal saja: 2026-08-29
        $dt = DateTime::createFromFormat('Y-m-d', $v);
        if ($dt !== false && $dt->format('Y-m-d') === $v) {
            return $v . ($isEnd ? ' 23:59:59' : ' 00:00:00');
        }

        return false;
    }

    /**
     * Terjemahkan input jadi boolean. Menerima true, 1, "1", "true", "ya".
     *
     * @param mixed $value
     */
    protected function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (! is_scalar($value)) {
            return false;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'ya', 'yes', 'y'], true);
    }

    /**
     * Normalisasi input menjadi array string yang bersih & unik.
     *
     * Menerima:
     *  - array  : ["A", "B"]
     *  - string : '["A","B"]' (JSON) atau "A,B" (dipisah koma)
     *
     * @param mixed $raw
     */
    protected function normalizeList($raw): array
    {
        if (is_string($raw)) {
            $trimmed = trim($raw);
            $decoded = json_decode($trimmed, true);

            if (is_array($decoded)) {
                $raw = $decoded;
            } elseif ($trimmed === '') {
                $raw = [];
            } else {
                $raw = explode(',', $trimmed);
            }
        }

        if (! is_array($raw)) {
            return [];
        }

        $clean = [];

        foreach ($raw as $item) {
            // Abaikan array/object bersarang -> input tidak valid untuk satu id.
            if (! is_scalar($item)) {
                continue;
            }

            $id = trim((string) $item);

            if ($id === '') {
                continue;
            }

            $clean[] = $id;
        }

        return array_values(array_unique($clean));
    }

    // =================================================================
    // HAK AKSES
    // =================================================================

    /**
     * Pastikan pengirim request adalah user dengan role admin.
     *
     * Dipakai sebagai gerbang seluruh CRUD device dan event. Kalau tidak
     * lolos, ResponseInterface siap-kirim yang dikembalikan -- pemanggil
     * cukup `return $gagal;`.
     *
     * Perbandingan role dilakukan case-insensitive karena kolomnya VARCHAR
     * bebas, jadi "Admin" dan "ADMIN" ikut diterima.
     *
     * @param mixed  $rawNik    nilai mentah dari q('nik') atau in('nik')
     * @param string $namaParam nama field pada pesan error -- register
     *                          memakai `created_by`, bukan `nik`
     *
     * @return array{0: array|null, 1: ResponseInterface|null} [user, response gagal]
     */
    /**
     * Pastikan NIK terdaftar -- tanpa syarat role.
     *
     * Dipakai endpoint yang boleh diakses siapa pun yang punya akun (riwayat
     * cetak, lapor keadaan cetak, baca setelan printer). Bentuk balasannya
     * sengaja sama dengan isAdmin(): [user, response]. Salah satu selalu null.
     *
     * Padanan CI3: pastikan_user_terdaftar()
     *
     * @return array{0: array<string, mixed>|null, 1: ResponseInterface|null}
     */
    protected function pastikanUserTerdaftar($rawNik, string $namaParam = 'nik'): array
    {
        $this->users ??= new UserModel();

        $nik = $this->cleanString($rawNik);

        if ($nik === '') {
            return [null, $this->gagal('Parameter "' . $namaParam . '" wajib diisi.', 400)];
        }

        $user = $this->users->getByNik($nik);

        if ($user === false) {
            return [null, $this->gagal('Gagal membaca data user', 500)];
        }

        if ($user === null) {
            return [null, $this->gagal('NIK "' . $nik . '" tidak terdaftar', 404)];
        }

        return [$user, null];
    }

    protected function isAdmin($rawNik, string $namaParam = 'nik'): array
    {
        $this->users ??= new UserModel();

        $nik = $this->cleanString($rawNik);

        if ($nik === '') {
            return [null, $this->gagal(
                'Parameter "' . $namaParam . '" wajib diisi. Endpoint ini hanya bisa '
                . 'diakses user dengan role admin.',
                400
            )];
        }

        $user = $this->users->getByNik($nik);

        if ($user === false) {
            return [null, $this->gagal('Gagal membaca data user', 500)];
        }

        if ($user === null) {
            return [null, $this->gagal('NIK "' . $nik . '" tidak terdaftar', 404)];
        }

        if (strtolower(trim((string) $user['role'])) !== self::ROLE_ADMIN) {
            return [null, $this->respond([
                'status'  => 'failed',
                'message' => 'Akses ditolak. Endpoint ini hanya untuk role admin, '
                             . 'sedangkan NIK "' . $nik . '" ber-role "' . $user['role'] . '".',
                'nik'     => $nik,
                'role'    => (string) $user['role'],
            ], 403)];
        }

        return [$user, null];
    }

    /**
     * Pastikan device_id benar-benar terdaftar di tabel devices.
     *
     * Supaya pemanggil dapat pesan yang jelas, bukan sekadar pelanggaran
     * foreign key dari driver.
     *
     * @return array{0: array|null, 1: ResponseInterface|null} [device, response gagal]
     */
    protected function pastikanDeviceAda(int $deviceId): array
    {
        $this->users ??= new UserModel();

        $device = $this->users->getDevice($deviceId);

        if ($device === false) {
            return [null, $this->gagal('Gagal membaca data device', 500)];
        }

        if ($device === null) {
            return [null, $this->gagal(
                'device_id ' . $deviceId . ' tidak terdaftar di tabel devices',
                404
            )];
        }

        return [$device, null];
    }

    /**
     * Validasi kolom users.permissions.
     *
     * Menerima array ["prepare","scan"] maupun string "prepare,scan".
     * Disimpan sebagai string dipisah koma, dikembalikan sebagai array.
     * Nilai kembar dibuang otomatis oleh normalizeList().
     *
     * @param mixed $value
     *
     * @return string|null|false null = tidak dikirim, '' = dikosongkan
     */
    protected function parsePermissions($value)
    {
        if ($value === null) {
            return null;
        }

        $list = $this->normalizeList($value);

        if ($list === []) {
            return ''; // dikirim kosong -> hak akses dilepas
        }

        $gabung = implode(',', $list);

        if (strlen($gabung) > UserModel::MAX_PERMISSIONS) {
            return false;
        }

        return $gabung;
    }

    // =================================================================
    // BENTUK RESPONSE BERSAMA
    // =================================================================

    /**
     * Bentuk response data user yang konsisten.
     *
     * @param array|null $areas null = jangan sertakan field area
     */
    protected function formatUser(array $user, ?array $areas): array
    {
        $str = static fn ($v): string => $v === null ? '' : (string) $v;

        $out = [
            'id'          => isset($user['id']) ? (int) $user['id'] : null,
            'nik'         => $str($user['nik'] ?? null),
            'role'        => $str($user['role'] ?? null),
            // disimpan sbg teks dipisah koma, dikembalikan sbg array
            'permissions' => isset($user['permissions']) && $user['permissions'] !== null && $user['permissions'] !== ''
                                ? array_values(array_filter(array_map('trim', explode(',', $user['permissions'])), 'strlen'))
                                : [],
            'tim'         => $str($user['tim'] ?? null),
            // device_id kolom users; device_name & android_id dari JOIN devices
            'device_id'   => isset($user['device_id']) && $user['device_id'] !== null ? (int) $user['device_id'] : null,
            'device_name' => $str($user['device_name'] ?? null),
            'android_id'  => $str($user['android_id'] ?? null),
            'created_by'  => isset($user['created_by']) && $user['created_by'] !== null ? (int) $user['created_by'] : null,
            'created_at'  => $str($user['created_at'] ?? null),
            'updated_at'  => $str($user['updated_at'] ?? null),
        ];

        if ($areas !== null) {
            $out['area']       = array_values($areas);
            $out['total_area'] = count($areas);
        }

        return $out;
    }

    /**
     * Bentuk response satu baris sto_data yang konsisten.
     * Dipakai bersama oleh print-tag dan scan-tag.
     */
    protected function formatTag(array $row): array
    {
        $str = static fn ($v): string => $v === null ? '' : (string) $v;

        return [
            'id'                   => (int) $row['id'],
            'id_tag'               => (string) $row['id_tag'],
            'id_event'             => $row['id_event'] === null ? null : (int) $row['id_event'],
            'id_item'              => (int) $row['id_item'],
            'area'                 => (string) $row['area'],
            // 4 field di bawah berasal dari master_data lewat JOIN id_item,
            // bukan lagi kolom sto_data.
            'job_number'           => $str($row['job_number'] ?? null),
            'part_number'          => $str($row['part_number'] ?? null),
            'material_description' => $str($row['material_description'] ?? null),
            'type'                 => $str($row['type'] ?? null),
            'status_part'          => $str($row['status_part'] ?? null),
            'customer'             => $str($row['customer'] ?? null),
            'plant'                => $str($row['plant'] ?? null),
            'process'              => $str($row['process'] ?? null),
            'category'             => $str($row['category'] ?? null),
            'model'                => $str($row['model'] ?? null),
            'nik_a'                => $str($row['nik_a']),
            'qty_a'                => (int) $row['qty_a'],
            'updated_a'            => $str($row['updated_a']),
            'nik_b'                => $str($row['nik_b']),
            'qty_b'                => (int) $row['qty_b'],
            'updated_b'            => $str($row['updated_b']),
            'is_canceled'          => (int) $row['is_canceled'],
            // jejak pengajuan pembatalan; kosong selama is_canceled = 0
            'cancel_reason'           => $str($row['cancel_reason'] ?? null),
            'cancel_requested_by'     => isset($row['cancel_requested_by']) && $row['cancel_requested_by'] !== null ? (int) $row['cancel_requested_by'] : null,
            'cancel_requested_by_nik' => $str($row['cancel_requested_by_nik'] ?? null),
            'cancel_requested_at'     => $str($row['cancel_requested_at'] ?? null),
            'cancel_approved_by'      => isset($row['cancel_approved_by']) && $row['cancel_approved_by'] !== null ? (int) $row['cancel_approved_by'] : null,
            'cancel_approved_by_nik'  => $str($row['cancel_approved_by_nik'] ?? null),
            'created_by'           => $row['created_by'] === null ? null : (int) $row['created_by'],
            'created_at'           => $str($row['created_at']),
            'updated_at'           => $str($row['updated_at']),
        ];
    }

    /**
     * Bentuk daftar kandidat item master_data (dipakai saat pencarian ganda).
     */
    protected function formatItems(array $rows): array
    {
        $str = static fn ($v): string => $v === null ? '' : (string) $v;
        $out = [];

        foreach ($rows as $row) {
            $out[] = [
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

        return $out;
    }

    /**
     * Ambil & validasi rentang tanggal WAJIB dari query string.
     *
     * Menerima key `start_date`/`end_date` atau `start`/`end`.
     * Format `Y-m-d` atau `Y-m-d H:i:s`. Kalau hanya tanggal, jam otomatis
     * dilebarkan jadi 00:00:00 s/d 23:59:59 supaya seharian penuh ikut.
     *
     * @return array{0: array|null, 1: ResponseInterface|null} [range, response gagal]
     */
    protected function resolveRange(): array
    {
        $startRaw = $this->q('start_date') ?? $this->q('start');
        $endRaw   = $this->q('end_date') ?? $this->q('end');

        if ($startRaw === null || $endRaw === null
            || $this->cleanString($startRaw) === '' || $this->cleanString($endRaw) === '') {
            return [null, $this->gagal('Parameter "start_date" dan "end_date" wajib diisi', 400)];
        }

        $start = $this->parseDatetime($startRaw, false);
        $end   = $this->parseDatetime($endRaw, true);

        if ($start === false || $end === false) {
            return [null, $this->gagal(
                'Format tanggal tidak valid, gunakan "YYYY-MM-DD" atau "YYYY-MM-DD HH:MM:SS"',
                400
            )];
        }

        if ($start > $end) {
            return [null, $this->gagal('start_date tidak boleh lebih besar dari end_date', 400)];
        }

        return [['start' => $start, 'end' => $end], null];
    }
}
