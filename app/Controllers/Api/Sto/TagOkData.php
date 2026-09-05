<?php

namespace App\Controllers\Api\Sto;

use App\Models\Sto\TagOkModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Tag OK sebagai satuan hitung STO -- majsf_sto.tag_ok_data.
 *
 *   GET  /api/sto/tag-ok          detail satu tag OK
 *   POST /api/sto/tag-ok-open     SIAPKAN: tandai siap dihitung
 *   POST /api/sto/tag-ok-scan     HITUNG: catat qty fisik lalu tutup
 *   GET  /api/sto/tag-ok-list     daftar + ringkasan
 *   POST /api/sto/tag-ok-cancel   ajukan/putuskan pembatalan
 *
 * Kelasnya terpisah dari TagOk (endpoint warisan cancel-tag-ok) karena
 * keduanya menyentuh database yang berbeda: yang ini majsf_sto, yang itu
 * majsf_inventory. Nama URL-nya tetap sama persis dengan CI3.
 *
 * Padanan CI3: Sto::tag_ok_get(), tag_ok_open_post(), tag_ok_scan_post(),
 * tag_ok_list_get(), tag_ok_cancel_post()
 */
class TagOkData extends BaseSto
{
    private TagOkModel $tagok;

    /**
     * GET /api/sto/tag-ok?nik=M.9276&id_tag_ok=MAJ2708260202754
     *
     * Satu Tag OK beserta keadaannya. Dipakai kedua menu: Siapkan Tag OK
     * (sebelum menyetujui) dan Scan Tag OK (sebelum mengisi qty).
     */
    public function detailPrepare(): ResponseInterface
    {
        $this->tagok = new TagOkModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
            }
            
            $idTagOk = $this->cleanString($this->q('id_tag_ok'));
            
            if ($idTagOk === '') {
                return $this->gagal('id_tag_ok wajib diisi', 400);
                }
                
                $row = $this->tagok->getPrepareById($idTagOk);
                
                if ($row === false) {
                    return $this->gagal('Gagal membaca data tag OK', 500);
                    }
                    
        if ($row === null) {
            return $this->respond([
                'status'    => 'failed',
                'message'   => 'Tag OK "' . $idTagOk . '" tidak ditemukan',
                'id_tag_ok' => $idTagOk,
            ], 404);
        }
            // var_dump($row); 
            // die;// Debugging line, can be removed in production

