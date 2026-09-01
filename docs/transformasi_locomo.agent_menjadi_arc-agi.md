# LOCOMO ARC-AGI: LOGIC REASONING OS (v10.2.0-arc-agi)
## Dokumen Panduan & Spesifikasi Arsitektur Kognitif untuk Pemecahan Masalah ARC-AGI

Dokumen ini disusun untuk memberikan pemahaman kontekstual yang mendalam bagi pengembang manusia (The Orchestrator) maupun agen AI (Swarm Roles) tentang bagaimana **Locomo ARC-AGI** diorganisasikan, dijalankan, dan dikontrol.

---

## 1. PENGANTAR: APA ITU LOCOMO ARC-AGI?
**Locomo ARC-AGI** adalah hasil pivot arsitektur dari *Locomo Agent OS V2.0* (yang semula dirancang sebagai software development engine) menjadi **Logic Reasoning Factory**. Tujuan tunggal dari sistem operasi agen otonom ini adalah untuk memecahkan teka-teki logika abstrak pada benchmark **ARC-AGI** (Abstraction and Reasoning Corpus).

### Tantangan ARC-AGI
Teka-teki ARC-AGI berupa transformasi grid 2D visual (direpresentasikan dalam format matriks angka `0-9` di mana setiap angka mewakili warna tertentu). Tugas sistem adalah mendeteksi pola transformasi logis dari 2-4 contoh pasangan input-output (*demonstrations*), kemudian menerapkan aturan deduktif tersebut pada grid *test input* untuk menghasilkan grid *test output* yang 100% tepat.

---

## 2. PILAR UTAMA ARSITEKTUR KOGNITIF (5-LAYER)
Sistem mempertahankan struktur modular 5-lapis khas Locomo OS, namun dengan redefinisi fungsi kognitif penuh:

```
locomo.arc-agi/
├── SKILL.md                 # Protokol CLI, Onboarding, & Shutdown (v10.2.0)
├── skills.json              # Pemetaan Peran ARC, Versi, & Dependensi
├── id.json                  # [TRINITY 1] Identitas, Batasan Grid, & Guardrails ARC
├── cortex/
│   ├── cortex-core.json     # [TRINITY 2] State CCC (PERCEPTION → HYPOTHESIS → IMPL → VERIF)
│   ├── decisions.jsonl      # Log Hipotesis (Lulus/Gagal) per teka-teki
│   └── conflicts.jsonl      
├── docs/
│   ├── task_loader.py       # Cartridge: Dynamic Token Routing & Parser Grid ARC
│   ├── arc-tasks/           # Folder penyimpanan soal-soal ARC (JSON)
│   └── fplans/              # [TRINITY 3] Puzzle Flow Plans (FPLAN-xxx.md)
├── brain/
│   └── cognitive_memories   # DSL Primitive Library (pgvector database)
├── scratch/                 # Python Sandbox (Docker Container)
│   ├── Dockerfile           # numpy + scipy.ndimage + networkx
│   └── matrix_sandbox.py    # Runner eksekusi skrip matriks & timeout
└── skills/
    ├── kernel.md            # Kernel: Aturan main Hypothetico-Deductive Loop
    ├── orchestrator-workflow.md  # State Machine: 6 langkah penalaran ARC
    ├── roles/               # Swarm Roles (Perceptor, Theorist, Coder, Validator, Gatekeeper)
    ├── hooks/
    │   └── arc_eval.sh      # Skrip Evaluasi Pasca-Eksekusi (Security scan & Sandbox run)
    └── roms/                # Rules of Memory ( HITL, Coding standards, Env)
```

---

## 3. PENJELASAN KOMPONEN DAN FUNGSI DETAIL

### 3.1 Berkas Keadaan Memori (The Trinity Pattern — ARC Edition)
Pola memori Trinity diselaraskan untuk mengamankan retensi logika dan mencegah token overflow:
1. **`id.json` (Trinity 1 — Identitas & Guardrails)**: Berisi batasan ukuran grid (maks 30x30), palet warna, dependensi sandbox yang diizinkan (numpy, scipy, networkx), batas maksimal percobaan (5 kali), dan aturan Asymmetric Swarm Routing.
2. **`cortex/cortex-core.json` (Trinity 2 — Status & Sejarah Aktif)**: Memantau status daur hidup pemecahan teka-teki saat ini (fase aktif, nomor percobaan/retry, daftar hipotesis yang sudah dicoba).
3. **`docs/fplans/FPLAN-xxx.md` (Trinity 3 — Puzzle Flow Plans)**: Rencana langkah kerja bertahap per teka-teki. Mengatur langkah dari analisis grid awal, formulasi hipotesis, penulisan script, eksekusi sandbox, hingga hasil perbandingan array.

