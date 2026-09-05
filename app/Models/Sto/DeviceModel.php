<?php

namespace App\Models\Sto;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * CRUD majsf_sto.devices -- perangkat (HT/tablet) petugas STO.
 *
 * `users.device_id` menunjuk ke tabel ini dengan ON DELETE SET NULL, jadi
 * menghapus perangkat TIDAK menghapus user-nya: user kehilangan keterikatan
 * perangkat. Karena itu jumlah user yang memakainya ikut dihitung dan dipakai
 * controller sebagai pengaman sebelum menghapus.
 */
class DeviceModel extends Model
{
    protected $DBGroup    = 'db_sto';
    protected $table      = 'devices';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['name', 'android_id', 'created_at'];

    protected $useTimestamps = false;

    /** Batas aman jumlah baris per request. */
    public const MAX_LIMIT     = 500;
    public const DEFAULT_LIMIT = 100;

    /** Panjang maksimal kolom. */
    public const MAX_NAME       = 100;
    public const MAX_ANDROID_ID = 64;

    /** Kode error MySQL untuk pelanggaran UNIQUE key. */
    public const ERR_DUPLICATE = 1062;

    public array $lastError = [];

    /**
     * Builder dasar: kolom device + jumlah user yang memakainya.
     * LEFT JOIN supaya perangkat yang belum dipakai siapapun tetap muncul.
     */
    private function baseBuilder()
    {
        return $this->db->table('devices d')
            ->select('d.id, d.name, d.android_id, d.created_at, d.updated_at, '
                . 'COUNT(u.id) AS total_user', false)
            ->join('users u', 'u.device_id = d.id', 'left');
    }

    /** Rapikan satu baris jadi bentuk response yang konsisten. */
    private function shape(array $row): array
    {
        return [
            'id'         => (int) $row['id'],
            'name'       => (string) $row['name'],
            'android_id' => (string) $row['android_id'],
            'total_user' => isset($row['total_user']) ? (int) $row['total_user'] : 0,
            'created_at' => $row['created_at'] === null ? '' : (string) $row['created_at'],
            'updated_at' => $row['updated_at'] === null ? '' : (string) $row['updated_at'],
        ];
    }

