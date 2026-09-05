<?php

namespace App\Models\Sto;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * CRUD majsf_sto.events -- daftar periode/event STO.
 *
 * `sto_data.id_event` menunjuk ke tabel ini dengan ON DELETE SET NULL, jadi
 * menghapus event TIDAK menghapus tag-nya: tag-nya kehilangan keterikatan
 * event (id_event jadi NULL). Karena itu jumlah tag yang menempel ikut
 * dihitung dan dipakai controller sebagai pengaman sebelum menghapus.
 */
class EventModel extends Model
{
    protected $DBGroup    = 'db_sto';
    protected $table      = 'events';
    protected $primaryKey = 'id_event';
    protected $returnType = 'array';

    protected $allowedFields = ['event_name', 'start_date', 'end_date', 'status', 'created_at'];

    protected $useTimestamps = false;

    public const MAX_LIMIT     = 500;
    public const DEFAULT_LIMIT = 100;

    /** Panjang maksimal kolom event_name. */
    public const MAX_NAME = 100;

    public array $lastError = [];

    /**
     * Builder dasar: kolom event + jumlah tag yang menempel.
     * LEFT JOIN supaya event yang belum punya tag tetap muncul dgn total 0.
     */
    private function baseBuilder()
    {
        return $this->db->table('events e')
            ->select('e.id_event, e.event_name, e.start_date, e.end_date, e.status, '
                . 'e.created_at, e.updated_at, COUNT(s.id) AS total_tag, '
                // "qty sudah ada isinya" = tag sudah pernah discan. qty_a/qty_b
                // NOT NULL DEFAULT 0, jadi qty-nya sendiri tidak bisa
                // membedakan tag yang belum discan dari yang hasil hitungnya
                // nol -- penandanya updated_a/updated_b, sama seperti
                // ScanModel::getSummaryByArea().
                . 'SUM(s.updated_a IS NOT NULL OR s.updated_b IS NOT NULL) AS total_used, '
                . 'SUM(s.is_canceled = 1) AS total_cancelled', false)
            ->join('sto_data s', 's.id_event = e.id_event', 'left');
    }

    private function shape(array $row): array
    {
        return [
            'id_event'   => (int) $row['id_event'],
            'event_name' => (string) $row['event_name'],
            'start_date' => (string) $row['start_date'],
            'end_date'   => $row['end_date'] === null ? '' : (string) $row['end_date'],
            'status'     => (int) $row['status'],
            'total_tag'  => isset($row['total_tag']) ? (int) $row['total_tag'] : 0,
            'total_used'      => isset($row['total_used']) ? (int) $row['total_used'] : 0,
            'total_cancelled' => isset($row['total_cancelled']) ? (int) $row['total_cancelled'] : 0,
            'created_at' => $row['created_at'] === null ? '' : (string) $row['created_at'],
            'updated_at' => $row['updated_at'] === null ? '' : (string) $row['updated_at'],
        ];
    }

    /**
     * Daftar event. Semua filter opsional.
     *
     * @param array $f [status, q, limit]
     *
     * @return array|false
     */
    public function getEvents(array $f)
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

            if ($f['status'] !== null) {
                $builder->where('e.status', $f['status']);
            }
            if ($f['q'] !== null) {
                $builder->like('e.event_name', $f['q'], 'both');
            }

            // id_event adalah PK tabel e, jadi group by PK saja sudah sah
            // termasuk di bawah ONLY_FULL_GROUP_BY (functional dependency).
            $rows = $builder->groupBy('e.id_event')
                ->orderBy('e.start_date', 'DESC')
                ->orderBy('e.id_event', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'EventModel::getEvents gagal: ' . $e->getMessage());

