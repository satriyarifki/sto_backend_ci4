<?php

namespace App\Models\Inventory;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * majsf_inventory.table_sto_tag_ok -- endpoint warisan cancel-tag-ok.
 *
 * Tabel ini diisi sistem lain dan TIDAK terhubung ke majsf_sto. Beda dengan
 * cancel-tag yang hanya menandai is_canceled, di sini barisnya benar-benar
 * DIHAPUS.
 *
 * Catatan struktur:
 *  - PRIMARY KEY : `id` (auto increment)
 *  - UNIQUE KEY  : (`id_tag_ok`, `id_event`)
 *    Artinya satu `id_tag_ok` BISA muncul lebih dari satu baris jika
 *    `id_event` berbeda -- karena itu ada filter opsional `id_event`.
 */
class TagOkModel extends Model
{
    protected $DBGroup    = 'db_inv';
    protected $table      = 'table_sto_tag_ok';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = false;

    /** Jumlah id per query, supaya klausa IN (...) tidak kebablasan panjangnya. */
    public const CHUNK_SIZE = 500;

    /**
     * Ambil daftar id_tag_ok yang BENAR-BENAR ada di database.
     * Dipakai untuk memisahkan mana yang akan dihapus dan mana yang di-skip.
     *
     * @return array|false daftar id_tag_ok yang ditemukan (unik)
     */
    public function getExistingTags(array $ids, ?int $idEvent = null)
    {
        if ($ids === []) {
            return [];
        }

        $found = [];

        try {
            foreach (array_chunk($ids, self::CHUNK_SIZE) as $chunk) {
                if ($chunk === []) {
                    continue;
                }

                $builder = $this->db->table('table_sto_tag_ok')
                    ->select('id_tag_ok')
                    ->whereIn('id_tag_ok', $chunk);

                if ($idEvent !== null) {
                    $builder->where('id_event', $idEvent);
                }

                foreach ($builder->get()->getResultArray() as $row) {
                    $found[] = (string) $row['id_tag_ok'];
                }
            }
        } catch (DatabaseException $e) {
            log_message('error', 'TagOkModel::getExistingTags gagal: ' . $e->getMessage());

            return false;
        }

        return array_values(array_unique($found));
    }

    /**
     * Hapus baris berdasarkan daftar id_tag_ok.
     * Dibungkus transaksi: kalau ada satu chunk yang gagal, semua di-rollback.
     *
     * @return int|false jumlah baris terhapus
     */
    public function deleteTags(array $ids, ?int $idEvent = null)
    {
        if ($ids === []) {
            return 0; // tidak ada yang perlu dihapus, bukan sebuah error
        }

        $deleted = 0;
        $this->db->transBegin();

        try {
            foreach (array_chunk($ids, self::CHUNK_SIZE) as $chunk) {
                // Guard: JANGAN PERNAH jalankan DELETE tanpa WHERE.
                if ($chunk === []) {
                    continue;
                }

                $builder = $this->db->table('table_sto_tag_ok')->whereIn('id_tag_ok', $chunk);

                if ($idEvent !== null) {
                    $builder->where('id_event', $idEvent);
                }

                $builder->delete();
                $deleted += (int) $this->db->affectedRows();
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', 'TagOkModel::deleteTags gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return $deleted;
    }
}
