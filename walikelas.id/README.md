# WaliKelas.id (v2.0 MVP)
> **Relational-First Primary Education Copilot & Shared Workspace**  
> *Memberdayakan Guru SD & Tutor Les Privat dengan AI Google Gemini Berlandaskan Keamanan Anak Mutlak.*

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![React](https://img.shields.io/badge/React-18.x-61DAFB?style=for-the-badge&logo=react&logoColor=black)](https://reactjs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-v1.0-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://postgresql.org)
[![Google Gemini](https://img.shields.io/badge/Google_Gemini-1.5_Flash-4285F4?style=for-the-badge&logo=google&logoColor=white)](https://ai.google.dev/)
[![Tests](https://img.shields.io/badge/Tests-30%20Passed%20(100%25)-22c55e?style=for-the-badge&logo=checkmarx&logoColor=white)](./tests)

---

## 📌 Sekilas Tentang Proyek

Pendidik sekolah dasar (SD) kelas 1–3 di Indonesia menghabiskan lebih dari **15 jam setiap minggu** untuk beban administratif non-mengajar: merancang modul ajar/RPP, memetakan rubrik capaian, memeriksa tugas uraian, dan merangkum laporan perkembangan anak untuk orang tua secara manual.

**WaliKelas.id** adalah platform *shared workspace* berbasis AI Google Gemini yang memangkas waktu kerja administratif guru hingga **80%**, tanpa pernah mengorbankan keamanan anak atau mendegradasi peran pendidik. 

Berbeda dari aplikasi EdTech konvensional yang memaksakan chatbot generatif bebas kepada anak, WaliKelas.id menerapkan prinsip **Server-Driven UI (SDUI)** tanpa chatbot bebas, mengunci privasi anak secara mutlak, dan mewajibkan kendali manusia (*Human-in-the-Loop*) pada setiap keputusan rapor dan intervensi belajar.

---

## 🚀 Fitur Unggulan Sistem

### 1. 🏫 Shared Educator Workspace (Dua Mode Operasional)
- **Mode Sekolah:** Terhubung dengan identitas satuan pendidikan (NPSN) dan kelas paralel.
- **Mode Privat Mandiri:** Mendukung guru les dan tutor independen tanpa memaksakan NPSN institusi (**TI-01**).
- **Peralihan Akun 1-Klik:** Navigasi cepat antara profil guru sekolah dan tutor privat.

### 2. 📝 Planning Copilot (Ekstraksi Silabus Gemini AI)
- **Jalur Impor Teks Bebas:** Salin-tempel teks silabus kasar $\rightarrow$ AI mengekstraksi sasaran capaian belajar, alur aktivitas, durasi pertemuan, dan rubrik terstandar dalam format skema ketat.
- **Deteksi Ambiguitas:** AI secara transparan menandai ketidaklengkapan (misal: durasi pertemuan belum spesifik) dan melarang klaim kepatuhan regulasi palsu (**TI-14**).
- **Draft-First Invariant:** Seluruh hasil AI wajib berstatus `draft`; dilarang mengaktifkan rencana secara otomatis tanpa pengesahan pendidik (**TI-04**).

### 3. 🧒 Child-Safe Learner Runner (Server-Driven UI PWA)
- **Tanpa Akun & Password Siswa:** Siswa masuk menggunakan *Launch Token* terenkripsi sekali pakai (**TI-22**).
- **Anti-Distraksi:** Sesi membaca fokus 10 menit dengan timer cerdas, tombol petunjuk bertingkat (*progressive hints*), dan anjuran istirahat layar setelah misi tuntas (**TI-13**).
- **Safeguarding Circuit Breaker:** Deteksi kata kunci berisiko seketika menjeda sesi runner anak dan mengirim alert darurat ke inbox guru (**TI-10**).
- **Zero Free Chatbot to Kids:** Materi disajikan melalui komponen SDUI terkurasi (`SingleChoiceCard`, `ContextWordMatcher`, `ShortPromptReader`).

### 4. 👨‍👩‍👧 Guardian Offline Checkpoint (Kemitraan Rumah)
- **Misi Dongeng Meja Makan:** Aktivitas non-digital 5 menit antara anak dan orang tua di rumah.
- **Konfirmasi 1-Klik:** Orang tua membuka tautan aman di smartphone dan memilih status: *Selesai* / *Belum Selesai* / *Butuh Bantuan Guru*.
- **Pemisahan Konfirmasi & Nilai:** Konfirmasi wali hanya mencatat penyelesaian tugas rumah, tidak dapat mengubah nilai capaian kompetensi anak (**TI-07**).
- **Quiet Hours & Honest Outbox:** Notifikasi ditahan otomatis pada jam tenang (20.00 – 07.00 WIB) dan dialihkan ke tautan web aman jika gateway WA offline (**TI-08, TI-09**).

### 5. 🚨 Teacher Triage Queue & Pengesahan Intervensi
- **Antrean Prioritas Cerdas:** Menyaring kasus siswa yang membutuhkan penanganan (Alert Keamanan $\rightarrow$ Permintaan Bantuan Wali $\rightarrow$ Tugas Belum Dinilai $\rightarrow$ Usulan Intervensi).
- **Human-in-the-Loop Mutlak:** Copilot merekomendasikan intervensi perbaikan. Jika guru menolak (`reject`), rekomendasi diabaikan dan **tidak ada intervensi yang dibuat** (**TI-05**).
- **Laporan Progres 3-Lapisan:** Penerbitan laporan berstruktur:
  - *Lapisan 1 (Fakta):* Kehadiran, durasi, misi selesai.
  - *Lapisan 2 (Temuan):* Capaian rubrik berbasis bukti respons nyata.
  - *Lapisan 3 (Rekomendasi):* Tindak lanjut belajar dan panduan orang tua di rumah.
  - *Invalidasi Otomatis:* Koreksi nilai asesmen di kemudian hari otomatis menandai laporan approved menjadi `needs_review` (**TI-12**).

### 6. 📊 Telemetri Efisiensi Guru & VC Data Room
- **Pelacakan Durasi Nyata:** Pencatatan waktu kerja guru secara empiris vs baseline waktu konvensional (**TAT-17**).
- **Ekspor CSV Data Room:** Tombol unduh instan berkas CSV telemetri efisiensi untuk kelengkapan *pitch deck* investor VC (`GET /telemetry/export-csv`).

---

## 🏛️ Arsitektur Defensibilitas (14 Technical Invariants)

| Kode | Invariant Teknis | Penjelasan Jaminan Sistem |
|---|---|---|
| **TI-01** | Zero-Friction Independence | Bebas institusi untuk program privat; kolom `organization_id` bersifat nullable. |
| **TI-02** | Multi-Tenant Isolation | Akses berbasis relasi program aktif; cross-tenant access menghasilkan **HTTP 404**. |
| **TI-03** | Verified Guardianship | Portal wali terbatas hanya untuk data anak yang sah diverifikasi dua sisi. |
| **TI-04** | Draft-First Planning | Ekstraksi Gemini AI diwajibkan berstatus `draft`; tanpa auto-approval. |
| **TI-05** | Educator-in-the-Loop | Intervensi AI berstatus `pending`; penolakan guru menjamin tidak ada aksi diterapkan. |
| **TI-06** | Human Mastery Validation | Status `educator_validated` dikunci di tingkat Check Constraint database dan Policy. |
| **TI-07** | Offline Independence | Konfirmasi wali tidak dapat memodifikasi `mastery_states`. |
| **TI-08** | Two-Sided Consent PDP | Pengiriman notifikasi ke wali non-verified diblokir (`blocked_policy`). |
| **TI-09** | Quiet Hours & Honest Outbox| Penjaga jam tenang (20.00 - 07.00 WIB) & status jujur jika adapter saluran offline. |
| **TI-10** | Safeguarding Circuit Breaker| Kata kunci berisiko tinggi memicu jeda otomatis dan alert darurat ke pendidik. |
| **TI-11** | Plan Approval Gate | Tugas misi ditolak (`PLAN_NOT_APPROVED`) bila rencana ajar belum approved. |
| **TI-12** | 3-Layer Reports & Invalidation | Koreksi nilai asesmen menandai laporan lama menjadi `needs_review`. |
| **TI-13** | Immutable Audit Trail | Tabel `audit_events` dilindungi trigger PostgreSQL (append-only, larangan UPDATE/DELETE). |
| **TI-14** | Resilient AI Fallback | Error/timeout Gemini dicatat di `ai_runs` dan menyediakan form penilaian manual. |

---

## 🛠️ Stack Teknologi

- **Backend:** Laravel 12 (PHP 8.4 FPM)
- **Frontend:** React 18, TypeScript, Inertia.js, Vanilla CSS Design System
- **Database:** PostgreSQL 16 (Relational DDL, JSONB indexing, check constraints)
- **AI Engine:** Google Gemini API (`gemini-1.5-flash`) dengan strict JSON Schema & local deterministic mock
- **Containerization:** Docker Compose (PHP-FPM, Nginx, PostgreSQL, Vite)

---

## ⚡ Panduan Menjalankan Sistem (Local Development)

### Prasyarat:
- Docker & Docker Compose terinstal di mesin Anda.

### Langkah Menjalankan:
```bash
# 1. Masuk ke direktori proyek
cd walikelas.id

# 2. Salin environment config
cp .env.example .env

# 3. Jalankan container Docker
docker compose up -d

# 4. Install dependensi & jalankan migrasi database
docker compose exec app composer install
docker compose exec app php artisan migrate --seed

# 5. Kompilasi asset frontend (React / Vite)
docker compose exec vite npm install --legacy-peer-deps
docker compose exec vite npm run build
```

Aplikasi siap diakses melalui web browser:
- 🌐 **Dashboard Guru & Ruang Kelas:** [http://localhost:8088](http://localhost:8088)
- 🧒 **Learner Mission Runner (Kiki):** [http://localhost:8088/runner/demo-runner-token-2026](http://localhost:8088/runner/demo-runner-token-2026)
- 👨‍👩‍👧 **Guardian Offline Checkpoint (Maya):** [http://localhost:8088/guardian/checkpoint/b4c30e74-71cf-4d7e-b5fe-5f7262648f4a](http://localhost:8088/guardian/checkpoint/b4c30e74-71cf-4d7e-b5fe-5f7262648f4a)
- 🚨 **Inbox Triage Guru:** [http://localhost:8088/programs/9ae4053b-2104-44aa-971e-8340e8cddfff/triage](http://localhost:8088/programs/9ae4053b-2104-44aa-971e-8340e8cddfff/triage)
- 📊 **Unduh CSV Telemetri Efisiensi:** [http://localhost:8088/telemetry/export-csv](http://localhost:8088/telemetry/export-csv)

---

## 🧪 Pengujian Otomatis (Acceptance Test Suite)

Untuk menjalankan seluruh 30 skenario uji otomatis (*TAT-01 s/d TAT-24*):

```bash
docker compose exec app php artisan test
```

Hasil pengujian mencakup:
- **Phase 0:** Fondasi Relasional & Policy RBAC (`TAT-01`, `TAT-02`, `TAT-07`, `TAT-19`, `TAT-20`)
- **Phase 1:** Planning Copilot & Versi Rencana (`TAT-03`, `TAT-04`, `TAT-05`, `TAT-23`)
- **Phase 2:** Enrollment, Runner SDUI & Checkpoint Rumah (`TAT-06`, `TAT-08`, `TAT-09`, `TAT-13`, `TAT-16`, `TAT-22`, `TAT-24`)
- **Phase 3:** Triage Inbox, Evaluator Gemini & Laporan 3-Lapis (`TAT-10`, `TAT-11`, `TAT-12`, `TAT-14`, `TAT-15`, `TAT-18`, `TAT-21`)
- **Phase 4:** Telemetri Waktu Guru, Security Audit & VC CSV (`TAT-17`, Security Audits)

---

## 📑 Dokumentasi Submission VC (Data Room)

Dokumen lengkap strategi pitching dan video demo tersedia di:
- 🎬 [VC Demo 90-Second Storyboard & Script](../docs/walikelas.id/VC_DEMO_90S_STORYBOARD_SCRIPT.md)
- 💼 [VC Pitch Defensibility & Data Room Brief](../docs/walikelas.id/VC_PITCH_DEFENSIBILITY_AND_DATA_ROOM.md)
- 📐 [Arsitektur Sistem & Rencana Fase Implementasi v2.0](../docs/walikelas.id/WALIKELAS_SYSTEM_ARCHITECTURE_AND_PHASED_IMPLEMENTATION_PLAN_v2.0.md)

---
*© 2026 WaliKelas.id — Empowering Educators, Inspiring Learners.*