### 3.2 Cartridge Layer & Dynamic Token Routing
Pengurangan token dan pencegahan pencemaran *Context Window* ditangani secara cerdas di dalam [docs/task_loader.py](file:///home/dev360_03/locomo.id/locomo.arc-agi/docs/task_loader.py):
* **Grid ≤ 10x10**: Grid diloloskan secara utuh sebagai array teks agar LLM dapat memetakan pola spasial secara langsung.
* **Grid > 10x10**: Pra-pemrosesan mengekstrak representasi semantik (posisi objek, ukuran bounding box, jumlah piksel, warna, keterkaitan). Grid penuh hanya dibaca langsung oleh Sandbox Python saat eksekusi kode dijalankan.

### 3.3 Python Sandbox Execution Engine (Isolasi scratch/)
Eksekusi kode buatan Coder diputar di dalam Docker container yang diisolasi di [scratch/Dockerfile](file:///home/dev360_03/locomo.id/locomo.arc-agi/scratch/Dockerfile) dengan dependensi:
* **`numpy`**: Operasi array, manipulasi dimensi, slicing, rotasi/pencerminan koordinat.
* **`scipy.ndimage`**: Segmentasi objek warna terhubung (`label()`), deteksi tepi, flood fill.
* **`networkx`**: Menyelesaikan teka-teki pencarian jalur (*pathfinding*) antar objek.
* **`matrix_sandbox.py`**: Mengontrol jalannya eksekusi script dengan timeout 30 detik dan membandingkan hasil secara *element-wise* dengan target.

### 3.4 Asymmetric Swarm Routing (Model Allocation)
Untuk mencegah pembengkakan biaya API tanpa menurunkan kemampuan penalaran tingkat tinggi:
* **Theorist / Architect (🔴 Premium Model — Gemini Pro)**: Hanya dipanggil di Fase 1 (Perencanaan Logika) dan eskalasi Deadlock (Gatekeeper). Tugasnya berfokus pada penalaran abstrak: mendeteksi aturan, merumuskan hipotesis logis, dan menulis pseudo-code matematika diskrit.
* **Perceptor, Coder, Validator, Fixer (🔵 Routine Model — Gemini 3.5 Flash)**: Menangani tugas-tugas bervolume tinggi, deterministik, dan mekanis seperti menerjemahkan pseudo-code ke Python numpy, memparsing log error, dan membenahi error kompilasi sederhana.

---

## 4. DAUR HIDUP PENALARAN (HYPOTHETICO-DEDUCTIVE LOOP)

Setiap teka-teki ARC-AGI yang masuk diproses melalui 6 langkah utama di dalam [skills/orchestrator-workflow.md](file:///home/dev360_03/locomo.id/locomo.arc-agi/skills/orchestrator-workflow.md):

```
+-------------------------------------------------------------+
|   STEP 0: KERNEL BOOT (Load task, verify Docker sandbox)    |
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
|   STEP 1: PERCEPTOR (Load grid & generate semantic description) |
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
|   STEP 2: THEORIST (Formulate hypothesis & pseudo-code)     |
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
|   STEP 3: DSL CODER (Convert pseudo-code to Python script)  |
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
|   STEP 4: VALIDATOR (Execute sandbox & compare element-wise)|
+-------------------------------------------------------------+
                              |
        +---------------------+---------------------+
        | (FAIL Logic / Retry < 5)                  | (PASS 100%)
        v                                           v
+-------------------------------------+   +-----------------------------------+
| STEP 5B: RE-HYPOTHESIS LOOP         |   | STEP 6: STORE DSL & FINALIZE      |
| Theorist formulates new hypothesis  |   | Save primitive function to pgvector|
+-------------------------------------+   | Generate test output prediction   |
        |                                 +-----------------------------------+
        v
    (If Retry >= 5)
        |
        v
+-------------------------------------+
| STEP 5C: GATEKEEPER CIRCUIT BREAKER |
| Forced context reset & switch       |
| heuristic / Escalate to human       |
+-------------------------------------+
```

---

## 5. INSTRUKSI UNTUK AGEN AI (SWARM AGENTS)
Ketika Anda diaktifkan dalam lingkungan **Locomo ARC-AGI**:
1. **Patuhi Peran Anda**: Rujuk berkas spesifik Anda di [skills/roles/](file:///home/dev360_03/locomo.id/locomo.arc-agi/skills/roles/). Jangan mengambil alih tugas peran lain (misal: Coder dilarang merubah logika tanpa instruksi Theorist; Theorist dilarang menulis kode).
2. **Patuhi Batasan Keamanan (Guardrails)**: Jangan pernah menghasilkan kode yang mengeksekusi modul sistem seperti `os`, `subprocess`, `eval`, atau `exec`. Kode Anda akan diblokir oleh `arc_eval.sh` sebelum dieksekusi.
3. **Optimalkan Konteks**: Jangan meminta grid penuh jika ukuran grid lebih besar dari 10x10. Andalkan deskripsi semantik terstruktur dari Perceptor dan Task Loader.
4. **Gunakan Memori (pgvector)**: Sebelum mendesain dari nol, selalu tanyakan perpustakaan primitif `brain/` untuk mempercepat pemecahan masalah dengan menggunakan fungsi yang sudah terbukti sukses pada teka-teki sebelumnya.