# ERD & STRUKTUR DATABASE

Database: **`bootcamp_fullstack`** (utf8mb4, InnoDB).  
File definisi lengkap: `database/database.sql`.

---

## 1. Diagram Hubungan (Ringkas)

```
students (1)
   │
   ├──< users                    (student_id, nullable)
   ├──< seminar_profiles         (student_id, UNIQUE)
   ├──< seminar_assessments      (student_id, assessor_id, challenge_id)
   ├──< student_progress         (student_id, session_id)
   ├──< student_jobsheets        (student_id, jobsheet_id)
   ├──< coding_attempts          (student_id, challenge_id)
   ├──< competency_assessments   (student_id, assessor_id)
   ├──< remediation              (student_id)
   └──< enrichment               (student_id)

bootcamp_sessions (1) ──< learning_materials (session_id)
                     └──< student_progress  (session_id)

jobsheets (1) ──< student_jobsheets (jobsheet_id)
coding_challenges (1) ──< coding_attempts (challenge_id)
                      └──< seminar_assessments (challenge_id)

users (1) ──< seminar_assessments (assessor_id)
         └──< competency_assessments (assessor_id)
         └──< activity_log (user_id)

question_bank  (berdiri sendiri, dipilih via IDs pada seminar_assessments)
settings       (key-value)
```

Legenda: `──<` berarti "satu ke banyak" (one-to-many).

---

## 2. Daftar Tabel (17 tabel)

| # | Tabel | Primary Key | Fungsi |
|---|---|---|---|
| 1 | `students` | `id` | Data siswa & ringkasan PKL |
| 2 | `users` | `id` | Akun login (admin/guru/siswa) |
| 3 | `settings` | `id` | Pengaturan key-value |
| 4 | `bootcamp_sessions` | `id` | 16 pertemuan bootcamp |
| 5 | `learning_materials` | `id` | Materi pendukung per pertemuan |
| 6 | `student_progress` | `id` | Progres belajar siswa per sesi |
| 7 | `seminar_profiles` | `id` | Identitas & presentasi PKL |
| 8 | `question_bank` | `id` | Pertanyaan bedah kode |
| 9 | `coding_challenges` | `id` | 15 tantangan live coding |
| 10 | `seminar_assessments` | `id` | Penilaian seminar |
| 11 | `coding_attempts` | `id` | Sesi live coding siswa |
| 12 | `jobsheets` | `id` | 12 jobsheet praktikum |
| 13 | `student_jobsheets` | `id` | Progres penyelesaian jobsheet |
| 14 | `competency_assessments` | `id` | Penilaian kompetensi UKK |
| 15 | `remediation` | `id` | Program remediasi |
| 16 | `enrichment` | `id` | Program pengayaan |
| 17 | `activity_log` | `id` | Jejak aktivitas sederhana |

---

## 3. Detail Kolom Penting

### students
`id`, `nis` (UNIQUE), `name`, `class_name`, `gender`, `phone`, `email`, `pkl_place`, `pkl_title`, `technologies`, `problem_desc`, `features`, `status`, `created_at`, `updated_at`

### users
`id`, `username` (UNIQUE), `password` (bcrypt hash), `full_name`, `role` (`admin`/`guru`/`siswa`), `student_id` (FK), `is_active`, `last_login`

### bootcamp_sessions
`id`, `week_no`, `session_no`, `title`, `objectives`, `core_material`, `concept`, `diagram`, `code_example`, `demo`, `exercise`, `questions`, `evaluation`, `reflection`, `duration_min`

### competency_assessments
`id`, `student_id`, `assessor_id`, `assessment_date`, 6 kolom skor (`score_understanding`, `score_trace`, `score_implementation`, `score_db_security`, `score_testing`, `score_communication`), `total_score` (DECIMAL 5,2), `notes`

### seminar_assessments
`id`, `student_id`, `assessor_id`, 6 aspek skor 0–100, `total_score`, `selected_question_ids` (daftar id dipisah koma), `challenge_id`, `observation_sheet`, `assessor_notes`, `assessed_at`

### coding_attempts
`id`, `student_id`, `challenge_id`, `started_at`, `ended_at`, `duration_sec`, `status` (`berjalan`/`selesai`/`dinilai`), `result_text`, `examiner_score`, `examiner_notes`

---

## 4. Bobot Penilaian

### Kompetensi UKK (skala 1–4 → persentase)
| Aspek | Bobot |
|---|---|
| Memahami Tugas | 10% |
| Menelusuri Kode | 15% |
| Implementasi | 30% |
| Database & Keamanan | 15% |
| Testing & Debugging | 20% |
| Komunikasi Teknis | 10% |

Rumus: `total = Σ(skor/4 × bobot)`

### Seminar PKL (skala 0–100)
| Aspek | Bobot |
|---|---|
| Pemahaman Masalah | 10% |
| Arsitektur & Database | 15% |
| Pemahaman Kode | 20% |
| Live Coding | 30% |
| Pengujian | 15% |
| Komunikasi | 10% |

Rumus: `total = Σ(skor/100 × bobot)`

### Predikat
| Nilai | Predikat | Tindak lanjut |
|---|---|---|
| ≥ 85 | Sangat Baik | Pengayaan |
| 75–84,99 | Baik | Pertahankan |
| 65–74,99 | Cukup | Remediasi ringan |
| < 65 | Perlu Remediasi | Remediasi intensif |

---

## 5. Data Dummy

| Tabel | Jumlah | Catatan |
|---|---|---|
| students | 12 | NIS 2023001–2023012 |
| users | 14 | 2 guru/admin + 12 siswa |
| bootcamp_sessions | 16 | 4 minggu × 4 pertemuan |
| learning_materials | 8 | Materi pendukung |
| question_bank | 23 | 9 kategori |
| jobsheets | 12 | JS-01 … JS-12 |
| coding_challenges | 15 | LC-01 … LC-15 |
| competency_assessments | 12 | Satu per siswa |
| student_jobsheets | 46 | Progres awal |
| settings | 8 | Konfigurasi sekolah |
| seminar_profiles | 4 | Peserta seminar |

---

## 6. Catatan Integritas

- Foreign key aktif (`SET FOREIGN_KEY_CHECKS = 1`).
- `ON DELETE CASCADE` pada tabel turunan siswa.
- `ON DELETE SET NULL` pada `assessor_id` bila akun guru dihapus.
- Unique constraint:
  - `users.username`
  - `students.nis`
  - `seminar_profiles.student_id`
  - `student_jobsheets(student_id, jobsheet_id)`
  - `student_progress(student_id, session_id)` (jika didefinisikan)
- Backup yang dibuat aplikasi menyertakan `SET FOREIGN_KEY_CHECKS = 0` saat restore agar urutan tabel tidak masalah.
