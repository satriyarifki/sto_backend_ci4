<?php

namespace App\Controllers\Api\Sto;

use App\Models\Sto\EventModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CRUD majsf_sto.events -- periode STO.
 *
 *   GET  /api/sto/event-list
 *   GET  /api/sto/event-detail
 *   POST /api/sto/event-create
 *   POST /api/sto/event-update
 *   POST /api/sto/event-delete
 *
 * SELURUH endpoint di sini wajib mengirim `nik` milik user ber-role admin.
 */
class Events extends BaseSto
{
    private EventModel $events;

    public function __construct()
    {
        $this->events = new EventModel();
    }

    /**
     * GET /api/sto/event-list?nik=A.001&status=1&q=internal&limit=50
     *
     * Daftar event, terbaru di atas. Tiap baris menyertakan `total_tag` --
     * jumlah baris sto_data yang menempel pada event tsb.
     */
    public function list(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $errors = [];

        $status = $this->parseStatus($this->q('status'));
        if ($status === false) {
            $errors[] = 'status harus 0 atau 1';
        }

        $q = $this->cleanString($this->q('q'));

        $rawLimit = $this->cleanString($this->q('limit'));
        $limit    = EventModel::DEFAULT_LIMIT;

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

        $rows = $this->events->getEvents([
            'status' => $status,
            'q'      => $q === '' ? null : $q,
            'limit'  => $limit,
        ]);

        if ($rows === false) {
            return $this->gagal('Gagal mengambil daftar event', 500);
        }

        return $this->ok([
            'status'         => 'success',
            'message'        => 'Daftar event',
            'limit_terpakai' => min($limit, EventModel::MAX_LIMIT),
            'total_row'      => count($rows),
            'data'           => $rows,
        ]);
    }

    /**
     * GET /api/sto/event-detail?nik=A.001&id_event=5
     */
    public function detail(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $idEvent = $this->parseId($this->q('id_event'));

        if ($idEvent === false) {
            return $this->gagal('Parameter "id_event" harus berupa angka', 400);
        }

        if ($idEvent === null) {
            return $this->gagal('Parameter "id_event" wajib diisi', 400);
        }

        $event = $this->events->getEvent($idEvent);

        if ($event === false) {
            return $this->gagal('Gagal membaca data event', 500);
        }

        if ($event === null) {
            return $this->gagal('id_event ' . $idEvent . ' tidak ditemukan', 404);
        }

        return $this->ok([
            'status'  => 'success',
            'message' => 'Detail event',
            'data'    => $event,
        ]);
    }

