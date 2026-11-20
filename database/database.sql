-- =====================================================================
-- BOOTCAMP PRE-UKK FULLSTACK DEVELOPER - LOCAL IFP EDITION
-- Database: bootcamp_fullstack
-- Penyusun: Riko Hardona, S.Pd. - SMKN 6 Muaro Jambi
-- =====================================================================
-- Cara import (Laragon):
--   mysql -u root < database/database.sql
-- Atau melalui phpMyAdmin: pilih Import, lalu pilih file ini.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS bootcamp_fullstack
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bootcamp_fullstack;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. students - Data siswa peserta bootcamp
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS student_jobsheets;
DROP TABLE IF EXISTS student_progress;
DROP TABLE IF EXISTS seminar_assessments;
DROP TABLE IF EXISTS competency_assessments;
DROP TABLE IF EXISTS coding_attempts;
DROP TABLE IF EXISTS remediation;
DROP TABLE IF EXISTS enrichment;
DROP TABLE IF EXISTS activity_log;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS learning_materials;
DROP TABLE IF EXISTS bootcamp_sessions;
DROP TABLE IF EXISTS seminar_profiles;
DROP TABLE IF EXISTS question_bank;
DROP TABLE IF EXISTS jobsheets;
DROP TABLE IF EXISTS coding_challenges;
DROP TABLE IF EXISTS settings;

