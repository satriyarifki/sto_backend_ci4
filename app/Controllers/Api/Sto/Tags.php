<?php

namespace App\Controllers\Api\Sto;

use App\Models\Sto\ScanModel;
use App\Models\Sto\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Siklus tag STO -- majsf_sto.sto_data.
 *
 *   GET  /api/sto/part-list
 *   GET  /api/sto/tag-detail
 *   POST /api/sto/print-tag
 *   POST /api/sto/print-tag-bulk
 *   POST /api/sto/cancel-request
 *   GET  /api/sto/cancel-requests   (admin)
 *   POST /api/sto/cancel-approve    (admin)
 *   POST /api/sto/cancel-reject     (admin)
 *   POST /api/sto/scan-tag
 *   POST /api/sto/cancel-tag
 *   GET  /api/sto/scan-history
 *
 * Tidak memerlukan role admin. `nik` di sini berarti siapa yang mencetak /
 * menghitung, bukan gerbang hak akses.
 */
class Tags extends BaseSto
{
    private ScanModel $scan;

    public function __construct()
    {
        $this->scan = new ScanModel();
    }

    // =================================================================
    // DAFTAR PART  (master_data)
    // =================================================================

    /**
     * GET /api/sto/part-list?area=IFRM
     *
     * Daftar part dari master_data.
     *
     *   area kosong / tidak dikirim -> semua area
     *   area=IFRM                   -> hanya IFRM
     *   area=IFRM,IFPD              -> gabungan beberapa area
     *
     * Huruf besar/kecil tidak berpengaruh -- kolom `area` bercollation
     * utf8mb4_general_ci, jadi `ifrm` sudah cocok dengan `IFRM`.
     *
     * Area yang tidak dikenal tidak dianggap error: hasilnya cuma kosong.
     * Field `area` pada response menampilkan filter yang benar-benar dipakai.
     */
    public function partList(): ResponseInterface
    {
        $errors = [];

        // area: kosong = semua; boleh satu, boleh dipisah koma, boleh array
        $rawArea = $this->q('area');
        $areas   = $rawArea === null ? [] : $this->normalizeList($rawArea);

        foreach ($areas as $a) {
            if (strlen($a) > 50) {
                $errors[] = 'area "' . $a . '" melebihi 50 karakter';
                break;
            }
        }

        $q = $this->cleanString($this->q('q'));

        $rawLimit = $this->cleanString($this->q('limit'));
        $limit    = ScanModel::PART_DEFAULT_LIMIT;

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

        $rows = $this->scan->getPartList([
            'areas' => $areas,
            'q'     => $q === '' ? null : $q,
            'limit' => $limit,
        ]);

        if ($rows === false) {
            return $this->gagal('Gagal mengambil daftar part', 500);
        }

        $terpakai = min($limit, ScanModel::PART_MAX_LIMIT);

        $out = [
            'status'         => 'success',
            'message'        => 'Daftar part',
            'area'           => $areas,
            'total_area'     => count($areas),
            'limit_terpakai' => $terpakai,
            'total_row'      => count($rows),
            'data'           => $rows,
        ];

        // Hasil pas sebanyak limit -> kemungkinan besar masih ada sisa.
        // Diberitahu supaya pemanggil tidak mengira daftarnya sudah lengkap.
        if (count($rows) === $terpakai) {
            $out['catatan'] = 'Hasil terpotong di batas ' . $terpakai . ' baris. '
                . 'Persempit dengan "area"/"q", atau naikkan "limit" '
                . '(maks ' . ScanModel::PART_MAX_LIMIT . ').';
        }

        return $this->ok($out);
    }

    // =================================================================
    // PRINT TAG
    // =================================================================

