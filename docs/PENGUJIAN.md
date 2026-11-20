# DOKUMENTASI PENGUJIAN

Dokumen ini mencatat pengujian yang dilakukan selama pengembangan aplikasi **Bootcamp Pre-UKK Fullstack Developer**.

---

## 1. Lingkungan Pengujian

| Item | Nilai |
|---|---|
| Sistem Operasi | Windows 11 (64-bit) |
| Web Server | PHP built-in server 8.3.36 (port 8080) + Laragon Apache (port 80) |
| Database | MySQL 8.4.3 (port 3306, user root) |
| Browser | Chrome/Edge (via cURL untuk pengujian otomatis) |
| Path aplikasi | `E:\VIBE CODING\open9\bootcamp-fullstack` |
| URL pengujian | `http://127.0.0.1:8080/bootcamp/` |
| URL produksi | `http://localhost/bootcamp/` |

---

## 2. Metode Pengujian

Pengujian dilakukan dengan:
1. **Lint PHP**: `php -l` pada seluruh file `.php`.
2. **HTTP smoke test**: permintaan GET ke setiap halaman memakai cookie session guru/siswa, memeriksa kode status `200` dan tidak adanya `Fatal error`/`Warning`.
3. **Login test**: GET halaman login → ambil CSRF token → POST kredensial → GET dashboard.
4. **Verifikasi database**: query `COUNT(*)` pada tabel-tabel utama.
5. **Uji alur POST**: simpan penilaian, jobsheet, bank soal, dan backup (dengan CSRF token).

---

## 3. Hasil Pengujian per Versi

### v0.1 — Fondasi (Login, Dashboard, Database)
| No | Pengujian | Hasil |
|---|---|---|
| 1 | Import `database/database.sql` | ✅ Berhasil (setelah perbaikan escape `\n` dan apostrop) |
| 2 | Jumlah data dummy | ✅ students 12, users 14, sessions 16, jobsheets 12, challenges 15 |
| 3 | GET `/login.php` | ✅ HTTP 200, token CSRF terbentuk |
| 4 | POST login `guru`/`guru123` | ✅ HTTP 302 redirect ke dashboard |
| 5 | GET dashboard (session aktif) | ✅ HTTP 200, statistik tampil |
| 6 | GET dashboard tanpa session | ✅ Redirect ke login |
| 7 | Query progres per minggu | ✅ Diperbaiki bug `s.status` → `p.status` |

### v0.2 — Seminar Hasil PKL
| No | Pengujian | Hasil |
|---|---|---|
| 1 | GET `modules/seminar/index.php` | ✅ HTTP 200 |
| 2 | GET `modules/seminar/detail.php?id=2` | ✅ HTTP 200, 5 tab tampil |
| 3 | GET `modules/seminar/print.php?id=2` | ✅ HTTP 200, lembar observasi tampil |

### v0.3 — Materi & Simulasi
| No | Pengujian | Hasil |
|---|---|---|
| 1 | GET `modules/materi/index.php` | ✅ HTTP 200, 16 kartu pertemuan |
| 2 | GET `modules/materi/detail.php?no=6` | ✅ HTTP 200 |
| 3 | GET `modules/simulasi/index.php` | ✅ HTTP 200 (setelah perbaiki tag `?>` parser PHP) |
| 4 | Simulasi 5 operasi | ✅ CREATE, READ, UPDATE, DELETE, LOGIN tersedia |

### v0.4 — Praktikum & Editor Kode
| No | Pengujian | Hasil |
|---|---|---|
| 1 | GET `modules/praktikum/index.php` | ✅ HTTP 200 |
| 2 | GET `modules/praktikum/detail.php?id=6` | ✅ HTTP 200, penjelasan kode otomatis |
| 3 | GET `modules/code/index.php?mode=modifikasi` | ✅ HTTP 200, textarea editor tampil |

### v0.5 — Jobsheet
| No | Pengujian | Hasil |
|---|---|---|
| 1 | GET `modules/jobsheet/index.php` | ✅ HTTP 200 |
| 2 | GET `modules/jobsheet/detail.php?id=6` | ✅ HTTP 200 |
| 3 | POST update status jobsheet | ✅ Tersimpan (INSERT ... ON DUPLICATE KEY UPDATE) |
| 4 | GET `modules/jobsheet/print.php?all=1` | ✅ HTTP 200 |

### v0.6 — Tantangan Live Coding
| No | Pengujian | Hasil |
|---|---|---|
| 1 | GET `modules/challenges/index.php` | ✅ HTTP 200, 15 kartu |
| 2 | GET `modules/challenges/detail.php?id=1` | ✅ HTTP 200, timer inisialisasi |
| 3 | GET `modules/challenges/history.php` | ✅ HTTP 200 |
| 4 | POST `start` → `submit` → `grade` | ✅ Status attempt berubah hingga `dinilai` |
| 5 | Role check pada `process.php` | ✅ Diperbaiki: guru bisa menilai tanpa `student_id` |

### v0.7 — Bank Soal
| No | Pengujian | Hasil |
|---|---|---|
| 1 | GET `modules/questions/index.php` | ✅ HTTP 200, 23 pertanyaan |
| 2 | GET `modules/questions/form.php` | ✅ HTTP 200 |
| 3 | POST tambah/edit/hapus | ✅ Berhasil dengan CSRF |

### v0.8 — Penilaian Kompetensi
| No | Pengujian | Hasil |
|---|---|---|
| 1 | GET `modules/assessment/index.php` | ✅ HTTP 200 |
| 2 | GET `modules/assessment/detail.php?student_id=1` | ✅ HTTP 200 |
| 3 | POST penilaian (6 aspek) | ✅ Nilai total terhitung sesuai bobot |
| 4 | GET `modules/assessment/print.php` | ✅ HTTP 200 |

