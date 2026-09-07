<?php

namespace App\Models\Sto;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * majsf_sto.users dan majsf_sto.users_area.
 *
 * Akun STO tidak punya password -- identitas cukup NIK.
 *
 * `users.device_id` menunjuk ke majsf_sto.devices. Nama perangkat dan
 * android_id-nya diambil lewat JOIN, bukan disimpan ulang di users.
 */
class UserModel extends Model
{
    protected $DBGroup    = 'db_sto';
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['nik', 'role', 'permissions', 'tim', 'device_id', 'created_by', 'created_at'];

    protected $useTimestamps = false;

    /** Batas aman jumlah baris per request pada user-list. */
    public const MAX_LIMIT     = 500;
    public const DEFAULT_LIMIT = 100;

    /** Panjang maksimal kolom users.permissions. */
    public const MAX_PERMISSIONS = 64;

    /** Kode error MySQL untuk pelanggaran UNIQUE key. */
    public const ERR_DUPLICATE = 1062;

    /** Isi array error terakhir dari driver, dipakai controller utk bedakan duplikat. */
    public array $lastError = [];

    /**
     * Ambil satu user berdasarkan NIK, lengkap dengan data perangkatnya.
     *
     * @return array|null|false null = tidak ketemu, false = query gagal
     */
    public function getByNik(string $nik)
    {
        try {
            $row = $this->db->table('users u')
                ->select('u.id, u.nik, u.role, u.permissions, u.tim, u.device_id, u.created_by, '
                    . 'u.created_at, u.updated_at, u.chat_muted_until, '
                    . 'd.name AS device_name, d.android_id', false)
                ->join('devices d', 'u.device_id = d.id', 'left')
                ->where('u.nik', $nik)
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (DatabaseException $e) {
            log_message('error', 'UserModel::getByNik gagal: ' . $e->getMessage());

            return false;
        }

        return $row ?: null;
    }

    /**
     * Daftar area milik satu user, sebagai array string.
     *
     * @return array|false
     */
    public function getAreas(int $idUser)
    {
        try {
            $rows = $this->db->table('users_area')
                ->select('area')
                ->where('id_user', $idUser)
                ->orderBy('area', 'ASC')
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'UserModel::getAreas gagal: ' . $e->getMessage());

            return false;
        }

        $areas = [];
        foreach ($rows as $row) {
            $areas[] = (string) $row['area'];
        }

        return $areas;
    }