    /**
     * POST /api/sto/print-tag
     *
     * Body: {"area":"IFPD","part_number":"57249-BZ040-00","nik":"S.10445"}
     *    atau {"area":"IFPD","job_number":"NA09"}
     *
     * Mencari item di master_data, lalu MEMBUAT satu baris baru di sto_data
     * dan mengembalikan detailnya untuk dicetak.
     *
     * id_tag dibentuk server: STO[YYMMDD]-[id_event][id sto_data],
     * mis. STO260903-51.
     *
     * `area` + `part_number` maupun `area` + `job_number` TIDAK unik di
     * master_data. Kalau pencarian menghasilkan lebih dari satu item,
     * TIDAK ada yang disimpan -- balasannya `status: "multiple"` berisi
     * daftar kandidat.
     */
    public function printTag(): ResponseInterface
    {
        $errors = [];

        $area       = $this->cleanString($this->in('area'));
        $partNumber = $this->cleanString($this->in('part_number'));
        $jobNumber  = $this->cleanString($this->in('job_number'));

        if ($area === '') {
            $errors[] = 'area wajib diisi';
        }

        if ($partNumber === '' && $jobNumber === '') {
            $errors[] = 'part_number atau job_number wajib diisi (boleh salah satu)';
        }

        $idItem = $this->parseId($this->in('id_item'));
        if ($idItem === false) {
            $errors[] = 'id_item harus berupa angka';
        }

        $idEvent = $this->parseId($this->in('id_event'));
        if ($idEvent === false) {
            $errors[] = 'id_event harus berupa angka';
        }

        $nik = $this->cleanString($this->in('nik'));

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        // ---------------------------------------------------------------
        // 1. Tentukan event, 2. cari item, 3. siapa pencetaknya.
        //    Ketiganya dipakai bersama print-tag-bulk, jadi dipisah ke helper.
        // ---------------------------------------------------------------
        [$event, $tolak] = $this->tentukanEvent($idEvent);
        if ($tolak !== null) {
            return $tolak;
        }
        $idEvent = (int) $event['id_event'];

        [$item, $tolak] = $this->cariSatuItem($area, $partNumber, $jobNumber, $idItem);
        if ($tolak !== null) {
            return $tolak;
        }

        [$createdBy, $tolak] = $this->cariPencetak($nik);
        if ($tolak !== null) {
            return $tolak;
        }

        // ---------------------------------------------------------------
        // 4. Buat tag.
        // ---------------------------------------------------------------
        $created = $this->scan->createTag($idEvent, (int) $item['id'], (string) $item['area'], $createdBy);

        if ($created === false) {
            $err   = $this->scan->lastError;
            $pesan = 'Gagal membuat tag, perubahan dibatalkan (rollback)';

            if (isset($err['code']) && (int) $err['code'] === ScanModel::ERR_DUPLICATE) {
                $pesan = 'Gagal membuat tag: id_tag hasil bentukan bentrok dengan '
                    . 'tag yang sudah ada. Tidak ada data yang tersimpan.';
            }

            return $this->gagal($pesan, 500);
        }

        log_message(
            'info',
            'Print tag STO: ' . $created['id_tag'] . ' id_item=' . $item['id']
            . ' area=' . $item['area'] . ' id_event=' . $idEvent
            . ($nik === '' ? '' : ' oleh ' . $nik)
        );

        // Ambil ulang lewat getTag supaya bentuk datanya sama persis dgn
        // response endpoint scan-tag.
        $tag = $this->scan->getTag($created['id_tag']);

        return $this->ok([
            'status'  => 'success',
            'message' => 'Tag berhasil dibuat dan disimpan',
            'id_tag'  => $created['id_tag'],
            'event'   => [
                'id_event'   => (int) $event['id_event'],
                'event_name' => (string) $event['event_name'],
            ],
            'data'    => is_array($tag) ? $this->formatTag($tag) : null,
        ], 201);
    }

    // =================================================================
    // SCAN TAG
    // =================================================================

