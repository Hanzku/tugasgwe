-- Jalankan pada MySQL/MariaDB. Data berlabel DEMO hanya untuk pengembangan.
CREATE DATABASE IF NOT EXISTS sia_malaka CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sia_malaka;

CREATE TABLE IF NOT EXISTS pelajaran (
  kd_pelajaran VARCHAR(20) NOT NULL,
  nama_pelajaran VARCHAR(100) NOT NULL,
  bobot DECIMAL(5,2) NOT NULL,
  PRIMARY KEY (kd_pelajaran),
  CONSTRAINT chk_pelajaran_bobot CHECK (bobot BETWEEN 1 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DEMO: contoh saja, bukan data resmi sekolah. INSERT IGNORE aman bila file dijalankan ulang.
INSERT IGNORE INTO pelajaran (kd_pelajaran, nama_pelajaran, bobot) VALUES
('DEMO-BIN-01', 'Bahasa Indonesia (Demo)', 10.00),
('DEMO-MTK-01', 'Matematika (Demo)', 15.00),
('DEMO-TKJ-01', 'Administrasi Infrastruktur Jaringan (Demo)', 25.00);