### v0.9 — Monitoring & Laporan
| No | Pengujian | Hasil |
|---|---|---|
| 1 | GET `modules/students/index.php` | ✅ HTTP 200 |
| 2 | GET `modules/students/detail.php?id=1` | ✅ HTTP 200, 5 tab |
| 3 | GET `modules/reports/index.php` | ✅ HTTP 200, rekomendasi otomatis |
| 4 | GET `modules/reports/import.php` | ✅ HTTP 200 |
| 5 | GET `modules/reports/export.php` | ✅ Menghasilkan file CSV + BOM UTF-8 |
| 6 | POST import CSV | ✅ Siswa baru ditambahkan / yang ada diperbarui |

### v0.10 — Pengaturan
| No | Pengujian | Hasil |
|---|---|---|
| 1 | GET `modules/settings/index.php` | ✅ HTTP 200, 4 tab |
| 2 | GET `modules/settings/form.php` | ✅ HTTP 200 |
| 3 | GET `modules/settings/backup.php` | ✅ File SQL terunduh + tersimpan di `storage/backups` |

### v1.0 — Mode IFP & Finalisasi
| No | Pengujian | Hasil |
|---|---|---|
| 1 | GET `ifp/index.php` | ✅ HTTP 200 |
| 2 | GET `ifp/materi.php?no=6` | ✅ HTTP 200, slide 1 tampil |
| 3 | GET `ifp/simulasi.php` | ✅ HTTP 200 |
| 4 | GET `ifp/seminar.php` | ✅ HTTP 200 |
| 5 | GET `ifp/challenges.php` | ✅ HTTP 200 |
| 6 | Navigasi keyboard ← → | ✅ Terpasang di `ifp.js` |
| 7 | Tombol fullscreen & A＋/A− | ✅ Terpasang di `app.js` |
| 8 | GET `http://localhost/bootcamp/login.php` (Apache port 80) | ✅ HTTP 200 |
| 9 | Login produksi `guru`/`guru123` | ✅ HTTP 302 redirect ke dashboard |
| 10 | 16 URL produksi (app + IFP) lewat Apache | ✅ Semua HTTP 200, 0 failure |
| 11 | Smoke test 37 halaman (guru) & 32 halaman (siswa) | ✅ 0 failure |
| 12 | 5 halaman khusus guru ditest sebagai siswa | ✅ Semua ditolak HTTP 403 |
| 13 | PHP lint 54 file | ✅ 0 failure |
| 14 | Sweep 18 halaman (log error kosong) | ✅ 0 warning |

---

## 4. Pengujian Keamanan Sederhana

| Pengujian | Cara | Hasil |
|---|---|---|
| CSRF | POST tanpa `csrf_token` | ✅ Ditolak (HTTP 403) |
| SQL Injection | Input `' OR 1=1 --` pada login | ✅ Ditolak (prepared statement) |
| XSS | Input `<script>alert(1)</script>` pada bank soal | ✅ Ditampilkan sebagai teks (e()) |
| Role siswa mengakses Pengaturan | GET `modules/settings/index.php` sebagai siswa | ✅ Diblokir `require_manager()` |
| Siswa mengubah jobsheet siswa lain | POST dengan `student_id` berbeda | ✅ Ditolak (HTTP 403) |
| Siswa melihat data siswa lain di Monitoring/Laporan | GET sebagai `2023001`, cek nama siswa lain | ✅ Hanya data sendiri yang tampil |
| Siswa mengunduh ekspor CSV seluruh kelas | GET `modules/reports/export.php` | ✅ Ditolak (HTTP 403) |

---

## 5. Cara Mengulang Pengujian

1. Pastikan MySQL & server PHP berjalan.
2. Import ulang `database/database.sql` untuk kondisi bersih.
3. Jalankan lint:
   ```cmd
   for /r %f in (*.php) do C:\laragon\bin\php\php-8.3.36-Win32-vs16-x64\php.exe -l "%f"
   ```
4. Buka browser dan login dengan akun demo.
5. Kunjungi setiap URL pada tabel di atas dan pastikan tidak ada error.

---

## 6. Catatan Perbaikan Selama Pengujian

| Masalah | Penyebab | Perbaikan |
|---|---|---|
| Import SQL gagal di baris 394 | Escape `\n` di dalam string SQL ditafsirkan CLI MySQL | Diubah menjadi newline aktual |
| Error SQL di baris 667 | Apostrop pada `Don't` | Diganti `Don''t` |
| Error SQL di baris 922 | Ekspresi PHP `. ($page * 5)` di dalam string SQL | Diganti teks deskriptif |
| Dashboard fatal `s.status` | Alias tabel salah pada query progres | Diubah ke `p.status` |
| `modules/simulasi/index.php` parse error | Tag penutup PHP `?>` tertinggal setelah array | Ditambahkan `?>` sebelum HTML |
| Guru tidak bisa menilai live coding | Cek `student_id` di awal `process.php` | Cek dipindah setelah blok `grade` |
| Typo `detika` | Kesalahan ketik di `auth/auth.php` | Diubah menjadi `detik` |
| Karakter asing (aksara Tionghoa) pada komentar/string | Tertulis saat generasi konten | Diganti dengan bahasa Indonesia |
| Warning `Undefined array key` di seminar detail.php | Query `$challenges` hanya memilih `id, code, title, level` | Query ditambah `description, duration_min, competency` |
| Monitoring & laporan menampilkan semua siswa ke akun siswa | Query tidak membatasi berdasarkan role | Query dibatasi `student_id`; ekspor CSV khusus guru; label disesuaikan |
