<?php

namespace App\Controllers\Api\Sto;

use App\Models\Sto\DeviceModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CRUD majsf_sto.devices -- perangkat (HT/tablet) petugas STO.
 *
 *   GET  /api/sto/device-list
 *   GET  /api/sto/device-detail
 *   POST /api/sto/device-create
 *   POST /api/sto/device-update
 *   POST /api/sto/device-delete
 *
 * SELURUH endpoint di sini wajib mengirim `nik` milik user ber-role admin.
 * Pemeriksaan dilakukan SEBELUM validasi input, jadi request yang ditolak
 * tidak pernah menyentuh tabel devices.
 */
class Devices extends BaseSto
{
    private DeviceModel $devices;

    public function __construct()
    {
        $this->devices = new DeviceModel();
    }

    /**
     * GET /api/sto/device-list?nik=A.001&q=ifpd&limit=50
     *
     * Tiap baris menyertakan `total_user` -- jumlah user yang device_id-nya
     * menunjuk ke perangkat tsb.
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
        $limit    = DeviceModel::DEFAULT_LIMIT;

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

        $rows = $this->devices->getDevices([
            'q'     => $q === '' ? null : $q,
            'limit' => $limit,
        ]);

        if ($rows === false) {
            return $this->gagal('Gagal mengambil daftar device', 500);
        }

        return $this->ok([
            'status'         => 'success',
            'message'        => 'Daftar device',
            'limit_terpakai' => min($limit, DeviceModel::MAX_LIMIT),
            'total_row'      => count($rows),
            'data'           => $rows,
        ]);
    }

    /**
     * GET /api/sto/device-detail?nik=A.001&id_device=1
     *  atau ?nik=A.001&android_id=a1b2c3d4e5f60718
     *
     * Pencarian lewat `android_id` disediakan karena itulah yang diketahui
     * aplikasi di perangkat -- id numerik database tidak dipegang klien.
     */
    public function detail(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $id = $this->parseId($this->q('id_device'));
        if ($id === null) {
            $id = $this->parseId($this->q('id'));
        }

        if ($id === false) {
            return $this->gagal('Parameter "id_device" harus berupa angka', 400);
        }

        $androidId = $this->cleanString($this->q('android_id'));

        if ($id === null && $androidId === '') {
            return $this->gagal('Isi salah satu: "id_device" atau "android_id"', 400);
        }

        $device = $id !== null
            ? $this->devices->getDevice($id)
            : $this->devices->getByAndroidId($androidId);

        if ($device === false) {
            return $this->gagal('Gagal membaca data device', 500);
        }

        if ($device === null) {
            return $this->gagal(
                $id !== null
                    ? 'device dengan id ' . $id . ' tidak ditemukan'
                    : 'device dengan android_id "' . $androidId . '" tidak ditemukan',
                404
            );
        }

        return $this->ok([
            'status'  => 'success',
            'message' => 'Detail device',
            'data'    => $device,
        ]);
    }

