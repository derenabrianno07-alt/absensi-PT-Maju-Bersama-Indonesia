# LAPORAN PROYEK REKAYASA PERANGKAT LUNAK
## APLIKASI ABSENSI PEGAWAI BERBASIS WEB (SISTEM ABSENSI PEGAWAI)
## PT MAJU BERSAMA INDONESIA

**PROGRAM STUDI TEKNIK INFORMATIKA**
**FAKULTAS SAINS DAN TEKNOLOGI**
**UNIVERSITAS BUDDHI DHARMA**
**2026**

---

### **DAFTAR ISI**
1. [Halaman Login](#1-halaman-login)
2. [Halaman Lupa Password](#2-halaman-lupa-password)
3. [Halaman Reset Password](#3-halaman-reset-password)
4. [Dashboard Administrator](#4-dashboard-administrator)
5. [Kelola Data Pegawai (Admin)](#5-kelola-data-pegawai-admin)
6. [Kelola Data Jabatan & Divisi (Admin)](#6-kelola-data-jabatan--divisi-admin)
7. [Kelola Data Pengguna (Admin)](#7-kelola-data-pengguna-admin)
8. [Kelola Data Absensi (Admin)](#8-kelola-data-absensi-admin)
9. [Kelola Data Pengajuan Izin & Sakit (Admin)](#9-kelola-data-pengajuan-izin--sakit-admin)
10. [Kelola Data Pengajuan Cuti (Admin)](#10-kelola-data-pengajuan-cuti-admin)
11. [Laporan Kehadiran & Cetak Laporan (Admin)](#11-laporan-kehadiran--cetak-laporan-admin)
12. [Dashboard Pegawai](#12-dashboard-pegawai)
13. [Form Absen Masuk & Absen Pulang (Pegawai)](#13-form-absen-masuk--absen-pulang-pegawai)
14. [Form Pengajuan Izin & Sakit (Pegawai)](#14-form-pengajuan-izin--sakit-pegawai)
15. [Form Pengajuan Cuti (Pegawai)](#15-form-pengajuan-cuti-pegawai)

---

### **1. Halaman Login**
* **Deskripsi**: Halaman autentikasi awal yang berfungsi untuk membatasi hak akses sistem. Hanya pengguna terdaftar (dengan role Administrator atau Pegawai) yang dapat masuk ke dalam sistem.
* **Fitur Utama**:
  * Input **Username** dan **Password**.
  * Show/Hide Password toggler untuk kenyamanan pengguna.
  * Tautan (Link) "Lupa Password" jika pengguna tidak dapat mengakses akunnya.
* **Proses & Validasi**:
  * Sistem memvalidasi kesesuaian Username dan Password melalui query ke tabel `users`.
  * Status keaktifan akun (`is_active`) diperiksa; jika bernilai `0` (nonaktif), login ditolak.
  * Autoredireksi berdasarkan hak akses: **Admin** akan diarahkan ke Dashboard Admin (`/admin/index.php`), sedangkan **Pegawai** diarahkan ke Dashboard Pegawai (`/pegawai/index.php`).

---

### **2. Halaman Lupa Password**
* **Deskripsi**: Fasilitas bagi pegawai yang lupa sandinya untuk dapat membuat password baru dengan verifikasi keamanan ganda.
* **Fitur Utama**:
  * Input **NIP Pegawai** (Nomor Induk Pegawai).
  * Input **Alamat Email Pegawai** yang terdaftar di sistem.
* **Proses & Validasi Keamanan**:
  * Sistem melakukan validasi silang (cross-check) apakah kombinasi **NIP** dan **Email** yang dimasukkan cocok dengan data di tabel `pegawai`.
  * Jika tidak cocok, sistem membatalkan proses dan memunculkan notifikasi SweetAlert2 berupa pesan kesalahan: *"Kombinasi NIP dan Email tidak cocok atau tidak terdaftar!"*.
  * Jika cocok, sistem membuat token unik aman (`reset_token`) dengan masa berlaku 1 jam (`reset_token_expires`) dan menyimpannya di database.
  * Tampilan popup SweetAlert2 akan menyajikan tombol **"Buat Password Baru"** sebagai alternatif praktis bagi pengguna untuk langsung menuju tautan ubah password.

---

### **3. Halaman Reset Password**
* **Deskripsi**: Halaman khusus untuk mengganti password lama dengan password baru menggunakan token verifikasi.
* **Fitur Utama**:
  * Input **Password Baru**.
  * Input **Konfirmasi Password Baru**.
* **Proses & Validasi**:
  * Sistem mencocokkan token di URL dengan kolom `reset_token` di tabel `users` serta memastikan belum melewati waktu kedaluwarsa (`reset_token_expires`).
  * Validasi input memastikan isi kolom Password Baru dan Konfirmasi Password Baru bernilai sama persis dan memiliki panjang yang cukup.
  * Setelah berhasil diperbarui, token reset akan dihapus dan pengguna diarahkan kembali ke halaman login.

---

### **4. Dashboard Administrator**
* **Deskripsi**: Tampilan utama panel kontrol bagi Administrator setelah login berhasil. Menyajikan ringkasan cepat kondisi kehadiran seluruh pegawai secara real-time.
* **Fitur Utama**:
  * **Informasi Ringkasan (Cards)**: Menampilkan jumlah total pegawai, jumlah pegawai hadir hari ini, pegawai terlambat hari ini, pegawai sakit/izin hari ini, dan pegawai yang sedang cuti.
  * **Grafik Kehadiran Mingguan**: Grafik visual presentase tingkat kehadiran pegawai.
  * **Tabel Aktivitas Absensi Terbaru**: Daftar 5 aktivitas absensi pegawai paling akhir.
  * **Sidebar Navigasi**: Menu terpusat untuk mengakses seluruh manajemen master data, data absensi, perizinan, cuti, laporan, dan pengaturan.

---

### **5. Kelola Data Pegawai (Admin)**
* **Deskripsi**: Menu master data yang digunakan admin untuk melakukan manipulasi data pegawai (CRUD: Create, Read, Update, Delete).
* **Fitur Utama**:
  * **Tabel Pegawai**: Menyajikan kolom NIP, Nama, Email, Jabatan, Divisi, No. HP, dan Foto Profil.
  * **Tambah Pegawai**: Form untuk menginput biodata pegawai baru sekaligus secara otomatis membuat akun login (username & password default) di tabel `users`.
  * **Edit Pegawai**: Fasilitas untuk memperbarui biodata, mengubah jabatan/divisi, mengganti foto profil, serta mengatur ulang status keaktifan akun.
  * **Hapus Pegawai**: Penghapusan data pegawai yang secara otomatis menghapus akun user terkait di database (Cascading Delete).

---

### **6. Kelola Data Jabatan & Divisi (Admin)**
* **Deskripsi**: Halaman manajemen klasifikasi organisasi kerja untuk memetakan peran setiap pegawai.
* **Fitur Utama**:
  * **Daftar Jabatan & Divisi**: Ditampilkan dalam tabel terpisah.
  * **Tambah/Edit/Hapus Jabatan**: Menambahkan nama jabatan (contoh: Staff IT, Manager, Supervisor).
  * **Tambah/Edit/Hapus Divisi**: Menambahkan kelompok divisi kerja (contoh: IT, HRD, Keuangan).

---

### **7. Kelola Data Pengguna (Admin)**
* **Deskripsi**: Menu khusus untuk memantau semua user yang memiliki hak akses sistem beserta pengaturan tingkat otoritasnya.
* **Fitur Utama**:
  * **Tabel User**: Menampilkan daftar username, peran/role (`admin` atau `pegawai`), dan status keaktifan.
  * **Ubah Status**: Admin dapat menonaktifkan akun pegawai tertentu jika yang bersangkutan keluar dari instansi atau melanggar aturan.

---

### **8. Kelola Data Absensi (Admin)**
* **Deskripsi**: Modul utama pemantauan rekam kehadiran harian pegawai.
* **Fitur Utama**:
  * **Tabel Log Absensi**: Kolom berisi Nama Pegawai, Tanggal, Jam Masuk, Foto Masuk, Jam Pulang, Foto Pulang, Lokasi Koordinat (GPS), dan Status Kehadiran.
  * **Bukti Visual (Foto Absen)**: Admin dapat mengeklik foto masuk/pulang untuk memvalidasi wajah asli pegawai saat mengambil absen lewat kamera.
  * **Peta Geofencing (Maps)**: Tombol koordinat GPS terhubung langsung dengan Google Maps untuk memvalidasi letak lokasi tempat pegawai melakukan absen.
  * **Filter Pencarian**: Penyaringan berdasarkan nama pegawai, tanggal tertentu, atau status absensi (Hadir, Terlambat, Alpha, Izin, Cuti).

---

### **9. Kelola Data Pengajuan Izin & Sakit (Admin)**
* **Deskripsi**: Halaman bagi admin untuk meninjau dan memberikan keputusan persetujuan atas pengajuan izin/sakit yang diajukan oleh pegawai.
* **Fitur Utama**:
  * **Detail Pengajuan**: Menampilkan nama pegawai, NIP, tanggal mulai, tanggal selesai, jenis (Izin atau Sakit), alasan, dan tautan file surat bukti.
  * **Tautan Surat Keterangan Dokter/Bukti**: File surat keterangan dokter (PDF/PNG/JPG) yang diunggah pegawai dapat diunduh/dilihat langsung oleh admin.
  * **Aksi Keputusan**: Tombol centang hijau untuk **Setujui** dan silang merah untuk **Tolak**.
* **Integrasi Otomatis**:
  * Jika disetujui, sistem secara otomatis memasukkan status "Izin" atau "Sakit" pada tabel `absensi` untuk seluruh rentang tanggal pengajuan yang disepakati, sehingga pegawai tidak dianggap mangkir (Alpha).

---

### **10. Kelola Data Pengajuan Cuti (Admin)**
* **Deskripsi**: Modul peninjauan pengajuan cuti tahunan pegawai.
* **Fitur Utama**:
  * Menampilkan informasi nama, NIP, durasi cuti, alasan cuti, dan sisa kuota cuti pegawai bersangkutan.
  * Tombol persetujuan (**Setujui** / **Tolak**).
* **Aturan Bisnis & Integrasi**:
  * Jika admin mengeklik **Setujui**, sistem akan mengurangi jatah/sisa kuota cuti pegawai secara otomatis berdasarkan jumlah hari cuti yang diambil.
  * Status kehadiran di tabel `absensi` pada rentang tanggal tersebut akan otomatis berubah menjadi "Cuti".

---

### **11. Laporan Kehadiran & Cetak Laporan (Admin)**
* **Deskripsi**: Menu penarikan laporan berkala yang dapat disaring berdasarkan periode bulanan atau rentang tanggal tertentu.
* **Fitur Utama**:
  * **Filter Rekap**: Pilihan berdasarkan bulan, tahun, atau nama pegawai tertentu.
  * **Cetak PDF**: Menghasilkan dokumen laporan siap cetak dengan tata letak profesional berisikan rangkuman total kehadiran, terlambat, izin, cuti, dan alpha masing-masing pegawai.
  * **Export Excel**: Ekspor rekap kehadiran ke format lembar kerja Excel untuk mempermudah perhitungan penggajian oleh bagian HRD/Finance.

---

### **12. Dashboard Pegawai**
* **Deskripsi**: Halaman beranda pegawai setelah berhasil login. Menyajikan pintasan absensi harian dan status pengumuman dari manajemen.
* **Fitur Utama**:
  * **Status Kehadiran Hari Ini**: Menampilkan waktu jam masuk dan jam pulang yang telah tercatat hari ini secara real-time.
  * **Pengumuman Terbaru**: Menampilkan papan pengumuman internal perusahaan yang dirilis oleh admin.
  * **Sisa Cuti**: Angka sisa kuota cuti yang masih dimiliki pegawai (maksimal 20 hari per tahun).

---

### **13. Form Absen Masuk & Absen Pulang (Pegawai)**
* **Deskripsi**: Form interaktif bagi pegawai untuk melakukan pencatatan kehadiran harian menggunakan perangkat kamera dan deteksi lokasi.
* **Fitur Utama**:
  * **Kamera Web (Webcam)**: Pegawai wajib mengambil foto wajah langsung saat absen.
  * **Geolokasi GPS (Geolocation API)**: Sistem mendeteksi koordinat garis lintang (latitude) dan garis bujur (longitude) dari browser secara otomatis.
* **Validasi Sistem**:
  * **Batas Toleransi Terlambat**: Jika pegawai absen masuk setelah pukul **08:15 WIB**, status otomatis tercatat sebagai "Terlambat" (Jam kerja reguler masuk 08:00 WIB).
  * **Absen Pulang**: Tombol absen pulang hanya aktif setelah jam pulang kantor (pukul 17:00 WIB) atau sesuai pengaturan perusahaan.

---

### **14. Form Pengajuan Izin & Sakit (Pegawai)**
* **Deskripsi**: Form khusus bagi pegawai yang berhalangan hadir agar dapat mengajukan permohonan dispensasi kepada manajemen.
* **Fitur Utama**:
  * Input **Tanggal Mulai** dan **Tanggal Selesai**.
  * Pilihan **Jenis Pengajuan**: *Izin (Keperluan Mendesak)* atau *Sakit (Memerlukan Istirahat)*.
  * Input **Alasan** penjelasan singkat.
  * Unggah **Surat Bukti** (Format yang didukung: PDF, JPG, JPEG, PNG).
* **Batasan & Validasi Baru (Keamanan Ketat)**:
  * **Batas Maksimal Izin 3 Hari**: Jika jenis yang dipilih adalah **Izin**, durasi antara tanggal mulai dan selesai tidak boleh melebihi **3 hari**. Jika melanggar, pengiriman form dibatalkan oleh SweetAlert2.
  * **Sakit Wajib Unggah Surat Dokter**: Jika jenis yang dipilih adalah **Sakit**, input unggahan berkas bersifat **Wajib (Mandatory)**. Form tidak dapat dikirim tanpa melampirkan berkas bukti medis dan label input akan berubah menampilkan indikator `*Wajib`.
  * **Format Berkas**: Sistem memvalidasi ekstensi berkas di sisi server hanya untuk dokumen gambar atau PDF guna menghindari berkas berbahaya (exploit).

---

### **15. Form Pengajuan Cuti (Pegawai)**
* **Deskripsi**: Form bagi pegawai yang ingin mengajukan cuti tahunan resmi.
* **Fitur Utama**:
  * Menampilkan sisa jatah cuti pegawai yang tersisa.
  * Input tanggal mulai, tanggal selesai, jenis cuti (Cuti Tahunan, Melahirkan, Menikah, dll), serta alasan cuti.
* **Validasi Aturan**:
  * **Kuota Maksimal 20 Hari**: Pegawai tidak dapat mengajukan jumlah hari cuti yang melebihi sisa cuti tahunan miliknya (Kuota awal diset maksimal 20 hari dalam setahun). Sistem akan memblokir pengiriman formulir jika sisa kuota kurang dari hari yang diajukan.
