DROP DATABASE IF EXISTS db_absensi;
CREATE DATABASE db_absensi;
USE db_absensi;

-- Tabel Jabatan
CREATE TABLE jabatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_jabatan VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Divisi
CREATE TABLE divisi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_divisi VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','pegawai') NOT NULL DEFAULT 'pegawai',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Pegawai
CREATE TABLE pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    jabatan_id INT,
    divisi_id INT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    no_hp VARCHAR(20),
    foto_profil VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (jabatan_id) REFERENCES jabatan(id) ON DELETE SET NULL,
    FOREIGN KEY (divisi_id) REFERENCES divisi(id) ON DELETE SET NULL
);

-- Tabel Absensi
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME,
    jam_pulang TIME,
    foto_masuk VARCHAR(255),
    foto_pulang VARCHAR(255),
    status ENUM('Hadir','Terlambat','Alpha','Izin','Cuti') DEFAULT 'Hadir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE
);

-- Tabel Izin
CREATE TABLE izin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    jenis ENUM('Izin','Sakit') NOT NULL,
    alasan TEXT,
    file_surat VARCHAR(255),
    status ENUM('Pending','Disetujui','Ditolak') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE
);

-- Tabel Cuti
CREATE TABLE cuti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    jenis_cuti VARCHAR(50) NOT NULL,
    alasan TEXT,
    sisa_cuti INT DEFAULT 12,
    status ENUM('Pending','Disetujui','Ditolak') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE
);

-- Tabel Pengumuman
CREATE TABLE pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- INSERT DATA DUMMY
-- =============================================

-- Insert Jabatan
INSERT INTO jabatan (nama_jabatan) VALUES
('Staff IT'), ('Staff HR'), ('Admin Keuangan'), ('Supervisor'),
('Staff Marketing'), ('Staff Gudang'), ('Customer Service'),
('Staff Produksi'), ('Staff Purchasing'), ('Manager');

-- Insert Divisi
INSERT INTO divisi (nama_divisi) VALUES
('IT'), ('HRD'), ('Finance'), ('Operasional'),
('Marketing'), ('Warehouse'), ('Customer Service'),
('Produksi'), ('Purchasing'), ('Management');

-- Insert Users (admin + 10 pegawai, password: 123456)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'admin'),
('budi', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'pegawai'),
('andi', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'pegawai'),
('siti', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'pegawai'),
('rudi', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'pegawai'),
('dinda', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'pegawai'),
('fajar', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'pegawai'),
('rina', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'pegawai'),
('yoga', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'pegawai'),
('dewi', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'pegawai'),
('rizky', '$2y$10$C6XDEc2RhCod0gq0Y.Z8z.CWVM3TddkmyEc4mEf9qr0bpZgD3RWOy', 'pegawai');

-- Insert Pegawai
INSERT INTO pegawai (nip, user_id, jabatan_id, divisi_id, nama, email, no_hp) VALUES
('PG001', (SELECT id FROM users WHERE username='budi'), 1, 1, 'Budi Santoso', 'budi@ptmaju.com', '081234567801'),
('PG002', (SELECT id FROM users WHERE username='andi'), 2, 2, 'Andi Pratama', 'andi@ptmaju.com', '081234567802'),
('PG003', (SELECT id FROM users WHERE username='siti'), 3, 3, 'Siti Aisyah', 'siti@ptmaju.com', '081234567803'),
('PG004', (SELECT id FROM users WHERE username='rudi'), 4, 4, 'Rudi Hartono', 'rudi@ptmaju.com', '081234567804'),
('PG005', (SELECT id FROM users WHERE username='dinda'), 5, 5, 'Dinda Putri', 'dinda@ptmaju.com', '081234567805'),
('PG006', (SELECT id FROM users WHERE username='fajar'), 6, 6, 'Fajar Nugroho', 'fajar@ptmaju.com', '081234567806'),
('PG007', (SELECT id FROM users WHERE username='rina'), 7, 7, 'Rina Oktavia', 'rina@ptmaju.com', '081234567807'),
('PG008', (SELECT id FROM users WHERE username='yoga'), 8, 8, 'Yoga Saputra', 'yoga@ptmaju.com', '081234567808'),
('PG009', (SELECT id FROM users WHERE username='dewi'), 9, 9, 'Dewi Lestari', 'dewi@ptmaju.com', '081234567809'),
('PG010', (SELECT id FROM users WHERE username='rizky'), 10, 10, 'Rizky Maulana', 'rizky@ptmaju.com', '081234567810');

-- Insert Pengumuman
INSERT INTO pengumuman (judul, isi, tanggal) VALUES
('Selamat Datang di Sistem Absensi', 'Sistem absensi online PT Maju Bersama Indonesia telah aktif. Seluruh pegawai wajib melakukan absensi melalui sistem ini.', '2026-07-01'),
('Peraturan Jam Kerja', 'Jam kerja perusahaan adalah pukul 08:00 - 17:00 WIB. Toleransi keterlambatan maksimal 15 menit.', '2026-07-01');
