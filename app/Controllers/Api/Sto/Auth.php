<?php

namespace App\Controllers\Api\Sto;

use App\Models\Sto\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Akun STO -- majsf_sto.users + users_area.
 *
 *   POST /api/sto/register
 *   POST /api/sto/login
 *
 * Tidak memerlukan role admin.
 */
class Auth extends BaseSto
{
    /**
     * POST /api/sto/register
     *
     * Body: {"nik":"S.10445","role":"counter","created_by":"A.001",
     *        "tim":"A","device_id":1,"area":["IFPD","IFPP"]}
     *
     * Akun STO tidak punya password -- identitas cukup NIK.
     *
     * `created_by` WAJIB berisi NIK admin yang mendaftarkan. Hanya role
     * admin yang boleh membuat akun; id admin tsb disimpan di kolom
     * users.created_by sebagai jejak siapa yang mendaftarkan.
     *
     * `tim`, `device_id`, dan `area` opsional.
     */
    public function register(): ResponseInterface
    {
        $this->users = new UserModel();

        // Pendaftaran hanya boleh oleh admin. Dicek paling awal supaya
        // request yang ditolak tidak sempat menyentuh database sama sekali.
        [$pendaftar, $tolak] = $this->isAdmin($this->in('created_by'), 'created_by');
        if ($tolak !== null) {
            return $tolak;
        }

        $errors = [];

        $nik  = $this->cleanString($this->in('nik'));
        $role = $this->cleanString($this->in('role'));

        if ($nik === '') {
            $errors[] = 'nik wajib diisi';
        } elseif (strlen($nik) > self::MAX_NIK) {
            $errors[] = 'nik maksimal ' . self::MAX_NIK . ' karakter';
        }

        if ($role === '') {
            $errors[] = 'role wajib diisi';
        } elseif (strlen($role) > self::MAX_ROLE) {
            $errors[] = 'role maksimal ' . self::MAX_ROLE . ' karakter';
        }

        // tim opsional; kalau diisi harus A atau B
        $tim = $this->parseTim($this->in('tim'));
        if ($tim === false) {
            $errors[] = 'tim harus "A" atau "B"';
        }

        // device_id opsional; kalau diisi harus terdaftar di tabel devices
        $deviceId = $this->parseId($this->in('device_id'));
        if ($deviceId === false) {
            $errors[] = 'device_id harus berupa angka';
        }

        // permissions opsional; array atau string dipisah koma
        $permissions = $this->parsePermissions($this->in('permissions'));
        if ($permissions === false) {
            $errors[] = 'permissions maksimal ' . UserModel::MAX_PERMISSIONS
                . ' karakter setelah digabung';
        }

        // area opsional, boleh array atau string dipisah koma
        $rawArea = $this->in('area') ?? $this->in('areas');
        $areas   = $rawArea === null ? [] : $this->normalizeList($rawArea);

        if (count($areas) > self::MAX_AREA) {
            $errors[] = 'jumlah area melebihi batas ' . self::MAX_AREA;
        }

        foreach ($areas as $a) {
            if (strlen($a) > 50) {
                $errors[] = 'area "' . $a . '" melebihi 50 karakter';
                break;
            }
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        // Pastikan device terdaftar dulu, supaya pesannya jelas dan bukan
        // sekadar error foreign key dari driver.
        if ($deviceId !== null) {
            [$device, $gagal] = $this->pastikanDeviceAda($deviceId);
            if ($gagal !== null) {
                return $gagal;
            }
        }

        // Cek duplikat NIK lebih dulu supaya pesannya jelas.
        $existing = $this->users->getByNik($nik);

        if ($existing === false) {
            return $this->gagal('Gagal memeriksa data user', 500);
        }

        if ($existing !== null) {
            return $this->respond([
                'status'  => 'failed',
                'message' => 'NIK "' . $nik . '" sudah terdaftar',
                'data'    => $this->formatUser($existing, null),
            ], 409);
        }

        $data = [
            'nik'        => $nik,
            'role'       => $role,
            'tim'        => $tim,
            'device_id'  => $deviceId,
            'permissions' => ($permissions === null || $permissions === '') ? null : $permissions,
            'created_by' => (int) $pendaftar['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $idUser = $this->users->createUser($data, $areas);

        if ($idUser === false) {
            // Bisa jadi kalah balapan dgn request lain yg mendaftar NIK sama.
            $err = $this->users->lastError;

            if (isset($err['code']) && (int) $err['code'] === UserModel::ERR_DUPLICATE) {
                return $this->gagal('NIK "' . $nik . '" sudah terdaftar', 409);
            }

            return $this->gagal('Gagal menyimpan user, perubahan dibatalkan (rollback)', 500);
        }

        // Ambil ulang supaya nilai bentukan DB (id, created_at) ikut terkirim.
        $user  = $this->users->getByNik($nik);
        $saved = $this->users->getAreas($idUser);

        return $this->ok([
            'status'  => 'success',
            'message' => 'User berhasil didaftarkan',
            'data'    => $this->formatUser(
                is_array($user) ? $user : $data,
                is_array($saved) ? $saved : $areas
            ),
        ], 201);
    }

    /**
     * POST /api/sto/login
     *
     * Body: {"nik":"S.10445","android_id":"a1b2c3d4e5f60718"}
     *
     * Tanpa password. Keduanya WAJIB. Urutannya: cek NIK dulu, baru cek
     * perangkat.
     *
     * `android_id` dicocokkan dengan android_id milik perangkat yang
     * TERDAFTAR pada user tsb. Tidak cocok -> ditolak 403
     * "Anda tidak terdaftar di perangkat ini".
     *
     * Login TIDAK mengubah perangkat user. Penugasan perangkat dilakukan
     * lewat register (`device_id`) atau device-update, bukan di sini --
     * kalau login bisa menugaskan ulang, pemeriksaan android_id jadi
     * tidak ada gunanya.
     */
    public function login(): ResponseInterface
    {
        $this->users = new UserModel();

        $errors = [];

        $nik = $this->cleanString($this->in('nik'));
        if ($nik === '') {
            $errors[] = 'nik wajib diisi';
        }

        // android_id sengaja belum divalidasi di sini: wajib atau tidaknya
        // bergantung pada role, dan role baru diketahui setelah NIK dicari.
        $androidId = $this->cleanString($this->in('android_id'));

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        $user = $this->users->getByNik($nik);

        if ($user === false) {
            return $this->gagal('Gagal membaca data user', 500);
        }

        if ($user === null) {
            return $this->gagal('NIK "' . $nik . '" tidak terdaftar', 404);
        }

        // ---------------------------------------------------------------
        // NIK sudah ketemu -> baru cek perangkatnya.
        //
        // Admin dikecualikan: boleh login dari perangkat mana pun, jadi
        // android_id tidak diperiksa dan tidak wajib dikirim. Untuk admin
        // perangkat memang tidak dipakai sebagai pembatas -- mereka perlu
        // bisa masuk dari HT mana saja saat menangani masalah di lapangan.
        //
        // Selain admin, dicek terhadap perangkat yang TERDAFTAR pada user
        // (hasil JOIN devices), bukan terhadap tabel devices secara umum.
        // User yang belum punya perangkat juga ditolak -- memang belum
        // terdaftar di perangkat manapun.
        // ---------------------------------------------------------------
        $isAdmin = strtolower(trim((string) $user['role'])) === self::ROLE_ADMIN;

        if (! $isAdmin) {
            if ($androidId === '') {
                return $this->gagalValidasi(['android_id wajib diisi']);
            }

            $terdaftar = $user['android_id'] === null ? '' : (string) $user['android_id'];

            if ($terdaftar === '' || $terdaftar !== $androidId) {
                return $this->respond([
                    'status'           => 'failed',
                    'message'          => 'Anda tidak terdaftar di perangkat ini',
                    'nik'              => $nik,
                    'android_id'       => $androidId,
                    'device_terdaftar' => $user['device_name'] === null ? '' : (string) $user['device_name'],
                ], 403);
            }
        }

        $areas = $this->users->getAreas((int) $user['id']);

        if ($areas === false) {
            return $this->gagal('Gagal membaca area user', 500);
        }

        return $this->ok([
            'status'       => 'success',
            'message'      => 'Login berhasil',
            'device_dicek' => ! $isAdmin,
            'data'         => $this->formatUser($user, $areas),
        ]);
    }
}