CREATE TABLE students (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nis           VARCHAR(30) NOT NULL UNIQUE,
  name          VARCHAR(100) NOT NULL,
  class_name    VARCHAR(30) NOT NULL DEFAULT 'XII PPLG 1',
  gender        ENUM('L','P') NOT NULL DEFAULT 'L',
  phone         VARCHAR(20) DEFAULT NULL,
  email         VARCHAR(120) DEFAULT NULL,
  pkl_place     VARCHAR(180) DEFAULT NULL,
  pkl_title     VARCHAR(200) DEFAULT NULL,
  technologies  TEXT DEFAULT NULL,
  problem_desc  TEXT DEFAULT NULL,
  features      TEXT DEFAULT NULL,
  status        ENUM('aktif','alumni') NOT NULL DEFAULT 'aktif',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. users - Login guru dan siswa
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username    VARCHAR(50) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  full_name   VARCHAR(120) NOT NULL,
  role        ENUM('admin','guru','siswa') NOT NULL DEFAULT 'guru',
  student_id  INT UNSIGNED DEFAULT NULL,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  last_login  DATETIME DEFAULT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_users_role (role),
  CONSTRAINT fk_users_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. settings - Pengaturan aplikasi
-- ---------------------------------------------------------------------
CREATE TABLE settings (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key   VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NOT NULL,
  description   VARCHAR(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. bootcamp_sessions - 16 pertemuan bootcamp
-- ---------------------------------------------------------------------
CREATE TABLE bootcamp_sessions (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  week_no        TINYINT UNSIGNED NOT NULL,
  session_no     TINYINT UNSIGNED NOT NULL UNIQUE,
  title          VARCHAR(200) NOT NULL,
  objectives     TEXT NOT NULL,
  core_material  TEXT NOT NULL,
  concept        TEXT NOT NULL,
  diagram        TEXT DEFAULT NULL,
  code_example   TEXT DEFAULT NULL,
  demo           TEXT DEFAULT NULL,
  exercise       TEXT NOT NULL,
  questions      TEXT DEFAULT NULL,
  evaluation     TEXT DEFAULT NULL,
  reflection     TEXT DEFAULT NULL,
  duration_min   INT NOT NULL DEFAULT 90,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. learning_materials - Materi pendukung tiap pertemuan
-- ---------------------------------------------------------------------
CREATE TABLE learning_materials (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id  INT UNSIGNED NOT NULL,
  title       VARCHAR(200) NOT NULL,
  material_type ENUM('teks','kode','gambar','video_lokal','dokumen') NOT NULL DEFAULT 'teks',
  content     LONGTEXT DEFAULT NULL,
  file_path   VARCHAR(255) DEFAULT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_lm_session (session_id),
  CONSTRAINT fk_lm_session FOREIGN KEY (session_id) REFERENCES bootcamp_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. student_progress - Kemajuan materi siswa
-- ---------------------------------------------------------------------
CREATE TABLE student_progress (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id  INT UNSIGNED NOT NULL,
  session_id  INT UNSIGNED NOT NULL,
  status      ENUM('belum','sedang','selesai') NOT NULL DEFAULT 'belum',
  completed_at DATETIME DEFAULT NULL,
  notes       TEXT DEFAULT NULL,
  UNIQUE KEY uq_progress (student_id, session_id),
  CONSTRAINT fk_progress_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_progress_session FOREIGN KEY (session_id) REFERENCES bootcamp_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. seminar_profiles - Identitas & presentasi PKL siswa
-- ---------------------------------------------------------------------
CREATE TABLE seminar_profiles (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id      INT UNSIGNED NOT NULL,
  pkl_place       VARCHAR(180) NOT NULL,
  pkl_period      VARCHAR(100) DEFAULT NULL,
  app_title       VARCHAR(200) NOT NULL,
  technologies    TEXT NOT NULL,
  problem_desc    TEXT NOT NULL,
  requirement     TEXT DEFAULT NULL,
  architecture    TEXT DEFAULT NULL,
  erd             TEXT DEFAULT NULL,
  folder_structure TEXT DEFAULT NULL,
  demo_notes      TEXT DEFAULT NULL,
  features        TEXT DEFAULT NULL,
  status          ENUM('draft','siap','dinilai') NOT NULL DEFAULT 'draft',
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_seminar_profile (student_id),
  CONSTRAINT fk_seminar_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. question_bank - Bank pertanyaan bedah kode
-- ---------------------------------------------------------------------
CREATE TABLE question_bank (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category       ENUM('HTML','CSS','JavaScript','PHP','MySQL','API','Authentication','Debugging','Security') NOT NULL,
  question       TEXT NOT NULL,
  expected_answer TEXT DEFAULT NULL,
  difficulty     ENUM('mudah','sedang','sulit') NOT NULL DEFAULT 'sedang',
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_qb_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. coding_challenges - Bank tantangan live coding
-- ---------------------------------------------------------------------
CREATE TABLE coding_challenges (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code            VARCHAR(30) NOT NULL UNIQUE,
  title           VARCHAR(200) NOT NULL,
  description     TEXT NOT NULL,
  level           ENUM('beginner','intermediate','advanced') NOT NULL,
  competency      VARCHAR(150) NOT NULL,
  duration_min    INT NOT NULL DEFAULT 15,
  instructions    TEXT NOT NULL,
  success_criteria TEXT NOT NULL,
  rubric          TEXT DEFAULT NULL,
  starter_code    TEXT DEFAULT NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_cc_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. seminar_assessments - Penilaian seminar PKL
-- ---------------------------------------------------------------------
CREATE TABLE seminar_assessments (
  id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id            INT UNSIGNED NOT NULL,
  assessor_id           INT UNSIGNED DEFAULT NULL,
  problem_understanding TINYINT UNSIGNED NOT NULL DEFAULT 0,
  architecture_db       TINYINT UNSIGNED NOT NULL DEFAULT 0,
  code_understanding    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  live_coding           TINYINT UNSIGNED NOT NULL DEFAULT 0,
  testing               TINYINT UNSIGNED NOT NULL DEFAULT 0,
  communication         TINYINT UNSIGNED NOT NULL DEFAULT 0,
  total_score           DECIMAL(5,2) NOT NULL DEFAULT 0,
  selected_question_ids TEXT DEFAULT NULL,
  challenge_id          INT UNSIGNED DEFAULT NULL,
  observation_sheet     TEXT DEFAULT NULL,
  assessor_notes        TEXT DEFAULT NULL,
  assessed_at           DATETIME NOT NULL,
  created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_sa_student (student_id),
  CONSTRAINT fk_sa_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sa_assessor FOREIGN KEY (assessor_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_sa_challenge FOREIGN KEY (challenge_id) REFERENCES coding_challenges(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. coding_attempts - Catatan sesi live coding
-- ---------------------------------------------------------------------
CREATE TABLE coding_attempts (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id     INT UNSIGNED NOT NULL,
  challenge_id   INT UNSIGNED NOT NULL,
  started_at     DATETIME NOT NULL,
  ended_at       DATETIME DEFAULT NULL,
  duration_sec   INT NOT NULL DEFAULT 0,
  status         ENUM('berjalan','selesai','dinilai') NOT NULL DEFAULT 'berjalan',
  result_text    TEXT DEFAULT NULL,
  examiner_score TINYINT UNSIGNED DEFAULT NULL,
  examiner_notes TEXT DEFAULT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_ca_student (student_id),
  CONSTRAINT fk_ca_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_ca_challenge FOREIGN KEY (challenge_id) REFERENCES coding_challenges(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 12. jobsheets - 12 jobsheet praktikum
-- ---------------------------------------------------------------------
CREATE TABLE jobsheets (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code         VARCHAR(30) NOT NULL UNIQUE,
  title        VARCHAR(200) NOT NULL,
  objective    TEXT NOT NULL,
  tools        TEXT NOT NULL,
  concept      TEXT NOT NULL,
  steps        LONGTEXT NOT NULL,
  source_code  LONGTEXT DEFAULT NULL,
  task         TEXT NOT NULL,
  reflection   TEXT NOT NULL,
  duration_min INT NOT NULL DEFAULT 90,
  sort_order   INT NOT NULL DEFAULT 0,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 13. student_jobsheets - Progres penyelesaian jobsheet
-- ---------------------------------------------------------------------
CREATE TABLE student_jobsheets (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id   INT UNSIGNED NOT NULL,
  jobsheet_id  INT UNSIGNED NOT NULL,
  status       ENUM('belum','proses','selesai') NOT NULL DEFAULT 'belum',
  completed_at DATETIME DEFAULT NULL,
  notes        TEXT DEFAULT NULL,
  UNIQUE KEY uq_student_jobsheet (student_id, jobsheet_id),
  CONSTRAINT fk_sj_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_sj_jobsheet FOREIGN KEY (jobsheet_id) REFERENCES jobsheets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 14. competency_assessments - Penilaian kompetensi (skala 1-4)
-- ---------------------------------------------------------------------
CREATE TABLE competency_assessments (
  id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id           INT UNSIGNED NOT NULL,
  assessor_id          INT UNSIGNED DEFAULT NULL,
  assessment_date      DATE NOT NULL,
  score_understanding  TINYINT UNSIGNED NOT NULL DEFAULT 1,
  score_trace          TINYINT UNSIGNED NOT NULL DEFAULT 1,
  score_implementation TINYINT UNSIGNED NOT NULL DEFAULT 1,
  score_db_security    TINYINT UNSIGNED NOT NULL DEFAULT 1,
  score_testing        TINYINT UNSIGNED NOT NULL DEFAULT 1,
  score_communication  TINYINT UNSIGNED NOT NULL DEFAULT 1,
  total_score          DECIMAL(5,2) NOT NULL DEFAULT 0,
  notes                TEXT DEFAULT NULL,
  created_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_ca_date (assessment_date),
  CONSTRAINT fk_comp_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_comp_assessor FOREIGN KEY (assessor_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 15. remediation - Program remediasi
-- ---------------------------------------------------------------------
CREATE TABLE remediation (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id  INT UNSIGNED NOT NULL,
  competency  VARCHAR(150) NOT NULL,
  material    TEXT NOT NULL,
  task        TEXT NOT NULL,
  notes       TEXT DEFAULT NULL,
  status      ENUM('rencana','proses','selesai') NOT NULL DEFAULT 'rencana',
  due_date    DATE DEFAULT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_rem_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 16. enrichment - Program pengayaan
-- ---------------------------------------------------------------------
CREATE TABLE enrichment (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id  INT UNSIGNED NOT NULL,
  competency  VARCHAR(150) NOT NULL,
  material    TEXT NOT NULL,
  task        TEXT NOT NULL,
  notes       TEXT DEFAULT NULL,
  status      ENUM('rencana','proses','selesai') NOT NULL DEFAULT 'rencana',
  due_date    DATE DEFAULT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ens_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 17. activity_log - Jejak aktivitas sederhana
-- ---------------------------------------------------------------------
CREATE TABLE activity_log (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED DEFAULT NULL,
  action      VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  ip_address  VARCHAR(45) DEFAULT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_al_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- DATA DUMMY
-- =====================================================================

INSERT INTO settings (setting_key, setting_value, description) VALUES
('app_name', 'BOOTCAMP PRE-UKK FULLSTACK DEVELOPER', 'Nama aplikasi'),
('school_name', 'SMKN 6 Muaro Jambi', 'Nama sekolah'),
('program_name', 'PPLG - Kelas XII', 'Program keahlian'),
('teacher_name', 'Riko Hardona, S.Pd.', 'Penyusun program'),
('academic_year', '2025/2026', 'Tahun ajaran'),
('session_duration', '90', 'Durasi satu pertemuan (menit)'),
('passing_score', '75', 'Kriteria siap UKK'),
('ifp_resolution', '1920x1080', 'Resolusi utama IFP');

-- 12 siswa dummy
INSERT INTO students (id, nis, name, class_name, gender, phone, email, pkl_place, pkl_title, technologies, problem_desc, features, status) VALUES
(1,  '2023001', 'Ahmad Fauzi',       'XII PPLG 1', 'L', '08127000001', 'ahmad.fauzi@siswa.smkn6mj.sch.id', 'PT Sinar Teknologi', 'Sistem Informasi Inventaris Barang', 'PHP, MySQL, Bootstrap, JavaScript', 'Pencatatan barang masih menggunakan buku tulis sehingga sering terjadi selisih stok.', 'Tambah barang, daftar barang, pencarian, laporan stok', 'aktif'),
(2,  '2023002', 'Siti Nurhaliza',    'XII PPLG 1', 'P', '08127000002', 'siti.nurhaliza@siswa.smkn6mj.sch.id', 'CV Digital Kreatif', 'Aplikasi Kasir Cafe', 'PHP, MySQL, jQuery, CSS', 'Perhitungan transaksi manual memakan waktu dan rawan salah.', 'Transaksi, keranjang, cetak struk, laporan harian', 'aktif'),
(3,  '2023003', 'Rizky Pratama',     'XII PPLG 1', 'L', '08127000003', 'rizky.pratama@siswa.smkn6mj.sch.id', 'Toko Buku Mandiri', 'Sistem Penjualan Buku', 'PHP Native, MySQL, HTML, CSS', 'Data penjualan dan stok buku tersebar di beberapa buku catatan.', 'Data buku, transaksi, stok, laporan penjualan', 'aktif'),
(4,  '2023004', 'Dewi Sartika',      'XII PPLG 2', 'P', '08127000004', 'dewi.sartika@siswa.smkn6mj.sch.id', 'RS Sehat Bersama', 'Sistem Antrian Pasien', 'PHP, MySQL, AJAX', 'Pasien menunggu tanpa informasi urutan antrian yang jelas.', 'Ambil nomor antrian, tampilkan antrian, panggil pasien', 'aktif'),
(5,  '2023005', 'Muhammad Ilham',   'XII PPLG 2', 'L', '08127000005', 'muhammad.ilham@siswa.smkn6mj.sch.id', 'PT Maju Jaya', 'Aplikasi Absensi Karyawan', 'PHP, MySQL, Chart.js lokal', 'Absensi karyawan masih menggunakan kertas dan sulit direkap.', 'Absensi harian, rekap kehadiran, grafik kehadiran', 'aktif'),
(6,  '2023006', 'Nur Aisyah',        'XII PPLG 2', 'P', '08127000006', 'nur.aisyah@siswa.smkn6mj.sch.id', 'SMA Negeri 3 Muaro Jambi', 'Sistem Perpustakaan Digital', 'PHP, MySQL, Bootstrap', 'Peminjaman buku dicatat manual dan sering terlambat dikembalikan.', 'Katalog buku, peminjaman, pengembalian, denda', 'aktif'),
(7,  '2023007', 'Andi Saputra',      'XII PPLG 3', 'L', '08127000007', 'andi.saputra@siswa.smkn6mj.sch.id', 'CV Mitra Komputer', 'Aplikasi Service Komputer', 'PHP, MySQL, JavaScript', 'Catatan servis komputer sulit dilacak saat garansi.', 'Registrasi servis, status perbaikan, catatan garansi', 'aktif'),
(8,  '2023008', 'Putri Maharani',    'XII PPLG 3', 'P', '08127000008', 'putri.maharani@siswa.smkn6mj.sch.id', 'Klinik Kecantikan Aura', 'Sistem Booking Konsultasi', 'PHP, MySQL, FullCalendar lokal', 'Jadwal konsultasi bertabrakan karena dibuat manual.', 'Booking online, jadwal dokter, konfirmasi, pembatalan', 'aktif'),
(9,  '2023009', 'Fajar Ramadhan',    'XII PPLG 3', 'L', '08127000009', 'fajar.ramadhan@siswa.smkn6mj.sch.id', 'PT Nusantara Logistik', 'Sistem Tracking Pengiriman', 'PHP, MySQL, AJAX, CSS', 'Customer sulit mengetahui posisi paket yang dikirim.', 'Input resi, lacak paket, riwayat pengiriman', 'aktif'),
(10, '2023010', 'Lala Fitri',        'XII PPLG 1', 'P', '08127000010', 'lala.fitri@siswa.smkn6mj.sch.id', 'UMKM Kopi Senja', 'Aplikasi Pemesanan Kopi', 'PHP, MySQL, JavaScript', 'Pemesanan masih lewatpesan chat sehingga mudah terlewat.', 'Menu kopi, keranjang, pemesanan, riwayat pesanan', 'aktif'),
(11, '2023011', 'Bimo Setiawan',    'XII PPLG 2', 'L', '08127000011', 'bimo.setiawan@siswa.smkn6mj.sch.id', 'PT Graha Grafika', 'Sistem Pesanan Cetak', 'PHP, MySQL, PDO', 'Data pesanan cetak belum terpusat dan sering dobel input.', 'Pesanan cetak, estimasi biaya, status produksi', 'aktif'),
(12, '2023012', 'Intan Permata',    'XII PPLG 3', 'P', '08127000012', 'intan.permata@siswa.smkn6mj.sch.id', 'Yayasan Pendidikan Nur', 'Sistem Pendaftaran Siswa Baru', 'PHP, MySQL, Bootstrap', 'Formulir pendaftaran masih berbasis kertas dan sulit direkap.', 'Form pendaftaran, upload berkas, status seleksi', 'aktif');

-- User guru + 12 user siswa (password: guru123 / siswa123)
INSERT INTO users (id, username, password, full_name, role, student_id, is_active) VALUES
(1, 'guru',  '$2y$10$2frRVC3jrhQ8NF4vFhsbkO2id43fujSrK8PwIOSdLjGvQqhUnkkMK', 'Riko Hardona, S.Pd.', 'admin', NULL, 1),
(2, 'guru2', '$2y$10$2frRVC3jrhQ8NF4vFhsbkO2id43fujSrK8PwIOSdLjGvQqhUnkkMK', 'Dewi Anggraini, S.Pd.', 'guru', NULL, 1),
(3,  '2023001', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Ahmad Fauzi', 'siswa', 1, 1),
(4,  '2023002', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Siti Nurhaliza', 'siswa', 2, 1),
(5,  '2023003', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Rizky Pratama', 'siswa', 3, 1),
(6,  '2023004', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Dewi Sartika', 'siswa', 4, 1),
(7,  '2023005', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Muhammad Ilham', 'siswa', 5, 1),
(8,  '2023006', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Nur Aisyah', 'siswa', 6, 1),
(9,  '2023007', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Andi Saputra', 'siswa', 7, 1),
(10, '2023008', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Putri Maharani', 'siswa', 8, 1),
(11, '2023009', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Fajar Ramadhan', 'siswa', 9, 1),
(12, '2023010', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Lala Fitri', 'siswa', 10, 1),
(13, '2023011', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Bimo Setiawan', 'siswa', 11, 1),
(14, '2023012', '$2y$10$SPsJXFt.MtteENTSYzB8Nuz.k5NkRU6x8Gi9562i2itenwMp7.xei', 'Intan Permata', 'siswa', 12, 1);

-- =====================================================================
-- 16 PERTEMUAN BOOTCAMP
-- =====================================================================
INSERT INTO bootcamp_sessions
(id, week_no, session_no, title, objectives, core_material, concept, diagram, code_example, demo, exercise, questions, evaluation, reflection, duration_min) VALUES

(1, 1, 1, 'Orientasi, Diagnostik, dan Konsep Client-Server',
'Tujuan: Siswa mampu menjelaskan alur kerja aplikasi web sederhana serta mengenal konsep client-server menggunakan analogi sehari-hari.
Siswa mampu mengikuti aturan bootcamp dan mengetahui target kompetensi UKK.',
'Materi inti: Perkenalan bootcamp 4 minggu, 16 pertemuan. Diagnostik awal kemampuan HTML, PHP, dan MySQL. Konsep client, server, dan database. Alur request-response HTTP.',
'Analogi: bayangkan sebuah warung. Pembeli adalah client (browser), kasir adalah server (PHP), dan gudang adalah database (MySQL). Pembeli memesan (request), kasir mengambil barang dari gudang, lalu memberi jawaban (response).',
'Browser / HTML-CSS-JS  ==>  HTTP Request  ==>  Apache + PHP  ==>  SQL  ==>  MySQL
MySQL  ==>  hasil query  ==>  PHP  ==>  HTTP Response  ==>  Browser',
'<!-- index.html -->
<form method="post" action="proses.php">
  <input type="text" name="nama">
  <button type="submit">Kirim</button>
</form>

<!-- proses.php -->
<?php echo "Halo " . htmlspecialchars($_POST["nama"]); ?>',
'Guru membuka halaman localhost di browser, mengetik nama, menekan tombol Kirim, lalu menunjukkan bahwa data dikirim ke PHP dan ditampilkan kembali.',
'Gambarlah diagram client-server di kertas. Tulis satu contoh kegiatan sehari-hari yang mirip dengan alur request-response.',
'Apa peran browser dalam aplikasi web?|Apa bedanya server dan database|Mengapa PHP disebut bahasa sisi server?|Apa yang terjadi setelah tombol Kirim ditekan?',
'Kuis lisan 5 pertanyaan tentang client-server dan pengisian angket diagnostik mandiri.',
'Tuliskan satu hal yang sudah kamu pahami dan satu hal yang masih membingungkan tentang alur aplikasi web.', 90),

(2, 1, 2, 'Bedah Aplikasi PKL, Struktur Folder, dan Arsitektur',
'Tujuan: Siswa mampu membaca struktur folder proyek dan menjelaskan fungsi setiap file dalam aplikasi PKL.',
'Materi inti: Struktur folder proyek (config, auth, modules, assets, database). Pemisahan tampilan, logika, dan data. Arsitektur tiga lapis (presentation, business, data). Dependensi lokal tanpa CDN.',
'Analogi: struktur folder seperti lemari arsip. Setiap laci punya label agar mudah dicari. Tanpa label, guru akan kesulitan menemukan file saat melakukan perbaikan.',
'ROOT/
|-- index.php        (halaman utama)
|-- config/          (pengaturan & koneksi)
|-- auth/            (login & hak akses)
|-- modules/         (fitur aplikasi)
|-- assets/          (css, js, gambar lokal)
|-- database/        (schema & dummy data)',
'/bootcamp-fullstack
  /config/database.php  -> koneksi PDO
  /auth/auth.php        -> session login
  /modules/materi/      -> halaman materi
  /database/database.sql-> struktur tabel',
'Guru membuka file manager Laragon, menunjukkan folder proyek, lalu membuka config/database.php dan menjelaskan baris demi baris.',
'Buatlah peta struktur folder aplikasi PKL-mu. Beri keterangan fungsi minimal 5 folder.',
'Mengapa file koneksi database sebaiknya dipisah?|Apa fungsi folder assets?|Apa yang terjadi bila semua kode ditaruh dalam satu file?',
'Penugasan: siswa mengumpulkan peta struktur folder aplikasi PKL dalam bentuk gambar atau teks.',
'Bandingkan struktur folder aplikasi PKL-mu dengan struktur yang dipelajari hari ini. Apa yang perlu diperbaiki?', 90),

(3, 1, 3, 'HTML, CSS, dan JavaScript untuk Antarmuka',
'Tujuan: Siswa mampu membuat tampilan formulir yang rapi dan menambahkan interaksi sederhana dengan JavaScript.',
'Materi inti: Struktur dasar HTML, semantic tag, form dan input. CSS flexbox/grid, warna, dan tipografi. JavaScript event, DOM, dan validasi sederhana.',
'Analogi: HTML adalah kerangka dan isi rumah, CSS adalah cat serta perabot, JavaScript adalah sakelar lampu yang membuat rumah menjadi interaktif.',
'HTML (struktur) + CSS (tampilan) + JavaScript (perilaku) --> Browser Render',
'<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>Form Siswa</title></head>
<body>
  <form id="formSiswa">
    <input type="text" id="nama" placeholder="Nama siswa">
    <button type="submit">Simpan</button>
  </form>
  <script>
    document.getElementById("formSiswa").addEventListener("submit", function (e) {
      e.preventDefault();
      alert("Data siap dikirim ke server");
    });
  </script>
</body>
</html>',
'Guru menulis form di editor kode, mengubah warna tombol lewat CSS, lalu menambahkan alert JavaScript dan mencobanya di browser.',
'Buatlah form biodata dengan minimal 4 field. Tambahkan validasi agar field kosong tidak bisa dikirim.',
'Apa fungsi tag form pada HTML?|Bagaimana cara menambahkan CSS ke halaman?|Apa bedanya onclick dan addEventListener?',
'Praktik mandiri: membuat form biodata responsif dengan validasi JavaScript.',
'Bagian mana dari HTML, CSS, dan JavaScript yang paling kamu kuasai?', 90),

(4, 1, 4, 'PHP Dasar dan HTTP Request-Response',
'Tujuan: Siswa mampu menulis skrip PHP sederhana dan menjelaskan perbedaan GET dan POST.',
'Materi inti: Sintaks PHP, variabel, operator, percabangan, perulangan. Superglobal GET, POST, SERVER. Header HTTP dan session awal. escaping output.',
'Analogi: GET seperti menulis pesan di kartu pos yang terlihat semua orang, POST seperti memasukkan surat ke amplop tertutup.',
'browser --> GET /halaman.php --> Apache --> PHP --> HTML --> browser
browser --> POST /simpan.php (data di body) --> PHP --> redirect',
'<?php
$nama = isset($_POST["nama"]) ? trim($_POST["nama"]) : "";
if ($nama === "") {
  echo "Nama wajib diisi";
} else {
  echo "Halo, " . htmlspecialchars($nama);
}
?>',
'Guru membuat dua file: form.php dengan method GET dan POST, lalu proses.php yang menampilkan isi superglobal tersebut.',
'Buatlah kalkulator PHP sederhana yang menerima dua angka dari form POST dan menampilkan hasil penjumlahan.',
'Apa bedanya $_GET dan $_POST?|Mengapa harus htmlspecialchars saat menampilkan data?|Apa fungsi isset?',
'Tes tertulis singkat: 10 soal pilihan ganda tentang variabel, percabangan, dan superglobal PHP.',
'Tuliskan satu kesalahan yang sering kamu lakukan saat menulis kode PHP.', 90),

(5, 2, 5, 'Database, ERD, Primary Key, Foreign Key, dan SQL',
'Tujuan: Siswa mampu merancang ERD sederhana dan menulis perintah SQL dasar.',
'Materi inti: Konsep tabel, baris, kolom. Primary key sebagai identitas unik. Foreign key sebagai penghubung antartabel. ERD. Perintah CREATE, INSERT, SELECT, UPDATE, DELETE.',
'Analogi: tabel database seperti buku absen. Primary key adalah nomor urut yang tidak boleh sama. Foreign key seperti mencantumkan nomor induk agar data saling terhubung.',
'students (id) <----1:N---- student_jobsheets (student_id)
coding_challenges (id) <----1:N---- coding_attempts (challenge_id)',
'CREATE TABLE siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nis VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL
);
INSERT INTO siswa (nis, nama) VALUES ("2023001", "Ahmad Fauzi");
SELECT * FROM siswa WHERE nis = "2023001";',
'Guru membuka phpMyAdmin, membuat tabel siswa, mengisi data dummy, lalu menjalankan SELECT untuk menampilkan hasil.',
'Buatlah ERD untuk aplikasi PKL-mu minimal 3 tabel dengan primary key dan foreign key yang jelas.',
'Apa fungsi primary key?|Kapan foreign key diperlukan?|Apa bedanya WHERE dan ORDER BY?',
'Praktik SQL: siswa menyelesaikan 8 perintah SELECT, INSERT, UPDATE, DELETE di phpMyAdmin.',
'Komponen ERD mana yang paling sulit kamu pahami?', 90),

(6, 2, 6, 'Koneksi PHP dengan MySQL Menggunakan PDO',
'Tujuan: Siswa mampu membuat koneksi PDO yang aman dan dapat menangani error.',
'Materi inti: PDO sebagai jembatan PHP ke MySQL. DSN, username, password. Opsi ERRMODE_EXCEPTION dan FETCH_ASSOC. Prepared statement. Penanganan exception.',
'Analogi: PDO seperti colokan listrik standar. Selama colokannya sesuai, berbagai perangkat bisa terhubung dengan aman.',
'PHP --> PDO --> MySQL Server --> Database --> Tabel --> Baris data',
'<?php
$dsn = "mysql:host=127.0.0.1;dbname=bootcamp_fullstack;charset=utf8mb4";
try {
  $pdo = new PDO($dsn, "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (PDOException $e) {
  die("Koneksi gagal: " . $e->getMessage());
}
?>',
'Guru membuka config/database.php, menjelaskan setiap baris, lalu sengaja salah nama database untuk menunjukkan penanganan error.',
'Buatlah file koneksi.php untuk database praktikummu. Uji dengan SELECT COUNT(*) dari tabel siswa.',
'Apa gunanya DSN?|Mengapa ERRMODE_EXCEPTION penting?|Apa bedanya fetch dan fetchAll?',
'Siswa membuat file koneksi.php yang berhasil terhubung ke database lokal.',
'Error apa yang paling sering muncul saat koneksi database?', 90),

(7, 2, 7, 'CRUD CREATE dan READ',
'Tujuan: Siswa mampu menambah data baru dan menampilkan data dari database.',
'Materi inti: INSERT dengan prepared statement. SELECT dengan WHERE, ORDER BY, LIMIT. Menampilkan data dalam tabel HTML. escaping output.',
'Analogi: CREATE seperti mengisi formulir pendaftaran baru, READ seperti membaca daftar yang sudah terdaftar.',
'Form --> POST --> INSERT INTO siswa --> SELECT * FROM siswa --> tampil tabel HTML',
'<?php
$stmt = $pdo->prepare("INSERT INTO siswa (nis, nama) VALUES (?, ?)");
$stmt->execute([$nis, $nama]);

$data = $pdo->query("SELECT * FROM siswa ORDER BY id DESC")->fetchAll();
foreach ($data as $row) {
  echo "<tr><td>" . e($row["nis"]) . "</td><td>" . e($row["nama"]) . "</td></tr>";
}
?>',
'Guru mengisi form tambah siswa, menekan Simpan, lalu menunjukkan data langsung muncul di tabel.',
'Tambahkan form input siswa pada aplikasi praktikummu dan tampilkan datanya dalam tabel.',
'Mengapa gunakan tanda ? pada query?|Apa fungsi ORDER BY?|Bagaimana cara menampilkan data ke tabel HTML?',
'Siswa berhasil menambah minimal 3 data dan menampilkannya.',
'Apa yang harus dilakukan agar data tidak dobel saat form dikirim dua kali?', 90),

(8, 2, 8, 'CRUD UPDATE dan DELETE',
'Tujuan: Siswa mampu mengubah dan menghapus data dengan aman.',
'Materi inti: UPDATE dengan WHERE, DELETE dengan WHERE. Bahaya UPDATE/DELETE tanpa WHERE. Konfirmasi hapus. Redirect setelah perubahan.',
'Analogi: UPDATE seperti memperbaiki alamat pada kartu anggota, DELETE seperti mencoret data yang sudah tidak berlaku. Selalu sebut nomor identitasnya.',
'klik Edit --> form berisi data lama --> POST UPDATE ... WHERE id=? --> redirect ke daftar
klik Hapus --> konfirmasi --> POST DELETE ... WHERE id=? --> redirect',
'<?php
// UPDATE
$stmt = $pdo->prepare("UPDATE siswa SET nama = ? WHERE id = ?");
$stmt->execute([$nama, $id]);

// DELETE
$stmt = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
$stmt->execute([$id]);
?>',
'Guru melakukan edit nama siswa, lalu menghapus data dummy, dan menunjukkan pentingnya WHERE lewat simulasi tanpa WHERE (dijelaskan, tidak dijalankan).',
'Buatlah tombol Edit dan Hapus pada tabel daftar siswa. Tambahkan konfirmasi sebelum hapus.',
'Mengapa WHERE wajib ada pada UPDATE?|Apa risiko DELETE tanpa WHERE?|Mengapa setelah simpan sebaiknya redirect?',
'Siswa berhasil mengubah dan menghapus data uji coba.',
'Apa yang kamu lakukan sebelum menghapus data penting?', 90),

(9, 3, 9, 'Pencarian dan Filter Data',
'Tujuan: Siswa mampu mencari data berdasarkan kata kunci dan filter tertentu.',
'Materi inti: SELECT dengan LIKE dan wildcard %. Filter berdasarkan kolom. Parameter binding untuk LIKE. Menampilkan jumlah hasil.',
'Analogi: pencarian seperti mencari nama di daftar telepon dengan huruf awal. Filter seperti memilih hanya siswa kelas XII PPLG 1.',
'form pencarian --> GET keyword --> SELECT ... WHERE nama LIKE ? --> tampil hasil + jumlah data',
'<?php
$keyword = "%" . $_GET["q"] . "%";
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE nama LIKE ? OR nis LIKE ?");
$stmt->execute([$keyword, $keyword]);
$data = $stmt->fetchAll();
?>',
'Guru mengetikkan kata kunci di kolom pencarian aplikasi praktikum dan menunjukkan hasil yang menyaring.',
'Tambahkan fitur pencarian berdasarkan nama dan filter berdasarkan kelas pada aplikasimu.',
'Apa arti tanda % pada LIKE?|Mengapa tetap harus prepared statement?|Bagaimana cara menggabungkan dua kondisi?',
'Siswa menyelesaikan tantangan pencarian dan filter dengan benar.',
'Bagaimana cara membuat pencarian agar tidak case-sensitive di MySQL?', 90),

(10, 3, 10, 'Login, Logout, dan Session',
'Tujuan: Siswa mampu membuat sistem login sederhana dengan session dan pembatasan akses.',
'Materi inti: session_start, menyimpan data user di session. Password hashing dengan password_hash dan password_verify. Redirect setelah login. require_login dan role.',
'Analogi: session seperti kartu pengunjung di gedung. Setelah login Anda mendapat kartu, dan setiap pintu akan memeriksanya.',
'form login --> POST --> cek user & password_verify --> session["user_id"] --> halaman terlindungi
logout --> hapus session --> kembali ke login',
'<?php
session_start();
$user = $stmt->fetch();
if ($user && password_verify($password, $user["password"])) {
  session_regenerate_id(true);
  $_SESSION["user_id"] = $user["id"];
  header("Location: dashboard.php");
  exit;
}
?>',
'Guru menunjukkan login sebagai guru, berpindah halaman tanpa login ulang, lalu logout.',
'Buatlah halaman login dan Terapkan require_login pada halaman dashboard aplikasi praktikummu.',
'Mengapa password tidak boleh disimpan plain text?|Apa fungsi session_regenerate_id?|Bagaimana cara logout yang benar?',
'Siswa berhasil login, berpindah halaman, dan logout dengan aman.',
'Apa bedanya cookie dan session?', 90),

(11, 3, 11, 'Validasi Input dan Keamanan Dasar',
'Tujuan: Siswa mampu menerapkan validasi server, escaping output, CSRF token, dan prepared statement.',
'Materi inti: Validasi required, panjang, format. filter_var. htmlspecialchars. CSRF token pada form perubahan data. Prinsip jangan percaya input pengguna.',
'Analogi: validasi seperti satpam yang memeriksa tas sebelum masuk. CSRF token seperti kode khusus yang hanya diketahui pengunjung asli.',
'input --> validasi server --> prepared statement --> database
output --> htmlspecialchars --> browser
form ubah data --> csrf token --> cek token di server',
'<?php
if (empty($_POST["nama"])) {
  $errors[] = "Nama wajib diisi";
}
if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
  $errors[] = "Email tidak valid";
}
echo htmlspecialchars($nama, ENT_QUOTES, "UTF-8");
?>',
'Guru mencoba mengisi form dengan tag HTML dan menunjukkan bahwa output tetap tampil sebagai teks, bukan dieksekusi.',
'Tambahkan CSRF token pada form tambah dan hapus aplikasimu. Uji dengan menghapus token secara manual.',
'Apa bahaya XSS?|Mengapa prepared statement mencegah SQL injection?|Apa fungsi CSRF token?',
'Siswa mengumpulkan checklist keamanan aplikasi praktikum.',
'Ancaman keamanan apa yang paling menurutmu berbahaya?', 90),

(12, 3, 12, 'Testing dan Debugging',
'Tujuan: Siswa mampu membaca pesan error, menggunakan teknik debugging sederhana, dan membuat daftar pengujian.',
'Materi inti: Jenis error PHP (notice, warning, fatal). Membaca error log. Teknik var_dump dan print_r. Test case positif, negatif, batas. Checklist pengujian.',
'Analogi: debugging seperti mencari kebocoran pipa. Kita menutup satu bagian demi bagian sampai air berhenti bocor.',
'gejala --> baca error --> lokasi baris --> var_dump nilai --> perbaiki --> uji ulang --> catat hasil',
'<?php
error_reporting(E_ALL);
ini_set("display_errors", "1");
var_dump($_POST);
// atau
error_log("Nilai id = " . $id);
?>',
'Guru sengaja membuat error undefined index, menunjukkan cara membaca pesan error, lalu memperbaikinya.',
'Buatlah 10 test case untuk aplikasi praktikummu. Catat hasil lulus atau gagal.',
'Apa bedanya warning dan fatal error?|Bagaimana cara menemukan baris yang error?|Mengapa perlu test case negatif?',
'Siswa mengumpulkan tabel test case minimal 10 baris.',
'Kesalahan apa yang paling lama kamu cari? Bagaimana akhirnya ketemu?', 90),

(13, 4, 13, 'Membangun Aplikasi Mini',
'Tujuan: Siswa mampu merangkai seluruh kemampuan menjadi aplikasi mini yang lengkap.',
'Materi inti: Perencanaan fitur minimal. Membuat database dan tabel. Membuat halaman list, tambah, edit, hapus, pencarian, dan login. Integrasi antarhalaman.',
'Analogi: seperti merakit makanan. Bahan sudah ada, sekarang harus disusun menjadi satu hidangan yang enak dilihat dan dimakan.',
'perencanaan --> database --> koneksi --> form --> proses --> tampilan --> login --> pengujian',
'// urutan pengerjaan aplikasi mini
1. database.sql
2. config/database.php
3. auth/auth.php
4. modules/siswa/index.php
5. modules/siswa/proses.php',
'Guru mendemonstrasikan aplikasi praktikum dari awal sampai bisa digunakan, lalu menunjukkan hasil akhirnya.',
'Selesaikan aplikasi mini manajemen data siswa sesuai jobsheet 1 sampai 10.',
'Fitur apa yang harus dibuat lebih dulu?|Mengapa database dibuat sebelum tampilan?|Bagaimana cara mengecek hasil akhir?',
'Siswa mengumpulkan aplikasi mini yang dapat dijalankan di Laragon.',
'Bagian mana dari proses pembangunan yang paling kamu sukai?', 90),

(14, 4, 14, 'Code Review dan Refactoring',
'Tujuan: Siswa mampu membaca kode orang lain, memberi masukan, dan menyederhanakan kode tanpa mengubah fungsi.',
'Materi inti: Prinsip DRY (Don''t Repeat Yourself). Pemisahan fungsi. Nama variabel yang jelas. Komentar yang berguna. Review checklist.',
'Analogi: refactoring seperti merapikan kamar. Barang tetap sama, tetapi mudah dicari dan tidak ada yang dobel.',
'kode berulang --> ekstrak ke fungsi --> nama variabel jelas --> hapus komentar tidak berguna --> uji ulang',
'<?php
// Sebelum
$a = $_POST["nama"]; $b = trim($a); $c = htmlspecialchars($b);

// Sesudah
function bersihkan($value) {
  return htmlspecialchars(trim($value), ENT_QUOTES, "UTF-8");
}
$nama = bersihkan($_POST["nama"]);
?>',
'Guru menampilkan dua versi kode yang sama, lalu bertanya kepada siswa mana yang lebih mudah dibaca dan mengapa.',
'Review kode teman sebangku. Berikan minimal 3 saran perbaikan yang spesifik.',
'Apa prinsip DRY?|Mengapa nama variabel penting?|Apakah refactoring mengubah perilaku program?',
'Siswa mengumpulkan hasil review kode beserta perbaikan yang sudah diterapkan.',
'Kebiasaan menulis kode apa yang ingin kamu perbaiki?', 90),

(15, 4, 15, 'Simulasi Live Coding',
'Tujuan: Siswa mampu menyelesaikan tantangan coding dalam batas waktu dan menjelaskan solusinya.',
'Materi inti: Strategi membaca soal. Membagi pekerjaan menjadi langkah kecil. Menguji inkremental. Mengelola waktu. Menjelaskan hasil ke penguji.',
'Analogi: live coding seperti lomba memasak dengan waktu terbatas. Bahan sudah disiapkan, yang diuji adalah ketelitian dan kecepatan berpikir.',
'baca soal --> pilih tantangan --> timer mulai --> implementasi --> uji --> serahkan --> penilaian',
'// contoh tantangan level beginner
Tambahkan field "jenis_kelamin" pada form siswa.
1. Tambah kolom di tabel
2. Tambah input radio di form
3. Ubah query INSERT
4. Tampilkan di tabel',
'Guru memilih tantangan secara acak, menjalankan timer 15 menit, lalu memanggil satu siswa untuk demonstrasi.',
'Siswa mengerjakan 2 tantangan live coding: satu beginner dan satu intermediate.',
'Apa langkah pertama saat menerima soal live coding?|Bagaimana bila waktu hampir habis?|Apa yang harus dijelaskan ke penguji?',
'Penilaian live coding menggunakan rubrik yang tersedia di bank tantangan.',
'Ceritakan pengalamanmu saat mengerjakan tantangan berbatas waktu.', 90),

(16, 4, 16, 'Evaluasi dan Remediasi',
'Tujuan: Siswa mengetahui capaian kompetensinya dan guru menyusun tindak lanjut remediasi atau pengayaan.',
'Materi inti: Menghitung nilai kompetensi dengan bobot. Membaca grafik kompetensi. Menentukan kompetensi kuat dan lemah. Rencana remediasi dan pengayaan.',
'Analogi: Like peta hasil belajar. Ada jalan yang sudah mulus dan ada yang masih berlubang, maka perlu diperbaiki sebelum ujian.',
'penilaian 6 aspek --> nilai per aspek --> grafik --> titik lemah --> remediasi --> uji ulang --> siap UKK',
'Nilai = (Skor/4) x Bobot
Contoh: Implementasi skor 3, bobot 30 --> (3/4) x 30 = 22,5
Total nilai adalah penjumlahan seluruh aspek.',
'Guru membuka halaman penilaian, menunjukkan grafik kompetensi satu siswa, lalu menetapkan remediasi dan pengayaan.',
'Siswa membaca profil kompetensinya sendiri dan menulis rencana belajar mandiri selama seminggu.',
'Aspek apa yang nilainya paling tinggi?|Aspek mana yang perlu remediasi?|Apa rencanamu untuk memperbaikinya?',
'Evaluasi akhir bootcamp dan pengisian angket refleksi.',
'Tuliskan tiga hal yang kamu capai setelah mengikuti bootcamp ini.', 90);

-- Materi pendukung
INSERT INTO learning_materials (session_id, title, material_type, content, sort_order) VALUES
(1, 'Lembar Observasi Diagnostik Awal', 'teks', 'Isi angket diagnostik: seberapa percaya diri kamu dengan HTML, CSS, JavaScript, PHP, dan MySQL (skala 1-4).', 1),
(4, 'Ringkasan Superglobal PHP', 'dokumen', 'GET untuk data tampil di URL, POST untuk data dikirim di body request, SERVER untuk informasi server, SESSION untuk data antarhalaman.', 1),
(5, 'Contoh ERD Manajemen Data Siswa', 'gambar', 'Tabel students, coding_challenges, dan coding_attempts dengan relasi satu ke banyak.', 1),
(6, 'Checklist Koneksi PDO', 'teks', 'Pastikan ekstensi pdo_mysql aktif, nama database benar, charset utf8mb4, dan ERRMODE_EXCEPTION aktif.', 1),
(10, 'Alur Login yang Aman', 'teks', 'Simpan hash password, gunakan password_verify, regenerate id session, dan batasi percobaan login.', 1),
(11, 'Daftar Ancaman Web Dasar', 'dokumen', 'SQL Injection dicegah dengan prepared statement. XSS dicegah dengan escaping output. CSRF dicegah dengan token.', 1),
(12, 'Template Test Case', 'dokumen', 'Kolom: no, skenario, input, hasil diharapkan, hasil aktual, status (lulus/gagal).', 1),
(15, 'Rubrik Live Coding', 'dokumen', 'Ketepatan solusi 40%, kode rapi 20%, kecepatan 20%, penjelasan 20%.', 1);

-- Progres materi dummy
INSERT INTO student_progress (student_id, session_id, status, completed_at) VALUES
(1,1,'selesai','2026-11-02 09:30:00'),(1,2,'selesai','2026-11-02 11:00:00'),(1,3,'selesai','2026-11-03 10:15:00'),(1,4,'selesai','2026-11-03 11:30:00'),
(2,1,'selesai','2026-11-02 09:35:00'),(2,2,'selesai','2026-11-02 11:10:00'),(2,3,'selesai','2026-11-03 10:20:00'),
(3,1,'selesai','2026-11-02 09:40:00'),(3,2,'selesai','2026-11-02 11:20:00'),
(4,1,'selesai','2026-11-02 09:45:00'),(5,1,'sedang',NULL),(6,1,'selesai','2026-11-02 10:00:00'),
(7,1,'sedang',NULL),(8,1,'belum',NULL),(9,1,'selesai','2026-11-02 10:10:00'),(10,1,'belum',NULL),
(11,1,'sedang',NULL),(12,1,'belum',NULL);

-- =====================================================================
-- SEMINAR PKL
-- =====================================================================
INSERT INTO seminar_profiles (student_id, pkl_place, pkl_period, app_title, technologies, problem_desc, requirement, architecture, erd, folder_structure, demo_notes, features, status) VALUES
(1,'PT Sinar Teknologi','Januari - Maret 2026','Sistem Informasi Inventaris Barang','PHP Native, MySQL, Bootstrap 5, JavaScript','Stok barang tercatat manual di buku sehingga sering terjadi selisih saat audit.','User dapat menambah, mengubah, menghapus, dan mencari barang. Admin dapat melihat laporan stok.','Browser sebagai tampilan, PHP sebagai pengolah logika, MySQL sebagai penyimpanan data barang dan transaksi.','Tabel barang (id, kode, nama, stok) dan tabel transaksi (id, barang_id, jumlah, tanggal).','config/, auth/, modules/barang/, modules/laporan/, assets/, database/','Demo dimulai dari login, tambah barang, lalu transaksi barang masuk.','Tambah barang, edit data, hapus, pencarian, laporan stok','siap'),
(2,'CV Digital Kreatif','Januari - Maret 2026','Aplikasi Kasir Cafe','PHP, MySQL, jQuery, CSS','Perhitungan transaksi manual memakan waktu dan sering salah hitung kembalian.','Kasir dapat membuat transaksi, memilih menu, dan mencetak struk.','Frontend form kasir, backend PHP menghitung total, database menyimpan menu dan transaksi.','Tabel menu, tabel transaksi, tabel detail_transaksi dengan foreign key.','config/, modules/kasir/, modules/menu/, assets/, database/','Demo transaksi dengan dua menu lalu cetak struk.','Transaksi, keranjang, struk, laporan penjualan','dinilai'),
(3,'Toko Buku Mandiri','Januari - Maret 2026','Sistem Penjualan Buku','PHP Native, MySQL, HTML, CSS','Data penjualan dan stok buku tersebar di beberapa buku catatan.','Petugas dapat mengelola buku dan transaksi penjualan.','Tiga lapis: tampilan HTML, logika PHP, penyimpanan MySQL.','Tabel buku dan tabel penjualan dengan relasi satu ke banyak.','config/, modules/buku/, modules/penjualan/, database/','Menampilkan daftar buku dan transaksi hari ini.','Data buku, transaksi, stok, laporan','siap'),
(4,'RS Sehat Bersama','Januari - Maret 2026','Sistem Antrian Pasien','PHP, MySQL, AJAX','Pasien tidak mengetahui urutan antrian sehingga terjadi ketidakteraturan.','Pasien mengambil nomor, petugas memanggil pasien berikutnya.','PHP mengelola nomor antrian, AJAX memperbarui tampilan tanpa reload.','Tabel pasien dan tabel antrian dengan relasi.','config/, modules/antrian/, assets/js/, database/','Demo ambil nomor lalu panggil pasien.','Ambil nomor, tampil antrian, panggil pasien','draft');

-- Bank pertanyaan bedah kode
INSERT INTO question_bank (category, question, expected_answer, difficulty) VALUES
('HTML','Apa perbedaan antara tag <div> dan tag <section>?','div adalah kontainer generik tanpa makna, sedangkan section memiliki makna semantik sebagai bagian dari dokumen.','mudah'),
('HTML','Mengapa setiap input sebaiknya memiliki atribut label?','Label meningkatkan aksesibilitas dan membantu pengguna mengetahui isian field, juga berguna untuk pembaca layar.','mudah'),
('CSS','Apa bedanya display:block dan display:inline?','Block menempati satu baris penuh dan bisa diatur lebar tingginya, inline hanya selebar konten.','mudah'),
('CSS','Bagaimana cara membuat tata letak dua kolom dengan Flexbox?','Gunakan container dengan display:flex lalu atur flex pada tiap anak, misalnya flex:1.','sedang'),
('CSS','Apa gunanya media query?','Menyesuaikan tampilan ketika lebar layar berubah, misalnya mode presentasi IFP dan laptop.','sedang'),
('JavaScript','Apa perbedaan let dan var?','let memiliki cakupan blok dan tidak bisa dideklarasikan ulang, var memiliki cakupan fungsi.','mudah'),
('JavaScript','Apa fungsi addEventListener?','Menempelkan fungsi ke suatu event pada elemen tanpa menimpa event lain.','mudah'),
('JavaScript','Mengapa perlu e.preventDefault() pada submit form?','Mencegah browser melakukan submit default agar bisa dilakukan validasi atau AJAX terlebih dahulu.','sedang'),
('PHP','Apa perbedaan include dan require?','require akan menghentikan skrip bila file tidak ditemukan, include hanya memberi warning.','mudah'),
('PHP','Mengapa harus menggunakan htmlspecialchars saat menampilkan data?','Mencegah XSS dengan mengubah karakter khusus menjadi entitas HTML.','sedang'),
('PHP','Apa fungsi session_start()?','Memulai atau melanjutkan session agar data antarhalaman dapat disimpan.','mudah'),
('PHP','Jelaskan perbedaan $_GET dan $_POST!','GET mengirim data melalui URL dan terlihat, POST mengirim data melalui body request.','mudah'),
('MySQL','Apa fungsi PRIMARY KEY?','Menjadi identitas unik setiap baris dan mempercepat pencarian serta menjaga integritas data.','mudah'),
('MySQL','Apa bedanya INNER JOIN dan LEFT JOIN?','INNER JOIN hanya menampilkan baris yang cocok di kedua tabel, LEFT JOIN tetap menampilkan semua baris tabel kiri.','sedang'),
('MySQL','Mengapa prepared statement penting?','Memisahkan query dari data sehingga mencegah SQL injection.','sedang'),
('API','Apa perbedaan metode GET dan POST pada HTTP?','GET untuk mengambil data dan dianggap aman, POST untuk mengirim data yang mengubah state.','mudah'),
('API','Apa arti status code 404 dan 500?','404 berarti sumber daya tidak ditemukan, 500 berarti kesalahan pada server.','mudah'),
('Authentication','Mengapa password harus di-hash?','Agar password asli tidak tersimpan di database dan tidak langsung diketahui bila database bocor.','mudah'),
('Authentication','Apa fungsi CSRF token?','Mencegah permintaan palsu dari situs lain dengan memvalidasi token yang hanya diketahui sesi pengguna.','sulit'),
('Debugging','Bagaimana cara menemukan lokasi error PHP?','Membaca pesan error yang menyebutkan file dan nomor baris, lalu memeriksa variabel di sekitarnya dengan var_dump atau error_log.','sedang'),
('Debugging','Apa bedanya error notice dan fatal error?','Notice hanya peringatan ringan dan skrip tetap jalan, fatal error menghentikan seluruh skrip.','sedang'),
('Security','Apa itu SQL injection dan cara mencegahnya?','SQL injection adalah penyisipan perintah SQL lewat input pengguna, dicegah dengan prepared statement dan validasi.','sedang'),
('Security','Mengapa output harus di-escape?','Agar data yang ditampilkan tidak diartikan sebagai kode HTML atau JavaScript oleh browser.','mudah');

-- =====================================================================
-- 12 JOBSHEET
-- =====================================================================
INSERT INTO jobsheets (id, code, title, objective, tools, concept, steps, source_code, task, reflection, duration_min, sort_order) VALUES
(1,'JS-01','Menyiapkan Lingkungan Pengembangan','Siswa mampu menyiapkan Laragon, PHP, MySQL, dan editor kode untuk praktikum.','Laragon, browser Chrome/Edge, editor teks, koneksi lokal tanpa internet.','Server lokal terdiri dari Apache sebagai web server, PHP sebagai pengolah logika, dan MySQL sebagai database.','1. Pastikan Laragon terinstal.
2. Jalankan Laragon dan aktifkan Apache serta MySQL.
3. Cek PHP melalui menu Terminal Laragon: php -v.
4. Buat folder bootcamp di C:/laragon/www.
5. Buat file info.php berisi phpinfo() lalu buka di browser.','<?php
phpinfo();
?>','Tampilkan halaman info.php kepada guru dan sebutkan versi PHP serta status pdo_mysql.','Apa perbedaan web server dan database server?',45,1),
(2,'JS-02','Membuat Database dan Tabel Siswa','Siswa mampu membuat database beserta tabel siswa melalui SQL.','phpMyAdmin atau mysql CLI, browser.','Database adalah wadah tabel. Primary key menjadi identitas unik tiap baris.','1. Buka phpMyAdmin.
2. Buat database praktikum_siswa.
3. Jalankan perintah CREATE TABLE di bawah.
4. Tambahkan 3 data dummy.
5. Jalankan SELECT untuk melihat hasil.','CREATE TABLE siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nis VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  kelas VARCHAR(30) NOT NULL
);
INSERT INTO siswa (nis, nama, kelas) VALUES
("2023001", "Ahmad Fauzi", "XII PPLG 1"),
("2023002", "Siti Nurhaliza", "XII PPLG 1"),
("2023003", "Rizky Pratama", "XII PPLG 1");','Buat tabel siswa minimal 5 kolom dan isi dengan 5 data dummy.','Mengapa kolom nis diberi UNIQUE?',60,2),
(3,'JS-03','Menghubungkan PHP dan MySQL dengan PDO','Siswa mampu membuat koneksi database yang aman menggunakan PDO.','Editor kode, Laragon, database dari jobsheet 2.','PDO menyediakan cara konsisten mengakses database dan mendukung prepared statement.','1. Buat folder config.
2. Buat file database.php.
3. Isi DSN, user, dan password.
4. Tambahkan pengaturan ERRMODE_EXCEPTION.
5. Uji koneksi dengan SELECT COUNT(*).','<?php
$dsn = "mysql:host=127.0.0.1;dbname=praktikum_siswa;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "", [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
echo $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
?>','Buat file database.php dan tampilkan jumlah siswa di browser.','Apa yang terjadi bila nama database salah?',60,3),
(4,'JS-04','Membuat Halaman Daftar Siswa','Siswa mampu menampilkan data database dalam tabel HTML.','File database.php dari jobsheet 3.','SELECT mengambil data, loop foreach menampilkan setiap baris ke dalam baris tabel.','1. Buat file index.php.
2. Require database.php.
3. Ambil data dengan query SELECT.
4. Buat tabel HTML.
5. Escape setiap nilai dengan htmlspecialchars.','<?php
require "config/database.php";
$data = $pdo->query("SELECT * FROM siswa ORDER BY id DESC")->fetchAll();
?>
<table border="1">
<?php foreach ($data as $row): ?>
  <tr><td><?= htmlspecialchars($row["nis"]) ?></td>
  <td><?= htmlspecialchars($row["nama"]) ?></td></tr>
<?php endforeach; ?>
</table>','Tampilkan daftar siswa lengkap dengan nomor urut dan kelas.','Mengapa data tetap harus di-escape walaupun berasal dari database?',75,4),
(5,'JS-05','Membuat Form Tambah Siswa','Siswa mampu membuat form input data siswa yang valid.','HTML form, CSS sederhana.','Form dengan method POST mengirim data ke file proses melalui body request.','1. Buat file tambah.php.
2. Buat form dengan method post.
3. Tambahkan input nis, nama, kelas.
4. Tambahkan atribut required.
5. Arahkan action ke simpan.php.','<form method="post" action="simpan.php">
  <label>NIS <input type="text" name="nis" required></label>
  <label>Nama <input type="text" name="nama" required></label>
  <label>Kelas <input type="text" name="kelas" required></label>
  <button type="submit">Simpan</button>
</form>','Buat form tambah siswa dengan tampilan rapi dan validasi HTML.','Apa fungsi atribut required?',60,5),
(6,'JS-06','Membuat Proses Simpan Data','Siswa mampu menyimpan data form ke database dengan prepared statement.','Form dari jobsheet 5, koneksi PDO.','Prepared statement menggunakan placeholder ? agar data tidak disisipkan langsung ke query.','1. Buat file simpan.php.
2. Cek method POST.
3. Baca dan trim data.
4. Siapkan query INSERT.
5. Execute dengan array parameter.
6. Redirect ke index.php.','<?php
require "config/database.php";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nis = trim($_POST["nis"]);
  $nama = trim($_POST["nama"]);
  $kelas = trim($_POST["kelas"]);
  $stmt = $pdo->prepare("INSERT INTO siswa (nis, nama, kelas) VALUES (?, ?, ?)");
  $stmt->execute([$nis, $nama, $kelas]);
  header("Location: index.php");
  exit;
}
?>','Simpan 3 data baru dan pastikan langsung muncul di daftar siswa.','Mengapa setelah simpan perlu redirect?',75,6),
(7,'JS-07','Membuat Fitur Edit Data','Siswa mampu mengubah data siswa berdasarkan id.','Tabel daftar siswa, form, koneksi PDO.','UPDATE wajib memiliki WHERE agar hanya satu baris yang berubah.','1. Tambah tautan Edit pada tabel.
2. Buat edit.php yang mengambil data berdasarkan id.
3. Isi form dengan data lama.
4. Buat update.php dengan query UPDATE.
5. Redirect ke daftar.','<?php
$id = (int) $_GET["id"];
$row = $pdo->prepare("SELECT * FROM siswa WHERE id = ?");
$row->execute([$id]);
$data = $row->fetch();
?>
<form method="post" action="update.php">
  <input type="hidden" name="id" value="<?= $data["id"] ?>">
  <input type="text" name="nama" value="<?= htmlspecialchars($data["nama"]) ?>">
  <button type="submit">Ubah</button>
</form>','Ubah nama salah satu siswa dan pastikan data lain tidak berubah.','Apa akibatnya bila UPDATE tanpa WHERE?',75,7),
(8,'JS-08','Membuat Fitur Hapus Data','Siswa mampu menghapus data dengan aman dan ada konfirmasi.','Tabel daftar siswa, koneksi PDO.','DELETE dengan WHERE dan konfirmasi pengguna sebelum eksekusi.','1. Tambah tombol Hapus pada tabel.
2. Tambahkan onsubmit confirm di browser.
3. Buat hapus.php.
4. Jalankan DELETE WHERE id = ?.
5. Redirect ke daftar.','<form method="post" action="hapus.php"
  onsubmit="return confirm(\'Yakin hapus data ini?\')">
  <input type="hidden" name="id" value="<?= $row["id"] ?>">
  <button type="submit">Hapus</button>
</form>','Hapus data uji coba dan pastikan data lain tetap ada.','Mengapa konfirmasi hapus perlu ditampilkan?',60,8),
(9,'JS-09','Membuat Pencarian Data','Siswa mampu mencari siswa berdasarkan kata kunci.','Halaman daftar, koneksi PDO.','LIKE dengan wildcard % mencocokkan sebagian teks.','1. Tambah form pencarian method GET.
2. Baca $_GET["q"].
3. Buat query dengan WHERE nama LIKE ?.
4. Binding nilai %keyword%.
5. Tampilkan jumlah hasil.','<?php
$q = $_GET["q"] ?? "";
$like = "%" . $q . "%";
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE nama LIKE ?");
$stmt->execute([$like]);
$data = $stmt->fetchAll();
?>','Cari siswa berdasarkan nama dan tampilkan jumlah hasil pencarian.','Apa arti tanda % pada query LIKE?',75,9),
(10,'JS-10','Membuat Login dan Session','Siswa mampu membatasi akses halaman dengan login.','Tabel users, password hash, session PHP.','Session menyimpan identitas user di server, cookie hanya menyimpan id session.','1. Buat tabel users.
2. Isi password dengan password_hash.
3. Buat login.php.
4. Verifikasi dengan password_verify.
5. Simpan $_SESSION["user_id"].
6. Buat auth.php dengan require_login.','<?php
if ($user && password_verify($password, $user["password"])) {
  session_regenerate_id(true);
  $_SESSION["user_id"] = $user["id"];
  header("Location: index.php");
  exit;
}
?>','Buat login untuk guru dan siswa, lalu uji akses halaman tanpa login.','Mengapa password tidak boleh disimpan apa adanya?',90,10),
(11,'JS-11','Pengujian Aplikasi','Siswa mampu menyusun dan menjalankan test case.','Aplikasi dari jobsheet 1 sampai 10.','Test case positif menguji skenario sukses, negatif menguji penolakan input salah.','1. Buat tabel test case.
2. Tulis 10 skenario.
3. Jalankan satu per satu.
4. Catat hasil diharapkan dan aktual.
5. Perbaiki yang gagal.','| No | Skenario | Input | Diharapkan | Status |
|----|----------|-------|------------|--------|
| 1 | Tambah siswa | data lengkap | muncul di tabel | Lulus |
| 2 | Tambah kosong | nama kosong | ditolak | Lulus |','Kumpulkan minimal 10 test case beserta hasilnya.','Apa bedanya pengujian positif dan negatif?',60,11),
(12,'JS-12','Pengembangan Mandiri','Siswa mampu menambahkan satu fitur pilihan secara mandiri.','Seluruh materi praktikum.','Fitur baru dibangun dengan mengikuti pola CRUD yang sudah dipelajari.','1. Pilih satu fitur: ekspor CSV, pagination, atau filter kelas.
2. Rencanakan perubahan database bila perlu.
3. Implementasi.
4. Uji.
5. Dokumentasikan.','// contoh ekspor CSV sederhana
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=siswa.csv");
$out = fopen("php://output", "w");
fputcsv($out, ["NIS", "Nama", "Kelas"]);
foreach ($data as $row) {
  fputcsv($out, [$row["nis"], $row["nama"], $row["kelas"]]);
}','Implementasi satu fitur mandiri dan tunjukkan kepada guru.','Fitur apa yang kamu pilih dan mengapa?',120,12);

-- =====================================================================
-- 15 TANTANGAN LIVE CODING
-- =====================================================================
INSERT INTO coding_challenges (id, code, title, description, level, competency, duration_min, instructions, success_criteria, rubric, starter_code) VALUES
(1,'LC-01','Menambah Field Baru','Tambahkan kolom jenis kelamin pada form dan tabel siswa.','beginner','Database & HTML Form',10,'1. Tambah kolom gender pada tabel siswa.
2. Tambah input radio di form.
3. Ubah query INSERT.
4. Tampilkan kolom baru di tabel.','Kolom tersimpan di database dan tampil di daftar siswa.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','<input type="radio" name="gender" value="L"> Laki-laki
<input type="radio" name="gender" value="P"> Perempuan'),
(2,'LC-02','Mengubah Label dan Warna Tombol','Ubah seluruh label form dan beri warna kontras tinggi.','beginner','HTML & CSS',8,'1. Ganti label field dengan bahasa yang lebih jelas.
2. Tambahkan warna latar tombol.
3. Pastikan teks mudah dibaca.','Semua label berubah dan tombol memiliki kontras yang baik.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','<button style="background:#0d6efd;color:#fff;padding:12px">Simpan</button>'),
(3,'LC-03','Menambahkan Validasi Nama','Tolak nama yang kosong atau kurang dari 3 karakter.','beginner','Validasi PHP',10,'1. Cek $_POST["nama"].
2. Gunakan trim dan strlen.
3. Tampilkan pesan error bila tidak valid.
4. Jangan simpan data bila error.','Data invalid tidak tersimpan dan pesan error muncul.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','<?php
$nama = trim($_POST["nama"] ?? "");
// TODO: cek panjang nama
?>'),
(4,'LC-04','Mengurutkan Data Terbaru','Ubah daftar siswa agar data terbaru muncul paling atas.','beginner','SQL ORDER BY',8,'1. Temukan query SELECT pada halaman daftar.
2. Tambahkan ORDER BY id DESC.
3. Uji dengan menambah data baru.','Data terbaru berada di baris pertama.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','SELECT * FROM siswa ORDER BY id DESC'),
(5,'LC-05','Memperbaiki Tampilan Tabel','Buat tabel lebih mudah dibaca dengan zebra stripe dan header warna gelap.','beginner','CSS',10,'1. Tambahkan class pada tabel.
2. Buat aturan CSS untuk header.
3. Buat aturan CSS untuk baris genap.
4. Pastikan teks tetap kontras.','Tabel memiliki header gelap dan baris bergantian warna.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','table tr:nth-child(even) { background:#f2f5f9; }'),
(6,'LC-06','Membuat Pencarian Siswa','Tambahkan fitur pencarian berdasarkan nama.','intermediate','SQL LIKE',15,'1. Buat form pencarian method GET.
2. Baca keyword.
3. Tambahkan WHERE nama LIKE ?.
4. Tampilkan jumlah hasil.','Pencarian menampilkan hanya data yang cocok.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','$like = "%" . $_GET["q"] . "%";'),
(7,'LC-07','Membuat Filter Kelas','Tambahkan dropdown filter kelas pada daftar siswa.','intermediate','SQL WHERE',15,'1. Buat select berisi daftar kelas.
2. Baca nilai filter.
3. Gabungkan dengan kondisi WHERE.
4. Tampilkan hasil.','Data tersaring sesuai kelas yang dipilih.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','WHERE kelas = ?'),
(8,'LC-08','Validasi Duplikasi NIS','Cegah NIS yang sama disimpan dua kali.','intermediate','Validasi & PDO',15,'1. Buat SELECT COUNT(*) WHERE nis = ?.
2. Jika lebih dari nol, tolak.
3. Tampilkan pesan yang jelas.
4. Gunakan prepared statement.','NIS duplikat tidak bisa disimpan.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','SELECT COUNT(*) FROM siswa WHERE nis = ?'),
(9,'LC-09','Membuat Pagination','Batasi tampilan 5 data per halaman.','intermediate','SQL LIMIT',20,'1. Hitung jumlah data.
2. Buat LIMIT 5 OFFSET ?.
3. Buat tautan halaman.
4. Jaga parameter tetap aman.','Halaman menampilkan maksimal 5 data dan ada navigasi.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','LIMIT 5 OFFSET 5   // ubah offset sesuai halaman'),
(10,'LC-10','Membuat Autentikasi Sederhana','Tambahkan login dan logout pada aplikasi.','intermediate','Session & Password',20,'1. Buat tabel users.
2. Hash password.
3. Buat form login.
4. Simpan session.
5. Lindungi halaman.','Halaman hanya bisa dibuka setelah login.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','password_verify($input, $hash)'),
(11,'LC-11','Membuat Relasi Tabel','Tambahkan tabel nilai dan hubungkan dengan siswa.','advanced','Foreign Key',20,'1. Buat tabel nilai.
2. Tambah kolom siswa_id.
3. Tambah FOREIGN KEY.
4. Input data nilai.','Relasi terlihat di ERD dan data bisa di-JOIN.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','FOREIGN KEY (siswa_id) REFERENCES siswa(id)'),
(12,'LC-12','Menggabungkan Data dengan JOIN','Tampilkan nama siswa beserta nilai mapelnya.','advanced','SQL JOIN',20,'1. Tulis query INNER JOIN.
2. Pilih kolom yang dibutuhkan.
3. Tampilkan dalam tabel.
4. Tangani data tanpa nilai.','Data siswa dan nilai tampil dalam satu tabel.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','SELECT s.nama, n.nilai FROM siswa s INNER JOIN nilai n ON n.siswa_id = s.id'),
(13,'LC-13','Transaksi Database','Pindahkan stok antargudang secara aman dengan transaksi.','advanced','Transaction',25,'1. Mulai beginTransaction.
2. Kurangi stok gudang asal.
3. Tambah stok gudang tujuan.
4. Commit atau rollback saat error.','Gagal di tengah proses tidak meninggalkan data setengah jadi.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','$pdo->beginTransaction();
// query...
$pdo->commit();'),
(14,'LC-14','Role Pengguna','Batasi menu berdasarkan role admin dan siswa.','advanced','Authorization',25,'1. Tambah kolom role.
2. Simpan role di session.
3. Buat fungsi require_role.
4. Sembunyikan menu yang tidak diizinkan.','Siswa tidak bisa membuka menu admin.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','if ($_SESSION["role"] !== "admin") { die("Ditolak"); }'),
(15,'LC-15','Ekspor Data ke CSV','Unduh data siswa dalam format CSV.','advanced','Export & Header',25,'1. Buat file ekspor.
2. Set header Content-Type.
3. Tulis baris header.
4. Loop data dengan fputcsv.','File CSV terunduh dan bisa dibuka di spreadsheet.','Ketepatan 40, kerapian 20, kecepatan 20, penjelasan 20','header("Content-Type: text/csv; charset=utf-8");');

-- Penilaian seminar dummy
INSERT INTO seminar_assessments (id, student_id, assessor_id, problem_understanding, architecture_db, code_understanding, live_coding, testing, communication, total_score, selected_question_ids, challenge_id, observation_sheet, assessor_notes, assessed_at) VALUES
(1, 2, 1, 85, 80, 78, 82, 75, 88, 81.30, '1,5,12,20', 6, 'Siswa menjelaskan alur transaksi dengan runut. Demo berjalan lancar.', 'Perlu memperdalam pemahaman JOIN.', '2026-11-10 09:00:00'),
(2, 1, 1, 90, 85, 80, 78, 85, 90, 83.10, '3,10,15,21', 1, 'Arsitektur aplikasi dijelaskan dengan diagram yang jelas.', 'Live coding masih perlu latihan kecepatan.', '2026-11-10 10:30:00'),
(3, 3, 2, 78, 75, 72, 80, 70, 82, 76.25, '2,9,14,22', 4, 'Presentasi cukup percaya diri.', 'Tambahkan latihan SQL ORDER BY.', '2026-11-11 08:30:00');

-- Sesi live coding dummy
INSERT INTO coding_attempts (id, student_id, challenge_id, started_at, ended_at, duration_sec, status, result_text, examiner_score, examiner_notes) VALUES
(1, 2, 6, '2026-11-10 08:00:00', '2026-11-10 08:14:20', 860, 'dinilai', 'Pencarian berhasil dibuat dengan prepared statement.', 88, 'Sudah baik, tambahkan penanganan keyword kosong.'),
(2, 1, 1, '2026-11-10 09:30:00', '2026-11-10 09:39:10', 550, 'dinilai', 'Field gender berhasil ditambahkan.', 92, 'Cepat dan rapi.'),
(3, 3, 4, '2026-11-11 08:00:00', '2026-11-11 08:11:30', 690, 'dinilai', 'ORDER BY id DESC sudah diterapkan.', 80, 'Perlu latihan lagi.');

-- Penilaian kompetensi dummy (skala 1-4)
INSERT INTO competency_assessments (id, student_id, assessor_id, assessment_date, score_understanding, score_trace, score_implementation, score_db_security, score_testing, score_communication, total_score, notes) VALUES
(1,  1, 1, '2026-11-12', 4, 3, 4, 3, 3, 4, 86.25, 'Kompeten di seluruh aspek.'),
(2,  2, 1, '2026-11-12', 4, 3, 3, 3, 4, 4, 83.75, 'Komunikasi teknik sangat baik.'),
(3,  3, 1, '2026-11-12', 3, 3, 3, 2, 2, 3, 68.75, 'Perlu remediasi database dan testing.'),
(4,  4, 2, '2026-11-13', 3, 2, 3, 2, 2, 3, 65.00, 'Perlu latihan menelusuri kode.'),
(5,  5, 2, '2026-11-13', 3, 3, 2, 2, 3, 3, 68.75, 'Implementasi masih lemah.'),
(6,  6, 2, '2026-11-13', 4, 4, 4, 3, 3, 4, 88.75, 'Siap UKK.'),
(7,  7, 1, '2026-11-14', 3, 2, 3, 2, 2, 3, 65.00, 'Perlu bimbingan debugging.'),
(8,  8, 1, '2026-11-14', 4, 3, 3, 3, 3, 4, 81.25, 'Cukup baik.'),
(9,  9, 2, '2026-11-14', 3, 3, 4, 3, 3, 3, 78.75, 'Implementasi kuat.'),
(10, 10, 2, '2026-11-15', 2, 2, 2, 2, 2, 3, 55.00, 'Perlu remediasi menyeluruh.'),
(11, 11, 2, '2026-11-15', 3, 3, 3, 3, 2, 3, 73.75, 'Testing perlu ditingkatkan.'),
(12, 12, 1, '2026-11-15', 4, 4, 3, 3, 4, 4, 86.25, 'Sangat baik.');

-- Progres jobsheet dummy
INSERT INTO student_jobsheets (student_id, jobsheet_id, status, completed_at) VALUES
(1,1,'selesai','2026-11-01 10:00:00'),(1,2,'selesai','2026-11-01 11:00:00'),(1,3,'selesai','2026-11-02 09:00:00'),(1,4,'selesai','2026-11-02 10:00:00'),(1,5,'selesai','2026-11-03 09:00:00'),(1,6,'selesai','2026-11-03 11:00:00'),(1,7,'proses',NULL),
(2,1,'selesai','2026-11-01 10:00:00'),(2,2,'selesai','2026-11-01 11:00:00'),(2,3,'selesai','2026-11-02 09:00:00'),(2,4,'selesai','2026-11-02 10:00:00'),(2,5,'selesai','2026-11-03 09:00:00'),(2,6,'selesai','2026-11-03 11:00:00'),(2,7,'selesai','2026-11-04 09:00:00'),(2,8,'selesai','2026-11-04 11:00:00'),
(3,1,'selesai','2026-11-01 10:00:00'),(3,2,'selesai','2026-11-01 11:00:00'),(3,3,'selesai','2026-11-02 09:00:00'),(3,4,'selesai','2026-11-02 10:00:00'),(3,5,'proses',NULL),
(4,1,'selesai','2026-11-01 10:00:00'),(4,2,'selesai','2026-11-01 11:00:00'),(4,3,'proses',NULL),
(5,1,'selesai','2026-11-01 10:00:00'),(5,2,'proses',NULL),
(6,1,'selesai','2026-11-01 10:00:00'),(6,2,'selesai','2026-11-01 11:00:00'),(6,3,'selesai','2026-11-02 09:00:00'),(6,4,'selesai','2026-11-02 10:00:00'),(6,5,'selesai','2026-11-03 09:00:00'),
(7,1,'selesai','2026-11-01 10:00:00'),(7,2,'proses',NULL),
(8,1,'selesai','2026-11-01 10:00:00'),
(9,1,'selesai','2026-11-01 10:00:00'),(9,2,'selesai','2026-11-01 11:00:00'),(9,3,'selesai','2026-11-02 09:00:00'),
(10,1,'proses',NULL),
(11,1,'selesai','2026-11-01 10:00:00'),(11,2,'proses',NULL),
(12,1,'selesai','2026-11-01 10:00:00'),(12,2,'selesai','2026-11-01 11:00:00'),(12,3,'selesai','2026-11-02 09:00:00'),(12,4,'selesai','2026-11-02 10:00:00'),(12,5,'selesai','2026-11-03 09:00:00'),(12,6,'selesai','2026-11-03 11:00:00'),(12,7,'selesai','2026-11-04 09:00:00');

-- Remediasi & pengayaan dummy
INSERT INTO remediation (id, student_id, competency, material, task, notes, status, due_date) VALUES
(1, 3, 'Database & Keamanan', 'Ulangi Pertemuan 5 dan 6: ERD, primary key, foreign key, PDO.', 'Buat 3 tabel dengan relasi lalu jalankan 5 query SELECT JOIN.', 'Fokus pada foreign key.', 'proses', '2026-11-22'),
(2, 3, 'Testing & Debugging', 'Ulangi Pertemuan 12: membaca error dan test case.', 'Buat 10 test case untuk aplikasi kasir.', NULL, 'rencana', '2026-11-25'),
(3, 4, 'Menelusuri Kode', 'Ulangi Pertemuan 14: code review.', 'Review kode teman dan beri 5 catatan perbaikan.', NULL, 'rencana', '2026-11-25'),
(4, 10, 'Implementasi', 'Ulangi Pertemuan 7 dan 8: CRUD CREATE READ UPDATE DELETE.', 'Selesaikan jobsheet 4 sampai 8.', 'Pendampingan intensif guru.', 'proses', '2026-11-28');

INSERT INTO enrichment (id, student_id, competency, material, task, notes, status, due_date) VALUES
(1, 1, 'Implementasi', 'Materi lanjutan: transaksi database.', 'Implementasi fitur transaksi pada aplikasi inventaris.', NULL, 'rencana', '2026-11-30'),
(2, 6, 'Menelusuri Kode', 'Refactoring tingkat lanjut.', 'Buat helper fungsi reusable untuk aplikasi perpustakaan.', NULL, 'proses', '2026-11-28'),
(3, 12, 'Testing & Debugging', 'Automated testing dasar.', 'Buat skrip pengujian sederhana untuk form pendaftaran.', NULL, 'rencana', '2026-11-30');

INSERT INTO activity_log (user_id, action, description, ip_address) VALUES
(1, 'login', 'Login guru pertama kali', '127.0.0.1'),
(1, 'import', 'Import database bootcamp', '127.0.0.1');

-- =====================================================================
-- SELESAI
-- =====================================================================
