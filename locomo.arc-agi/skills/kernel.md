# Locomo ARC-AGI Kernel (Master State Machine)
**Version:** 10.2.0-arc-agi
**Core Strategy:** Hypothetico-Deductive Loop — 5-Layer Cognitive Architecture for ARC-AGI Puzzle Solving with Asymmetric Swarm Routing and Dynamic Token Routing.
**Project Identity:** Baca `id.json` (Trinity 1) untuk batasan grid, dependensi sandbox, model routing, dan guardrails ARC.

---

## 1. System Boot & Perception Fallback Protocol

### 1.1 PERCEPTION AUGMENTATION & GRACEFUL DEGRADATION
Sebelum mengeksekusi instruksi APA PUN, agen WAJIB memeriksa status Cognitive Driver dan Sandbox Python.
- **[DRIVERLESS MODE]:** JIKA sandbox Docker gagal merespons, agen tetap boleh menalar (PERCEPTION & HYPOTHESIS) tetapi TIDAK boleh mengklaim verifikasi otomatis. Verifikasi harus manual oleh Orchestrator.

### 1.2 Local Context Boot Priority (Trinity Pattern Memory — ARC Edition)
Agen WAJIB membaca file keadaan berikut secara berurutan:
1. **`id.json` (Trinity 1 — Identitas & Guardrails ARC)**: Batasan ukuran grid (maks 30x30), palet warna (0-9), dependensi sandbox (numpy/scipy/networkx), batas retries (5), dan konfigurasi Asymmetric Swarm Routing.
2. **`cortex/cortex-core.json` (Trinity 2 — Status Penalaran Aktif)**: Fase aktif (`PERCEPTION`/`HYPOTHESIS`/`IMPLEMENTATION`/`VERIFICATION`), jumlah retry saat ini, dan hipotesis yang telah dicoba.
3. **`docs/fplans/` (Trinity 3 — Puzzle Flow Plans)**: Berkas pelacakan hipotesis per teka-teki ARC (bukan per fitur perangkat lunak).
4. **`brain/` (DSL Primitive Library)**: Perpustakaan fungsi primitif yang telah tervalidasi (rotasi, mirroring, flood-fill, dsb.) di pgvector.
5. **`scratch/` (Python Execution Engine)**: Sandbox Docker terisolasi untuk eksekusi dan verifikasi script matriks.

### 1.3 Dynamic Token Routing Protocol (Context Window Management)
> [!CAUTION]
> **[STRICT]:** Untuk mencegah Token Overflow dan pencemaran context window, agen DILARANG memuat grid mentah berukuran > 10x10 langsung ke dalam konteks. Task Loader (`docs/task_loader.py`) WAJIB menerapkan Dynamic Token Routing:
> - **Grid ≤ 10x10**: Loloskan array utuh sebagai teks agar agen bisa melihat pola langsung.
> - **Grid > 10x10**: Ekstrak metadata semantik saja (posisi objek, ukuran, warna, relasi spasial).
> - **Sandbox Python** membaca file grid JSON langsung saat eksekusi — LLM tidak perlu memuat matriks penuh.

## 2. Definition of Done (DoD) — ARC Edition
Sebuah teka-teki hanya dinyatakan selesai (STATE: SOLVED) jika memenuhi kriteria absolut:
1. Script Python yang dihasilkan, ketika dieksekusi pada **SEMUA** contoh input-output (demonstrations), menghasilkan output identik 100% (element-wise match).
2. Script tersebut kemudian dieksekusi pada test input dan menghasilkan prediksi output.
3. Fungsi primitif yang valid disimpan ke `brain/` (pgvector DSL Library) untuk digunakan kembali.

---

## 3. Orchestration Loop (Hypothetico-Deductive Loop)

### [PHASE 1: PERCEPTION] (Grid Analysis & Semantic Translation)
- **Kondisi:** Awal siklus penalaran — soal ARC baru dimuat.
- **Model:** 🔵 Routine LLM (Gemini 3.5 Flash)
- **Action:** Task Loader memuat soal ARC. Perceptor menerima grid (raw array atau metadata semantik) dan menghasilkan deskripsi semantik: objek apa yang ada, posisi, warna, ukuran, dan pola relasi yang terlihat di antara input dan output contoh.
- **Gate:** Otomatis (Fully Autonomous). Langsung transit ke HYPOTHESIS.

### [PHASE 2: HYPOTHESIS] (Abstract Reasoning & Pseudo-code)
- **Kondisi:** Perceptor telah menghasilkan deskripsi semantik.
- **Model:** 🔴 Premium LLM (Gemini Pro)
- **Action:** Theorist mengambil observasi dari Perceptor dan memformulasikan hipotesis aturan transformasi ke dalam pseudo-code atau instruksi matematika diskrit. Theorist DILARANG menulis kode implementasi (hanya logika abstrak). Sebelum menulis hipotesis baru, Theorist WAJIB query `brain/` untuk memeriksa apakah ada fungsi primitif yang relevan dan siap pakai.
- **Gate:** Otomatis. Hipotesis direkam ke `cortex/decisions.jsonl`.

