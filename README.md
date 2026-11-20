# BOOTCAMP PRE-UKK FULLSTACK DEVELOPER

Aplikasi pembelajaran **Fullstack Developer** offline untuk SMKN 6 Muaro Jambi, dirancang khusus untuk digunakan di komputer Laragon dan layar **Interactive Flat Panel (IFP)** kelas.

> **Versi:** 1.0.0  
> **Penyusun:** Riko Hardona, S.Pd.  
> **Stack:** PHP 8+ · MySQL/MariaDB · PDO · HTML/CSS/JS vanilla (tanpa CDN)

---

## 1. Tentang Aplikasi

Aplikasi ini membantu guru menyampaikan materi bootcamp 4 minggu (16 pertemuan) dan mengelola seluruh proses pembelajaran praktik fullstack mulai dari **frontend → backend → database**, hingga simulasi industri UKK.

Seluruh aplikasi berjalan **100% lokal/offline**. Tidak ada Bootstrap CDN, Google Fonts, Firebase, atau API eksternal yang dipakai. CSS dan JavaScript ditulis sendiri dan disimpan di folder `assets/`.

### Fitur utama
| Modul | Keterangan |
|---|---|
| Dashboard | Ringkasan statistik, akses cepat, progres per minggu |
| Seminar Hasil PKL | Identitas siswa, presentasi, bedah kode, developer challenge, lembar observasi |
| Materi Bootcamp | 16 pertemuan lengkap (tujuan, konsep, diagram, kode, demo, latihan, evaluasi) |
| Simulasi Arsitektur | Simulasi interaktif CREATE / READ / UPDATE / DELETE / LOGIN |
| Praktikum Fullstack | 12 laboratorium dengan source code + penjelasan otomatis per baris |
| Editor Kode | 4 mode: lihat, pelajari, modifikasi, tantangan (local storage draft) |
| Jobsheet | 12 jobsheet, checklist, penandaan selesai, cetak |
| Tantangan Live Coding | 15 tantangan bertingkat dengan timer dan penilaian |
| Bank Soal | CRUD pertanyaan bedah kode per kategori |
| Penilaian Kompetensi | 6 aspek berbobot, skala 1–4, predikat otomatis |
| Monitoring Siswa | Progres materi, jobsheet, live coding, dan nilai tiap siswa |
| Laporan & Tindak Lanjut | Rekomendasi remediasi/pengayaan, ekspor–impor CSV, log aktivitas |
| Pengaturan | Pengaturan umum, kelola akun, backup SQL, log aktivitas |
| Mode IFP | Tampilan presentasi font besar + tombol sentuh ≥60px + fullscreen |

---

## 2. Struktur Folder

```
bootcamp-fullstack/
├── assets/
│   ├── css/        app.css, ifp.css
│   ├── js/         app.js, ifp.js
│   ├── images/
│   └── vendor/
├── auth/           auth.php (login, role, require helpers)
├── config/         config.php, database.php, helpers.php
├── database/       database.sql (schema + dummy data)
├── docs/           ARSITEKTUR.md, ERD.md, PENGUJIAN.md
├── ifp/            index.php, materi.php, simulasi.php, seminar.php, challenges.php, _layout.php
├── includes/       header.php, footer.php
├── modules/
│   ├── seminar/    index, detail, process, print
│   ├── materi/     index, detail, process
│   ├── simulasi/   index
│   ├── praktikum/  index, detail
│   ├── code/       index
│   ├── jobsheet/   index, detail, process, print
│   ├── challenges/ index, detail, process, history, result, grade
│   ├── questions/  index, form, process
│   ├── assessment/ index, detail, process, print
│   ├── students/   index, detail
│   ├── reports/    index, process, export, import, print, contoh.csv
│   └── settings/   index, form, process, backup
├── storage/
│   ├── uploads/
│   └── backups/
├── backup/         restore.bat
├── index.php       Dashboard
├── login.php
├── logout.php
├── install.bat
├── start-server.bat
├── stop-server.bat
└── backup.bat
```

---

## 3. Instan Cepat (Lihat README-INSTALASI.md untuk panduan lengkap)

1. Pastikan **Laragon** sudah terinstall dan MySQL berjalan.
2. Jalankan `install.bat` (membuat junction `C:\laragon\www\bootcamp`).
3. Import `database/database.sql` ke database `bootcamp_fullstack`.
4. Buka `http://localhost/bootcamp/`.
5. Login:
   - Guru: `guru` / `guru123`
   - Siswa: `2023001` / `siswa123`
6. Mode presentasi: `http://localhost/bootcamp/ifp/`.

---

## 4. Akun Demo

| Role | Username | Password | Keterangan |
|---|---|---|---|
| Admin | `guru` | `guru123` | Pengelola utama (role admin, penuh) |
| Guru | `guru2` | `guru123` | Guru pengajar |
| Siswa | `2023001` | `siswa123` | Ahmad Fauzi (login memakai NIS) |

Akun siswa lain memakai NIS `2023002`–`2023012` dengan password `siswa123`.

---

## 5. Prinsip Desain

- **Offline-first:** tidak ada permintaan jaringan ke luar komputer.
- **Session auth:** `session_regenerate_id()` saat login, CSRF token di semua form POST.
- **Role-based:** `admin`/`guru` mengelola, `siswa` hanya melihat dan mengisi progresnya.
- **Touch-friendly:** tombol minimal 48px (app) dan 60px (IFP).
- **Font minimal 18px** di mode IFP, bisa diperbesar dengan tombol A＋.
- **PDO prepared statements** di seluruh query untuk mencegah SQL injection.

---

## 6. Lisensi & Catatan

Aplikasi ini dibuat untuk keperluan pembelajaran di SMKN 6 Muaro Jambi. Seluruh konten materi, source code, dan data dummy disimpan lokal dan dapat dimodifikasi oleh guru sesuai kebutuhan kelas.