    /**
     * Buat user baru beserta daftar area-nya, dalam satu transaksi.
     *
     * @return int|false id user baru
     */
    public function createUser(array $data, array $areas = [])
    {
        $this->lastError = [];
        $this->db->transBegin();

        try {
            $this->db->table('users')->insert($data);
            $idUser = (int) $this->db->insertID();

            if ($this->db->transStatus() === false || $idUser <= 0) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }

            if ($areas !== []) {
                $batch = [];

                foreach ($areas as $area) {
                    $batch[] = [
                        'id_user' => $idUser,
                        'area'    => $area,
                        // created_at diisi MySQL (DEFAULT CURRENT_TIMESTAMP)
                    ];
                }

                $this->db->table('users_area')->insertBatch($batch);

                if ($this->db->transStatus() === false) {
                    $this->lastError = $this->db->error();
                    $this->db->transRollback();

                    return false;
                }
            }
        } catch (DatabaseException $e) {
            $this->lastError = $this->db->error();
            $this->db->transRollback();
            log_message('error', 'UserModel::createUser gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return $idUser;
    }

    /**
     * Perbarui perangkat yang dipakai user (dipakai saat login).
     *
     * @param int|null $deviceId null untuk melepas perangkat
     */
    public function updateDevice(int $idUser, ?int $deviceId): bool
    {
        try {
            $this->db->table('users')
                ->where('id', $idUser)
                ->update(['device_id' => $deviceId]);
        } catch (DatabaseException $e) {
            log_message('error', 'UserModel::updateDevice gagal: ' . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Ambil satu perangkat berdasarkan id.
     *
     * Dipakai untuk memastikan device_id yang dikirim benar-benar terdaftar
     * sebelum disimpan -- supaya pesannya jelas, bukan sekadar error FK.
     *
     * Sengaja ada di sini (bukan hanya di DeviceModel) supaya register/login
     * tidak perlu memuat model kedua hanya untuk satu pembacaan.
     *
     * @return array|null|false
     */
    public function getDevice(int $idDevice)
    {
        try {
            $row = $this->db->table('devices')
                ->select('id, name, android_id, created_at, updated_at')
                ->where('id', $idDevice)
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (DatabaseException $e) {
            log_message('error', 'UserModel::getDevice gagal: ' . $e->getMessage());

            return false;
        }

        return $row ?: null;
    }

    /**
     * Ambil satu user berdasarkan id.
     * Dipakai endpoint user-update / user-delete untuk menemukan sasaran.
     *
     * @return array|null|false
     */
    public function getById(int $id)
    {
        try {
            $row = $this->db->table('users u')
                ->select('u.id, u.nik, u.role, u.permissions, u.tim, u.device_id, u.created_by, '
                    . 'u.created_at, u.updated_at, u.chat_muted_until, '
                    . 'd.name AS device_name, d.android_id', false)
                ->join('devices d', 'u.device_id = d.id', 'left')
                ->where('u.id', $id)
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (DatabaseException $e) {
            log_message('error', 'UserModel::getById gagal: ' . $e->getMessage());

            return false;
        }

        return $row ?: null;
    }

    /**
     * Perbarui user. Hanya kolom yang ada di $data yang disentuh.
     *
     * Kalau $areas berupa array, daftar area user DIGANTI seluruhnya dengan
     * isi array tsb (array kosong = semua area dilepas). null = jangan
     * sentuh area sama sekali.
     *
     * Kolom dan area dikerjakan dalam SATU transaksi supaya tidak mungkin
     * kolomnya tersimpan sementara areanya gagal, atau sebaliknya.
     */
    public function updateUser(int $idUser, array $data, ?array $areas = null): bool
    {
        $adaKolom = $data !== [];
        $adaArea  = $areas !== null;

        if (! $adaKolom && ! $adaArea) {
            return false; // guard: tidak ada yang perlu dikerjakan
        }

        $this->lastError = [];
        $this->db->transBegin();

        try {
            if ($adaKolom) {
                $this->db->table('users')->where('id', $idUser)->update($data);
            }

            if ($adaArea) {
                // Ganti seluruh daftar: buang yang lama, masukkan yang baru.
                // WHERE selalu ada, jadi tidak mungkin menghapus area user lain.
                $this->db->table('users_area')->where('id_user', $idUser)->delete();

                if ($areas !== []) {
                    $batch = [];

                    foreach ($areas as $area) {
                        $batch[] = [
                            'id_user' => $idUser,
                            'area'    => $area,
                            // created_at diisi MySQL (DEFAULT CURRENT_TIMESTAMP)
                        ];
                    }

                    $this->db->table('users_area')->insertBatch($batch);
                }
            }

            if ($this->db->transStatus() === false) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = $this->db->error();
            $this->db->transRollback();
            log_message('error', 'UserModel::updateUser gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return true;
    }

    /**
     * Hapus user.
     *
     * Perilaku foreign key yang menempel:
     *  - users_area.id_user   ON DELETE CASCADE  -> area user ikut terhapus
     *  - sto_data.created_by  ON DELETE SET NULL -> tag tetap ada, jejak
     *                                              pencetaknya dikosongkan
     *  - users.created_by     ON DELETE SET NULL -> akun yang pernah ia
     *                                              daftarkan tetap ada
     *
     * `sto_data.nik_a` / `nik_b` menyimpan NIK sebagai teks biasa, BUKAN
     * foreign key -- jadi riwayat scan tetap utuh setelah user dihapus.
     */
    public function deleteUser(int $idUser): bool
    {
        $this->lastError = [];
        $this->db->transBegin();

        try {
            $this->db->table('users')->where('id', $idUser)->delete();

            if ($this->db->transStatus() === false) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = $this->db->error();
            $this->db->transRollback();
            log_message('error', 'UserModel::deleteUser gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return true;
    }

    /**
     * Jejak yang menempel pada satu user -- dipakai sebagai pengaman
     * sebelum menghapus, supaya admin tahu apa yang ikut terdampak.
     *
     * @return array{tag_dicetak: int, scan: int, area: int, akun_didaftarkan: int}
     */
    public function getUserJejak(int $idUser, string $nik): array
    {
        try {
            $tag = (int) $this->db->table('sto_data')
                ->where('created_by', $idUser)
                ->countAllResults();

            $scan = (int) $this->db->table('sto_data')
                ->groupStart()
                ->where('nik_a', $nik)
                ->orWhere('nik_b', $nik)
                ->groupEnd()
                ->countAllResults();

            $area = (int) $this->db->table('users_area')
                ->where('id_user', $idUser)
                ->countAllResults();

            $akun = (int) $this->db->table('users')
                ->where('created_by', $idUser)
                ->countAllResults();
        } catch (DatabaseException $e) {
            log_message('error', 'UserModel::getUserJejak gagal: ' . $e->getMessage());

            return ['tag_dicetak' => 0, 'scan' => 0, 'area' => 0, 'akun_didaftarkan' => 0];
        }

        return [
            'tag_dicetak'      => $tag,
            'scan'             => $scan,
            'area'             => $area,
            'akun_didaftarkan' => $akun,
        ];
    }

    /**
     * Daftar akun beserta areanya.
     *
     * Area diambil dengan SATU query untuk seluruh user lalu dikelompokkan
     * di PHP -- bukan satu query per user (N+1).
     *
     * @param array $f [q, limit]
     *
     * @return array|false
     */
    public function getUserList(array $f)
    {
        $limit = (int) $f['limit'];
        if ($limit <= 0) {
            $limit = self::DEFAULT_LIMIT;
        }
        if ($limit > self::MAX_LIMIT) {
            $limit = self::MAX_LIMIT;
        }

        try {
            $builder = $this->db->table('users u')
                // created_by sengaja TIDAK diambil di sini -- mengikuti CI3,
                // yang juga tidak menyertakannya pada daftar user. formatUser()
                // memakai isset(), jadi field-nya tetap muncul bernilai null.
                ->select('u.id, u.nik, u.role, u.permissions, u.tim, u.device_id, '
                    . 'u.created_at, u.updated_at, '
                    . 'd.name AS device_name, d.android_id', false)
                ->join('devices d', 'u.device_id = d.id', 'left');

            if ($f['q'] !== null) {
                // Digroup supaya OR-nya tidak bocor keluar dari kondisi lain.
                $builder->groupStart()
                    ->like('u.nik', $f['q'], 'both')
                    ->orLike('u.role', $f['q'], 'both')
                    ->groupEnd();
            }

            $users = $builder->orderBy('u.nik', 'ASC')
                ->limit($limit)
                ->get()
                ->getResultArray();

            if ($users === []) {
                return [];
            }

            $ids = array_map(static fn ($u): int => (int) $u['id'], $users);

            $areaRows = $this->db->table('users_area')
                ->select('id_user, area')
                ->whereIn('id_user', $ids)
                ->orderBy('area', 'ASC')
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'UserModel::getUserList gagal: ' . $e->getMessage());

            return false;
        }

        $perUser = [];

        foreach ($areaRows as $row) {
            $perUser[(int) $row['id_user']][] = (string) $row['area'];
        }

        foreach ($users as $k => $u) {
            $users[$k]['__areas'] = $perUser[(int) $u['id']] ?? [];
        }

        return $users;
    }
}
