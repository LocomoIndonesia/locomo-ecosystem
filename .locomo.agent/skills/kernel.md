# Locomo OS Kernel (Master State Machine)
**Version:** 10.2.0-master
**Core Strategy:** Central Cognitive Core (CCC) Orchestration — 5-Layer Hybrid Intelligence V2.0 with Trinity Pattern Memory, Model Routing, and Evals & Hooks.
**Project Identity:** Baca `id.json` (Trinity 1) dan `docs/project-context.json` untuk identitas, tech stack, dan guardrails aktif.

---

## 1. System Boot & Perception Fallback Protocol

### 1.1 PERCEPTION AUGMENTATION & GRACEFUL DEGRADATION
Sebelum mengeksekusi instruksi APA PUN, agen WAJIB memeriksa status Cognitive Driver via API (http://localhost:8000). 
- **[DRIVERLESS MODE]:** JIKA driver gagal merespons, beralihlah ke mode fallback manual. Jangan memblokir alur kerja utama. Gunakan metode pencarian file native.

### 1.2 Local Context Boot Priority (Trinity Pattern Memory)
Agen WAJIB memprioritaskan kesadaran penuh terhadap kondisi proyek dengan membaca file keadaan berikut secara berurutan:
1. **`id.json` (Trinity 1 - Identitas & Guardrails)**: Membaca aturan arsitektur teknis inti dan guardrails mutlak proyek.
2. **`cortex/cortex-core.json` (Trinity 2 - Status & Sejarah Aktif)**: Memahami fase aktif berjalan, prioritas utama, dan status *health*.
3. **`docs/fplans/` (Trinity 3 - Flow Plans)**: Memuat berkas rencana alur kerja langkah-demi-langkah (Misal: `FPLAN-001.md`) untuk menentukan aksi selanjutnya.
4. **`brain/cognitive_memories`**: Memuat *vector embeddings* relevan via pgvector.
5. **`scratch/`**: Sandbox utama untuk pengujian sementara dan kontainer scaffolding.

### 1.3 Lazy Loading Protocol (Context Window Management)
> [!CAUTION]
> **[STRICT]:** Untuk mencegah Token Overflow, agen DILARANG memuat isi penuh dari `docs/domain-knowledge.json`, `docs/feature-map.json`, atau file berukuran besar lainnya secara keseluruhan. Agen WAJIB menggunakan metode *Progressive Disclosure* lewat tool file-search/grep untuk mengekstrak hanya informasi yang relevan dengan tugas saat ini.

**Batas baca per fase (Tiket 4.4):** THINK = indeks/daftar isi + hasil grep + maksimal 1 file target (bagian head); SYNC = hanya section relevan dari file yang ditunjuk THINK (file di luar scope butuh eskalasi lajur); ACT = file target yang diubah + registry perintah, tanpa baca ulang file yang sudah dibaca fase ini; REFLECT = diff/ringkasan perubahan, bukan baca ulang repo. **Aturan pointer:** antar-peran mengoper path + referensi baris, bukan tempelan isi file.

## 2. Definition of Done (DoD)
Sebuah tugas hanya dinyatakan selesai (STATE: DONE) jika memenuhi kriteria absolut:
1. Semua log Audit dan Keamanan telah bersih dan di-approve (Autonomous/HITL).
2. Semua Automated Tests dan Hooks evaluasi berstatus PASS.
3. Kode 100% mematuhi aturan Digital Signature, guardrails spesifik di `id.json`, dan standar arsitektur.

---

## 2.5 Task-Value Gating: Tiga Lajur Eksekusi (Tiket 4.3)
Sebelum Phase 1 berjalan penuh, orkestrator WAJIB mengklasifikasikan TUGAS ke satu lajur. Lajur salah = pemborosan token (multi-agent membakar ±15x lipat; belanjakan hanya bila nilai tugas membenarkannya).

| Lajur | Sinyal | Alur | Gerbang | Budget konteks |
|---|---|---|---|---|
| **VIBE** (trivial) | Baca file, edit kecil satu file, tanya-jawab, tanpa DB/auth/deploy | Kerja langsung, tanpa peran penuh | Hanya `[DESTRUCTIVE_GATE]` bila terpicu | Minimal: tanpa load persona peran |
| **FACTORY** (standar) | Fitur/modul baru, multi-file, integrasi API | 4 makro-fase penuh + peran | `[START_APPROVAL]` bila berdampak masif, `[STRICT_VALIDATION]`, `[MERGE_APPROVAL]` | Penuh + progressive disclosure |
| **CRITICAL** | Auth/otorisasi, query mentah, bulk upload/file parsing, migrasi, operasi destruktif | 4 fase + Security Auditor wajib | Semua gerbang + HITL eksplisit | Penuh + audit |

Aturan:
1. Klasifikasi TERCATAT di entri task cortex (`lane` field), header FPLAN, dan siklus telemetry. Tanpa catatan lajur = pelanggaran prosedur.
2. VIBE otomatis untuk sinyal trivial; user boleh memaksa lajur mana pun secara eksplisit; sinyal CRITICAL selalu menang atas klasifikasi lain.
3. Eskalasi lajur (VIBE→FACTORY→CRITICAL) kapan pun sinyal baru muncul; tidak ada de-eskalasi otomatis.
4. `speed_mode` (strategies.json) TIDAK BOLEH melewati pemetaan lajur ini. Otoritas aktivasi (Tiket 3.2): user eksplisit boleh di lajur mana pun; orkestrator otomatis hanya di VIBE; FACTORY/CRITICAL butuh persetujuan user; setiap aktivasi tercatat di telemetry.
5. **Disclosure keputusan otonom (Tiket 4.1):** setiap keputusan yang diambil agen tanpa persetujuan eksplisit — klasifikasi lajur, aktivasi `speed_mode` otomatis (VIBE), auto-approval gerbang, pilihan strategi — WAJIB dicatat di siklus telemetry (`decisions: [{decision, by, reason, at}]`) DAN ditampilkan ke user dalam ringkasan akhir siklus sebelum `[MERGE_APPROVAL]`. Otonomi yang tak tercatat = pelanggaran prosedur; user berhak membatalkan hasil siklus yang disembunyikan.

## 3. Orchestration Loop (The 4 Macro-Phases)

### [PHASE 1: THINK] (Perception & Planning)
- **Kondisi:** Awal siklus kerja.
- **Action:** Kernel membedah instruksi, memanggil Architect untuk merancang struktur data dan batasan sistem. Hasilkan `runtime/current-spec.md`.
- **Gate:** `[START_APPROVAL]` (Wajib HITL untuk persetujuan blueprint arsitektur & Flow Plan).

**[CCC_AUTHORIZATION_GATE]**: SEBELUM agen berpindah ke Phase 2 (SYNC) atau Phase 3 (ACT), agen WAJIB membaca `cortex-core.json`. 
- JIKA `"system_health": "critical"`, SELURUH EKSEKUSI DIHENTIKAN seketika dan masuk mode DEGRADED.
- JIKA sehat, agen HANYA diizinkan mengeksekusi task dengan skor tertinggi di array `"priorities"`.

### [PHASE 2: SYNC] (Agentic Swarm Reasoning)
- **Kondisi:** Perancangan solusi teknis terperinci.
- **Action:** DBA, UIUX, dan Integrator bekerja paralel. Integrator memastikan kontrak API/Data antar komponen sinkron.
- **Peran yang boleh aktif diatur `skills/roles/_matrix.md` (Tiket 3.1) — di luar matriks = pelanggaran.
- **Gate:** Tidak ada HITL (Fully Autonomous).

### [PHASE 3: ACT] (Execution, Hooks & Self-Healing)
- **Kondisi:** Penulisan dan Validasi Kode.
- **Action:** Coder menulis kode dengan standar Transactional Safety & Strict Typing. Setelah penulisan kode, sistem secara otomatis memicu skrip evaluasi deterministik yang berada di `skills/hooks/` (*Post-Execution Evals*). Auditor (Security/Performance) dan Tester berjalan otomatis. Jika Test atau Hook Gagal, sistem melakukan auto-repair.
- **Gate:** `[STRICT_VALIDATION]` (Hanya boleh lanjut jika Test dan Evals PASS).
- **[CIRCUIT_BREAKER_PROTOCOL]:** Batas maksimal Auto-Repair (Loop Tester/Hook -> Fixer) adalah 3 KALI (MAX_RETRIES = 3). Jika error kompilasi/test berulang di titik yang sama lebih dari 3 kali, HENTIKAN PROSES. Ubah state menjadi DEGRADED dan trigger `[ESCALATION_GATE]` untuk intervensi manusia (The Orchestrator).

### [PHASE 4: REFLECT] (Learning & Merging)
- **Kondisi:** Akhir siklus kerja.
- **Action:** Synthesizer mengunci hasil, menulis ke `learning-log.jsonl`, dan menyiapkan merge.
- **Learning Loop (Tiket 4.5):** setiap siklus FACTORY/CRITICAL yang sukses WAJIB menambah satu baris JSONL ke `skills/runtime/learning-log.jsonl` dengan skema `{ts, lane, cycle, trigger, change, why, reusable}` — `trigger` = pola situasi yang bila muncul lagi harus memicu pelajaran ini; `reusable` = ya/tidak + syarat. Siklus VIBE: tulis hanya bila menemukan pola baru (boleh kosong, jangan mengarang).
- **Read-back (Tiket 4.5):** di awal THINK berikutnya, cari (`grep`) `learning-log.jsonl` dengan kata kunci tugas; pelajaran yang cocok WAJIB dirujuk di rencana. Log bukan arsip pajangan.
- **Kompaksi (Tiket 4.5):** bila log >200 baris, ringkas pelajaran basi/duplikat menjadi <50 baris pola abadi (otomasi bulanan atau perintah user); arsip mentah ke `skills/runtime/archive/`.
- **Gate:** `[MERGE_APPROVAL]` (Final HITL). Ringkasan merge WAJIB memuat blok disclosure 4.1 (keputusan otonom siklus ini + file yang diubah).

---

## 4. Destructive Operations Protocol
Agen WAJIB meminta persetujuan eksplisit dari User SEBELUM:
1. Mengeksekusi `DELETE`, `TRUNCATE`, `DROP` di database.
2. Menghapus file di workspace.
3. Memodifikasi file konfigurasi utama.

> [!CAUTION]
> Operasi destruktif SELALU terikat pada `[DESTRUCTIVE_GATE]` (Manual Approval).

## 5. Locomo Signature
Lihat format kanonis di `skills/patterns/locomo-signature.json` dan Amandemen A3 (`docs/ARCHITECTURE.md`): `File generated by <Tool>, and Orchestrated by Ronald Poetra (Zaid) last updated at <timestamp ISO>`, dibungkus komentar sesuai bahasa. Aturan lama `CREATED BY ... LOCOMO SIGNATURE` DINYATAKAN TIDAK BERLAKU.

---

## 6. Development Lifecycle Logging (Changelogs)
Agen WAJIB mendokumentasikan setiap siklus pengembangan yang signifikan ke dalam folder `docs/changelogs/`.
1. **Format:** Wajib menggunakan format `.json` untuk pengindeksan cepat oleh OS.
2. **Penamaan File:** Gunakan format `YYYY-MM-DD-feature-name.json`.
3. **Struktur Data:** Harus memuat `timestamp`, `title`, dan `content` (markdown format di dalam string JSON).
4. **Prinsip:** Dilarang menumpuk semua log dalam satu file besar. Gunakan pendekatan inkremental per-tugas atau per-hari.