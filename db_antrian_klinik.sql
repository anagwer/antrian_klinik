-- Database: db_antrian_klinik
CREATE DATABASE IF NOT EXISTS `db_antrian_klinik` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_antrian_klinik`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','dokter','petugas') NOT NULL DEFAULT 'petugas',
  `nama_lengkap` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping default users (password: 'admin123')
INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `nama_lengkap`) VALUES
(1, 'admin', '$2y$10$wNvs2qHqg.4jL2G1QJz55Osn6Z5h8dD1yZ4.06u7bQ.xXy9hF17B2', 'admin', 'Administrator Klinik'),
(2, 'petugas', '$2y$10$wNvs2qHqg.4jL2G1QJz55Osn6Z5h8dD1yZ4.06u7bQ.xXy9hF17B2', 'petugas', 'Petugas Pendaftaran');

-- --------------------------------------------------------
-- Table structure for table `poliklinik`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `poliklinik` (
  `id_poli` varchar(10) NOT NULL,
  `nama_poli` varchar(100) NOT NULL,
  `kode_antrian` varchar(2) NOT NULL, -- e.g. 'A' for Umum, 'B' for Obgyn
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_poli`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `poliklinik` (`id_poli`, `nama_poli`, `kode_antrian`, `deskripsi`, `status`) VALUES
('UMM', 'Poli Umum', 'A', 'Pelayanan kesehatan umum tingkat pertama.', 'aktif'),
('OBG', 'Poli Obgyn (Kandungan)', 'B', 'Pelayanan kandungan, kehamilan, dan kesehatan reproduksi.', 'aktif'),
('GIG', 'Poli Gigi', 'C', 'Pelayanan pemeriksaan dan tindakan kesehatan gigi.', 'aktif'),
('ANK', 'Poli Anak', 'D', 'Pelayanan tumbuh kembang dan penyakit anak.', 'aktif');

-- --------------------------------------------------------
-- Table structure for table `dokter`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dokter` (
  `id_dokter` int(11) NOT NULL AUTO_INCREMENT,
  `nama_dokter` varchar(100) NOT NULL,
  `id_poli` varchar(10) NOT NULL,
  `jadwal_praktek` varchar(255) DEFAULT NULL,
  `status` enum('aktif','cuti') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_dokter`),
  FOREIGN KEY (`id_poli`) REFERENCES `poliklinik` (`id_poli`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `dokter` (`id_dokter`, `nama_dokter`, `id_poli`, `jadwal_praktek`, `status`) VALUES
(1, 'dr. Andi Wibowo', 'UMM', 'Senin - Jumat (08:00 - 14:00)', 'aktif'),
(2, 'dr. Citra Lestari, Sp.OG', 'OBG', 'Senin, Rabu, Jumat (09:00 - 13:00)', 'aktif'),
(3, 'drg. Budi Santoso', 'GIG', 'Selasa & Kamis (10:00 - 15:00)', 'aktif'),
(4, 'dr. Dian Pratama, Sp.A', 'ANK', 'Senin - Sabtu (08:00 - 12:00)', 'aktif');

-- --------------------------------------------------------
-- Table structure for table `pasien`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pasien` (
  `id_pasien` int(11) NOT NULL AUTO_INCREMENT,
  `nik` varchar(16) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pasien`),
  UNIQUE KEY `nik` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed a default patient
INSERT INTO `pasien` (`id_pasien`, `nik`, `nama`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `no_hp`, `foto_ktp`) VALUES
(1, '3201011212950001', 'Budi Hermawan', 'Jakarta', '1995-12-12', 'L', 'Jl. Kenanga No. 12, Jakarta', '081234567890', 'mock_ktp_budi.jpg');

-- --------------------------------------------------------
-- Table structure for table `antrian`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `antrian` (
  `id_antrian` int(11) NOT NULL AUTO_INCREMENT,
  `nik_pasien` varchar(16) NOT NULL,
  `nama_pasien` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `keluhan` text NOT NULL,
  `id_poli` varchar(10) NOT NULL,
  `id_dokter` int(11) NOT NULL,
  `nomor_antrian` varchar(10) NOT NULL, -- e.g. A-01, B-03
  `nomor_urut` int(11) NOT NULL, -- Numeric sequence of the day, e.g. 1, 2, 3
  `status` enum('menunggu','dipanggil','selesai','dilewati') NOT NULL DEFAULT 'menunggu',
  `tipe_pendaftaran` enum('online','offline') NOT NULL DEFAULT 'online',
  `tanggal_antrian` date NOT NULL,
  `waktu_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu_dilayani` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_antrian`),
  FOREIGN KEY (`id_poli`) REFERENCES `poliklinik` (`id_poli`) ON DELETE CASCADE,
  FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
