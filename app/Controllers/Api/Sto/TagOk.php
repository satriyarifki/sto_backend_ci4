<?php

namespace App\Controllers\Api\Sto;

use App\Models\Inventory\TagOkModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Endpoint warisan -- majsf_inventory.table_sto_tag_ok.
 *
 *   POST /api/sto/cancel-tag-ok
 *
 * JANGAN tertukar dengan cancel-tag:
 *
 *                cancel-tag                  cancel-tag-ok
 *   Tabel        majsf_sto.sto_data          majsf_inventory.table_sto_tag_ok
 *   Aksi         is_canceled = 1 (soft)      DELETE baris (permanen)
 *   Input        id_tag                      ids
 *
 * Tidak memerlukan role admin.
 */
class TagOk extends BaseSto
{
    /**
     * POST /api/sto/cancel-tag-ok
     *
     * Body: {"ids": ["MAJWLD2808260204514", "MAJ1908260101628"], "id_event": 4}
     *
     * id yang TIDAK ditemukan di-skip (bukan error) dan dilaporkan balik
     * lewat field `skipped`. Request tetap balas HTTP 200 walau tidak ada
     * satupun id yang terhapus, selama format input benar.
     */
    public function cancel(): ResponseInterface
    {
        $tags = new TagOkModel();

        // Menerima key `ids`, `id_tag_ok`, atau `id` supaya fleksibel
        // dari sisi front-end.
        $raw = $this->in('ids') ?? $this->in('id_tag_ok') ?? $this->in('id');

        if ($raw === null) {
            return $this->gagal(
                'Parameter "ids" wajib diisi dan harus berupa array id_tag_ok',
                400
            );
        }

        $ids = $this->normalizeList($raw);

        if ($ids === []) {
            return $this->gagal('Parameter "ids" kosong / tidak berisi id_tag_ok yang valid', 400);
        }

        if (count($ids) > self::MAX_IDS) {
            return $this->gagal(
                'Jumlah id melebihi batas maksimal ' . self::MAX_IDS . ' per request',
                400
            );
        }

        // Filter opsional: batasi penghapusan hanya pada 1 event STO.
        // Kalau tidak dikirim, semua baris dengan id_tag_ok tsb akan dihapus.
        $idEvent = $this->parseId($this->in('id_event'));

        if ($idEvent === false) {
            return $this->gagal('Parameter "id_event" harus berupa angka', 400);
        }

        $found = $tags->getExistingTags($ids, $idEvent);

        if ($found === false) {
            return $this->gagal('Gagal membaca data tag STO', 500);
        }

        // Yang tidak ketemu -> di-skip, tidak dianggap error.
        $skipped = array_values(array_diff($ids, $found));

        if ($found === []) {
            return $this->ok([
                'status'          => 'success',
                'message'         => 'Tidak ada id_tag_ok yang cocok, semua di-skip',
                'total_requested' => count($ids),
                'total_deleted'   => 0,
                'total_skipped'   => count($skipped),
                'deleted'         => [],
                'skipped'         => $skipped,
            ]);
        }

        $deletedRows = $tags->deleteTags($found, $idEvent);

        if ($deletedRows === false) {
            return $this->gagal(
                'Gagal menghapus data tag STO, perubahan dibatalkan (rollback)',
                500
            );
        }

        log_message(
            'info',
            'Cancel tag STO: ' . count($found) . ' id_tag_ok, ' . $deletedRows . ' baris terhapus'
            . ($idEvent === null ? '' : ' (id_event=' . $idEvent . ')')
        );

        return $this->ok([
            'status'          => 'success',
            'message'         => 'Cancel tag STO selesai',
            'total_requested' => count($ids),
            'total_deleted'   => count($found),
            'total_skipped'   => count($skipped),
            'deleted_rows'    => $deletedRows,
            'deleted'         => $found,
            'skipped'         => $skipped,
        ]);
    }
}