            return $this->ok([
            'status'  => 'success',
            'message' => 'Detail tag OK',
            'data'    => $row,
        ]);
    }

    public function detail(): ResponseInterface
    {
        $this->tagok = new TagOkModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $idTagOk = $this->cleanString($this->q('id_tag_ok'));

        if ($idTagOk === '') {
            return $this->gagal('id_tag_ok wajib diisi', 400);
        }

        $row = $this->tagok->getById($idTagOk);

        if ($row === false) {
            return $this->gagal('Gagal membaca data tag OK', 500);
        }

        if ($row === null) {
            return $this->respond([
                'status'    => 'failed',
                'message'   => 'Tag OK "' . $idTagOk . '" tidak ditemukan',
                'id_tag_ok' => $idTagOk,
            ], 404);
        }

        return $this->ok([
            'status'  => 'success',
            'message' => 'Detail tag OK',
            'data'    => $this->formatTagOk($row),
        ]);
    }

    /**
     * POST /api/sto/tag-ok-open
     * Body: {"nik":"M.9276","id_tag_ok":"MAJ2708260202754"}
     *
     * SIAPKAN: menandai tag OK siap dihitung (scan_open = 1).
     */
    public function open(): ResponseInterface
    {
        $this->tagok = new TagOkModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $idTagOk = $this->cleanString($this->in('id_tag_ok'));

        if ($idTagOk === '') {
            return $this->gagal('id_tag_ok wajib diisi', 400);
        }

        $row = $this->tagok->getById($idTagOk);

        if ($row === false) {
            return $this->gagal('Gagal membaca data tag OK', 500);
        }

        if ($row === null) {
            return $this->respond([
                'status'    => 'failed',
                'message'   => 'Tag OK "' . $idTagOk . '" tidak ditemukan',
                'id_tag_ok' => $idTagOk,
            ], 404);
        }

        // Tag yang dibatalkan (atau sedang diajukan batal) berhenti di sini.
        // Tanpa ini permintaannya "berhasil" padahal tidak ada yang berubah --
        // syarat is_canceled ada di WHERE, jadi barisnya memang tidak tersentuh.
        if ((int) $row['is_canceled'] !== 0) {
            return $this->respond([
                'status'  => 'failed',
                'message' => (int) $row['is_canceled'] === 1
                                ? 'Tag OK ini sudah dibatalkan'
                                : 'Tag OK ini sedang diajukan batal, menunggu keputusan admin',
                'data'    => $this->formatTagOk($row),
            ], 409);
        }

        // Tag yang sudah pernah dihitung tidak dibuka lagi diam-diam:
        // angkanya sudah masuk perhitungan STO.
        if ($row['scanned_at'] !== null && $row['scanned_at'] !== '') {
            return $this->respond([
                'status'  => 'failed',
                'message' => 'Tag OK ini sudah dihitung (' . (int) $row['qty_scan']
                             . ' pcs oleh ' . (string) $row['scanned_by'] . ')',
                'data'    => $this->formatTagOk($row),
            ], 409);
        }

        $idEvent = $this->parseId($this->in('id_event'));

        if ($idEvent === false) {
            $idEvent = null;
        }

        $diubah = $this->tagok->buka($idTagOk, (string) $user['nik'], $idEvent);

        if ($diubah === false) {
            return $this->gagal('Gagal menyiapkan tag OK', 500);
        }

        $baru = $this->tagok->getById($idTagOk);

        return $this->ok([
            'status'  => 'success',
            'message' => 'Tag OK siap dihitung',
            'data'    => is_array($baru) ? $this->formatTagOk($baru) : null,
        ]);
    }

    /**
     * POST /api/sto/tag-ok-scan
     * Body: {"nik":"M.9276","id_tag_ok":"MAJ...","qty":36}
     *
     * HITUNG: mencatat qty fisik lalu menutup tag (scan_open = 0).
     */
    public function scan(): ResponseInterface
    {
        $this->tagok = new TagOkModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $errors = [];

        $idTagOk = $this->cleanString($this->in('id_tag_ok'));

        if ($idTagOk === '') {
            $errors[] = 'id_tag_ok wajib diisi';
        }

        // Divalidasi sendiri, tidak lewat parseQty(): batas qty tag OK
        // mengikuti kolom INT pada tag_ok_data, bukan kolom qty sto_data.
        $rawQty = $this->cleanString($this->in('qty'));
        $qty    = null;

        if ($rawQty === '' || ! ctype_digit($rawQty)) {
            $errors[] = 'qty harus bilangan bulat >= 0';
        } elseif ((int) $rawQty > TagOkModel::MAX_QTY) {
            $errors[] = 'qty melebihi batas';
        } else {
            $qty = (int) $rawQty;
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        $row = $this->tagok->getById($idTagOk);

        if ($row === false) {
            return $this->gagal('Gagal membaca data tag OK', 500);
        }

        if ($row === null) {
            return $this->respond([
                'status'    => 'failed',
                'message'   => 'Tag OK "' . $idTagOk . '" tidak ditemukan',
                'id_tag_ok' => $idTagOk,
            ], 404);
        }

        // Tag yang dibatalkan (atau sedang diajukan batal) berhenti di sini.
        // Tanpa ini permintaannya "berhasil" padahal tidak ada yang berubah --
        // syarat is_canceled ada di WHERE, jadi barisnya memang tidak tersentuh.
        if ((int) $row['is_canceled'] !== 0) {
            return $this->respond([
                'status'  => 'failed',
                'message' => (int) $row['is_canceled'] === 1
                                ? 'Tag OK ini sudah dibatalkan'
                                : 'Tag OK ini sedang diajukan batal, menunggu keputusan admin',
                'data'    => $this->formatTagOk($row),
            ], 409);
        }

        // Hanya tag yang sudah disiapkan yang boleh dihitung - itu gunanya
        // langkah Siapkan Tag OK.
        if ((int) $row['scan_open'] !== 1) {
            $sudah = $row['scanned_at'] !== null && $row['scanned_at'] !== '';

            return $this->respond([
                'status'  => 'failed',
                'message' => $sudah
                    ? 'Tag OK ini sudah dihitung (' . (int) $row['qty_scan']
                      . ' pcs oleh ' . (string) $row['scanned_by'] . ')'
                    : 'Tag OK ini belum disiapkan. Buka lewat menu Siapkan Tag OK dulu.',
                'data'    => $this->formatTagOk($row),
            ], 409);
        }

        $idEvent = $this->parseId($this->in('id_event'));

        if ($idEvent === false) {
            $idEvent = null;
        }

        $diubah = $this->tagok->hitung($idTagOk, (string) $user['nik'], $qty, $idEvent);

        if ($diubah === false) {
            return $this->gagal('Gagal menyimpan hasil hitung tag OK', 500);
        }

        if ($diubah === 0) {
            // Perangkat lain mendahului di antara pembacaan dan penyimpanan.
            return $this->gagal('Tag OK ini baru saja dihitung perangkat lain', 409);
        }

        $baru = $this->tagok->getById($idTagOk);

        return $this->ok([
            'status'  => 'success',
            'message' => 'Hasil hitung tag OK tersimpan',
            'data'    => is_array($baru) ? $this->formatTagOk($baru) : null,
        ]);
    }

    /**
     * GET /api/sto/tag-ok-list?nik=&open=1&area=&q=&limit=
     *
     * Daftar tag OK + ringkasannya.
     */
    public function list(): ResponseInterface
    {
        $this->tagok = new TagOkModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $open = $this->q('open');

        if ($open !== null && $open !== '') {
            $open = (string) $open === '1' ? 1 : 0;
        } else {
            $open = null;
        }

        $filter = [
            'open'     => $open,
            'area'     => $this->cleanString($this->q('area')),
            'q'        => $this->cleanString($this->q('q')),
            'nik'      => $this->cleanString($this->q('milik')),
            'batal'    => $this->cleanString($this->q('batal')),
            'id_event' => $this->parseId($this->q('id_event')),
            'limit'    => (int) $this->cleanString($this->q('limit')),
        ];

        $rows = $this->tagok->getList($filter);

        if ($rows === false) {
            return $this->gagal('Gagal membaca daftar tag OK', 500);
        }

        $ringkas = $this->tagok->getSummary($filter);

        $data = [];

        foreach ($rows as $row) {
            $data[] = $this->formatTagOk($row);
        }

        return $this->ok([
            'status'    => 'success',
            'message'   => 'Daftar tag OK',
            'total_row' => count($data),
            'summary'   => $ringkas === false ? null : $ringkas,
            'data'      => $data,
        ]);
    }

    /**
     * POST /api/sto/tag-ok-cancel
     * Body: {"nik":"M.9276","id_tag_ok":"MAJ...","alasan":"salah hitung"}
     *       opsional {"keputusan":"setuju"|"tolak"} -- khusus admin
     *
     * Alurnya sama dengan pembatalan tag STO: siapa pun yang mengajukan,
     * termasuk admin, masuk ke daftar pengajuan lebih dulu (is_canceled = 2)
     * supaya jejak persetujuannya selalu ada. Admin lalu memutuskan dengan
     * `keputusan`.
     */
    public function cancel(): ResponseInterface
    {
        $this->tagok = new TagOkModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $idTagOk = $this->cleanString($this->in('id_tag_ok'));

        if ($idTagOk === '') {
            return $this->gagal('id_tag_ok wajib diisi', 400);
        }

        $keputusan = strtolower($this->cleanString($this->in('keputusan')));
        $alasan    = $this->cleanString($this->in('alasan'));

        if ($keputusan === '' && $alasan === '') {
            return $this->gagal('Alasan pembatalan wajib diisi', 400);
        }

        if (strlen($alasan) > 255) {
            $alasan = substr($alasan, 0, 255);
        }

        $row = $this->tagok->getById($idTagOk);

        if ($row === false) {
            return $this->gagal('Gagal membaca tag OK', 500);
        }

        if ($row === null) {
            return $this->respond([
                'status'    => 'failed',
                'message'   => 'Tag OK "' . $idTagOk . '" tidak ditemukan',
                'id_tag_ok' => $idTagOk,
            ], 404);
        }

        $keadaan = (int) $row['is_canceled'];
        $admin   = strtolower((string) $user['role']) === self::ROLE_ADMIN;

        // ------------------------------------------------- keputusan admin
        if ($keputusan !== '') {
            if (! $admin) {
                return $this->gagal(
                    'Hanya admin yang boleh memutuskan pengajuan pembatalan',
                    403
                );
            }

            if ($keadaan !== 2) {
                return $this->respond([
                    'status'  => 'failed',
                    'message' => 'Tag OK ini tidak sedang menunggu keputusan pembatalan',
                    'data'    => $this->formatTagOk($row),
                ], 409);
            }

            $setuju = $keputusan === 'setuju' || $keputusan === 'approve';
            $diubah = $this->tagok->batal(
                $idTagOk,
                (string) $user['nik'],
                $alasan === '' ? (string) $row['cancel_reason'] : $alasan,
                $setuju ? 1 : 0,
                2
            );
            $pesan = $setuju
                ? 'Pembatalan disetujui, tag OK dibatalkan'
                : 'Pengajuan ditolak, tag OK kembali normal';
        } else {
            // ------------------------------------------------- pengajuan
            if ($keadaan === 1) {
                return $this->respond([
                    'status'  => 'failed',
                    'message' => 'Tag OK ini sudah dibatalkan',
                    'data'    => $this->formatTagOk($row),
                ], 409);
            }

            if ($keadaan === 2) {
                return $this->respond([
                    'status'  => 'failed',
                    'message' => 'Tag OK ini sudah diajukan batal oleh '
                                 . (string) $row['canceled_by'] . ', menunggu keputusan admin',
                    'data'    => $this->formatTagOk($row),
                ], 409);
            }

            $diubah = $this->tagok->batal($idTagOk, (string) $user['nik'], $alasan, 2, 0);
            $pesan  = 'Pengajuan pembatalan tag OK terkirim, menunggu keputusan admin';
        }

        if ($diubah === false) {
            return $this->gagal('Gagal menyimpan pembatalan tag OK', 500);
        }

        if ($diubah === 0) {
            return $this->gagal(
                'Keadaan tag OK baru saja berubah di perangkat lain, coba muat ulang',
                409
            );
        }

        $baru = $this->tagok->getById($idTagOk);

        return $this->ok([
            'status'  => 'success',
            'message' => $pesan,
            'data'    => is_array($baru) ? $this->formatTagOk($baru) : null,
        ]);
    }

    /**
     * Bentuk satu baris tag_ok_data untuk dikirim ke aplikasi.
     *
     * Padanan CI3: Sto::format_tag_ok()
     *
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function formatTagOk(array $row): array
    {
        return [
            'id_tag_ok'     => (string) $row['id_tag_ok'],
            'area'          => (string) $row['area'],
            'part_number'   => (string) $row['part_number'],
            'job_number'    => (string) $row['job_number'],
            'process'       => $row['process'] === null ? '' : (string) $row['process'],
            'line'          => $row['line'] === null ? '' : (string) $row['line'],
            'shift'         => $row['shift'] === null ? null : (int) $row['shift'],
            'customer'      => $row['customer'] === null ? '' : (string) $row['customer'],
            'project'       => $row['project'] === null ? '' : (string) $row['project'],
            'status'        => $row['status'] === null ? '' : (string) $row['status'],
            'qty_kbn'       => $row['qty_kbn'] === null ? '' : (string) $row['qty_kbn'],
            'id_event'      => $row['id_event'] === null ? null : (int) $row['id_event'],
            'scan_open'     => (int) $row['scan_open'],
            'opened_by'     => $row['opened_by'] === null ? '' : (string) $row['opened_by'],
            'opened_at'     => $row['opened_at'] === null ? '' : (string) $row['opened_at'],
            'qty_scan'      => $row['qty_scan'] === null ? null : (int) $row['qty_scan'],
            'scanned_by'    => $row['scanned_by'] === null ? '' : (string) $row['scanned_by'],
            'scanned_at'    => $row['scanned_at'] === null ? '' : (string) $row['scanned_at'],
            'scan_at'       => $row['scan_at'] === null ? '' : (string) $row['scan_at'],
            'scan_by'       => $row['scan_by'] === null ? '' : (string) $row['scan_by'],
            'is_canceled'   => (int) $row['is_canceled'],
            'cancel_reason' => $row['cancel_reason'] === null ? '' : (string) $row['cancel_reason'],
            'canceled_by'   => $row['canceled_by'] === null ? '' : (string) $row['canceled_by'],
            'canceled_at'   => $row['canceled_at'] === null ? '' : (string) $row['canceled_at'],
        ];
    }
}
