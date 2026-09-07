-- =====================================================================
-- Optimalisasi majsf_inventory.v_print_tag_ok_all  (server 192.168.10.67)
-- MySQL 5.7.32
--
-- MASALAH
--   Lookup `SELECT * FROM v_print_tag_ok_all WHERE id_tag_ok = ?` = 9.1 detik.
--   View ini ber-UNION ALL, dan MySQL 5.7 tidak bisa me-merge view ber-UNION
--   (tidak ada derived condition pushdown -- itu baru ada di MySQL 8.0.22+).
--   Jadi urutan kerjanya:
--     1. full scan welding_production_tag_ok (1.517.749 baris)
--        + full scan ms_patan_tag_ok (778.401 baris)
--        -> idx_create_date_part_number DIABAIKAN optimizer karena window
--           3 bulan masih menyentuh ~356rb / ~151rb baris (terlalu banyak
--           untuk range scan + lookup, jadi full scan dianggap lebih murah)
--     2. LEFT JOIN master part untuk ~507rb baris hasil
--     3. materialisasi ke temp table + filesort (ORDER BY create_date DESC)
--     4. BARU filter id_tag_ok = ?
--
--   Kedua pemakainya di aplikasi (TagOkModel::getPrepareById dan
--   M_tag_ok::getTagOk) sama-sama hanya mengambil SATU baris per id_tag_ok.
--
-- HASIL UKUR
--   view apa adanya .............................. 9.1 s
--   view tanpa ORDER BY .......................... 8.7 s   (filesort bukan biang keroknya)
--   filter didorong ke tiap cabang UNION ......... 2.5 s   (masih tanpa index di bawah)
--   + index id_tag_ok (bagian 1) ................. diharapkan orde milidetik
--
-- URUTAN JALANKAN: bagian 1 dulu, baru bagian 2.
-- =====================================================================


-- ---------------------------------------------------------------------
-- BAGIAN 1 -- Index pencarian per id_tag_ok  (INI YANG PALING BERDAMPAK)
--
-- Saat ini TIDAK ADA index yang bisa mencari berdasarkan id_tag_ok:
--   welding_production_tag_ok  PK = (date, shift, line_id, id_tag_ok, part_number)
--   ms_patan_tag_ok            PK = (date, shift, line_id, id_tag_ok)
-- id_tag_ok ada di posisi ke-4/ke-5, jadi tidak bisa dipakai sebagai prefix.
--   log_print_tag_ok           tidak punya index apa pun untuk id_label.
--
-- ALGORITHM=INPLACE, LOCK=NONE -> tabel tetap bisa dibaca DAN ditulis
-- selama index dibangun. Tetap disarankan jalankan di jam sepi.
-- Perkiraan: welding ~30-60 detik, patan ~15-30 detik, log_print instan.
-- Tambahan ruang disk: kira-kira 60-90 MB untuk ketiganya.
-- ---------------------------------------------------------------------

ALTER TABLE majsf_andon_welding.welding_production_tag_ok
    ADD INDEX idx_id_tag_ok (id_tag_ok),
    ALGORITHM = INPLACE, LOCK = NONE;

ALTER TABLE majsf_inventory.ms_patan_tag_ok
    ADD INDEX idx_id_tag_ok (id_tag_ok),
    ALGORITHM = INPLACE, LOCK = NONE;

ALTER TABLE majsf_inventory.log_print_tag_ok
    ADD INDEX idx_id_label (id_label),
    ALGORITHM = INPLACE, LOCK = NONE;


-- ---------------------------------------------------------------------
-- BAGIAN 2 -- Definisi ulang view: buang ORDER BY
--
-- ORDER BY create_date DESC di dalam view memaksa filesort atas seluruh
-- hasil materialisasi (~507rb baris) SETIAP kali view disentuh, padahal
-- kedua pemakainya cuma mengambil satu baris berdasarkan id_tag_ok --
-- tidak ada yang memanfaatkan urutan itu.
--
-- Sisa definisinya sengaja dibiarkan identik (termasuk window 3 bulan dan
-- CONVERT(... USING utf8) yang memang perlu, karena part_number di tabel
-- welding/patan latin1 sedangkan di master part utf8).
--
-- CATATAN: kalau nanti ada pemakai baru yang butuh daftar berurutan, dia
-- harus menulis ORDER BY create_date DESC sendiri di query pemanggil.
-- ---------------------------------------------------------------------

ALTER ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW majsf_inventory.v_print_tag_ok_all AS
SELECT
    'WELDING'          AS `process`,
    wto.id_tag_ok      AS `id_tag_ok`,
    wto.`date`         AS `date`,
    wto.shift          AS `shift`,
    wto.line_id        AS `line`,
    wto.part_number    AS `part_number`,
    msi.job_number     AS `job_number`,
    msi.qty_kbn        AS `qty_kbn`,
    wto.status         AS `status`,
    msi.project        AS `project`,
    msi.customer       AS `customer`,
    wto.create_date    AS `create_date`,
    wto.user_create    AS `user_create`
FROM majsf_andon_welding.welding_production_tag_ok wto
LEFT JOIN majsf_inventory.ms_data_ifp msi
       ON msi.part_number = CONVERT(wto.part_number USING utf8)
WHERE wto.create_date > (CURDATE() - INTERVAL 3 MONTH)

UNION ALL

SELECT
    'PRESS',
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
WHERE pto.create_date > (CURDATE() - INTERVAL 3 MONTH)

UNION ALL

SELECT
    'PRESS',
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
WHERE lto.date_print > (CURDATE() - INTERVAL 3 MONTH);


-- ---------------------------------------------------------------------
-- VERIFIKASI setelah bagian 1 & 2 dijalankan
-- ---------------------------------------------------------------------
-- Ganti '<ID_TAG_OK>' dengan id tag yang benar-benar ada, lalu bandingkan
-- waktu tempuh query lama vs query baru yang dipakai aplikasi.
--
-- Harapan pada EXPLAIN query baru: setiap cabang UNION bertipe `ref`
-- dengan key = idx_id_tag_ok / idx_id_label dan rows = 1.

-- SET profiling = 1;
-- SELECT * FROM majsf_inventory.v_print_tag_ok_all WHERE id_tag_ok = '<ID_TAG_OK>';
-- SHOW PROFILES;


-- ---------------------------------------------------------------------
-- ROLLBACK (kalau perlu dikembalikan)
-- ---------------------------------------------------------------------
-- ALTER TABLE majsf_andon_welding.welding_production_tag_ok DROP INDEX idx_id_tag_ok;
-- ALTER TABLE majsf_inventory.ms_patan_tag_ok              DROP INDEX idx_id_tag_ok;
-- ALTER TABLE majsf_inventory.log_print_tag_ok             DROP INDEX idx_id_label;
-- Untuk view: jalankan ulang ALTER VIEW di atas dengan menambahkan
--   ORDER BY create_date DESC
-- di baris paling akhir.