            return false;
        }

        return array_map([$this, 'shape'], $rows);
    }

    /**
     * @return array|null|false
     */
    public function getEvent(int $idEvent)
    {
        try {
            $row = $this->baseBuilder()
                ->where('e.id_event', $idEvent)
                ->groupBy('e.id_event')
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (DatabaseException $e) {
            log_message('error', 'EventModel::getEvent gagal: ' . $e->getMessage());

            return false;
        }

        return $row ? $this->shape($row) : null;
    }

    /**
     * Event lain yang juga berstatus berjalan (status = 1).
     *
     * Dipakai controller untuk memperingatkan, BUKAN untuk menolak --
     * print-tag sudah bisa menangani kondisi lebih dari satu event aktif.
     *
     * @param int|null $kecuali id_event yang tidak ikut dilaporkan
     *
     * @return array|false
     */
    public function getOtherActive(?int $kecuali = null)
    {
        try {
            $builder = $this->db->table('events')
                ->select('id_event, event_name, start_date, end_date, status')
                ->where('status', 1);

            if ($kecuali !== null) {
                $builder->where('id_event !=', $kecuali);
            }

            $rows = $builder->orderBy('id_event', 'ASC')->get()->getResultArray();
        } catch (DatabaseException $e) {
            log_message('error', 'EventModel::getOtherActive gagal: ' . $e->getMessage());

            return false;
        }

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id_event'   => (int) $row['id_event'],
                'event_name' => (string) $row['event_name'],
                'start_date' => (string) $row['start_date'],
                'end_date'   => $row['end_date'] === null ? '' : (string) $row['end_date'],
                'status'     => (int) $row['status'],
            ];
        }

        return $out;
    }

    /**
     * Jumlah baris sto_data yang menunjuk ke event ini.
     * Dipakai sebagai pengaman sebelum menghapus.
     */
    /**
     * Tutup semua event yang masih berjalan, kecuali $kecuali.
     *
     * Dipakai saat satu event dijadikan satu-satunya yang aktif: status event
     * lain dijatuhkan ke 0 dalam satu perintah UPDATE, bukan satu per satu,
     * supaya tidak ada jeda di mana dua event sama-sama berjalan.
     *
     * Padanan CI3: M_sto_event::close_others()
     *
     * @return array<int, int>|false daftar id_event yang ditutup
     */
    public function closeOthers(?int $kecuali = null)
    {
        $lain = $this->getOtherActive($kecuali);

        if ($lain === false) {
            return false;
        }

        if ($lain === []) {
            return [];
        }

        $this->lastError = [];
        $this->db->transBegin();

        try {
            $builder = $this->db->table('events')->where('status', 1);

            if ($kecuali !== null) {
                $builder->where('id_event !=', $kecuali);
            }

            $ok = $builder->update([
                'status'     => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

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

        $this->db->transCommit();

        $ditutup = [];

        foreach ($lain as $e) {
            $ditutup[] = (int) $e['id_event'];
        }

        return $ditutup;
    }

    public function countStoData(int $idEvent): int
    {
        try {
            return (int) $this->db->table('sto_data')->where('id_event', $idEvent)->countAllResults();
        } catch (DatabaseException $e) {
            log_message('error', 'EventModel::countStoData gagal: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * @return int|false id_event baru
     */
    public function createEvent(array $data)
    {
        $this->lastError = [];
        $this->db->transBegin();

        try {
            $this->db->table('events')->insert($data);
            $idEvent = (int) $this->db->insertID();

            if ($this->db->transStatus() === false || $idEvent <= 0) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = $this->db->error();
            $this->db->transRollback();
            log_message('error', 'EventModel::createEvent gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return $idEvent;
    }

    /**
     * Perbarui event. Hanya kolom yang ada di $data yang disentuh.
     */
    public function updateEvent(int $idEvent, array $data): bool
    {
        if ($data === []) {
            return false; // guard: jangan jalankan UPDATE tanpa isi
        }

        $this->lastError = [];
        $this->db->transBegin();

        try {
            $this->db->table('events')->where('id_event', $idEvent)->update($data);

            if ($this->db->transStatus() === false) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = $this->db->error();
            $this->db->transRollback();
            log_message('error', 'EventModel::updateEvent gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return true;
    }

    /**
     * Hapus event.
     *
     * FK sto_data.id_event memakai ON DELETE SET NULL, jadi tag yang
     * menempel TIDAK ikut terhapus -- hanya id_event-nya dikosongkan.
     */
    public function deleteEvent(int $idEvent): bool
    {
        $this->lastError = [];
        $this->db->transBegin();

        try {
            $this->db->table('events')->where('id_event', $idEvent)->delete();

            if ($this->db->transStatus() === false) {
                $this->lastError = $this->db->error();
                $this->db->transRollback();

                return false;
            }
        } catch (DatabaseException $e) {
            $this->lastError = $this->db->error();
            $this->db->transRollback();
            log_message('error', 'EventModel::deleteEvent gagal: ' . $e->getMessage());

            return false;
        }

        $this->db->transCommit();

        return true;
    }
}