    /**
     * POST /api/sto/event-create
     *
     * Body: {"nik":"A.001","event_name":"STO Internal HMMI IFPD",
     *        "start_date":"2026-09-03","end_date":null,"status":1}
     */
    public function create(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $errors = [];

        $nama = $this->cleanString($this->in('event_name'));
        if ($nama === '') {
            $errors[] = 'event_name wajib diisi';
        } elseif (strlen($nama) > EventModel::MAX_NAME) {
            $errors[] = 'event_name maksimal ' . EventModel::MAX_NAME . ' karakter';
        }

        $start = $this->parseDateOnly($this->in('start_date'));
        if ($start === false || $start === null) {
            $errors[] = 'start_date wajib diisi dengan format "YYYY-MM-DD"';
        }

        // end_date boleh kosong -> event masih berjalan / belum ditutup
        $end = $this->parseDateOnly($this->in('end_date'));
        if ($end === false) {
            $errors[] = 'end_date tidak valid, gunakan format "YYYY-MM-DD"';
        }

        $status = $this->parseStatus($this->in('status'));
        if ($status === false) {
            $errors[] = 'status harus 0 atau 1';
        }
        if ($status === null) {
            $status = 0; // ikut DEFAULT kolom
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        $idEvent = $this->events->createEvent([
            'event_name' => $nama,
            'start_date' => $start,
            'end_date'   => $end,
            'status'     => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($idEvent === false) {
            return $this->gagal('Gagal menyimpan event, perubahan dibatalkan (rollback)', 500);
        }

        log_message('info', 'Event STO dibuat: id_event=' . $idEvent . ' "' . $nama . '"');

        $event = $this->events->getEvent($idEvent);

        $out = [
            'status'  => 'success',
            'message' => 'Event berhasil dibuat',
            'data'    => is_array($event) ? $event : null,
        ];

        $peringatan = $this->kumpulkanPeringatan($start, $end, $status, $idEvent);
        if ($peringatan !== []) {
            $out['warnings'] = $peringatan;
        }

        return $this->ok($out, 201);
    }

    /**
     * POST /api/sto/event-update
     *
     * Body: {"nik":"A.001","id_event":5,"status":0}
     *
     * Partial update -- hanya field yang dikirim yang diubah.
     * Untuk mengosongkan end_date, kirim string kosong: "end_date": "".
     */
    public function update(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $idEvent = $this->parseId($this->in('id_event'));

        if ($idEvent === false || $idEvent === null) {
            return $this->gagal('Parameter "id_event" wajib diisi berupa angka', 400);
        }

        $lama = $this->events->getEvent($idEvent);

        if ($lama === false) {
            return $this->gagal('Gagal membaca data event', 500);
        }

        if ($lama === null) {
            return $this->gagal('id_event ' . $idEvent . ' tidak ditemukan', 404);
        }

        $errors = [];
        $data   = [];

        if ($this->in('event_name') !== null) {
            $nama = $this->cleanString($this->in('event_name'));

            if ($nama === '') {
                $errors[] = 'event_name tidak boleh dikosongkan';
            } elseif (strlen($nama) > EventModel::MAX_NAME) {
                $errors[] = 'event_name maksimal ' . EventModel::MAX_NAME . ' karakter';
            } else {
                $data['event_name'] = $nama;
            }
        }

        if ($this->in('start_date') !== null) {
            $start = $this->parseDateOnly($this->in('start_date'));

            if ($start === false || $start === null) {
                $errors[] = 'start_date tidak valid, gunakan format "YYYY-MM-DD"';
            } else {
                $data['start_date'] = $start;
            }
        }

        // Dikirim sebagai "" atau null -> end_date dikosongkan.
        if ($this->in('end_date') !== null) {
            $end = $this->parseDateOnly($this->in('end_date'));

            if ($end === false) {
                $errors[] = 'end_date tidak valid, gunakan format "YYYY-MM-DD"';
            } else {
                $data['end_date'] = $end;
            }
        }

        if ($this->in('status') !== null) {
            $status = $this->parseStatus($this->in('status'));

            if ($status === false || $status === null) {
                $errors[] = 'status harus 0 atau 1';
            } else {
                $data['status'] = $status;
            }
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        if ($data === []) {
            return $this->gagal(
                'Tidak ada field yang diubah. Kirim minimal satu dari '
                . 'event_name, start_date, end_date, atau status.',
                400
            );
        }

        if ($this->events->updateEvent($idEvent, $data) === false) {
            return $this->gagal('Gagal memperbarui event, perubahan dibatalkan (rollback)', 500);
        }

        log_message(
            'info',
            'Event STO diperbarui: id_event=' . $idEvent . ' field=' . implode(',', array_keys($data))
        );

        $baru = $this->events->getEvent($idEvent);

        $out = [
            'status'  => 'success',
            'message' => 'Event berhasil diperbarui',
            'diubah'  => array_keys($data),
            'data'    => is_array($baru) ? $baru : null,
        ];

        if (is_array($baru)) {
            $peringatan = $this->kumpulkanPeringatan(
                $baru['start_date'],
                $baru['end_date'] === '' ? null : $baru['end_date'],
                $baru['status'],
                $idEvent
            );

            if ($peringatan !== []) {
                $out['warnings'] = $peringatan;
            }
        }

        return $this->ok($out);
    }

    /**
     * POST /api/sto/event-delete
     *
     * Body: {"nik":"A.001","id_event":5}
     *
     * FK sto_data.id_event memakai ON DELETE SET NULL, jadi menghapus event
     * TIDAK menghapus tag-nya -- tag-nya kehilangan keterikatan event.
     * Karena itu penghapusan DITOLAK selama masih ada tag yang menempel,
     * kecuali dikirim "force": true.
     */
    public function delete(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $idEvent = $this->parseId($this->in('id_event'));

        if ($idEvent === false || $idEvent === null) {
            return $this->gagal('Parameter "id_event" wajib diisi berupa angka', 400);
        }

        $event = $this->events->getEvent($idEvent);

        if ($event === false) {
            return $this->gagal('Gagal membaca data event', 500);
        }

        if ($event === null) {
            return $this->gagal('id_event ' . $idEvent . ' tidak ditemukan', 404);
        }

        $jmlTag = $this->events->countStoData($idEvent);
        $force  = $this->toBool($this->in('force'));

        if ($jmlTag > 0 && $force !== true) {
            return $this->ok([
                'status'    => 'confirm',
                'message'   => 'Event ini masih dipakai ' . $jmlTag . ' tag. Menghapusnya '
                               . 'TIDAK menghapus tag tsb, tapi id_event pada tag akan '
                               . 'dikosongkan dan tag jadi lepas dari event manapun. '
                               . 'Kirim ulang dengan "force": true bila memang dikehendaki.',
                'id_event'  => $idEvent,
                'total_tag' => $jmlTag,
                'data'      => $event,
            ]);
        }

        if ($this->events->deleteEvent($idEvent) === false) {
            return $this->gagal('Gagal menghapus event, perubahan dibatalkan (rollback)', 500);
        }

        log_message(
            'info',
            'Event STO dihapus: id_event=' . $idEvent . ' "' . $event['event_name'] . '"'
            . ' (' . $jmlTag . ' tag dilepas)'
        );

        return $this->ok([
            'status'      => 'success',
            'message'     => 'Event berhasil dihapus',
            'id_event'    => $idEvent,
            'tag_dilepas' => $jmlTag,
            'data'        => $event,
        ]);
    }

    /**
     * Peringatan yang TIDAK menggagalkan permintaan.
     *
     * Sengaja bukan error:
     *  - Data existing sudah memuat event dgn end_date lebih awal dari
     *    start_date. Menolaknya akan membuat baris itu tidak bisa diedit.
     *  - Lebih dari satu event berjalan bukan kondisi rusak -- print-tag
     *    sudah menanganinya dgn meminta id_event disebut.
     */
    private function kumpulkanPeringatan(?string $start, ?string $end, int $status, int $idEvent): array
    {
        $out = [];

        if ($start !== null && $end !== null && $end !== '' && $end < $start) {
            $out[] = 'end_date (' . $end . ') lebih awal dari start_date (' . $start . ').';
        }

        if ($status === 1) {
            $lain = $this->events->getOtherActive($idEvent);

            if (is_array($lain) && $lain !== []) {
                $nama = [];

                foreach ($lain as $e) {
                    $nama[] = '#' . $e['id_event'] . ' ' . $e['event_name'];
                }

                $out[] = 'Ada ' . count($lain) . ' event lain yang juga berstatus berjalan ('
                    . implode('; ', $nama) . '). print-tag akan meminta id_event '
                    . 'disebut eksplisit selama lebih dari satu event aktif.';
            }
        }

        return $out;
    }
}
