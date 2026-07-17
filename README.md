# Sistem Antrean Digital - Aurelia Mom, Kids and Baby Spa

Sistem Antrean Digital berbasis web premium yang dirancang khusus untuk mengelola antrean layanan spa ibu, anak, dan bayi pada **Aurelia Mom, Kids and Baby Spa**.

Sistem ini memisahkan arsitektur backend dan frontend secara modern, menggunakan **CodeIgniter 3 (PHP)** sebagai REST API dan **React JS (Vite + TypeScript + Material UI)** sebagai antarmuka pengguna (Single Page Application).

---

## 🚀 Fitur Utama

1. **Dashboard Manajemen Antrean & Analitik**:
   - Statistik antrean harian (Total, Menunggu, Dilayani, Selesai, Dilewati).
   - Grafik interaktif jumlah antrean per layanan dan distribusi pendaftaran (Online vs Offline).
   - Kontrol pemanggilan antrean berbasis algoritma **FIFO (First In First Out)**.

2. **Panggilan Suara Antrean (Text-to-Speech)**:
   - Pemanggilan otomatis nomor antrean dalam **Bahasa Indonesia dengan suara perempuan (logat Indonesia asli)**.
   - Fitur panggil ulang (Recall) langsung dari dashboard.

3. **Monitor TV Antrean Utama (Tampilan Ruang Tunggu)**:
   - Desain **Bright/Light Theme** premium yang cerah, bersih, dan mudah dibaca dari jarak jauh.
   - Papan panggil aktif berukuran besar beserta daftar status antrean per kategori layanan.
   - Dilengkapi *running text* informasi di bagian bawah layar.

4. **Portal Pendaftaran Mandiri Pasien (Online)**:
   - Pasien dapat mendaftar antrean secara online secara mandiri.
   - Form pendaftaran dilengkapi dengan unggah KTP dan keluhan.

5. **Manajemen Master Data (CRUD Layanan & Terapis)**:
   - **Menu Data Layanan**: CRUD untuk mengelola kategori layanan spa (Baby & Kids Care, Mom Care, dll), deskripsi, status, dan prefix kode antrean (A, B, C, D).
   - **Menu Data Terapis/Bidan**: CRUD untuk mengelola bidan, terapis, jadwal praktek, dan penempatan kategori layanan.

6. **Notifikasi Navbar Real-time**:
   - Menu dropdown notifikasi di navbar untuk memantau pendaftaran antrean baru dan panggilan aktif secara real-time.

7. **Pengaturan Profil Terintegrasi**:
   - Modal edit profil premium untuk mengubah Nama Lengkap, Username, dan Password langsung di navbar.

---

## 🛠️ Teknologi & Arsitektur

Sistem ini dikembangkan dengan arsitektur modern terpisah:
- **Backend API**: PHP CodeIgniter 3 (terletak di folder `application/` dan `system/`). Backend ini dirancang bersih dan efisien, hanya menyisakan `Api.php` untuk melayani request data. Semua file legacy dan contoh view/controller yang tidak terpakai telah dibersihkan sepenuhnya.
- **Frontend SPA**: React JS, Vite, TypeScript, Material UI 5. Terletak di folder `template_ui/`. React app dikompilasi menjadi aset statis dan dideploy secara otomatis ke root folder agar dapat disajikan langsung oleh web server (Apache/XAMPP).
- **Database**: MySQL.

---

## 🔧 Langkah Instalasi & Konfigurasi

### 1. Prasyarat
- Install **XAMPP** (dengan PHP versi 7.4 ke atas).
- Install **Node.js** (rekomendasi versi 18 atau terbaru) untuk proses build frontend.

### 2. Konfigurasi Database
1. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Buat database baru bernama `db_antrian_klinik`.
3. Import file database **`db_antrian_klinik.sql`** yang berada di root project ke dalam database tersebut.
4. Pastikan file konfigurasi database di `application/config/database.php` sudah sesuai dengan username dan password MySQL Anda.

### 3. Setup Project di XAMPP
1. Pindahkan folder project `antrian` ke dalam directory `C:/xampp/htdocs/project/antrian`.
2. Sistem dapat diakses langsung melalui browser di: `http://localhost/project/antrian/`.
3. Login default petugas/admin:
   - **Username**: `admin`
   - **Password**: `admin123`

---

## 💻 Panduan Pengembangan Frontend (`template_ui`)

Jika Anda ingin melakukan modifikasi pada kode program frontend (React):

1. Masuk ke terminal dan arahkan ke directory `template_ui`:
   ```bash
   cd template_ui
   ```
2. Install semua dependencies:
   ```bash
   npm install
   ```
3. Jalankan development server:
   ```bash
   npm run dev
   ```
4. Setelah selesai melakukan perubahan kode, lakukan build untuk mengompilasi dan mendeploy file ke root directory:
   ```bash
   npm run build
   ```
   *Catatan: Perintah `npm run build` akan otomatis menjalankan TypeScript check, kompilasi Vite, dan memindahkan aset ke root directory menggunakan script `deploy.cjs`.*
