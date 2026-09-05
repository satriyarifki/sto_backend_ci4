-- Database: majsf_inventory (connection group: kanbanInventory)
-- Tabel penampung hasil scan TAG OK untuk kebutuhan Stock Opname.

CREATE TABLE IF NOT EXISTS `table_sto_tag_ok` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `id_tag_ok`   VARCHAR(50)  NOT NULL,
  `process`     VARCHAR(20)  DEFAULT NULL,
  `date`        DATE         DEFAULT NULL,
  `shift`       INT(11)      DEFAULT NULL,
  `line`        VARCHAR(50)  DEFAULT NULL,
  `part_number` VARCHAR(50)  DEFAULT NULL,
  `job_number`  VARCHAR(512) DEFAULT NULL,
  `qty_kbn`     VARCHAR(11)  DEFAULT NULL,
  `status`      VARCHAR(50)  DEFAULT NULL,
  `project`     VARCHAR(15)  DEFAULT NULL,
  `customer`    VARCHAR(20)  DEFAULT NULL,
  `user_create` VARCHAR(50)  DEFAULT NULL,
  `area`        VARCHAR(50)  NOT NULL,
  `scan_at`     DATETIME     NOT NULL,
  `scan_by`     VARCHAR(50)  NOT NULL,
  `id_event`    INT(11)      DEFAULT NULL,
  `created_at`  DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tag_event` (`id_tag_ok`, `id_event`),
  KEY `idx_scan_by` (`scan_by`),
  KEY `idx_area` (`area`),
  KEY `idx_scan_at` (`scan_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