**[CCC_AUTHORIZATION_GATE]**: SEBELUM berpindah ke IMPLEMENTATION, agen WAJIB membaca `cortex-core.json`.
- JIKA `"system_health": "critical"`, SELURUH EKSEKUSI DIHENTIKAN dan masuk mode DEGRADED.
- JIKA `"retry_count"` >= `"max_retries"` (5), trigger `[GATEKEEPER_RESET]`.

### [PHASE 3: IMPLEMENTATION] (DSL Coding & Sandbox Execution)
- **Kondisi:** Hipotesis telah dirumuskan oleh Theorist.
- **Model:** 🔵 Routine LLM (Gemini 3.5 Flash)
- **Action:** DSL Coder mengonversi pseudo-code menjadi script Python menggunakan numpy/scipy/networkx. Script dikirim ke sandbox `scratch/` via `matrix_sandbox.py` untuk dieksekusi terhadap seluruh contoh input-output.
- **Guardrails:** Script DILARANG menggunakan `eval()`, `exec()`, `os.system()`, atau `subprocess`. Output WAJIB berupa list-of-lists integer (0-9).
- **Gate:** `[STRICT_VALIDATION]` — Hanya boleh lanjut ke VERIFICATION jika sandbox mengembalikan output (bukan crash/timeout).

### [PHASE 4: VERIFICATION] (Output Comparison & Learning)
- **Kondisi:** Sandbox telah mengembalikan output array.
- **Model:** 🔵 Routine LLM (Gemini 3.5 Flash)
- **Action:** Validator membandingkan output script vs target output dari contoh-contoh soal secara element-wise.
  - **JIKA 100% match pada SEMUA contoh:**
    - STATE → `SOLVED`. Script dieksekusi pada test input untuk menghasilkan prediksi.
    - Fungsi primitif valid disimpan ke `brain/` (pgvector DSL Library).
    - Update `cortex-core.json`: reset `retry_count`, rekam solusi.
  - **JIKA gagal:**
    - Increment `"retry_count"` di `cortex-core.json`.
    - Log error dikirim ke Fixer (Routine LLM) untuk perbaikan syntax ringan.
    - JIKA error bersifat logis (bukan syntax): Kembali ke PHASE 2 (HYPOTHESIS) — Theorist diminta re-formulate.
- **[CIRCUIT_BREAKER_PROTOCOL]:** Batas maksimal loop penuh (HYPOTHESIS→IMPLEMENTATION→VERIFICATION) adalah **5 KALI** (`MAX_RETRIES = 5`). Jika batas tercapai, Gatekeeper memaksa `context_reset_and_heuristic_switch` — menghapus hipotesis lama dari konteks dan mencoba pendekatan heuristik alternatif (misal: beralih dari pendekatan geometris ke pendekatan topologis). Jika setelah reset masih gagal, trigger `[ESCALATION_GATE]` untuk intervensi Orchestrator.

---

## 4. Asymmetric Swarm Routing Protocol
> [!IMPORTANT]
> Untuk menjaga efisiensi biaya API, kernel menerapkan **Asymmetric Swarm Routing**:
> - **Theorist** (Fase HYPOTHESIS dan eskalasi Deadlock): Selalu menggunakan **Premium LLM** (Gemini Pro). Ini adalah satu-satunya peran yang membutuhkan penalaran abstrak tingkat tinggi.
> - **Perceptor, DSL Coder, Validator, Gatekeeper**: Selalu menggunakan **Routine LLM** (Gemini 3.5 Flash). Tugas mereka bersifat deterministik dan volume tinggi.

## 5. Destructive Operations Protocol
Agen WAJIB meminta persetujuan eksplisit dari User SEBELUM:
1. Menghapus data dari `brain/` (DSL Library).
2. Menghapus file soal ARC dari `docs/arc-tasks/`.
3. Memodifikasi file konfigurasi utama (`id.json`, `cortex-core.json`).

> [!CAUTION]
> Operasi destruktif SELALU terikat pada `[DESTRUCTIVE_GATE]` (Manual Approval).

## 6. Locomo Signature
Semua script Python yang dihasilkan WAJIB menyertakan header signature sesuai `patterns/locomo-signature.json`.

---

## 7. Reasoning Lifecycle Logging
Agen WAJIB mendokumentasikan setiap siklus penalaran ke dalam `cortex/decisions.jsonl`:
1. **Format:** Satu baris JSON per keputusan/hipotesis.
2. **Struktur Data:** Harus memuat `timestamp`, `task_id`, `phase`, `hypothesis`, `result` (pass/fail), dan `retry_number`.
3. **Prinsip:** Hipotesis yang gagal tetap direkam agar Theorist tidak mengulangi pendekatan yang sama.