<?php

namespace App\Controllers\Api\Sto;

use App\Models\Sto\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pengelolaan akun STO -- majsf_sto.users.
 *
 *   GET  /api/sto/user-list
 *   POST /api/sto/user-update
 *   POST /api/sto/user-delete
 *
 * Keduanya wajib mengirim `nik` milik user ber-role admin. Perhatikan
 * pembagian parameternya:
 *
 *   nik       -> ADMIN yang menjalankan permintaan
 *   id_user   -> user SASARAN (boleh diganti nik_user)
 *
 * Pendaftaran akun ada di Auth::register, bukan di sini.
 */
class Users extends BaseSto
{
    public function __construct()
    {
        $this->users = new UserModel();
    }

    /**
     * GET /api/sto/user-list?nik=A.001&q=&limit=
     *
     * Daftar akun. Hanya admin.
     *
     * Bentuk tiap baris sama dengan objek user pada login, lengkap dengan
     * daftar area -- jadi layar Setting > User bisa memakai parser yang sama.
     */
    public function list(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $errors = [];

        $q = $this->cleanString($this->q('q'));

        $rawLimit = $this->cleanString($this->q('limit'));
        $limit    = UserModel::DEFAULT_LIMIT;

        if ($rawLimit !== '') {
            if (! ctype_digit($rawLimit) || (int) $rawLimit <= 0) {
                $errors[] = 'limit harus bilangan bulat lebih besar dari 0';
            } else {
                $limit = (int) $rawLimit;
            }
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        $rows = $this->users->getUserList([
            'q'     => $q === '' ? null : $q,
            'limit' => $limit,
        ]);

        if ($rows === false) {
            return $this->gagal('Gagal mengambil daftar user', 500);
        }

        $data = [];

        foreach ($rows as $row) {
            $areas = $row['__areas'];
            unset($row['__areas']);
            $data[] = $this->formatUser($row, $areas);
        }

        return $this->ok([
            'status'         => 'success',
            'message'        => 'Daftar user',
            'limit_terpakai' => min($limit, UserModel::MAX_LIMIT),
            'total_row'      => count($data),
            'data'           => $data,
        ]);
    }

    /**
     * POST /api/sto/user-update
     *
     * Body: {"nik":"A.001","id_user":3,"role":"leader","tim":"B"}
     *
     * Partial update -- hanya field yang dikirim yang diubah.
     * Sasaran ditunjuk lewat `id_user` ATAU `nik_user`.
     *
     * `area` bersifat GANTI SELURUHNYA, bukan tambah: daftar yang dikirim
     * menjadi satu-satunya area user tsb. Kirim array kosong untuk melepas
     * semua area. Tidak dikirim = area dibiarkan apa adanya.
     */
    public function update(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        [$sasaran, $tolak] = $this->cariUserSasaran($this->in('id_user'), $this->in('nik_user'));
        if ($tolak !== null) {
            return $tolak;
        }

        $errors = [];
        $data   = [];

        if ($this->in('role') !== null) {
            $role = $this->cleanString($this->in('role'));

            if ($role === '') {
                $errors[] = 'role tidak boleh dikosongkan';
            } elseif (strlen($role) > self::MAX_ROLE) {
                $errors[] = 'role maksimal ' . self::MAX_ROLE . ' karakter';
            } else {
                $data['role'] = $role;
            }
        }

        // Dikirim sebagai "" atau null -> tim dikosongkan.
        if ($this->in('tim') !== null) {
            $tim = $this->parseTim($this->in('tim'));

            if ($tim === false) {
                $errors[] = 'tim harus "A" atau "B"';
            } else {
                $data['tim'] = $tim;
            }
        }

        // Dikirim sebagai "" atau null -> perangkat dilepas.
        if ($this->in('device_id') !== null) {
            $rawDevice = $this->cleanString($this->in('device_id'));

            if ($rawDevice === '') {
                $data['device_id'] = null;
            } else {
                $deviceId = $this->parseId($this->in('device_id'));

                if ($deviceId === false) {
                    $errors[] = 'device_id harus berupa angka';
                } else {
                    [$device, $gagal] = $this->pastikanDeviceAda($deviceId);

                    if ($gagal !== null) {
                        return $gagal;
                    }

                    $data['device_id'] = $deviceId;
                }
            }
        }

        // permissions: dikirim kosong = hak akses dilepas
        if ($this->in('permissions') !== null) {
            $permissions = $this->parsePermissions($this->in('permissions'));

            if ($permissions === false) {
                $errors[] = 'permissions maksimal ' . UserModel::MAX_PERMISSIONS
                    . ' karakter setelah digabung';
            } else {
                $data['permissions'] = $permissions === '' ? null : $permissions;
            }
        }

        // area: dikirim = ganti seluruh daftar; tidak dikirim = jangan disentuh
        $rawArea = $this->in('area') ?? $this->in('areas');
        $areas   = null;

        if ($rawArea !== null) {
            $areas = $this->normalizeList($rawArea);

            if (count($areas) > self::MAX_AREA) {
                $errors[] = 'jumlah area melebihi batas ' . self::MAX_AREA;
            }

            foreach ($areas as $a) {
                if (strlen($a) > 50) {
                    $errors[] = 'area "' . $a . '" melebihi 50 karakter';
                    break;
                }
            }
        }

        // NIK sengaja TIDAK bisa diubah -- lihat penjelasan di tolakUbahNik().
        if ($this->in('nik_baru') !== null) {
            return $this->tolakUbahNik();
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        if ($data === [] && $areas === null) {
            return $this->gagal(
                'Tidak ada field yang diubah. Kirim minimal satu dari role, permissions, '
                . 'tim, device_id, atau area.',
                400
            );
        }

        if ($this->users->updateUser((int) $sasaran['id'], $data, $areas) === false) {
            return $this->gagal('Gagal memperbarui user, perubahan dibatalkan (rollback)', 500);
        }

        $diubah = array_keys($data);
        if ($areas !== null) {
            $diubah[] = 'area';
        }

        log_message(
            'info',
            'User STO diperbarui: nik=' . $sasaran['nik'] . ' field=' . implode(',', $diubah)
        );

        $baru     = $this->users->getById((int) $sasaran['id']);
        $areaBaru = $this->users->getAreas((int) $sasaran['id']);

        return $this->ok([
            'status'  => 'success',
            'message' => 'User berhasil diperbarui',
            'diubah'  => $diubah,
            'data'    => $this->formatUser(
                is_array($baru) ? $baru : $sasaran,
                is_array($areaBaru) ? $areaBaru : null
            ),
        ]);
    }

    /**
     * POST /api/sto/user-delete
     *
     * Body: {"nik":"A.001","id_user":3}
     *
     * Ditahan selama user masih meninggalkan jejak (pernah mencetak tag,
     * pernah men-scan, punya area, atau pernah mendaftarkan akun lain),
     * kecuali dikirim "force": true.
     */
    public function delete(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        [$sasaran, $tolak] = $this->cariUserSasaran($this->in('id_user'), $this->in('nik_user'));
        if ($tolak !== null) {
            return $tolak;
        }

        // Admin tidak boleh menghapus akunnya sendiri -- gampang terjadi
        // tidak sengaja, dan bisa membuat sistem kehilangan admin terakhir.
        if ((int) $sasaran['id'] === (int) $admin['id']) {
            return $this->gagal('Tidak bisa menghapus akun Anda sendiri', 409);
        }

        $jejak = $this->users->getUserJejak((int) $sasaran['id'], (string) $sasaran['nik']);
        $total = $jejak['tag_dicetak'] + $jejak['scan'] + $jejak['area'] + $jejak['akun_didaftarkan'];
        $force = $this->toBool($this->in('force'));

        if ($total > 0 && $force !== true) {
            return $this->ok([
                'status'  => 'confirm',
                'message' => 'User ini masih meninggalkan jejak. Menghapusnya TIDAK '
                             . 'menghapus tag maupun riwayat scan-nya, tapi area user '
                             . 'ikut terhapus dan jejak pencetak pada tag dikosongkan. '
                             . 'Kirim ulang dengan "force": true bila memang dikehendaki.',
                'id_user' => (int) $sasaran['id'],
                'nik'     => (string) $sasaran['nik'],
                'jejak'   => $jejak,
                'data'    => $this->formatUser($sasaran, null),
            ]);
        }

        if ($this->users->deleteUser((int) $sasaran['id']) === false) {
            return $this->gagal('Gagal menghapus user, perubahan dibatalkan (rollback)', 500);
        }

        log_message(
            'info',
            'User STO dihapus: nik=' . $sasaran['nik'] . ' oleh ' . $admin['nik']
            . ' (jejak: ' . $total . ')'
        );

        return $this->ok([
            'status'  => 'success',
            'message' => 'User berhasil dihapus',
            'id_user' => (int) $sasaran['id'],
            'nik'     => (string) $sasaran['nik'],
            'jejak'   => $jejak,
            'data'    => $this->formatUser($sasaran, null),
        ]);
    }

    /**
     * Cari user sasaran untuk user-update / user-delete.
     *
     * Sasaran ditunjuk lewat `id_user` ATAU `nik_user` -- sengaja bukan
     * `nik`, karena `nik` pada body sudah dipakai untuk mengidentifikasi
     * ADMIN yang menjalankan permintaan.
     *
     * @param mixed $rawId
     * @param mixed $rawNik
     *
     * @return array{0: array|null, 1: ResponseInterface|null} [user sasaran, response gagal]
     */
    private function cariUserSasaran($rawId, $rawNik): array
    {
        $idUser  = $this->parseId($rawId);
        $nikUser = $this->cleanString($rawNik);

        if ($idUser === false) {
            return [null, $this->gagal('Parameter "id_user" harus berupa angka', 400)];
        }

        if ($idUser === null && $nikUser === '') {
            return [null, $this->gagal(
                'Isi salah satu: "id_user" atau "nik_user" untuk menunjuk user yang '
                . 'dimaksud. (Parameter "nik" adalah admin yang menjalankan permintaan ini.)',
                400
            )];
        }

        $user = $idUser !== null
            ? $this->users->getById($idUser)
            : $this->users->getByNik($nikUser);

        if ($user === false) {
            return [null, $this->gagal('Gagal membaca data user', 500)];
        }

        if ($user === null) {
            return [null, $this->gagal(
                $idUser !== null
                    ? 'user dengan id ' . $idUser . ' tidak ditemukan'
                    : 'NIK "' . $nikUser . '" tidak terdaftar',
                404
            )];
        }

        return [$user, null];
    }

    /**
     * Tolak permintaan mengubah NIK, beserta alasannya.
     *
     * `sto_data.nik_a` dan `nik_b` menyimpan NIK sebagai TEKS biasa, bukan
     * foreign key. Kalau NIK diubah, seluruh riwayat scan lama masih memuat
     * NIK lama dan jadi yatim -- tidak bisa lagi ditelusuri ke akunnya, dan
     * tidak ada mekanisme cascade yang memperbaikinya.
     */
    private function tolakUbahNik(): ResponseInterface
    {
        return $this->gagal(
            'NIK tidak bisa diubah. sto_data.nik_a / nik_b menyimpan NIK sebagai teks, '
            . 'jadi mengubahnya akan memutus riwayat scan lama dari akun ini. Hapus akun '
            . 'lalu daftarkan yang baru bila NIK-nya memang keliru.',
            409
        );
    }
}