    /**
     * POST /api/sto/device-create
     *
     * Body: {"nik":"A.001","name":"HT IFPD 01","android_id":"a1b2c3d4e5f60718"}
     */
    public function create(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $errors = [];

        $nama = $this->cleanString($this->in('name'));
        if ($nama === '') {
            $errors[] = 'name wajib diisi';
        } elseif (strlen($nama) > DeviceModel::MAX_NAME) {
            $errors[] = 'name maksimal ' . DeviceModel::MAX_NAME . ' karakter';
        }

        $androidId = $this->cleanString($this->in('android_id'));
        if ($androidId === '') {
            $errors[] = 'android_id wajib diisi';
        } elseif (strlen($androidId) > DeviceModel::MAX_ANDROID_ID) {
            $errors[] = 'android_id maksimal ' . DeviceModel::MAX_ANDROID_ID . ' karakter';
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        // Cek duplikat lebih dulu supaya pesannya jelas.
        $existing = $this->devices->getByAndroidId($androidId);

        if ($existing === false) {
            return $this->gagal('Gagal memeriksa data device', 500);
        }

        if ($existing !== null) {
            return $this->respond([
                'status'  => 'failed',
                'message' => 'android_id "' . $androidId . '" sudah terdaftar',
                'data'    => $existing,
            ], 409);
        }

        $id = $this->devices->createDevice([
            'name'       => $nama,
            'android_id' => $androidId,
            // created_at diisi MySQL (DEFAULT CURRENT_TIMESTAMP)
        ]);

        if ($id === false) {
            // Bisa jadi kalah balapan dgn request lain yg mendaftar android_id sama.
            $err = $this->devices->lastError;

            if (isset($err['code']) && (int) $err['code'] === DeviceModel::ERR_DUPLICATE) {
                return $this->gagal('android_id "' . $androidId . '" sudah terdaftar', 409);
            }

            return $this->gagal('Gagal menyimpan device, perubahan dibatalkan (rollback)', 500);
        }

        log_message('info', 'Device STO dibuat: id=' . $id . ' "' . $nama . '"');

        $device = $this->devices->getDevice($id);

        return $this->ok([
            'status'  => 'success',
            'message' => 'Device berhasil dibuat',
            'data'    => is_array($device) ? $device : null,
        ], 201);
    }

    /**
     * POST /api/sto/device-update
     *
     * Body: {"nik":"A.001","id_device":1,"name":"HT IFPD 01 (ganti)"}
     *
     * Partial update -- hanya field yang dikirim yang diubah.
     */
    public function update(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $id = $this->parseId($this->in('id_device'));
        if ($id === null) {
            $id = $this->parseId($this->in('id'));
        }

        if ($id === false || $id === null) {
            return $this->gagal('Parameter "id_device" wajib diisi berupa angka', 400);
        }

        $lama = $this->devices->getDevice($id);

        if ($lama === false) {
            return $this->gagal('Gagal membaca data device', 500);
        }

        if ($lama === null) {
            return $this->gagal('device dengan id ' . $id . ' tidak ditemukan', 404);
        }

        $errors = [];
        $data   = [];

        if ($this->in('name') !== null) {
            $nama = $this->cleanString($this->in('name'));

            if ($nama === '') {
                $errors[] = 'name tidak boleh dikosongkan';
            } elseif (strlen($nama) > DeviceModel::MAX_NAME) {
                $errors[] = 'name maksimal ' . DeviceModel::MAX_NAME . ' karakter';
            } else {
                $data['name'] = $nama;
            }
        }

        if ($this->in('android_id') !== null) {
            $androidId = $this->cleanString($this->in('android_id'));

            if ($androidId === '') {
                $errors[] = 'android_id tidak boleh dikosongkan';
            } elseif (strlen($androidId) > DeviceModel::MAX_ANDROID_ID) {
                $errors[] = 'android_id maksimal ' . DeviceModel::MAX_ANDROID_ID . ' karakter';
            } else {
                $data['android_id'] = $androidId;
            }
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        if ($data === []) {
            return $this->gagal(
                'Tidak ada field yang diubah. Kirim minimal satu dari name atau android_id.',
                400
            );
        }

        // android_id UNIQUE -- pastikan tidak bentrok dgn perangkat LAIN.
        if (isset($data['android_id']) && $data['android_id'] !== $lama['android_id']) {
            $bentrok = $this->devices->getByAndroidId($data['android_id']);

            if ($bentrok === false) {
                return $this->gagal('Gagal memeriksa data device', 500);
            }

            if ($bentrok !== null) {
                return $this->respond([
                    'status'  => 'failed',
                    'message' => 'android_id "' . $data['android_id'] . '" sudah dipakai device lain',
                    'data'    => $bentrok,
                ], 409);
            }
        }

        if ($this->devices->updateDevice($id, $data) === false) {
            return $this->gagal('Gagal memperbarui device, perubahan dibatalkan (rollback)', 500);
        }

        log_message('info', 'Device STO diperbarui: id=' . $id . ' field=' . implode(',', array_keys($data)));

        $baru = $this->devices->getDevice($id);

        return $this->ok([
            'status'  => 'success',
            'message' => 'Device berhasil diperbarui',
            'diubah'  => array_keys($data),
            'data'    => is_array($baru) ? $baru : null,
        ]);
    }

    /**
     * POST /api/sto/device-delete
     *
     * Body: {"nik":"A.001","id_device":1}
     *
     * FK users.device_id memakai ON DELETE SET NULL, jadi menghapus perangkat
     * TIDAK menghapus user-nya -- user kehilangan keterikatan perangkat.
     * Karena itu penghapusan DITAHAN selama masih ada user yang memakainya,
     * kecuali dikirim "force": true.
     */
    public function delete(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $id = $this->parseId($this->in('id_device'));
        if ($id === null) {
            $id = $this->parseId($this->in('id'));
        }

        if ($id === false || $id === null) {
            return $this->gagal('Parameter "id_device" wajib diisi berupa angka', 400);
        }

        $device = $this->devices->getDevice($id);

        if ($device === false) {
            return $this->gagal('Gagal membaca data device', 500);
        }

        if ($device === null) {
            return $this->gagal('device dengan id ' . $id . ' tidak ditemukan', 404);
        }

        $jmlUser = $this->devices->countUsers($id);
        $force   = $this->toBool($this->in('force'));

        if ($jmlUser > 0 && $force !== true) {
            return $this->ok([
                'status'     => 'confirm',
                'message'    => 'Device ini masih dipakai ' . $jmlUser . ' user. Menghapusnya '
                                . 'TIDAK menghapus user tsb, tapi device_id pada user akan '
                                . 'dikosongkan. Kirim ulang dengan "force": true bila memang '
                                . 'dikehendaki.',
                'id'         => $id,
                'total_user' => $jmlUser,
                'data'       => $device,
            ]);
        }

        if ($this->devices->deleteDevice($id) === false) {
            return $this->gagal('Gagal menghapus device, perubahan dibatalkan (rollback)', 500);
        }

        log_message(
            'info',
            'Device STO dihapus: id=' . $id . ' "' . $device['name'] . '"'
            . ' (' . $jmlUser . ' user dilepas)'
        );

        return $this->ok([
            'status'       => 'success',
            'message'      => 'Device berhasil dihapus',
            'id'           => $id,
            'user_dilepas' => $jmlUser,
            'data'         => $device,
        ]);
    }
}