    /**
     * POST /api/sto/scan-tag
     *
     * Body: {"id_tag":"STO260903-51","nik":"S.10445","qty":200,"tim":"A"}
     *
     * tim A menulis ke (nik_a, qty_a, updated_a),
     * tim B menulis ke (nik_b, qty_b, updated_b).
     *
     * Kalau tag SUDAH pernah discan oleh tim yang sama, request pertama
     * TIDAK langsung menimpa: balasannya `status: "confirm"` berisi data
     * lama + qty baru yang diajukan. Kirim ulang dengan `"confirm": true`
     * untuk benar-benar meng-update.
     */
    public function scanTag(): ResponseInterface
    {
        $errors = [];

        $idTag = $this->cleanString($this->in('id_tag'));
        $nik   = $this->cleanString($this->in('nik'));

        if ($idTag === '') {
            $errors[] = 'id_tag wajib diisi';
        }

        if ($nik === '') {
            $errors[] = 'nik wajib diisi';
        }

        // tim wajib di endpoint ini
        $tim = $this->parseTim($this->in('tim'));
        if ($tim === false || $tim === null) {
            $errors[] = 'tim wajib diisi dan harus "A" atau "B"';
        }

        $qty = $this->parseQty($this->in('qty'), ScanModel::MAX_QTY);
        if ($qty === false) {
            $errors[] = 'qty wajib diisi berupa bilangan bulat 0 s/d ' . ScanModel::MAX_QTY;
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        $confirm = $this->toBool($this->in('confirm'));

        // ---------------------------------------------------------------
        // 1. NIK harus terdaftar.
        // ---------------------------------------------------------------
        $users = new UserModel();
        $user  = $users->getByNik($nik);

        if ($user === false) {
            return $this->gagal('Gagal membaca data user', 500);
        }

        if ($user === null) {
            return $this->gagal('NIK "' . $nik . '" tidak terdaftar', 404);
        }

        // ---------------------------------------------------------------
        // 2. Tag harus ada di sto_data.
        // ---------------------------------------------------------------
        $tag = $this->scan->getTag($idTag);

        if ($tag === false) {
            return $this->gagal('Gagal membaca data tag', 500);
        }

        if ($tag === null) {
            return $this->gagal('id_tag "' . $idTag . '" tidak ditemukan', 404);
        }

        // ---------------------------------------------------------------
        // 2b. Tag yang SUDAH dibatalkan tidak boleh dihitung lagi.
        //     Yang pembatalannya baru diajukan (2) masih boleh -- keputusannya
        //     belum jatuh, dan menghentikan hitungan di situ justru merepotkan
        //     kalau pengajuannya akhirnya ditolak.
        // ---------------------------------------------------------------
        if ((int) $tag['is_canceled'] === 1) {
            return $this->respond([
                'status'  => 'failed',
                'message' => 'Tag ini sudah dibatalkan, tidak bisa discan',
                'data'    => $this->formatTag($tag),
            ], 409);
        }

        // ---------------------------------------------------------------
        // 3. Sudah pernah discan oleh tim ini?
        //    Penanda dipakai `updated_x IS NOT NULL`, bukan `qty > 0`,
        //    karena hasil hitung 0 itu sah dan tetap terhitung sudah discan.
        // ---------------------------------------------------------------
        $suffix      = $tim === 'A' ? 'a' : 'b';
        $sudahDiscan = $tag['updated_' . $suffix] !== null && $tag['updated_' . $suffix] !== '';

        if ($sudahDiscan && $confirm !== true) {
            return $this->ok([
                'status'     => 'confirm',
                'message'    => 'Tag ini sudah discan oleh tim ' . $tim
                                . '. Kirim ulang dengan "confirm": true untuk menimpa data lama.',
                'tim'        => $tim,
                'qty_lama'   => (int) $tag['qty_' . $suffix],
                'qty_baru'   => $qty,
                'nik_lama'   => $tag['nik_' . $suffix] === null ? '' : (string) $tag['nik_' . $suffix],
                'scanned_at' => (string) $tag['updated_' . $suffix],
                'data'       => $this->formatTag($tag),
            ]);
        }

        // ---------------------------------------------------------------
        // 4. Simpan.
        // ---------------------------------------------------------------
        if ($this->scan->saveScan($idTag, $tim, $qty, $nik) === false) {
            return $this->gagal('Gagal menyimpan hasil scan, perubahan dibatalkan (rollback)', 500);
        }

        $updated = $this->scan->getTag($idTag);

        log_message(
            'info',
            'Scan STO: tag=' . $idTag . ' tim=' . $tim . ' qty=' . $qty . ' nik=' . $nik
            . ' aksi=' . ($sudahDiscan ? 'update' : 'create')
        );

        return $this->ok([
            'status'  => 'success',
            'message' => $sudahDiscan
                            ? 'Hasil scan tim ' . $tim . ' berhasil diperbarui'
                            : 'Hasil scan tim ' . $tim . ' berhasil disimpan',
            'action'  => $sudahDiscan ? 'update' : 'create',
            'tim'     => $tim,
            'data'    => $this->formatTag(is_array($updated) ? $updated : $tag),
        ]);
    }

    // =================================================================
    // CANCEL TAG
    // =================================================================

    /**
     * POST /api/sto/cancel-tag
     *
     * Body: {"id_tag": "STO260903-51"}
     *    atau {"id_tag": ["STO260903-51", "STO260903-52"]}
     *
     * Membatalkan tag dengan menyetel `is_canceled` = 1. Barisnya TIDAK
     * dihapus -- hasil hitung tim A / B tetap tersimpan dan bisa diaudit.
     * Tag yang sudah dibatalkan otomatis hilang dari kedua summary.
     *
     * Jangan tertukar dengan cancel-tag-ok yang menghapus baris di
     * majsf_inventory.table_sto_tag_ok (tabel & perilaku berbeda).
     */
    public function cancelTag(): ResponseInterface
    {
        // Menerima `id_tag`, `id_tags`, atau `ids`; isinya boleh satu string
        // atau array.
        $raw = $this->in('id_tag') ?? $this->in('id_tags') ?? $this->in('ids');

        if ($raw === null) {
            return $this->gagal(
                'Parameter "id_tag" wajib diisi (boleh satu string atau array)',
                400
            );
        }

        $idTags = $this->normalizeList($raw);

        if ($idTags === []) {
            return $this->gagal('Parameter "id_tag" kosong / tidak berisi id_tag yang valid', 400);
        }

        if (count($idTags) > self::MAX_IDS) {
            return $this->gagal(
                'Jumlah id_tag melebihi batas maksimal ' . self::MAX_IDS . ' per request',
                400
            );
        }

        $status = $this->scan->getTagsCancelStatus($idTags);

        if ($status === false) {
            return $this->gagal('Gagal membaca data tag', 500);
        }

        $perluCancel     = [];
        $sudahDibatalkan = [];
        $skipped         = [];

        foreach ($idTags as $idTag) {
            if (! array_key_exists($idTag, $status)) {
                $skipped[] = $idTag;
            } elseif ($status[$idTag] === 1) {
                $sudahDibatalkan[] = $idTag;
            } else {
                $perluCancel[] = $idTag;
            }
        }

        if ($perluCancel !== []) {
            $affected = $this->scan->cancelTags($perluCancel);

            if ($affected === false) {
                return $this->gagal('Gagal membatalkan tag, perubahan dibatalkan (rollback)', 500);
            }

            log_message(
                'info',
                'Cancel tag STO (sto_data): ' . count($perluCancel) . ' id_tag, '
                . $affected . ' baris ditandai is_canceled=1'
            );
        }

        return $this->ok([
            'status'                 => 'success',
            'message'                => $perluCancel === []
                                            ? 'Tidak ada tag yang perlu dibatalkan'
                                            : 'Cancel tag STO selesai',
            'total_requested'        => count($idTags),
            'total_canceled'         => count($perluCancel),
            'total_already_canceled' => count($sudahDibatalkan),
            'total_skipped'          => count($skipped),
            'canceled'               => $perluCancel,
            'already_canceled'       => $sudahDibatalkan,
            'skipped'                => $skipped,
        ]);
    }

    // =================================================================
    // RIWAYAT
    // =================================================================

    /**
     * GET /api/sto/scan-history
     *
     * Semua filter OPSIONAL: nik, tim, area, id_tag, start_date, end_date, limit.
     * Tanpa filter apapun -> mengembalikan scan terbaru (default 100 baris).
     *
     * sto_data menyimpan hasil tim A & B pada satu baris, jadi satu tag yang
     * sudah discan kedua tim menghasilkan DUA entri riwayat.
     */
    public function scanHistory(): ResponseInterface
    {
        $errors = [];

        $tim = $this->parseTim($this->q('tim'));
        if ($tim === false) {
            $errors[] = 'tim harus "A" atau "B"';
        }

        $nik   = $this->cleanString($this->q('nik'));
        $area  = $this->cleanString($this->q('area'));
        $idTag = $this->cleanString($this->q('id_tag'));

        $start = null;
        $end   = null;

        $rawStart = $this->q('start_date') ?? $this->q('start');
        if ($rawStart !== null && $this->cleanString($rawStart) !== '') {
            $start = $this->parseDatetime($rawStart, false);

            if ($start === false) {
                $errors[] = 'start_date tidak valid, gunakan "YYYY-MM-DD" atau "YYYY-MM-DD HH:MM:SS"';
            }
        }

        $rawEnd = $this->q('end_date') ?? $this->q('end');
        if ($rawEnd !== null && $this->cleanString($rawEnd) !== '') {
            $end = $this->parseDatetime($rawEnd, true);

            if ($end === false) {
                $errors[] = 'end_date tidak valid, gunakan "YYYY-MM-DD" atau "YYYY-MM-DD HH:MM:SS"';
            }
        }

        if (is_string($start) && is_string($end) && $start > $end) {
            $errors[] = 'start_date tidak boleh lebih besar dari end_date';
        }

        $rawLimit = $this->cleanString($this->q('limit'));
        $limit    = ScanModel::DEFAULT_LIMIT;

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

        $q = $this->cleanString($this->q('q'));

        $filter = [
            'tim'        => $tim,
            'nik'        => $nik === '' ? null : $nik,
            'q'          => $q === '' ? null : $q,
            'area'       => $area === '' ? null : $area,
            'id_tag'     => $idTag === '' ? null : $idTag,
            'start_date' => is_string($start) ? $start : null,
            'end_date'   => is_string($end) ? $end : null,
            'limit'      => $limit,
        ];

        $rows = $this->scan->getScanHistory($filter);

        if ($rows === false) {
            return $this->gagal('Gagal mengambil riwayat scan', 500);
        }

        $totalQty = 0;

        foreach ($rows as $row) {
            $totalQty += $row['qty'];
        }

        return $this->ok([
            'status'         => 'success',
            'message'        => 'Riwayat scan',
            'filter'         => $filter,
            'limit_terpakai' => min($limit, ScanModel::MAX_LIMIT),
            'total_row'      => count($rows),
            'total_qty'      => $totalQty,
            'data'           => $rows,
        ]);
    }
    /**
     * POST /api/sto/print-tag-bulk
     *
     * Body: {"area":"IFPD","part_number":"57249-BZ040-00","id_item":399,
     *        "qty":50,"id_event":5,"nik":"S.10445"}
     *
     * Sama seperti print-tag, tapi mencetak `qty` tag sekaligus untuk item
     * yang sama. Maksimal MAX_BULK tag per request.
     *
     * BEST EFFORT: kalau ada yang gagal di tengah jalan, yang sudah jadi
     * TETAP tersimpan -- tidak di-rollback. Yang gagal dilaporkan lewat
     * `total_gagal` dan `gagal[]`, jadi pemanggil bisa mencetak ulang
     * kekurangannya tanpa menduplikasi yang sudah jadi.
     */
    public function printTagBulk(): ResponseInterface
    {
        $errors = [];

        $area       = $this->cleanString($this->in('area'));
        $partNumber = $this->cleanString($this->in('part_number'));
        $jobNumber  = $this->cleanString($this->in('job_number'));

        if ($area === '') {
            $errors[] = 'area wajib diisi';
        }
        if ($partNumber === '' && $jobNumber === '') {
            $errors[] = 'part_number atau job_number wajib diisi (boleh salah satu)';
        }

        $idItem = $this->parseId($this->in('id_item'));
        if ($idItem === false) {
            $errors[] = 'id_item harus berupa angka';
        }

        $idEvent = $this->parseId($this->in('id_event'));
        if ($idEvent === false) {
            $errors[] = 'id_event harus berupa angka';
        }

        $qty = $this->parseId($this->in('qty'));
        if ($qty === false || $qty === null) {
            $errors[] = 'qty wajib diisi berupa bilangan bulat lebih besar dari 0';
        } elseif ($qty > self::MAX_BULK) {
            $errors[] = 'qty melebihi batas ' . self::MAX_BULK . ' tag per request';
        }

        $nik = $this->cleanString($this->in('nik'));

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        [$event, $tolak] = $this->tentukanEvent($idEvent);
        if ($tolak !== null) {
            return $tolak;
        }
        $idEvent = (int) $event['id_event'];

        [$item, $tolak] = $this->cariSatuItem($area, $partNumber, $jobNumber, $idItem);
        if ($tolak !== null) {
            return $tolak;
        }

        [$createdBy, $tolak] = $this->cariPencetak($nik);
        if ($tolak !== null) {
            return $tolak;
        }

        $hasil = $this->scan->createTagsBulk(
            $idEvent,
            (int) $item['id'],
            (string) $item['area'],
            $createdBy,
            $qty
        );

        $dibuat = $hasil['dibuat'];
        $gagal  = $hasil['gagal'];
        $idTags = array_column($dibuat, 'id_tag');

        log_message(
            'info',
            'Print tag bulk STO: diminta ' . $qty . ', jadi ' . count($idTags)
            . ', gagal ' . count($gagal) . '; id_item=' . $item['id']
            . ' area=' . $item['area'] . ' id_event=' . $idEvent
            . ($nik === '' ? '' : ' oleh ' . $nik)
        );

        // Tidak ada satupun yang jadi -> ini kegagalan sungguhan, bukan
        // hasil separuh. Balas 500 supaya tidak terbaca sebagai sukses.
        if ($idTags === []) {
            return $this->respond([
                'status'        => 'failed',
                'message'       => 'Tidak ada tag yang berhasil dibuat',
                'total_diminta' => $qty,
                'total_dibuat'  => 0,
                'total_gagal'   => count($gagal),
                'gagal'         => $gagal,
            ], 500);
        }

        $rows = $this->scan->getTagsByIdTags($idTags);
        $data = [];

        if (is_array($rows)) {
            foreach ($rows as $row) {
                $data[] = $this->formatTag($row);
            }
        }

        $sebagian = $gagal !== [];

        return $this->ok([
            'status'        => $sebagian ? 'partial' : 'success',
            'message'       => $sebagian
                                  ? count($idTags) . ' dari ' . $qty . ' tag berhasil dibuat, '
                                    . count($gagal) . ' gagal. Yang sudah jadi tetap tersimpan.'
                                  : count($idTags) . ' tag berhasil dibuat dan disimpan',
            'total_diminta' => $qty,
            'total_dibuat'  => count($idTags),
            'total_gagal'   => count($gagal),
            'event'         => [
                'id_event'   => (int) $event['id_event'],
                'event_name' => (string) $event['event_name'],
            ],
            'data'          => $data,
            'gagal'         => $gagal,
        ], 201);
    }

    /**
     * GET /api/sto/tag-detail?id_tag=STO260903-51
     *
     * Detail satu tag TANPA mengubah apapun -- dipakai layar Scan untuk
     * menampilkan part sebelum operator mengisi qty.
     *
     * Bentuk `data`-nya sama persis dengan print-tag dan scan-tag, termasuk
     * `is_canceled` beserta jejak pengajuan pembatalannya.
     */
    public function tagDetail(): ResponseInterface
    {
        $idTag = $this->cleanString($this->q('id_tag'));

        if ($idTag === '') {
            return $this->gagal('Parameter "id_tag" wajib diisi', 400);
        }

        [$tag, $tolak] = $this->ambilTag($idTag);
        if ($tolak !== null) {
            return $tolak;
        }

        return $this->ok([
            'status'  => 'success',
            'message' => 'Detail tag',
            'data'    => $this->formatTag($tag),
        ]);
    }

    // =================================================================
    // PENGAJUAN PEMBATALAN
    // =================================================================

    /**
     * POST /api/sto/cancel-request
     *
     * Body: {"id_tag":"STO260903-51","reason":"Mispart","nik":"S.10445"}
     *
     * Siapa pun yang terdaftar boleh mengajukan; admin yang memutuskan.
     * Tag berpindah ke status is_canceled = 2 dan TETAP ikut terhitung di
     * summary sampai pengajuannya disetujui.
     */
    public function cancelRequest(): ResponseInterface
    {
        $errors = [];

        $idTag  = $this->cleanString($this->in('id_tag'));
        $nik    = $this->cleanString($this->in('nik'));
        $reason = $this->cleanString($this->in('reason'));

        if ($idTag === '') {
            $errors[] = 'id_tag wajib diisi';
        }
        if ($nik === '') {
            $errors[] = 'nik wajib diisi';
        }
        if ($reason === '') {
            $errors[] = 'reason wajib diisi';
        } elseif (strlen($reason) > self::MAX_REASON) {
            $errors[] = 'reason maksimal ' . self::MAX_REASON . ' karakter';
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        $users = new UserModel();
        $user  = $users->getByNik($nik);

        if ($user === false) {
            return $this->gagal('Gagal membaca data user', 500);
        }
        if ($user === null) {
            return $this->gagal('NIK "' . $nik . '" tidak terdaftar', 404);
        }

        [$tag, $tolak] = $this->ambilTag($idTag);
        if ($tolak !== null) {
            return $tolak;
        }

        $status = (int) $tag['is_canceled'];

        if ($status === 1) {
            return $this->respond([
                'status'  => 'failed',
                'message' => 'Tag ini sudah dibatalkan',
                'data'    => $this->formatTag($tag),
            ], 409);
        }

        if ($status === 2) {
            return $this->respond([
                'status'  => 'failed',
                'message' => 'Pembatalan tag ini sudah diajukan dan sedang menunggu keputusan admin',
                'data'    => $this->formatTag($tag),
            ], 409);
        }

        if ($this->scan->requestCancel($idTag, $reason, (int) $user['id']) === false) {
            return $this->gagal('Gagal mengajukan pembatalan, perubahan dibatalkan (rollback)', 500);
        }

        log_message('info', 'Pengajuan batal STO: ' . $idTag . ' oleh ' . $nik . ' -- ' . $reason);

        $baru = $this->scan->getTag($idTag);

        return $this->ok([
            'status'  => 'success',
            'message' => 'Pengajuan pembatalan terkirim, menunggu keputusan admin',
            'data'    => $this->formatTag(is_array($baru) ? $baru : $tag),
        ]);
    }

    /**
     * GET /api/sto/cancel-requests?nik=A.001
     *
     * Daftar pengajuan yang masih menunggu keputusan, urut dari yang paling
     * lama diajukan. Hanya untuk admin -- ini kotak masuk keputusannya.
     *
     * Pengaju yang ingin memantau statusnya sendiri cukup memakai
     * tag-detail: field `is_canceled` dan `cancel_*` ada di sana.
     */
    public function cancelRequests(): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $errors = [];

        $area = $this->cleanString($this->q('area'));

        $rawLimit = $this->cleanString($this->q('limit'));
        $limit    = ScanModel::DEFAULT_LIMIT;

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

        $rows = $this->scan->getCancelRequests([
            'area'  => $area === '' ? null : $area,
            'limit' => $limit,
        ]);

        if ($rows === false) {
            return $this->gagal('Gagal mengambil daftar pengajuan pembatalan', 500);
        }

        $data = [];

        foreach ($rows as $row) {
            $data[] = $this->formatTag($row);
        }

        return $this->ok([
            'status'         => 'success',
            'message'        => 'Daftar pengajuan pembatalan',
            'limit_terpakai' => min($limit, ScanModel::MAX_LIMIT),
            'total_row'      => count($data),
            'data'           => $data,
        ]);
    }

    /**
     * POST /api/sto/cancel-approve
     *
     * Body: {"nik":"A.001","id_tag":"STO260903-51"}
     *
     * Menyetujui pengajuan: is_canceled 2 -> 1. Hanya admin.
     */
    public function cancelApprove(): ResponseInterface
    {
        return $this->putuskanPengajuan(true);
    }

    /**
     * POST /api/sto/cancel-reject
     *
     * Body: {"nik":"A.001","id_tag":"STO260903-51"}
     *
     * Menolak pengajuan: is_canceled 2 -> 0, jejak pengajuan dibersihkan
     * sehingga tag kembali normal. Hanya admin.
     */
    public function cancelReject(): ResponseInterface
    {
        return $this->putuskanPengajuan(false);
    }

    /**
     * Badan bersama cancel-approve dan cancel-reject -- keduanya hanya beda
     * pada arah keputusannya.
     */
    private function putuskanPengajuan(bool $setuju): ResponseInterface
    {
        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $idTag = $this->cleanString($this->in('id_tag'));

        if ($idTag === '') {
            return $this->gagal('Parameter "id_tag" wajib diisi', 400);
        }

        [$tag, $tolak] = $this->ambilTag($idTag);
        if ($tolak !== null) {
            return $tolak;
        }

        if ((int) $tag['is_canceled'] !== 2) {
            return $this->respond([
                'status'  => 'failed',
                'message' => 'Tag ini tidak sedang menunggu keputusan pembatalan '
                             . '(is_canceled = ' . (int) $tag['is_canceled'] . ').',
                'data'    => $this->formatTag($tag),
            ], 409);
        }

        $ok = $setuju
            ? $this->scan->approveCancel($idTag, (int) $admin['id'])
            : $this->scan->rejectCancel($idTag);

        if ($ok === false) {
            return $this->gagal('Gagal menyimpan keputusan, perubahan dibatalkan (rollback)', 500);
        }

        log_message(
            'info',
            'Keputusan batal STO: ' . $idTag . ' -> ' . ($setuju ? 'DISETUJUI' : 'DITOLAK')
            . ' oleh ' . $admin['nik']
        );

        $baru = $this->scan->getTag($idTag);

        return $this->ok([
            'status'  => 'success',
            'message' => $setuju
                            ? 'Pembatalan disetujui, tag dibatalkan'
                            : 'Pengajuan ditolak, tag kembali normal',
            'data'    => $this->formatTag(is_array($baru) ? $baru : $tag),
        ]);
    }

    // =================================================================
    // HELPER BERSAMA print-tag / print-tag-bulk / pembatalan
    // =================================================================

    /**
     * Tentukan event yang dipakai print-tag / print-tag-bulk.
     *
     * @return array{0: array|null, 1: ResponseInterface|null}
     */
    private function tentukanEvent(?int $idEvent): array
    {
        if ($idEvent !== null) {
            $event = $this->scan->getEvent($idEvent);

            if ($event === false) {
                return [null, $this->gagal('Gagal membaca data event', 500)];
            }
            if ($event === null) {
                return [null, $this->gagal('id_event ' . $idEvent . ' tidak ditemukan', 404)];
            }

            return [$event, null];
        }

        $aktif = $this->scan->getActiveEvents();

        if ($aktif === false) {
            return [null, $this->gagal('Gagal membaca data event', 500)];
        }
        if ($aktif === []) {
            return [null, $this->gagal(
                'Tidak ada event STO yang berjalan (status = 1). '
                . 'Aktifkan event dulu atau sebutkan id_event pada request.',
                409
            )];
        }
        if (count($aktif) > 1) {
            return [null, $this->respond([
                'status'  => 'failed',
                'message' => 'Ada ' . count($aktif) . ' event berjalan sekaligus, '
                             . 'sebutkan id_event pada request untuk memilih',
                'events'  => $aktif,
            ], 409)];
        }

        return [$aktif[0], null];
    }

    /**
     * Cari TEPAT SATU item di master_data.
     *
     * Nol hasil -> 404. Lebih dari satu -> balasan `multiple` berisi daftar
     * kandidat, dan TIDAK ada yang disimpan.
     *
     * @return array{0: array|null, 1: ResponseInterface|null}
     */
    private function cariSatuItem(string $area, string $partNumber, string $jobNumber, ?int $idItem): array
    {
        $items = $this->scan->findMasterItems(
            $area,
            $partNumber === '' ? null : $partNumber,
            $jobNumber === '' ? null : $jobNumber,
            $idItem
        );

        if ($items === false) {
            return [null, $this->gagal('Gagal membaca master data', 500)];
        }

        if ($items === []) {
            return [null, $this->gagal('Item tidak ditemukan di master_data untuk kriteria tsb', 404)];
        }

        if (count($items) > 1) {
            return [null, $this->ok([
                'status'     => 'multiple',
                'message'    => 'Ditemukan ' . count($items) . ' item. Kirim ulang '
                                . 'dengan "id_item" untuk memilih salah satu.',
                'total_item' => count($items),
                'items'      => $this->formatItems($items),
            ])];
        }

        return [$items[0], null];
    }

    /**
     * Terjemahkan NIK pencetak jadi users.id.
     * NIK kosong itu sah -- artinya pencetaknya tidak dicatat.
     *
     * @return array{0: int|null, 1: ResponseInterface|null}
     */
    private function cariPencetak(string $nik): array
    {
        if ($nik === '') {
            return [null, null];
        }

        $users = new UserModel();
        $user  = $users->getByNik($nik);

        if ($user === false) {
            return [null, $this->gagal('Gagal membaca data user', 500)];
        }
        if ($user === null) {
            return [null, $this->gagal('NIK "' . $nik . '" tidak terdaftar', 404)];
        }

        return [(int) $user['id'], null];
    }

    /**
     * Ambil satu tag, siapkan response error bila gagal / tidak ketemu.
     *
     * @return array{0: array|null, 1: ResponseInterface|null}
     */
    private function ambilTag(string $idTag): array
    {
        $tag = $this->scan->getTag($idTag);

        if ($tag === false) {
            return [null, $this->gagal('Gagal membaca data tag', 500)];
        }
        if ($tag === null) {
            return [null, $this->gagal('id_tag "' . $idTag . '" tidak ditemukan', 404)];
        }

        return [$tag, null];
    }
}
