# LOCOMO ORCHESTRATOR ENGINE (AgOS)
**File Name:** `orchestrator-workflow.md`
**Role:** Master Controller / State Machine
**Global Constraint:** Sebelum memulai transisi *state* manapun, Anda WAJIB me-load dan mematuhi isi direktori `skills/roms/` (tech-stack, environment, business-rules) serta `skills/patterns/locomo-signature.md`. Instruksi pada kelompok *core* ini bersifat mutlak.

## DEFINITION OF DONE (DoD)
Sebuah tugas hanya dinyatakan selesai (STATE: DONE) jika memenuhi seluruh kriteria absolut berikut:
1. Semua log Audit dan Keamanan telah bersih dan di-approve oleh *Reviewer/Security Auditor*.
2. Semua Automated Tests (Unit/Feature) berstatus PASS oleh *Tester*.
3. Kode 100% mematuhi aturan di dalam file-file `skills/roms/`.

---

## ⚙️ THE STATE MACHINE WORKFLOW (AI SOFTWARE FACTORY)

**INPUT:** Goal System (Deskripsi fitur/modul yang akan dikerjakan)
**STATE:** `INIT`

### STEP 0 — THE KERNEL BOOT (Initialization)
* **Action:** Baca keseluruhan konfigurasi di `skills/roms/*` dan `skills/patterns/locomo-signature.md`.
* **Transition:** Pahami *environment* ekseskusi dan tumpukan teknologinya. Pindah ke STEP 1.

### STEP 1 — THE ARCHITECT (System Design)
* **[CONTEXT SWITCH]:** Load and strictly follow `skills/roles/architect.md`. 
* **Action:** Rancang arsitektur sistem, tentukan skema *database*, layanan integrasi, dan batasan teknis berdasarkan `roms/tech-stack.md`. Hasilkan spesifikasi final di `docs/current-spec.md`.
* **Gate:** `[HITL_APPROVAL]` — Tunggu konfirmasi persetujuan user atas spesifikasi jika berdampak masif atau jika tidak ada instruksi langsung dari user. Jika *Approved*, **STATE -> `DESIGN_APPROVED`**. 
* **Intelligent Routing:** Lanjut ke STEP 2A (Coder) atau STEP 2B (UI Developer).

### STEP 2A — THE CODER (Backend / Full-Stack Implementation)
* **[CONTEXT SWITCH]:** Explicitly load `skills/roles/coder.md`.
* **Action:** Jika spesifikasi membutuhkan pekerjaan manipulasi data, integrasi API, logika service/controller, dan rancangan model, Coder mengeksekusinya di sini berpedoman pada `current-spec.md`.
* **Transition:** **STATE -> `CODE_GENERATED`**. Lanjut ke STEP 3A (Reviewer) atau STEP 3B (Security Auditor).

### STEP 2B — THE UI DEVELOPER (Frontend Specialized Implementation)
* **[CONTEXT SWITCH]:** Explicitly load `skills/roles/ui-developer.md`.
* **Action:** Jika tugas spesifik mengubah wujud CSS, visual antarmuka, *Micro-interactions*, animasi DOM, atau modifikasi file View, agen ini akan mengambil alih.
* **Transition:** **STATE -> `UI_GENERATED`**. Lanjut ke STEP 3A (Reviewer).

### STEP 3A — THE REVIEWER (Code Quality & Compliance Audit)
* **[CONTEXT SWITCH]:** Explicitly load `skills/roles/reviewer.md`.
* **Action:** Audit hasil kerja Step 2. Periksa apakah kode sesuai dengan `locomo-signature.md` dan `roms/*`. Asumsikan kode cacat.
* **Output:** Tulis temuan di `docs/issue-log.md`.
* **Routing:** Jika *CLEAN*, lanjut ke STEP 4 (Tester). Jika kotor, ke STEP 5 (Fixer).

### STEP 3B — THE SECURITY AUDITOR (Red-Team Threat Hunting)
* **[CONTEXT SWITCH]:** Explicitly load `skills/roles/security-auditor.md`.
* **Condition:** WAJIB diakses jika fitur melibatkan Autentikasi, otorisasi ID rentan, bulk upload/file parsing, atau query database mentah.
* **Routing:** Catat kelemahan di `docs/issue-log.md` lalu kembalikan ke FIXER.

### STEP 4 — THE TESTER (Execution Engine)
* **[CONTEXT SWITCH]:** Explicitly load `skills/roles/tester.md`.
* **Action:** Jalankan perintah *testing* / *script validation* di CLI Linux WSL sesuai yang tercantum pada `roms/environment.md` (misal via Sail atau Native Artisan).
* **Routing:** Jika *FAIL*, catat log error (*stack trace*) dan lemparkan ke STEP 5 (FIXER). Jika *PASS*, lemparkan ke STEP 6.

### STEP 5 — THE FIXER (Surgeon / Repair Specialist)
* **[CONTEXT SWITCH]:** Explicitly load `skills/roles/fixer.md`.
* **Action:** Baca log error di terminal atau `docs/issue-log.md`. Perbaiki baris kode penyebab kerusakan secara mikroskopikal tanpa mengubah arsitektur.
* **Routing:** Jika diperbaiki, KEMBALI MUTER DALAM LOOP ke **STEP 4 (TESTER)** atau **STEP 3A (REVIEWER)**. Jika gagal repetitif 3x, serahkan ke manusia `[HITL_APPROVAL_REQUIRED]`.

### STEP 6 — FINAL VALIDATION & RELEASE (The Gatekeeper)
* **[CONTEXT SWITCH]:** Kembalikan semua state.
* **Action:** Verifikasi akhir rilis berdasarkan metrik `roms/environment.md`. Rilis dipaketisasi (jika direquest secara eksplisit). Sistem mengirimkan konfirmasi penyelesaian.