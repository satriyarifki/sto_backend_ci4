<?php

namespace App\Controllers\Api\Sto;

use App\Models\Sto\ScanModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Rekap STO -- majsf_sto.sto_data (+ master_data).
 *
 *   GET /api/sto/summary-area
 *   GET /api/sto/summary-part
 *
 * Rentang tanggal WAJIB pada keduanya, difilter ke `sto_data.created_at`
 * yaitu kapan tag DICETAK, bukan kapan discan. Baris yang sudah dibatalkan
 * (is_canceled = 1) tidak ikut dihitung.
 *
 * Tidak memerlukan role admin.
 */
class Reports extends BaseSto
{
    private ScanModel $scan;

    public function __construct()
    {
        $this->scan = new ScanModel();
    }

    /**
     * GET /api/sto/summary-area?start_date=2026-09-03&end_date=2026-09-03
     *
     * Rekap per area: jumlah tag, jumlah tag yang sudah discan tim A / tim B,
     * total qty_a, total qty_b, dan selisihnya.
     */
    public function summaryArea(): ResponseInterface
    {
        [$range, $tolak] = $this->resolveRange();
        if ($tolak !== null) {
            return $tolak;
        }

        $rows = $this->scan->getSummaryByArea($range['start'], $range['end']);

        if ($rows === false) {
            return $this->gagal('Gagal mengambil summary per area', 500);
        }

        [$totalTag, $totalQtyA, $totalQtyB] = $this->hitungGrandTotal($rows);

        return $this->ok([
            'status'      => 'success',
            'message'     => 'Summary per area',
            'start_date'  => $range['start'],
            'end_date'    => $range['end'],
            'total_area'  => count($rows),
            'grand_total' => [
                'total_tag'   => $totalTag,
                'total_qty_a' => $totalQtyA,
                'total_qty_b' => $totalQtyB,
                'selisih'     => $totalQtyA - $totalQtyB,
            ],
            'data'        => $rows,
        ]);
    }

    /**
     * GET /api/sto/summary-part?start_date=2026-09-03&end_date=2026-09-03
     *
     * List per item (digroup per id_item + area), di-JOIN ke master_data
     * untuk part_number / job_number / deskripsi.
     */
    public function summaryPart(): ResponseInterface
    {
        [$range, $tolak] = $this->resolveRange();
        if ($tolak !== null) {
            return $tolak;
        }

        $rows = $this->scan->getSummaryByPart($range['start'], $range['end']);

        if ($rows === false) {
            return $this->gagal('Gagal mengambil summary per part number', 500);
        }

        [$totalTag, $totalQtyA, $totalQtyB] = $this->hitungGrandTotal($rows);

        return $this->ok([
            'status'      => 'success',
            'message'     => 'Summary per part number',
            'start_date'  => $range['start'],
            'end_date'    => $range['end'],
            'total_row'   => count($rows),
            'grand_total' => [
                'total_tag'   => $totalTag,
                'total_qty_a' => $totalQtyA,
                'total_qty_b' => $totalQtyB,
                'selisih'     => $totalQtyA - $totalQtyB,
            ],
            'data'        => $rows,
        ]);
    }

    /**
     * @return array{0: int, 1: int, 2: int} [total_tag, total_qty_a, total_qty_b]
     */
    private function hitungGrandTotal(array $rows): array
    {
        $totalTag  = 0;
        $totalQtyA = 0;
        $totalQtyB = 0;

        foreach ($rows as $row) {
            $totalTag  += $row['total_tag'];
            $totalQtyA += $row['total_qty_a'];
            $totalQtyB += $row['total_qty_b'];
        }

        return [$totalTag, $totalQtyA, $totalQtyB];
    }
}
