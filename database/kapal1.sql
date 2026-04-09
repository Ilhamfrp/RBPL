-- ============================================================
-- DATABASE: kapal1
-- Sistem Informasi Manajemen Pelayanan Kapal
-- Based on Functional Requirements & Product Backlog
-- ============================================================

CREATE DATABASE IF NOT EXISTS kapal1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kapal1;

-- ============================================================
-- SPRINT 1: Autentikasi & Otorisasi (PB-01)
-- ============================================================

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(50) NOT NULL UNIQUE, -- Agen Kapal, KSOP, Planner, Pandu
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(200) NOT NULL,
    email VARCHAR(200),
    no_telepon VARCHAR(20),
    role_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- ============================================================
-- SPRINT 2: Manajemen Permohonan PKK (PB-02)
-- ============================================================

CREATE TABLE kapal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kapal VARCHAR(200) NOT NULL,
    jenis_kapal ENUM('Kontainer','Tanker','Bulk Carrier','General Cargo','RoRo','Penumpang','Lainnya') NOT NULL,
    bendera VARCHAR(100),
    gt DECIMAL(10,2),
    imo_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pkk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_pkk VARCHAR(50) NOT NULL UNIQUE,
    agen_id INT NOT NULL,
    kapal_id INT NOT NULL,
    jenis_layanan ENUM('Kedatangan','Keberangkatan','Pindah Tambat') NOT NULL,
    tanggal_kedatangan DATE,
    jam_kedatangan TIME,
    tanggal_keberangkatan DATE,
    jam_keberangkatan TIME,
    dermaga_diminta VARCHAR(20),
    keterangan TEXT,
    status ENUM('Menunggu Validasi','Divalidasi','Ditolak','Dijadwalkan','Selesai') DEFAULT 'Menunggu Validasi',
    catatan_ksop TEXT,
    tanggal_ajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agen_id) REFERENCES users(id),
    FOREIGN KEY (kapal_id) REFERENCES kapal(id)
);

-- ============================================================
-- SPRINT 3: Verifikasi Dokumen (PB-03)
-- ============================================================

CREATE TABLE dokumen_pkk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pkk_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    path_file VARCHAR(500) NOT NULL,
    ukuran_file VARCHAR(50),
    jenis_dokumen ENUM('Manifest','Crew List','Surat Permohonan','Sertifikat Kapal','Izin Berlayar','Lainnya') DEFAULT 'Lainnya',
    status_verifikasi ENUM('Belum Diperiksa','Valid','Tidak Valid') DEFAULT 'Belum Diperiksa',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pkk_id) REFERENCES pkk(id) ON DELETE CASCADE
);

CREATE TABLE validasi_pkk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pkk_id INT NOT NULL,
    ksop_id INT NOT NULL,
    status_validasi ENUM('Valid','Tidak Valid') NOT NULL,
    catatan TEXT,
    tanggal_validasi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pkk_id) REFERENCES pkk(id),
    FOREIGN KEY (ksop_id) REFERENCES users(id)
);

-- ============================================================
-- SPRINT 4: Penjadwalan Kapal (PB-04)
-- ============================================================

CREATE TABLE dermaga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_dermaga VARCHAR(20) NOT NULL UNIQUE,
    nama_dermaga VARCHAR(100),
    kapasitas_gt DECIMAL(10,2),
    status ENUM('Tersedia','Digunakan','Maintenance') DEFAULT 'Tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE jadwal_pelayanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pkk_id INT NOT NULL,
    planner_id INT NOT NULL,
    dermaga_id INT,
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME,
    status ENUM('Draft','Dikonfirmasi','Berlangsung','Selesai','Dibatalkan','Ditunda') DEFAULT 'Draft',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pkk_id) REFERENCES pkk(id),
    FOREIGN KEY (planner_id) REFERENCES users(id),
    FOREIGN KEY (dermaga_id) REFERENCES dermaga(id)
);

CREATE TABLE alokasi_sumber_daya (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jadwal_id INT NOT NULL,
    pandu_id INT,
    jenis_sumber_daya ENUM('Pandu','Kapal Tunda','Pilot Boat','Mooring Boat') NOT NULL,
    nama_sumber_daya VARCHAR(200),
    status ENUM('Tersedia','Dialokasikan','Selesai') DEFAULT 'Dialokasikan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jadwal_id) REFERENCES jadwal_pelayanan(id),
    FOREIGN KEY (pandu_id) REFERENCES users(id)
);

-- ============================================================
-- SPRINT 5: Manajemen Pembayaran (PB-05)
-- ============================================================

