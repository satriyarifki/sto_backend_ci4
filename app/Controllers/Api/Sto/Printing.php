<?php

namespace App\Controllers\Api\Sto;

use App\Models\Sto\PrintModel;
use App\Models\Sto\SettingModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Alur CETAK tag STO.
 *
 *   POST /api/sto/print-status     lapor keadaan cetak   (semua user terdaftar)
 *   GET  /api/sto/print-history    riwayat + ringkasan   (semua user terdaftar)
 *   GET  /api/sto/printer-setting  baca setelan printer  (semua user terdaftar)
 *   POST /api/sto/printer-setting  simpan setelan        (HANYA admin)
 *
 * Pembuatan tag-nya sendiri ada di Tags (print-tag / print-tag-bulk). Di sini
 * hanya keadaan cetaknya: apakah lembarannya benar-benar keluar dari printer.
 *
 * Padanan CI3: Sto::print_status_post(), print_history_get(),
 * printer_setting_get(), printer_setting_post()
 */
class Printing extends BaseSto
{
    private PrintModel $prints;
    private SettingModel $settings;

    /**
     * POST /api/sto/print-status
     *
     * Menandai keadaan cetak satu/beberapa tag.
     *
     *   nik     wajib, NIK terdaftar (siapa pun, bukan hanya admin)
     *   id_tag  wajib, string tunggal atau array (maks PrintModel::MAX_TAG)
     *   status  wajib: draft | printed | error
     *   message opsional, alasan saat status = error (maks 255 karakter)
     *
     * Dipakai aplikasi handheld: tag dibuat lewat print-tag, lalu keadaan
     * cetaknya dilaporkan ke sini SETELAH printer menjawab. Tag yang
     * lembarannya tidak keluar tetap tersimpan sebagai draft/error supaya
     * bisa dicetak ulang atau dibatalkan admin -- bukan hilang bersama
     * perangkat yang mencetaknya.
     */
    public function printStatus(): ResponseInterface
    {
        $this->prints = new PrintModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $errors = [];

        $rawTag = $this->in('id_tag');
        $idTags = [];

        if (is_array($rawTag)) {
            foreach ($rawTag as $satu) {
                $satu = $this->cleanString($satu);

                if ($satu !== '') {
                    $idTags[] = $satu;
                }
            }
        } else {
            $satu = $this->cleanString($rawTag);

            if ($satu !== '') {
                $idTags[] = $satu;
            }
        }

        if ($idTags === []) {
            $errors[] = 'id_tag wajib diisi (string atau array)';
        }

        if (count($idTags) > PrintModel::MAX_TAG) {
            $errors[] = 'id_tag maksimal ' . PrintModel::MAX_TAG . ' per permintaan';
        }

        $status = strtolower($this->cleanString($this->in('status')));

        if (! in_array($status, PrintModel::STATUS_SAH, true)) {
            $errors[] = 'status harus salah satu dari: '
                        . implode(', ', PrintModel::STATUS_SAH);
        }

        $pesan = $this->cleanString($this->in('message'));

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        $hasil = $this->prints->setStatus($idTags, $status, $pesan);

        if ($hasil === false) {
            return $this->gagal('Gagal menyimpan keadaan cetak', 500);
        }

        if ((int) $hasil['diubah'] === 0 && $hasil['tidak_ditemukan'] !== []) {
            return $this->respond([
                'status'          => 'failed',
                'message'         => 'id_tag tidak ditemukan',
                'tidak_ditemukan' => $hasil['tidak_ditemukan'],
            ], 404);
        }

        return $this->ok([
            'status'          => 'success',
            'message'         => 'Keadaan cetak disimpan',
            'print_status'    => $status,
            'diubah'          => (int) $hasil['diubah'],
            'tidak_ditemukan' => $hasil['tidak_ditemukan'],
        ]);
    }

