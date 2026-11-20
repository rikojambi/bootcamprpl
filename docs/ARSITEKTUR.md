# ARSITEKTUR APLIKASI

## 1. Gambaran Umum

Aplikasi menggunakan arsitektur **3 lapis (three-tier)** yang berjalan sepenuhnya di satu komputer lokal:

```
┌─────────────────────────────────────────────────────────────┐
│  PRESENTATION LAYER (Browser / IFP)                         │
│  HTML + CSS custom (app.css, ifp.css) + JS vanilla          │
│  - Dashboard, modul, form, tabel                            │
│  - Mode presentasi IFP (font besar, tombol sentuh)          │
└───────────────────────────┬─────────────────────────────────┘
                            │ HTTP (GET/POST) + session cookie
┌───────────────────────────▼─────────────────────────────────┐
│  APPLICATION LAYER (Apache/PHP 8.3)                         │
│  - config/     : konfigurasi, PDO, helper                   │
│  - auth/       : login, role, require_login, CSRF           │
│  - includes/   : header/footer layout                       │
│  - modules/    : logika tiap fitur                          │
│  - ifp/        : presenter layout                           │
└───────────────────────────┬─────────────────────────────────┘
                            │ PDO prepared statements
┌───────────────────────────▼─────────────────────────────────┐
│  DATA LAYER (MySQL 8.4 - bootcamp_fullstack)                │
│  17 tabel: students, users, settings, sessions, materials,  │
│  progress, seminar, questions, challenges, jobsheets,       │
│  attempts, assessments, remediation, enrichment, log ...    │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Alur Request

Contoh: guru membuka halaman Penilaian Kompetensi.

1. Browser mengirim `GET /bootcamp/modules/assessment/index.php` + cookie session.
2. PHP memuat `config/database.php` → `config.php` → `helpers.php`; session dimulai.
3. `auth/auth.php` memeriksa `require_login()`; bila belum login → redirect ke `login.php`.
4. Halaman memanggil `db_all()` (PDO) untuk mengambil data siswa + nilai terakhir.
5. `includes/header.php` merender sidebar, topbar, flash message.
6. Data ditampilkan dalam tabel; `includes/footer.php` menutup layout dan memuat `app.js`.
7. Tidak ada permintaan ke internet sama sekali.

---

## 3. Lapisan Konfigurasi

| File | Fungsi |
|---|---|
| `config/config.php` | Konstanta aplikasi, BASE_PATH, kredensial DB, bobot penilaian, session |
| `config/database.php` | Singleton PDO + helper `db_all`, `db_one`, `db_run`, `db_value`, `setting` |
| `config/helpers.php` | `e()`, `url()`, `asset()`, CSRF, flash, validasi skor, format tanggal |
| `auth/auth.php` | `attempt_login()`, `logout_user()`, `current_user()`, `can_manage()`, `require_*()` |

### Keamanan
- **Session regenerated** saat login (`session_regenerate_id(true)`).
- **CSRF token** di semua form POST (`csrf_field()` + `csrf_guard()`).
- **Prepared statements** di semua query PDO.
- **Role check**: `can_manage()` hanya untuk `admin`/`guru`.
- **Output escaping** memakai `e()` (htmlspecialchars) di seluruh tampilan.
- Password disimpan sebagai **bcrypt hash** (`password_hash`/`password_verify`).

---

## 4. Modul & Dependensi Database

| Modul | Tabel Utama |
|---|---|
| Dashboard | students, jobsheets, student_jobsheets, competency_assessments, bootcamp_sessions, student_progress |
| Seminar | seminar_profiles, seminar_assessments, question_bank, coding_challenges |
| Materi | bootcamp_sessions, learning_materials, student_progress |
| Simulasi | (data statis dalam PHP, tidak memakai tabel) |
| Praktikum & Editor Kode | jobsheets, bootcamp_sessions, coding_challenges |
| Jobsheet | jobsheets, student_jobsheets |
| Live Coding | coding_challenges, coding_attempts |
| Bank Soal | question_bank |
| Penilaian | competency_assessments, remediation, enrichment |
| Monitoring | students + hampir seluruh tabel progres |
| Laporan | competency_assessments, remediation, enrichment, activity_log, settings |
| Pengaturan | settings, users, activity_log |

---

## 5. Mode Presentasi IFP

IFP memakai layout terpisah (`ifp/_layout.php`) tanpa sidebar agar layar penuh digunakan untuk konten. Perbedaan utama:

| Aspek | Aplikasi Utama | Mode IFP |
|---|---|---|
| Font dasar | 16px | 20px (bisa diubah) |
| Tinggi tombol | minimal 48px | minimal 60px |
| Navigasi | Sidebar + topbar | Tombol besar + keyboard ← → |
| Fullscreen | Tombol di topbar | Tombol di header + tombol `F` |
| Tujuan | Mengelola data | Menyampaikan materi ke kelas |

Halaman IFP:
- `ifp/index.php` — beranda + statistik + menu
- `ifp/materi.php?no=N&slide=S` — 8 slide per pertemuan
- `ifp/simulasi.php?op=CREATE&step=S` — simulasi 5 operasi
- `ifp/seminar.php` — daftar peserta + detail
- `ifp/challenges.php` — daftar tantangan + detail

---

## 6. Keputusan Teknis

| Keputusan | Alasan |
|---|---|
| CSS custom, bukan Bootstrap CDN | Menjaga aplikasi tetap offline; tidak bergantung internet |
| Editor berbasis `<textarea>` + `highlight_string()` | Ringan, bawaan PHP, cukup untuk pembelajaran |
| Data simulasi disimpan sebagai array PHP | Mudah dipelajari siswa, tidak perlu tabel tambahan |
| Backup SQL dibuat dengan PDO | Tidak bergantung `mysqldump` (bisa dinonaktifkan di hosting) |
| Import/Export CSV manual | Memenuhi kebutuhan offline tanpa library eksternal |

---

## 7. Batasan yang Disengaja

- Tidak ada notifikasi email / push (offline).
- Tidak ada multi-sekolah / tenant (satu sekolah, satu database).
- Live coding **tidak mengeksekusi kode siswa** di server; kode hanya disimpan sebagai teks untuk dinilai guru. Ini menjaga keamanan server lokal.
- Editor kode menyimpan draft di `localStorage` browser, bukan ke database.