CREATE TABLE ppkb (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_ppkb VARCHAR(50) NOT NULL UNIQUE,
    pkk_id INT NOT NULL,
    agen_id INT NOT NULL,
    jasa_labuh DECIMAL(15,2) DEFAULT 0,
    jasa_tambat DECIMAL(15,2) DEFAULT 0,
    jasa_pemanduan DECIMAL(15,2) DEFAULT 0,
    jasa_penundaan DECIMAL(15,2) DEFAULT 0,
    biaya_lain DECIMAL(15,2) DEFAULT 0,
    total_biaya DECIMAL(15,2) DEFAULT 0,
    status_ppkb ENUM('Draft','Diajukan','Disetujui','Ditolak') DEFAULT 'Draft',
    catatan TEXT,
    tanggal_ajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pkk_id) REFERENCES pkk(id),
    FOREIGN KEY (agen_id) REFERENCES users(id)
);

CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_epb VARCHAR(50) NOT NULL UNIQUE,
    ppkb_id INT NOT NULL,
    agen_id INT NOT NULL,
    jumlah_bayar DECIMAL(15,2) NOT NULL,
    metode_pembayaran ENUM('Transfer Bank','Virtual Account','Tunai') DEFAULT 'Transfer Bank',
    bank_pengirim VARCHAR(100),
    nomor_referensi VARCHAR(100),
    bukti_pembayaran VARCHAR(500),
    status_pembayaran ENUM('Menunggu','Lunas','Gagal') DEFAULT 'Menunggu',
    tanggal_pembayaran TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ppkb_id) REFERENCES ppkb(id),
    FOREIGN KEY (agen_id) REFERENCES users(id)
);

-- ============================================================
-- SPRINT 6: Distribusi SPK (PB-06)
-- ============================================================

CREATE TABLE dokumen_resmi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_dokumen VARCHAR(100) NOT NULL UNIQUE,
    jenis_dokumen ENUM('PKK Resmi','PPKB Resmi','SPK','Jadwal Resmi') NOT NULL,
    pkk_id INT NOT NULL,
    ksop_id INT NOT NULL,
    path_dokumen VARCHAR(500),
    tanggal_terbit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pkk_id) REFERENCES pkk(id),
    FOREIGN KEY (ksop_id) REFERENCES users(id)
);

CREATE TABLE spk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_spk VARCHAR(100) NOT NULL UNIQUE,
    jadwal_id INT NOT NULL,
    pandu_id INT NOT NULL,
    ksop_id INT NOT NULL,
    isi_instruksi TEXT NOT NULL,
    status ENUM('Terbit','Diterima','Selesai','Dibatalkan') DEFAULT 'Terbit',
    tanggal_terbit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jadwal_id) REFERENCES jadwal_pelayanan(id),
    FOREIGN KEY (pandu_id) REFERENCES users(id),
    FOREIGN KEY (ksop_id) REFERENCES users(id)
);

-- ============================================================
-- SPRINT 7: Realisasi Pelayanan (PB-07)
-- ============================================================

CREATE TABLE laporan_pelayanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_laporan VARCHAR(100) NOT NULL UNIQUE,
    spk_id INT NOT NULL,
    pandu_id INT NOT NULL,
    tanggal_mulai_realisasi DATETIME,
    tanggal_selesai_realisasi DATETIME,
    kondisi_cuaca ENUM('Cerah','Berawan','Hujan','Badai','Berkabut') DEFAULT 'Cerah',
    kecepatan_angin VARCHAR(50),
    tinggi_gelombang VARCHAR(50),
    jumlah_kapal_tunda INT DEFAULT 0,
    catatan_pelayanan TEXT,
    kendala TEXT,
    status_laporan ENUM('Draft','Dikirim','Ditinjau','Disetujui','Perlu Revisi') DEFAULT 'Draft',
    path_lampiran VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (spk_id) REFERENCES spk(id),
    FOREIGN KEY (pandu_id) REFERENCES users(id)
);

-- ============================================================
-- SPRINT 8: Pelaporan & Dashboard (PB-08)
-- ============================================================

CREATE TABLE tinjauan_laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laporan_id INT NOT NULL,
    ksop_id INT NOT NULL,
    status_tinjauan ENUM('Disetujui','Perlu Revisi') NOT NULL,
    catatan_tinjauan TEXT,
    tanggal_tinjauan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (laporan_id) REFERENCES laporan_pelayanan(id),
    FOREIGN KEY (ksop_id) REFERENCES users(id)
);

CREATE TABLE notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    pesan TEXT NOT NULL,
    jenis ENUM('Info','Sukses','Peringatan','Error') DEFAULT 'Info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ============================================================
-- SEED DATA
-- ============================================================

INSERT INTO roles (nama_role, deskripsi) VALUES
('Agen Kapal', 'Petugas agen yang mengajukan permohonan pelayanan kapal'),
('KSOP', 'Petugas KSOP yang memvalidasi dokumen dan menerbitkan dokumen resmi'),
('Planner', 'Petugas planner yang menyusun jadwal pelayanan kapal'),
('Pandu', 'Petugas pandu yang melaksanakan pelayanan kapal di lapangan');

