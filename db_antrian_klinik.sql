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
  `role` enum('admin','terapis','petugas') NOT NULL DEFAULT 'petugas',
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
('BYK', 'Baby & Kids Care', 'A', 'Pelayanan pijat, renang, totok wajah, dan spa bayi & anak.', 'aktif'),
('MTB', 'Massage Therapy Baby & Kids', 'B', 'Pijat terapi untuk bayi prematur, batuk pilek, kolik, rewel, dll.', 'aktif'),
('MMC', 'Mom Care', 'C', 'Pelayanan pijat hamil, pijat nifas, laktasi, dan breast care.', 'aktif'),
('MMH', 'Mom Health', 'D', 'Yoga kesuburan, prenatal yoga, hypnobirthing, dan persiapan persalinan.', 'aktif');

-- --------------------------------------------------------
-- Table structure for table `terapis`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `terapis` (
  `id_terapis` int(11) NOT NULL AUTO_INCREMENT,
  `nama_terapis` varchar(100) NOT NULL,
  `id_poli` varchar(10) NOT NULL,
  `jadwal_praktek` varchar(255) DEFAULT NULL,
  `status` enum('aktif','cuti') NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_terapis`),
  FOREIGN KEY (`id_poli`) REFERENCES `poliklinik` (`id_poli`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `terapis` (`id_terapis`, `nama_terapis`, `id_poli`, `jadwal_praktek`, `status`) VALUES
(1, 'Bidan Aurelia, A.Md.Keb', 'MMC', 'Senin - Sabtu (08:00 - 16:00)', 'aktif'),
(2, 'Terapis Indah Lestari', 'BYK', 'Senin - Minggu (09:00 - 17:00)', 'aktif'),
(3, 'Fisioterapis Budi Santoso, S.Ft', 'MTB', 'Selasa, Kamis, Sabtu (10:00 - 15:00)', 'aktif'),
(4, 'Bidan Dian Pratama, S.Tr.Keb', 'MMH', 'Senin - Jumat (08:00 - 14:00)', 'aktif');

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
  `id_terapis` int(11) NOT NULL,
  `nomor_antrian` varchar(10) NOT NULL, -- e.g. A-01, B-03
  `nomor_urut` int(11) NOT NULL, -- Numeric sequence of the day, e.g. 1, 2, 3
  `status` enum('menunggu','dipanggil','selesai','dilewati') NOT NULL DEFAULT 'menunggu',
  `tipe_pendaftaran` enum('online','offline') NOT NULL DEFAULT 'online',
  `tanggal_antrian` date NOT NULL,
  `waktu_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu_dilayani` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_antrian`),
  FOREIGN KEY (`id_poli`) REFERENCES `poliklinik` (`id_poli`) ON DELETE CASCADE,
  FOREIGN KEY (`id_terapis`) REFERENCES `terapis` (`id_terapis`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
