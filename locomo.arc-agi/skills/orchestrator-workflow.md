# LOCOMO ARC-AGI ORCHESTRATOR ENGINE
**File Name:** `orchestrator-workflow.md`
**Role:** Master Controller / State Machine — ARC Puzzle Solver
**Global Constraint:** Sebelum memulai transisi *state* manapun, Anda WAJIB me-load `id.json` (guardrails, model routing, grid constraints) dan `cortex/cortex-core.json` (fase aktif, retry count). Instruksi pada kelompok identitas ini bersifat mutlak.

## DEFINITION OF DONE (DoD) — ARC Edition
Sebuah teka-teki hanya dinyatakan selesai (STATE: SOLVED) jika:
1. Script Python menghasilkan output identik 100% dengan target pada SEMUA contoh (demonstrations).
2. Script berhasil dieksekusi pada test input tanpa error.
3. Fungsi primitif valid disimpan ke `brain/` (pgvector DSL Library).

---

## ⚙️ THE STATE MACHINE WORKFLOW (LOGIC REASONING FACTORY)

**INPUT:** Soal ARC-AGI (file JSON dari `docs/arc-tasks/`)
**STATE:** `INIT`

### STEP 0 — THE KERNEL BOOT (Initialization)
* **Action:** Baca `id.json` (guardrails ARC, sandbox config, model routing) dan `cortex/cortex-core.json` (fase aktif). Verifikasi bahwa sandbox Docker (numpy+scipy+networkx) tersedia.
* **Transition:** Pahami batasan grid dan model routing. Pindah ke STEP 1.

### STEP 1 — THE PERCEPTOR (Grid Analysis & Semantic Translation)
* **[CONTEXT SWITCH]:** Load `skills/roles/perceptor.md`.
* **Model:** 🔵 Routine LLM (Gemini 3.5 Flash)
* **Action:** Task Loader (`docs/task_loader.py`) memuat soal ARC. Jika grid ≤ 10x10, raw array dikirim utuh. Jika grid > 10x10, metadata semantik diekstrak. Perceptor menganalisis SEMUA pasangan input-output contoh dan menghasilkan deskripsi semantik terstruktur:
  - Objek apa yang ada (warna, ukuran, posisi)?
  - Pola apa yang berubah antara input dan output?
  - Relasi spasial apa yang terlihat (adjacency, containment, symmetry)?
* **Output:** Deskripsi semantik terstruktur (teks).
* **Transition:** **STATE → `PERCEIVED`**. Lanjut ke STEP 2.

### STEP 2 — THE THEORIST (Hypothesis Formulation)
* **[CONTEXT SWITCH]:** Load `skills/roles/theorist.md`.
* **Model:** 🔴 Premium LLM (Gemini Pro)
* **Action:** Mengambil deskripsi semantik dari Perceptor. Sebelum merumuskan hipotesis baru, WAJIB query `brain/` untuk memeriksa fungsi primitif yang relevan. Memformulasikan aturan transformasi ke dalam **pseudo-code** atau **instruksi matematika diskrit**. DILARANG menulis kode Python implementasi.
* **Output:** Pseudo-code terstruktur dan daftar langkah transformasi.
* **Gate:** Hipotesis direkam ke `cortex/decisions.jsonl`.
* **Transition:** **STATE → `HYPOTHESIZED`**. Lanjut ke STEP 3.

### STEP 3 — THE DSL CODER (Python Implementation)
* **[CONTEXT SWITCH]:** Load `skills/roles/coder.md`.
* **Model:** 🔵 Routine LLM (Gemini 3.5 Flash)
* **Action:** Mengonversi pseudo-code dari Theorist menjadi script Python menggunakan numpy, scipy.ndimage, dan/atau networkx. Script harus:
  - Menerima input sebagai list-of-lists (matriks 2D).
  - Mengembalikan output sebagai list-of-lists integer (0-9).
  - TIDAK menggunakan `eval()`, `exec()`, `os.system()`, atau `subprocess`.
  - Menyertakan header Locomo Signature.
* **Output:** File Python (`.py`) siap eksekusi.
* **Transition:** **STATE → `CODE_GENERATED`**. Lanjut ke STEP 4.

### STEP 4 — THE VALIDATOR (Sandbox Execution & Comparison)
* **[CONTEXT SWITCH]:** Load `skills/roles/validator.md`.
* **Model:** 🔵 Routine LLM (Gemini 3.5 Flash)
* **Action:** Mengirim script Python ke `scratch/matrix_sandbox.py` untuk dieksekusi di dalam kontainer Docker terisolasi. Sandbox mengeksekusi script terhadap SEMUA contoh input dan membandingkan hasilnya dengan target output secara element-wise.
* **Routing:**
  - **JIKA 100% match pada SEMUA contoh:** → STEP 6 (Store & Solve).
  - **JIKA error syntax/runtime:** → STEP 5A (Fixer — perbaikan kode ringan).
  - **JIKA error logis (output salah tapi tidak crash):** → STEP 5B (Re-Hypothesize — kembali ke Theorist).

### STEP 5A — THE FIXER (Syntax & Runtime Repair)
* **Model:** 🔵 Routine LLM (Gemini 3.5 Flash)
* **Action:** Membaca log error dari sandbox. Memperbaiki masalah syntax, index out of bounds, type mismatch, atau import error secara mikroskopikal tanpa mengubah logika transformasi.
* **Routing:** KEMBALI ke **STEP 4 (VALIDATOR)** untuk re-test. Jika gagal repetitif, increment `retry_count`.

### STEP 5B — THE RE-HYPOTHESIS LOOP (Theorist Re-Invocation)
* **Model:** 🔴 Premium LLM (Gemini Pro)
* **Action:** Output salah secara logis. Theorist menerima log perbandingan (expected vs actual) dan hipotesis sebelumnya dari `cortex/decisions.jsonl`. WAJIB mencoba pendekatan berbeda.
* **Routing:** KEMBALI ke **STEP 3 (DSL CODER)** → **STEP 4 (VALIDATOR)**.
* **[CIRCUIT_BREAKER_PROTOCOL]:** Setiap kali loop ini dieksekusi, `retry_count` di `cortex-core.json` di-increment. Batas maksimal: **5 kali**.

### STEP 5C — THE GATEKEEPER (Circuit Breaker & Context Reset)
* **Kondisi:** `retry_count >= max_retries (5)`.
* **Model:** 🔵 Routine LLM (awal) → 🔴 Premium LLM (jika re-invoke Theorist)
* **Action:**
  1. Memaksa `context_reset_and_heuristic_switch` — menghapus hipotesis lama dari konteks aktif.
  2. Mencoba pendekatan heuristik alternatif (misal: beralih dari pendekatan geometris ke topologis, atau dari rule-based ke template-matching).
  3. JIKA setelah reset masih gagal: trigger `[ESCALATION_GATE]` — minta intervensi Orchestrator `[HITL_APPROVAL_REQUIRED]`.

### STEP 6 — STORE DSL PRIMITIVE & FINALIZE
* **Action:**
  1. Eksekusi script pada test input → hasilkan prediksi output.
  2. Simpan fungsi primitif yang valid ke `brain/` (pgvector DSL Library) dengan metadata: `task_id`, `transformation_type`, `confidence_score`.
  3. Update `cortex-core.json`: reset `retry_count`, rekam status `SOLVED`.
  4. Rekam log ke `docs/changelogs/` dan `cortex/decisions.jsonl`.
  5. Deklarasikan: `[TASK_SOLVED]`.