INSERT INTO users (username, password, nama_lengkap, email, no_telepon, role_id) VALUES
('agen1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Rizki - PT Maritim Express', 'agen1@maritim.co.id', '08111234567', 1),
('agen2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso - PT Samudera Line', 'agen2@samudera.co.id', '08122345678', 1),
('ksop1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Capt. Doni Firmansyah', 'ksop1@pelabuhan.go.id', '08133456789', 2),
('ksop2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ibu Sari Dewi', 'ksop2@pelabuhan.go.id', '08144567890', 2),
('planner1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eko Prasetyo', 'planner1@pelabuhan.go.id', '08155678901', 3),
('pandu1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Capt. Faisal Ibrahim', 'pandu1@pelabuhan.go.id', '08166789012', 4),
('pandu2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Capt. Ahmad Gunawan', 'pandu2@pelabuhan.go.id', '08177890123', 4);
-- Default password: "password" for all users

INSERT INTO dermaga (kode_dermaga, nama_dermaga, kapasitas_gt, status) VALUES
('D-01', 'Dermaga 1 - Kontainer', 80000.00, 'Tersedia'),
('D-02', 'Dermaga 2 - Kontainer', 60000.00, 'Tersedia'),
('D-03', 'Dermaga 3 - General Cargo', 40000.00, 'Tersedia'),
('D-04', 'Dermaga 4 - Tanker', 100000.00, 'Tersedia'),
('D-05', 'Dermaga 5 - RoRo', 30000.00, 'Tersedia');

INSERT INTO kapal (nama_kapal, jenis_kapal, bendera, gt, imo_number) VALUES
('MV Ocean Star', 'Kontainer', 'Indonesia', 45000.00, 'IMO9234567'),
('MV Pacific Dream', 'Bulk Carrier', 'Panama', 62000.00, 'IMO9345678'),
('MV Blue Horizon', 'Kontainer', 'Indonesia', 50000.00, 'IMO9456789'),
('MV Sea Voyager', 'General Cargo', 'Singapura', 28000.00, 'IMO9567890'),
('MV Maritime Express', 'Kontainer', 'Indonesia', 55000.00, 'IMO9678901');

INSERT INTO pkk (nomor_pkk, agen_id, kapal_id, jenis_layanan, tanggal_kedatangan, jam_kedatangan, tanggal_keberangkatan, jam_keberangkatan, dermaga_diminta, keterangan, status) VALUES
('PKK-2024-001', 1, 1, 'Kedatangan', '2024-11-16', '08:00:00', '2024-11-18', '14:00:00', 'D-01', 'Kapal membawa muatan kontainer dari Singapura', 'Dijadwalkan'),
('PKK-2024-002', 1, 2, 'Keberangkatan', '2024-11-16', '10:30:00', NULL, NULL, 'D-03', 'Keberangkatan menuju Surabaya', 'Dijadwalkan'),
('PKK-2024-003', 2, 3, 'Kedatangan', '2024-11-18', '08:00:00', '2024-11-20', '14:00:00', 'D-02', 'Kapal membawa muatan kontainer dari Singapura', 'Divalidasi'),
('PKK-2024-004', 1, 4, 'Keberangkatan', '2024-11-16', '16:00:00', NULL, NULL, 'D-04', 'Menuju Pelabuhan Merak', 'Menunggu Validasi'),
('PKK-2024-005', 2, 5, 'Kedatangan', '2024-11-17', '07:00:00', '2024-11-19', '10:00:00', 'D-01', 'Muatan kontainer dari Malaysia', 'Menunggu Validasi');

INSERT INTO notifikasi (user_id, judul, pesan, jenis, is_read) VALUES
(1, 'PKK Divalidasi', 'PKK #2024-001 telah divalidasi oleh KSOP', 'Sukses', 0),
(1, 'Pembayaran Berhasil', 'Pembayaran PPKB #2024-002 berhasil diproses', 'Sukses', 0),
(1, 'Dokumen Tersedia', 'Dokumen SPK tersedia untuk diunduh', 'Info', 1),
(3, 'PKK Baru Masuk', 'PKK #2024-005 dari PT Samudera Line menunggu validasi', 'Info', 0),
(5, 'PKK Siap Dijadwalkan', 'PKK #2024-003 telah divalidasi dan siap dijadwalkan', 'Info', 0),
(6, 'SPK Baru', 'Anda mendapat penugasan SPK baru untuk MV Blue Horizon', 'Info', 0);

