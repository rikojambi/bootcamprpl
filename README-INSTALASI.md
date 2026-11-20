# PANDUAN INSTALASI LENGKAP

## A. Persyaratan

| Komponen | Versi / Keterangan |
|---|---|
| Sistem Operasi | Windows 10 / 11 (64-bit) |
| Laragon | Terinstall di `C:\laragon` (disarankan versi lengkap dengan Apache + MySQL) |
| PHP | 8.2 atau lebih baru (modul `pdo_mysql`, `mbstring`, `session`, `openssl`) |
| MySQL / MariaDB | 8.x (port default 3306, user `root`, password kosong) |
| Browser | Chrome / Edge versi terbaru |
| Layar | IFP 1920x1080 (utama), fallback 1366x768 |

---

## B. Langkah Instalasi

### 1. Pastikan Laragon Berjalan
- Buka Laragon, klik **Start All**.
- Pastikan indikator Apache dan MySQL hijau.
- Jika MySQL gagal start dengan pesan `ibdata1 must be writable`, hentikan semua proses `mysqld.exe` melalui Task Manager lalu jalankan ulang Laragon.

### 2. Hubungkan Folder Proyek ke Laragon
Jalankan **install.bat** dari folder proyek (klik kanan → *Run as administrator* bila diminta).

Skrip ini membuat junction:
```
C:\laragon\www\bootcamp  →  E:\VIBE CODING\open9\bootcamp-fullstack
```

Alternatif manual (Command Prompt):
```cmd
mklink /J "C:\laragon\www\bootcamp" "E:\VIBE CODING\open9\bootcamp-fullstack"
```

### 3. Buat Database
**Cara termudah (phpMyAdmin):**
1. Buka `http://localhost/phpmyadmin` di browser.
2. Klik **Import**.
3. Pilih file `database/database.sql` dari folder proyek.
4. Klik **Go**. Tunggu sampai selesai.

**Cara command line:**
```cmd
cd /d E:\VIBE CODING\open9\bootcamp-fullstack
C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root < database\database.sql
```

### 4. Verifikasi Database
Jalankan query berikut di phpMyAdmin atau MySQL CLI:
```sql
SELECT 'students' AS tabel, COUNT(*) AS jumlah FROM students
UNION ALL SELECT 'users', COUNT(*) FROM users
UNION ALL SELECT 'bootcamp_sessions', COUNT(*) FROM bootcamp_sessions
UNION ALL SELECT 'jobsheets', COUNT(*) FROM jobsheets
UNION ALL SELECT 'coding_challenges', COUNT(*) FROM coding_challenges;
```
Angka yang diharapkan: `12, 14, 16, 12, 15`.

### 5. Buka Aplikasi
| Kebutuhan | URL |
|---|---|
| Aplikasi utama | `http://localhost/bootcamp/` |
| Mode presentasi IFP | `http://localhost/bootcamp/ifp/` |

> Jika Apache Laragon tidak bisa memakai port 80 karena aplikasi lain, jalankan `start-server.bat` lalu buka `http://localhost:8080/bootcamp/`.

### 6. Login Demo
| Username | Password | Role |
|---|---|---|
| `guru` | `guru123` | Admin (akses penuh) |
| `guru2` | `guru123` | Guru |
| `2023001` | `siswa123` | Siswa |

---

## C. Konfigurasi

### BASE_PATH
Jika folder junction tidak bernama `bootcamp`, ubah konstanta di `config/config.php`:
```php
define('BASE_PATH', '/nama_folder_anda');
```

### Koneksi Database
Sesuaikan di `config/config.php` bila password MySQL tidak kosong:
```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'bootcamp_fullstack');
define('DB_USER', 'root');
define('DB_PASS', 'password_anda');
```

### Ukuran Font IFP
Ubah `html { font-size: 20px; }` pada `assets/css/ifp.css` sesuai kebutuhan. Tombol A＋/A− di header juga bisa dipakai saat presentasi (tersimpan di browser).

---

## D. Backup & Restore

**Backup otomatis lewat aplikasi:**
- Buka `Pengaturan → Backup & Database → Unduh Backup SQL`.
- File juga tersimpan di `storage/backups/backup_YYYYMMDD_HHMMSS.sql`.

**Backup lewat command prompt:**
```cmd
backup.bat
```

**Restore:**
```cmd
backup\restore.bat
```
Atau via phpMyAdmin → Database `bootcamp_fullstack` → tab Import → pilih file backup.

---

## E. Pemecahan Masalah

| Masalah | Solusi |
|---|---|
| Halaman putih / koneksi database gagal | Pastikan MySQL berjalan dan database sudah di-import. Periksa `config/config.php`. |
| CSS tidak termuat | Pastikan junction Laragon sudah dibuat dan URL memakai `/bootcamp/...`. |
| Port 80 dipakai aplikasi lain | Jalankan `start-server.bat` dan pakai port 8080, atau ganti port Apache di Laragon. |
| `ibdata1 must be writable` | Matikan semua `mysqld.exe`, lalu start ulang Laragon sebagai administrator. |
| Login gagal | Pastikan hash password di `database.sql` ter-import. Gunakan akun demo di atas. |
| Tombol fullscreen tidak bekerja | Browser tertentu memerlukan izin; tekan `F11` sebagai alternatif. |

---

## F. Menggunakan Mode IFP di Kelas

1. Hubungkan laptop ke IFP (HDMI / wireless display).
2. Atur resolusi ke **1920x1080**.
3. Pastikan sudah login di aplikasi utama (`http://localhost/bootcamp/`), karena mode IFP memakai session yang sama.
4. Buka `http://localhost/bootcamp/ifp/` di browser IFP/laptop.
5. Tekan tombol **⛶ Layar Penuh** atau `F11`.
6. Pilih materi/simulasi yang akan dipresentasikan.
7. Gunakan tombol **← / →** atau keyboard untuk berpindah slide.
8. Geser tombol **A＋** bila tulisan dirasa terlalu kecil dari baris belakang.