    /**
     * Daftar perangkat. Semua filter opsional.
     *
     * @param array $f [q, limit]
     *
     * @return array|false
     */
    public function getDevices(array $f)
    {
        $limit = (int) $f['limit'];
        if ($limit <= 0) {
            $limit = self::DEFAULT_LIMIT;
        }
        if ($limit > self::MAX_LIMIT) {
            $limit = self::MAX_LIMIT;
        }

        try {
            $builder = $this->baseBuilder();

            if ($f['q'] !== null) {
                // Cocok pada nama ATAU android_id, digroup supaya tidak bocor
                // keluar dari kondisi lain di WHERE.
                $builder->groupStart()
                    ->like('d.name', $f['q'], 'both')
                    ->orLike('d.android_id', $f['q'], 'both')
                    ->groupEnd();
            }

            // id adalah PK tabel d, jadi group by PK saja sudah sah termasuk
            // di bawah ONLY_FULL_GROUP_BY (functional dependency).
            $rows = $builder->groupBy('d.id')
                ->orderBy('d.name', 'ASC')
                ->orderBy('d.id', 'ASC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'DeviceModel::getDevices gagal: ' . $e->getMessage());

            return false;
        }

        return array_map([$this, 'shape'], $rows);
    }

    /**
     * @return array|null|false
     */
    public function getDevice(int $id)
    {
        try {
            $row = $this->baseBuilder()
                ->where('d.id', $id)
                ->groupBy('d.id')
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (DatabaseException $e) {
            log_message('error', 'DeviceModel::getDevice gagal: ' . $e->getMessage());

            return false;
        }

        return $row ? $this->shape($row) : null;
    }

    /**
     * Cari perangkat berdasarkan android_id.
     *
     * Ini kunci pencarian yang paling wajar dari sisi aplikasi Android --
     * perangkat tahu ANDROID_ID miliknya, bukan id numerik di database.
     *
     * @return array|null|false
     */
    public function getByAndroidId(string $androidId)
    {
        try {
            $row = $this->baseBuilder()
                ->where('d.android_id', $androidId)
                ->groupBy('d.id')
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (DatabaseException $e) {
            log_message('error', 'DeviceModel::getByAndroidId gagal: ' . $e->getMessage());

            return false;
        }

        return $row ? $this->shape($row) : null;
    }

    /**
     * Jumlah user yang memakai perangkat ini.
     * Dipakai sebagai pengaman sebelum menghapus.
     */
    public function countUsers(int $id): int
    {
        try {
            return (int) $this->db->table('users')->where('device_id', $id)->countAllResults();
        } catch (DatabaseException $e) {
            log_message('error', 'DeviceModel::countUsers gagal: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * @return int|false id baru
     */
    /**
     * Lepas satu perangkat dari SEMUA user yang memakainya.
     *
     * `users.device_id` dikosongkan, bukan barisnya dihapus -- melepas
     * perangkat tidak boleh menghilangkan akunnya. Setelah ini user yang
     * bersangkutan tidak bisa login sampai diberi perangkat lain, kecuali
     * ia admin (admin bebas perangkat).
     *
     * Padanan CI3: M_sto_device::unassign_all()
     *
     * @return int|false jumlah user yang terlepas
     */
    public function unassignAll(int $id)
    {
        $this->lastError = [];
        $this->db->transBegin();

        try {
            $ok = $this->db->table('users')
                ->where('device_id', $id)
                ->update(['device_id' => null]);

            if ($ok === false) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];
            $this->db->transRollback();

            return false;
        }

        $jml = (int) $this->db->affectedRows();
        $this->db->transCommit();

        return $jml;
    }

    /**
     * Lepas perangkat dari satu user berdasarkan NIK.
     *
     * Syarat `device_id IS NOT NULL` ikut dipasang supaya affectedRows()
     * benar-benar berarti "ada yang dilepas" -- tanpa itu, user yang memang
     * belum punya perangkat tetap terhitung.
     *
     * Padanan CI3: M_sto_device::unassign_user()
     *
     * @return int|false jumlah user yang terlepas (0 atau 1)
     */
    public function unassignUser(string $nik)
    {
        $this->lastError = [];
        $this->db->transBegin();

        try {
            $ok = $this->db->table('users')
                ->where('nik', $nik)
                ->where('device_id IS NOT NULL', null, false)
                ->update(['device_id' => null]);

            if ($ok === false) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = ['code' => (int) $e->getCode(), 'message' => $e->getMessage()];
            $this->db->transRollback();

            return false;
        }

        $jml = (int) $this->db->affectedRows();
        $this->db->transCommit();

        return $jml;
    }

    public function createDevice(array $data)
    {
        $this->lastError = [];
        $this->db->transBegin();

        try {
            $this->db->table('devices')->insert($data);
            $id = (int) $this->db->insertID();

            if ($this->db->transStatus() === false || $id <= 0) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = $this->db->error();
            $this->db->transRollback();
            log_message('error', 'DeviceModel::createDevice gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return $id;
    }

    /**
     * Perbarui perangkat. Hanya kolom yang ada di $data yang disentuh.
     */
    public function updateDevice(int $id, array $data): bool
    {
        if ($data === []) {
            return false; // guard: jangan jalankan UPDATE tanpa isi
        }

        $this->lastError = [];
        $this->db->transBegin();

        try {
            $this->db->table('devices')->where('id', $id)->update($data);

            if ($this->db->transStatus() === false) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = $this->db->error();
            $this->db->transRollback();
            log_message('error', 'DeviceModel::updateDevice gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return true;
    }

    /**
     * Hapus perangkat.
     *
     * FK users.device_id memakai ON DELETE SET NULL, jadi user yang memakai
     * perangkat ini TIDAK ikut terhapus -- hanya device_id-nya dikosongkan.
     * Controller yang memutuskan boleh/tidaknya penghapusan dilakukan.
     */
    public function deleteDevice(int $id): bool
    {
        $this->lastError = [];
        $this->db->transBegin();

        try {
            $this->db->table('devices')->where('id', $id)->delete();

            if ($this->db->transStatus() === false) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = $this->db->error();
            $this->db->transRollback();
            log_message('error', 'DeviceModel::deleteDevice gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return true;
    }
}
