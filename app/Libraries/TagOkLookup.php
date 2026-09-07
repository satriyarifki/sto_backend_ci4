<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Pencarian satu tag OK produksi berdasarkan id_tag_ok.
 *
 * KENAPA TIDAK LANGSUNG `SELECT * FROM majsf_inventory.v_print_tag_ok_all
 * WHERE id_tag_ok = ?`
 *
 * View itu ber-UNION ALL, dan MySQL 5.7 tidak bisa me-merge view ber-UNION
 * (derived condition pushdown baru ada di MySQL 8.0.22+). Jadi filter
 * id_tag_ok di luar view diterapkan PALING AKHIR: MySQL lebih dulu full-scan
 * welding_production_tag_ok (~1,5 juta baris) dan ms_patan_tag_ok (~778 ribu),
 * mem-LEFT JOIN master part untuk ~507 ribu baris hasil, memateralisasinya ke
 * temp table, lalu filesort -- baru mencari satu baris yang dicari.
 * Terukur 9,1 detik untuk satu tag.
 *
 * Kelas ini menulis ulang query yang sama dengan filter id_tag_ok DIDORONG
 * MASUK ke tiap cabang UNION, sehingga tiap cabang bisa memakai index dan
 * hanya menyentuh satu baris. Terukur 2,5 detik tanpa index tambahan, dan
 * turun ke orde milidetik setelah index di app/Database/optimize_v_print_tag_ok_all.sql
 * dipasang.
 *
 * Kolom dan aturan bisnisnya sengaja dijaga persis sama dengan view -- termasuk
 * window 3 bulan dan CONVERT(... USING utf8) pada join part_number (kolomnya
 * latin1 di tabel produksi tapi utf8 di master part). Kalau definisi view
 * berubah, kelas ini harus ikut diubah.
 *
 * @see app/Database/optimize_v_print_tag_ok_all.sql
 */
class TagOkLookup
{
    /**
     * Padanan v_print_tag_ok_all untuk satu id_tag_ok.
     *
     * Ketiga cabang memakai parameter yang sama, jadi pemanggil mengikat
     * id_tag_ok sebanyak tiga kali.
     */
    private const SQL = <<<'SQL'
        SELECT 'WELDING'       AS `process`,
               wto.id_tag_ok   AS `id_tag_ok`,
               wto.`date`      AS `date`,
               wto.shift       AS `shift`,
               wto.line_id     AS `line`,
               wto.part_number AS `part_number`,
               msi.job_number  AS `job_number`,
               msi.qty_kbn     AS `qty_kbn`,
               wto.status      AS `status`,
               msi.project     AS `project`,
               msi.customer    AS `customer`,
               wto.create_date AS `create_date`,
               wto.user_create AS `user_create`
          FROM majsf_andon_welding.welding_production_tag_ok wto
          LEFT JOIN majsf_inventory.ms_data_ifp msi
                 ON msi.part_number = CONVERT(wto.part_number USING utf8)
         WHERE wto.id_tag_ok = ?
           AND wto.create_date > (CURDATE() - INTERVAL 3 MONTH)

        UNION ALL

        SELECT 'PRESS',
               pto.id_tag_ok,
               pto.`date`,
               pto.shift,
               pto.line_id,
               pto.part_number,
               msi.job_number,
               msi.qty_pallet,
               pto.status,
               '-',
               '-',
               pto.create_date,
               pto.user_create
          FROM majsf_inventory.ms_patan_tag_ok pto
          LEFT JOIN majsf_inventory.ms_part_sap msi
                 ON msi.part_number = CONVERT(pto.part_number USING utf8)
         WHERE pto.id_tag_ok = ?
           AND pto.create_date > (CURDATE() - INTERVAL 3 MONTH)

        UNION ALL

        SELECT 'PRESS',
               lto.id_label,
               lto.`date`,
               lto.shift,
               lto.line_id,
               lto.part_number,
               msi.job_number,
               msi.qty_kbn,
               lto.status,
               '-',
               '-',
               lto.date_print,
               lto.user_create
          FROM majsf_inventory.log_print_tag_ok lto
          LEFT JOIN majsf_inventory.ms_data_kanban msi
                 ON msi.part_number = lto.part_number
         WHERE lto.id_label = ?
           AND lto.date_print > (CURDATE() - INTERVAL 3 MONTH)

         LIMIT 1
        SQL;

    /**
     * Satu baris detail tag OK, atau null bila tidak ketemu.
     *
     * Query-nya lintas-database dengan nama tabel yang sudah berkualifikasi
     * penuh, jadi koneksi mana pun ke server 10.67 bisa dipakai -- baik
     * `db_sto` maupun `kanbanInventory`.
     *
     * @return array<string, mixed>|null
     */
    public static function byId(BaseConnection $db, string $idTagOk): ?array
    {
        $row = $db->query(self::SQL, [$idTagOk, $idTagOk, $idTagOk])->getRowArray();

        return $row ?: null;
    }
}