-- ============================================================
-- PATCH: Perbaikan & Seed Data Tambahan
-- ============================================================
-- ============================================================
-- PERBAIKAN DATABASE: tambahan kolom & seed data yang kurang
-- ============================================================

-- Jadwal_pelayanan perlu nomor untuk referensi (opsional tapi berguna)
ALTER TABLE jadwal_pelayanan 
  ADD COLUMN IF NOT EXISTS nomor_jadwal VARCHAR(50) AFTER id;

-- Buat nomor_jadwal otomatis via trigger
DROP TRIGGER IF EXISTS trg_nomor_jadwal;
DELIMITER $$
CREATE TRIGGER trg_nomor_jadwal
BEFORE INSERT ON jadwal_pelayanan
FOR EACH ROW
BEGIN
  IF NEW.nomor_jadwal IS NULL OR NEW.nomor_jadwal = '' THEN
    SET NEW.nomor_jadwal = CONCAT('JDW-', YEAR(NOW()), '-', LPAD((SELECT IFNULL(MAX(id),0)+1 FROM jadwal_pelayanan), 3, '0'));
  END IF;
END$$
DELIMITER ;

-- Tambah planner2 kalau belum ada
INSERT IGNORE INTO users (username, password, nama_lengkap, email, no_telepon, role_id) VALUES
('planner2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hendra Setiawan', 'planner2@pelabuhan.go.id', '08166678901', 3);

-- Tambah seed jadwal_pelayanan supaya halaman alokasi tidak kosong
INSERT IGNORE INTO jadwal_pelayanan (nomor_jadwal, pkk_id, planner_id, dermaga_id, tanggal_mulai, tanggal_selesai, status, catatan) VALUES
('JDW-2024-001', 1, 5, 1, '2024-11-16 08:00:00', '2024-11-18 14:00:00', 'Dikonfirmasi', 'Jadwal kedatangan MV Ocean Star'),
('JDW-2024-002', 2, 5, 3, '2024-11-16 10:30:00', '2024-11-16 14:00:00', 'Dikonfirmasi', 'Jadwal keberangkatan MV Pacific Dream');

-- Alokasi pandu untuk jadwal di atas
INSERT IGNORE INTO alokasi_sumber_daya (jadwal_id, pandu_id, jenis_sumber_daya, nama_sumber_daya, status)
SELECT j.id, 6, 'Pandu', 'Capt. Faisal Ibrahim', 'Dialokasikan'
FROM jadwal_pelayanan j WHERE j.nomor_jadwal = 'JDW-2024-001'
AND NOT EXISTS (SELECT 1 FROM alokasi_sumber_daya WHERE jadwal_id=j.id);

-- Tambah SPK seed supaya halaman pandu tidak kosong
INSERT IGNORE INTO spk (nomor_spk, jadwal_id, pandu_id, ksop_id, isi_instruksi, status) 
SELECT 'SPK/001/XI/2024', j.id, 6, 3, 'Pandu kapal MV Ocean Star dari luar ke Dermaga D-01. Gunakan kapal tunda 2 unit. Kondisi cuaca normal.', 'Selesai'
FROM jadwal_pelayanan j WHERE j.nomor_jadwal = 'JDW-2024-001'
AND NOT EXISTS (SELECT 1 FROM spk WHERE nomor_spk='SPK/001/XI/2024');

INSERT IGNORE INTO spk (nomor_spk, jadwal_id, pandu_id, ksop_id, isi_instruksi, status)
SELECT 'SPK/002/XI/2024', j.id, 7, 3, 'Pandu kapal MV Pacific Dream dari Dermaga D-03 keluar. Koordinasi dengan tim mooring.', 'Terbit'
FROM jadwal_pelayanan j WHERE j.nomor_jadwal = 'JDW-2024-002'
AND NOT EXISTS (SELECT 1 FROM spk WHERE nomor_spk='SPK/002/XI/2024');

-- Tambah laporan seed untuk halaman pandu
INSERT IGNORE INTO laporan_pelayanan (nomor_laporan, spk_id, pandu_id, tanggal_mulai_realisasi, tanggal_selesai_realisasi, kondisi_cuaca, kecepatan_angin, tinggi_gelombang, jumlah_kapal_tunda, catatan_pelayanan, status_laporan)
SELECT 'LP-2024-001', s.id, 6, '2024-11-16 08:05:00', '2024-11-16 09:55:00', 'Cerah', '10 knot', '0.2 m', 2, 'Pelayanan berjalan lancar tanpa kendala. Kapal berhasil ditambatkan di dermaga.', 'Disetujui'
FROM spk s WHERE s.nomor_spk='SPK/001/XI/2024'
AND NOT EXISTS (SELECT 1 FROM laporan_pelayanan WHERE nomor_laporan='LP-2024-001');

SELECT 'Database fixes applied OK' AS status;