    /**
     * GET /api/sto/print-history?nik=M.9276&status=draft,error&limit=100
     *
     * Riwayat cetak tag beserta identitas materialnya dan ringkasannya.
     *
     *   nik      wajib, sekaligus penyaring pemilik tag
     *   status   opsional, draft|printed|error (boleh dipisah koma)
     *   area     opsional
     *   id_event opsional
     *   id_tag   opsional
     *   limit    opsional (default 100, maks 500)
     *
     * `summary` ikut dikirim supaya kartu ringkasan di aplikasi memakai angka
     * server, bukan hitungan lokal yang bisa berbeda antar perangkat.
     * Keadaan pembatalan mengikuti konvensi is_canceled: 0 normal, 1 batal,
     * 2 menunggu keputusan admin.
     */
    public function printHistory(): ResponseInterface
    {
        $this->prints = new PrintModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $errors = [];

        $status    = [];
        $rawStatus = $this->cleanString($this->q('status'));

        if ($rawStatus !== '') {
            foreach (explode(',', $rawStatus) as $satu) {
                $satu = strtolower(trim($satu));

                if ($satu === '') {
                    continue;
                }

                if (! in_array($satu, PrintModel::STATUS_SAH, true)) {
                    $errors[] = 'status "' . $satu . '" tidak dikenal, pilihannya: '
                                . implode(', ', PrintModel::STATUS_SAH);

                    continue;
                }

                $status[] = $satu;
            }
        }

        $limit    = PrintModel::DEFAULT_LIMIT;
        $rawLimit = $this->cleanString($this->q('limit'));

        if ($rawLimit !== '') {
            if (! ctype_digit($rawLimit) || (int) $rawLimit <= 0) {
                $errors[] = 'limit harus bilangan bulat lebih besar dari 0';
            } else {
                $limit = (int) $rawLimit;
            }
        }

        $idEvent = $this->parseId($this->q('id_event'));

        if ($idEvent === false) {
            $errors[] = 'id_event harus berupa angka';
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        $filter = [
            'nik'      => $this->cleanString($this->q('nik')),
            'q'        => $this->cleanString($this->q('q')),
            'status'   => $status,
            'area'     => $this->cleanString($this->q('area')),
            'id_event' => $idEvent,
            'id_tag'   => $this->cleanString($this->q('id_tag')),
            'limit'    => $limit,
        ];

        $rows = $this->prints->getPrintHistory($filter);

        if ($rows === false) {
            return $this->gagal('Gagal membaca riwayat cetak', 500);
        }

        $ringkas = $this->prints->getPrintSummary($filter);

        if ($ringkas === false) {
            return $this->gagal('Gagal membaca ringkasan cetak', 500);
        }

        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'id_tag'               => (string) $row['id_tag'],
                'id_event'             => $row['id_event'] === null ? null : (int) $row['id_event'],
                'event_name'           => (string) $row['event_name'],
                'area'                 => (string) $row['area'],
                'print_status'         => (string) $row['print_status'],
                'print_error'          => $row['print_error'] === null ? '' : (string) $row['print_error'],
                'printed_at'           => $row['printed_at'] === null ? '' : (string) $row['printed_at'],
                'is_canceled'          => (int) $row['is_canceled'],
                'cancel_reason'        => $row['cancel_reason'] === null ? '' : (string) $row['cancel_reason'],
                'cancel_requested_at'  => $row['cancel_requested_at'] === null ? '' : (string) $row['cancel_requested_at'],
                'cancel_requested_by'  => $row['cancel_requested_nik'] === null ? '' : (string) $row['cancel_requested_nik'],
                'cancel_approved_by'   => $row['cancel_approved_nik'] === null ? '' : (string) $row['cancel_approved_nik'],
                'created_by'           => (string) $row['created_by_nik'],
                'created_at'           => (string) $row['created_at'],
                'id_item'              => $row['id_item'] === null ? null : (int) $row['id_item'],
                'part_number'          => (string) $row['part_number'],
                'job_number'           => (string) $row['job_number'],
                'material_description' => (string) $row['material_description'],
                'type'                 => (string) $row['type'],
                'status_part'          => (string) $row['status_part'],
                'customer'             => (string) $row['customer'],
                'model'                => (string) $row['model'],
                'plant'                => (string) $row['plant'],
            ];
        }

        return $this->ok([
            'status'         => 'success',
            'message'        => 'Riwayat cetak',
            'limit_terpakai' => $limit,
            'total_row'      => count($data),
            'summary'        => $ringkas,
            'data'           => $data,
        ]);
    }

    /**
     * GET /api/sto/printer-setting?nik=M.9276
     *
     * Setelan printer bersama - dibaca SEMUA user terdaftar karena setiap
     * handheld memerlukannya saat mencetak.
     */
    public function printerSettingGet(): ResponseInterface
    {
        $this->settings = new SettingModel();

        [$user, $tolak] = $this->pastikanUserTerdaftar($this->q('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $hasil = $this->settings->getAll();

        if ($hasil === false) {
            return $this->gagal('Gagal membaca setelan printer', 500);
        }

        return $this->ok([
            'status'  => 'success',
            'message' => 'Setelan printer',
            'data'    => $hasil['nilai'],
            'rinci'   => $hasil['rinci'],
        ]);
    }

    /**
     * POST /api/sto/printer-setting
     * Body: {"nik":"E.9948","gap_antar_tag_dots":96,"feed_akhir_dots":0,
     *        "paper_size":"mm58"}
     *
     * Menyimpan setelan - HANYA ADMIN. Kirim hanya field yang ingin diubah;
     * yang tidak disebut dibiarkan apa adanya.
     *
     * Setelan ini milik bersama, bukan milik perangkat: selama tersimpan di
     * masing-masing handheld, hasil cetak antar operator berbeda-beda dan
     * tidak ada yang bisa memastikan mana yang benar.
     */
    public function printerSettingSave(): ResponseInterface
    {
        $this->settings = new SettingModel();

        [$admin, $tolak] = $this->isAdmin($this->in('nik'));
        if ($tolak !== null) {
            return $tolak;
        }

        $errors   = [];
        $pasangan = [];

        foreach (SettingModel::NAMA_SAH as $nama) {
            $raw = $this->in($nama);

            if ($raw === null) {
                continue; // tidak dikirim = tidak diubah
            }

            $nilai = $this->cleanString($raw);

            if ($nilai === '') {
                $errors[] = $nama . ' tidak boleh kosong';

                continue;
            }

            $pesan = $this->settings->periksa($nama, $nilai);

            if ($pesan !== false) {
                $errors[] = $pesan;

                continue;
            }

            $pasangan[$nama] = $nilai;
        }

        if ($pasangan === [] && $errors === []) {
            $errors[] = 'tidak ada setelan yang dikirim; pilihannya: '
                        . implode(', ', SettingModel::NAMA_SAH);
        }

        if ($errors !== []) {
            return $this->gagalValidasi($errors);
        }

        if ($this->settings->simpan($pasangan, (int) $admin['id']) === false) {
            return $this->gagal('Gagal menyimpan setelan printer', 500);
        }

        log_message('info', 'Setelan printer STO diubah oleh ' . $admin['nik']
                            . ': ' . json_encode($pasangan));

        $hasil = $this->settings->getAll();

        return $this->ok([
            'status'  => 'success',
            'message' => 'Setelan printer disimpan',
            'diubah'  => array_keys($pasangan),
            'data'    => $hasil === false ? $pasangan : $hasil['nilai'],
        ]);
    }
}
