# WALIKELAS.ID
## Spesifikasi Arsitektur Capability Kernel & Intent Compiler v1.0
### Mekanisme Peningkatan Kemampuan Layanan Tanpa Perubahan Source Code Fundamental

---

**Versi Dokumen:** 1.0 — *Technical Architecture & Implementation Specification*  
**Tanggal Rilis:** 13 Oktober 2026  
**Status Dokumen:** DIKUNCI sebagai panduan teknis implementasi Dynamic Capability Engine khusus untuk platform WaliKelas.id  
**Dokumen Induk:** 
- [WALIKELAS_MVP_FUNDAMENTAL_PRODUCT_ARCHITECTURE_v1.0.md](file:///home/dev360_03/locomo.id/docs/walikelas.id/WALIKELAS_MVP_FUNDAMENTAL_PRODUCT_ARCHITECTURE_v1.0.md)
- [WALIKELAS_TECHNICAL_ARCHITECTURE_IMPLEMENTATION_PLAN_v1.0.md](file:///home/dev360_03/locomo.id/docs/walikelas.id/WALIKELAS_TECHNICAL_ARCHITECTURE_IMPLEMENTATION_PLAN_v1.0.md)

---

## DAFTAR ISI

1. [Latar Belakang & Filosofi Inti](#1-latar-belakang--filosofi-inti)
2. [Prinsip Invarian & Batas Keamanan Mutlak (Hard Guardrails)](#2-prinsip-invarian--batas-keamanan-mutlak-hard-guardrails)
3. [Arsitektur 4 Lapisan Sistem (The 4-Layer Dynamic Architecture)](#3-arsitektur-4-lapisan-sistem-the-4-layer-dynamic-architecture)
4. [Lapisan 1: AI Intent Compiler Engine](#4-lapisan-1-ai-intent-compiler-engine)
5. [Lapisan 2: Validation Gate & Policy Enforcement](#5-lapisan-2-validation-gate--policy-enforcement)
6. [Lapisan 3: Capability Registry & Version Store](#6-lapisan-3-capability-registry--version-store)
7. [Lapisan 4: Runtime Kernel & Deterministik Interpreters](#7-lapisan-4-runtime-kernel--deterministik-interpreters)
8. [Spesifikasi Server-Driven UI (SDUI) Khusus WaliKelas.id](#8-spesifikasi-server-driven-ui-sdui-khusus-walikelasid)
9. [Spesifikasi Dynamic Schema & Attribute Extension (Data Layer)](#9-spesifikasi-dynamic-schema--attribute-extension-data-layer)
10. [Skenario End-to-End Peningkatan Kemampuan](#10-skenario-end-to-end-peningkatan-kemampuan)
11. [Mitigasi Antipola & Jebakan Keamanan (Inner-Platform Effect)](#11-mitigasi-antipola--jebakan-keamanan-inner-platform-effect)
12. [Panduan Implementasi Teknis & Struktur Modul](#12-panduan-implementasi-teknis--struktur-modul)

---

## 1. Latar Belakang & Filosofi Inti

### 1.1 Masalah Siklus Perangkat Lunak Tradisional
Pada platform EdTech konvensional, setiap perubahan kebutuhan produk memerlukan siklus pengembangan penuh (*code change → build → test → deploy*):
- Guru meminta variasi format pertanyaan literasi baru (misal: menyusun kartu kata acak).
- Kurator pedagogi ingin menambahkan variabel rubrik baru atau menyesuaikan petunjuk bertingkat.
- Orang tua meminta tambahan isian observasi non-akademik di rumah (misal: tingkat kelelahan anak).
- Tim produk ingin menyesuaikan layout kartu laporan perkembangan.

Siklus ini lambat, berisiko memicu regresi pada modul yang tidak terkait, dan menambah beban pemeliharaan kode sumber (*technical debt*).

### 1.2 Solusi Fundamental: Pemisahan Kernel Tetap dan Spesifikasi Deklaratif
Untuk memungkinkan WaliKelas.id berkembang (*scale up*) tanpa terus-menerus memodifikasi source code produksinya, sistem dirancang menggunakan pola:

> **"KODE SEBAGAI PENAFSIR (INTERPRETER KERNEL) YANG STABIL, KEMAMPUAN BISNIS SEBAGAI SPESIFIKASI DATA (DECLARATIVE SPEC) BERVERSI."**

Dalam paradigma ini:
1. **Source Code Tetap (The Kernel):** Berisi mesin eksekusi, penegak keamanan, isolasi hak akses, perutean antarmuka, dan mesin basis data relasional. Kode ini jarang berubah.
2. **Spesifikasi Deklaratif (The Capabilities):** Seluruh bentuk antarmuka interaktif, skema pertanyaan, kriteria rubrik AI, opsi observasi orang tua, dan aturan transisi FSM disimpan sebagai objek data terstruktur (JSON/YAML) yang memiliki versi, validasi skema, dan mekanisme rollback.
3. **Agent AI (The Intent Compiler):** Berfungsi sebagai jembatan cerdas antara instruksi bahasa manusia (dari founder, guru, atau kurator pedagogi) menjadi spesifikasi deklaratif yang valid dan aman bagi kernel.

---

## 2. Prinsip Invarian & Batas Keamanan Mutlak (Hard Guardrails)

Tidak semua hal boleh diubah secara dinamis. Untuk domain pendidikan anak dasar, batas antara hal yang dapat dikonfigurasi melalui data dan hal yang terkunci di kode sumber didefinisikan secara mutlak:

```
┌────────────────────────────────────────────────────────────────────────┐
│                      BATAS PERUBAHAN SISTEM                            │
├───────────────────────────────────┬────────────────────────────────────┤
│ BISA DIUBAH DINAMIS VIA SPEC      │ HARUS TERKUNCI DI KODE SUMBER      │
│ (Tanpa Perubahan Source Code)     │ (Dilarang Diubah Dinamis / AI)     │
├───────────────────────────────────┼────────────────────────────────────┤
│ • Layout UI Misi & Pertanyaan     │ • Isolasi data antar-siswa (RBAC)  │
│ • Konten cerita & audio narasi    │ • Larangan chat bebas pada anak    │
│ • Jenis input (Pilihan/Kartu/Teks)│ • Batas waktu layar (Screen Time)  │
│ • Rubrik & bobot penilaian AI     │ • Keputusan 'Tervalidasi Guru'     │
│ • Tingkatan petunjuk (hints)      │ • Otentikasi & izin wali (Consent) │
│ • Bidang observasi orang tua      │ • Circuit breaker keselamatan      │
│ • Template pesan notifikasi       │ • Mekanisme hashing & enkripsi DB  │
└───────────────────────────────────┴────────────────────────────────────┘
```

### Invarian Keamanan (Security Invariants):
1. **Zero Execution of Untrusted Code:** Kernel DILARANG KERAS mengeksekusi fungsi arbitrer seperti `eval()`, string interpolation menjadi SQL query mentah, atau dynamic script loading dari spesifikasi JSON.
2. **Schema-Constrained Compilation:** Agent AI tidak menghasilkan kode yang dapat dieksekusi, melainkan dokumen JSON yang divalidasi 100% terhadap Zod / JSON Schema yang telah dibakukan.
3. **Audit Trail & Provenance:** Setiap modifikasi spesifikasi wajib mencatat `actor_id`, `prompt_origin`, `compiled_diff`, `approved_by`, dan `schema_version`.
4. **Immediate Reversibility:** Setiap kapabilitas memiliki nomor versi terurut. Jika suatu spesifikasi baru bermasalah saat runtime, sistem dapat melakukan rollback dalam hitungan milidetik ke versi sebelumnya.

---

## 3. Arsitektur 4 Lapisan Sistem (The 4-Layer Dynamic Architecture)

```mermaid
flowchart TD
    subgraph HumanInput [1. Masukan Manusia]
        UserPrompt["Instruksi Bahasa Alami\n(Founder / Guru / Kurator)"]
    end

    subgraph Layer1 [2. Intent Compiler Layer (AI Service)]
        PromptEngine["LLM Intent Parser & Context Binder"]
        SpecGenerator["Declarative JSON Generator"]
    end

    subgraph Layer2 [3. Validation Gate & Policy Enforcement]
        SchemaValidator["Strict Zod / JSON Schema Validator"]
        PolicyChecker["Security & Guardrail Policy Enforcer"]
        DryRunSandbox["Headless Sandbox & Diff Inspector"]
        HumanApproval{"Tingkat Risiko & Persetujuan"}
    end

    subgraph Layer3 [4. Capability Registry & Version Store]
        RegistryDB[("PostgreSQL Capability Store\n(Versioned Specs & Audit Log)")]
        CacheStore[("Redis Capability Cache")]
    end

    subgraph Layer4 [5. Runtime Kernel (Fixed Source Code)]
        SDUIRenderer["Server-Driven UI Renderer (PWA)"]
        FSMInterpreter["FSM & Activity Step Interpreter"]
        RubricEngine["AI Rubric Evaluation Engine"]
        DynamicDataEngine["Schematized Dynamic Entity Engine"]
    end

    UserPrompt --> PromptEngine
    PromptEngine --> SpecGenerator
    SpecGenerator --> SchemaValidator
    SchemaValidator --> PolicyChecker
    PolicyChecker --> DryRunSandbox
    DryRunSandbox --> HumanApproval

    HumanApproval -->|Risiko Rendah: Otomatis / Disetujui| RegistryDB
    HumanApproval -->|Risiko Tinggi: Butuh Konfirmasi Founder| RegistryDB
    HumanApproval -->|Gagal Uji / Ditolak| UserPrompt

    RegistryDB --> CacheStore
    CacheStore --> SDUIRenderer
    CacheStore --> FSMInterpreter
    CacheStore --> RubricEngine
    CacheStore --> DynamicDataEngine
```

---

## 4. Lapisan 1: AI Intent Compiler Engine

### 4.1 Peran dan Batasan Intent Compiler
Intent Compiler adalah servis mandiri yang menerima kebutuhan fungsional dalam bahasa manusia dan mengompilasinya menjadi dokumen konfigurasi yang sesuai dengan ontologi WaliKelas.id.

Intent Compiler **tidak memiliki akses tulis langsung** ke database utama maupun kode sumber. Servis ini hanya menghasilkan output teks berformat JSON terstruktur yang lolos skema.

### 4.2 Tipe Target Kompilasi (Compilation Targets)
Intent Compiler dapat menghasilkan empat jenis target spesifikasi:
1. `TARGET_SDUI_MISSION`: Rancangan antarmuka langkah misi belajar anak.
2. `TARGET_RUBRIC_SPEC`: Kriteria penilaian pedagogis dan kata kunci evaluasi.
3. `TARGET_GUARDIAN_CHECKPOINT`: Form observasi dan panduan aktivitas rumah.
4. `TARGET_DATA_EXTENSION`: Penambahan atribut data fleksibel pada entitas yang diizinkan.

### 4.3 Contoh Kontrak Input-Output Intent Compiler

#### Prompt Masukan (Human Request):
> *"Tolong tambahkan aktivitas untuk materi fabel kelas 3: anak diminta menyusun 3 kalimat acak tentang urutan kejadian burung bangau mencari ikan, berikan 2 petunjuk bantuan, dan minta orang tua memeriksa apakah anak bisa menceritakan kembali inti cerita di rumah."*

#### Output Terkompilasi (Zod-Validated JSON Specification):
```json
{
  "$schema": "https://walikelas.id/schemas/v1/mission-step.json",
  "capability_id": "step-seq-bangau-001",
  "version": "1.0.0",
  "target_competency": "LIT-GR3-SEQ-EVENT",
  "step_type": "INTERACTIVE_SEQUENCE",
  "ui_layout": {
    "view_type": "CARD_ORDERING",
    "prompt_text": "Urutkan kejadian dalam cerita Burung Bangau di bawah ini dengan benar!",
    "items": [
      { "id": "card_1", "text": "Burung Bangau terbang ke tepi telaga yang jernih." },
      { "id": "card_2", "text": "Bangau melihat seekor ikan kecil berenang cepat." },
      { "id": "card_3", "text": "Bangau menangkap ikan itu dengan paruhnya yang panjang." }
    ]
  },
  "hints": [
    { "level": 1, "text": "Ingat, apa yang dilakukan burung bangau sebelum melihat ikan?" },
    { "level": 2, "text": "Pertama bangau terbang ke telaga, lalu melihat ikan, dan terakhir menangkapnya." }
  ],
  "rubric_rules": {
    "correct_sequence": ["card_1", "card_2", "card_3"],
    "partial_credit_rules": [
      { "condition": "card_1_at_index_0", "score": 0.5 }
    ]
  },
  "guardian_offline_task": {
    "activity_title": "Bercerita Kembali Bersama Ibu/Ayah",
    "instructions": "Ajak anak menceritakan kembali apa yang terjadi pada burung bangau tanpa melihat gawai.",
    "observation_fields": [
      {
        "field_id": "retold_fluently",
        "label": "Apakah anak menceritakan urutan cerita dengan lancar?",
        "type": "BOOLEAN"
      }
    ]
  }
}
```

---

## 5. Lapisan 2: Validation Gate & Policy Enforcement

Validation Gate adalah pintu gerbang terisolasi yang memastikan tidak ada spesifikasi yang rusak, korup, atau membahayakan sistem yang dapat masuk ke lingkungan runtime.

### 5.1 Tahapan Validasi (Validation Pipeline)

```
[Kandidat Spec JSON]
        │
        ▼
[1. Zod Schema Matcher] ───────────► Gagal: Tolak (Syntax/Type Error)
        │
        ▼
[2. Policy & Guardrail Checker] ───► Gagal: Tolak (Pelanggaran Invarian)
        │
        ▼
[3. Headless Simulator (Dry-Run)] ─► Gagal: Tolak (Render Crash / Unhandled State)
        │
        ▼
[4. Risk Assessment & Approval] ───► Tingkat Risiko Ditentukan
        │
        ▼
[Siap Didaftarkan ke Registry]
```

### 5.2 Kebijakan Pemeriksaan Keamanan (Policy Check Rules)
Validation Gate menerapkan aturan filter ketat:
1. **Forbidden Key Inspection:** Menolak setiap JSON yang memuat properti mencurigakan seperti `script`, `__proto__`, `constructor`, `sql_raw`, `execute`, atau ekspresi regex berbahaya (*ReDoS*).
2. **Pedagogical Boundary Check:** Memastikan teks instruksi dan cerita tidak memuat kata-kata bernada kekerasan, pornografi, atau konten yang tidak sesuai untuk anak usia 8–9 tahun.
3. **Screen-Time Constraint Check:** Menolak spesifikasi misi yang dirancang dengan estimasi waktu digital melebihi batas invarian (maksimal 15 menit).
4. **Offline Balance Check:** Menolak misi belajar yang tidak menyertakan komponen aktivitas luar-layar (*offline activity*) bersama pendamping.

### 5.3 Matriks Tingkat Risiko & Hak Persetujuan

| Tingkat Risiko | Contoh Operasi | Kebijakan Persetujuan |
|---|---|---|
| **Risiko Rendah (L1)** | Penyesuaian kata petunjuk (*hints*), perbaikan teks cerita, perubahan urutan kartu. | Diterapkan otomatis setelah lolos uji skema dan simulasi sandbox. |
| **Risiko Sedang (L2)** | Pembuatan jenis misi literasi baru, penambahan field observasi orang tua, pembaruan rubrik skor AI. | Memerlukan tinjauan dan konfirmasi 1-klik dari Kurator Pedagogi / Product Owner. |
| **Risiko Tinggi (L3)** | Perubahan aturan transisi FSM, penambahan field database relasional, modifikasi kebijakan notifikasi. | Wajib persetujuan eksplisit Founder/Lead Architect setelah melihat diff simulasi. |

---

## 6. Lapisan 3: Capability Registry & Version Store

Semua spesifikasi yang telah disetujui disimpan secara terpusat pada tabel basis data PostgreSQL dengan versi semantik dan di-cache pada Redis untuk akses berkecepatan tinggi.

### 6.1 Skema Database Registry Kapabilitas

```sql
CREATE TYPE capability_type AS ENUM (
    'SDUI_MISSION_STEP', 
    'RUBRIC_SPEC', 
    'GUARDIAN_CHECKPOINT_SPEC', 
    'REPORT_LAYOUT_SPEC', 
    'DYNAMIC_FIELD_DEF'
);

CREATE TYPE deployment_status AS ENUM (
    'DRAFT', 'SANDBOX_PASSED', 'ACTIVE', 'DEPRECATED', 'ROLLED_BACK'
);

CREATE TABLE capability_specs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    capability_key VARCHAR(100) NOT NULL, -- e.g., 'mission.step.ordering.v1'
    cap_type capability_type NOT NULL,
    version VARCHAR(20) NOT NULL,        -- e.g., '1.0.0', '1.1.0'
    schema_version VARCHAR(20) NOT NULL, -- e.g., 'schema-v1'
    spec_payload JSONB NOT NULL,         -- Dokumen JSON deklaratif murni
    checksum VARCHAR(64) NOT NULL,       -- SHA-256 integritas payload
    status deployment_status DEFAULT 'DRAFT',
    created_by UUID REFERENCES users(id),
    approved_by UUID REFERENCES users(id),
    activation_notes TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    activated_at TIMESTAMPTZ,
    UNIQUE(capability_key, version)
);

CREATE TABLE capability_audit_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    capability_spec_id UUID NOT NULL REFERENCES capability_specs(id),
    action VARCHAR(50) NOT NULL, -- 'PROPOSED', 'VALIDATED', 'ACTIVATED', 'ROLLED_BACK'
    actor_id UUID REFERENCES users(id),
    prompt_context TEXT,
    diff_payload JSONB,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
```

### 6.2 Pola Cache & Strategi Rollback
- **Fast Lookup:** Endpoint API membaca konfigurasi aktif dari Redis: `HGET capabilities:active mission.step.ordering.v1`.
- **Atomic Instant Rollback:** Jika terjadi kegagalan runtime pada versi `1.2.0`, instruksi rollback mengeksekusi satu baris update di database dan memancarkan event invalidasi Redis:
  ```sql
  UPDATE capability_specs SET status = 'ROLLED_BACK' WHERE capability_key = 'mission.step.ordering.v1' AND version = '1.2.0';
  UPDATE capability_specs SET status = 'ACTIVE' WHERE capability_key = 'mission.step.ordering.v1' AND version = '1.1.0';
  ```
  Sistem kembali ke kondisi stabil dalam < 50 milidetik tanpa intervensi build atau deploy ulang server.

---

## 7. Lapisan 4: Runtime Kernel & Deterministik Interpreters

Runtime Kernel dibangun menggunakan kode sumber statis (TypeScript/Fastify & React PWA). Kernel tidak memikirkan logika bisnis spesifik, melainkan hanya bertindak sebagai *mesin penafsir komponen*.

### 7.1 Daftar Modul Kernel Tetap
1. **Server-Driven UI (SDUI) Engine:** Menafsirkan hierarki JSON menjadi pohon komponen visual React pada Student Runner dan Guardian Portal.
2. **Activity FSM Interpreter:** Memandu langkah demi langkah (baca → jawab → petunjuk → jeda → aktivitas offline) berdasarkan aturan state JSON.
3. **Rubric Evaluation Runner:** Memanggil LLM Gateway dengan menyuntikkan skema rubrik dinamis dari database, memvalidasi respons kembali ke Zod contract.
4. **Schematized Dynamic Entity Engine:** Menangani penyimpanan dan pembacaan atribut khusus (*custom fields*) tanpa merusak integritas skema database relasional.

---

## 8. Spesifikasi Server-Driven UI (SDUI) Khusus WaliKelas.id

### 8.1 Komponen Primitif yang Diizinkan (Allowlist UI Catalog)
Untuk menjaga keamanan antarmuka anak dan keseragaman visual, aplikasi klien (PWA) hanya mengenali himpunan komponen primitif berikut:

| Nama Primitif SDUI | Fungsi Pedagogis | Properti yang Didukung |
|---|---|---|
| `StoryReader` | Menampilkan teks narasi bacaan per paragraf | `paragraphs`, `highlight_words`, `fontSize`, `theme` |
| `AudioNarrator` | Kontrol pemutar suara bacaan anak | `audio_url`, `auto_play_allow`, `speed_control` |
| `SingleChoiceQuiz` | Pertanyaan pilihan ganda sederhana | `question`, `options`, `shuffle`, `allow_retry` |
| `WordCardSorter` | Aktivitas interaktif menyusun kartu urutan | `cards`, `max_slots`, `interactive_mode` |
| `ContextWordMatch` | Mencocokkan kata sulit dengan maknanya | `word_pairs`, `drag_enabled` |
| `HintBox` | Kotak petunjuk bertingkat yang dapat dibuka anak | `hints_available`, `reveal_step_by_step` |
| `SessionBreakGate` | Layar jeda dan istirahat mata | `duration_minutes`, `encouragement_text` |
| `OfflineTaskCard` | Kartu panduan orang tua di rumah | `instructions`, `confirm_checkbox`, `notes_prompt` |

### 8.2 Contoh Parser Sederhana pada Sisi Klien (React SDUI Runner)

```typescript
// client/sdui/ComponentRegistry.tsx
import React from 'react';
import { StoryReader } from './components/StoryReader';
import { WordCardSorter } from './components/WordCardSorter';
import { SingleChoiceQuiz } from './components/SingleChoiceQuiz';
import { HintBox } from './components/HintBox';
import { SessionBreakGate } from './components/SessionBreakGate';

const COMPONENT_MAP: Record<string, React.FC<any>> = {
  StoryReader,
  WordCardSorter,
  SingleChoiceQuiz,
  HintBox,
  SessionBreakGate
};

export const RenderSDUINode: React.FC<{ node: { component: string; props: any } }> = ({ node }) => {
  const Component = COMPONENT_MAP[node.component];
  
  if (!Component) {
    // Fail-Safe: Fallback aman jika komponen tidak dikenali
    return (
      <div className="p-4 bg-amber-50 text-amber-800 rounded-lg">
        Langkah kegiatan ini sedang disiapkan. Silakan lanjutkan ke aktivitas membaca berikutnya.
      </div>
    );
  }

  return <Component {...node.props} />;
};
```

---

## 9. Spesifikasi Dynamic Schema & Attribute Extension (Data Layer)

### 9.1 Dilema Perubahan Skema Database
Ketika produk berkembang, sering kali muncul kebutuhan data baru seperti:
- Menambahkan kriteria observasi: *"Apakah anak membaca dengan mengeja atau membaca lancar?"*
- Menambahkan penanda guru: *"Apakah siswa memerlukan rujukan bimbingan membaca intensif?"*

Menjalankan perintah SQL DDL (`ALTER TABLE ... ADD COLUMN`) langsung di lingkungan produksi memiliki risiko fatal: table lock, migrasi gagal di tengah jalan, atau korupsi skema.

### 9.2 Arsitektur Dynamic Attribute Berbasis Schematized JSONB
WaliKelas.id mengadopsi pola **Hybrid Relational-Schematized JSONB**. Tabel utama tetap menggunakan skema relasional terindeks, sementara data tambahan disimpan pada kolom `custom_attributes` yang dikendalikan oleh dokumen `dynamic_field_defs`.

```sql
-- Contoh penambahan kolom fleksibel pada tabel assignments dan guardian_observations
ALTER TABLE guardian_observations 
ADD COLUMN IF NOT EXISTS dynamic_responses JSONB DEFAULT '{}'::jsonb;

-- Metadata Catalog: Mendefinisikan atribut apa saja yang sah diisi
CREATE TABLE dynamic_field_definitions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    target_entity VARCHAR(50) NOT NULL, -- 'GUARDIAN_OBSERVATION', 'TEACHER_REVIEW'
    field_key VARCHAR(50) NOT NULL UNIQUE, -- e.g., 'reading_fluency_indicator'
    label VARCHAR(150) NOT NULL,
    field_type VARCHAR(30) NOT NULL,     -- 'BOOLEAN', 'SCALE_1_5', 'ENUM', 'SHORT_TEXT'
    validation_rules JSONB NOT NULL,     -- e.g., {"min": 1, "max": 5}
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
```

### 9.3 Jalur Khusus Jika Perubahan DDL Memang Diperlukan (Database Migration Path)
Jika ada kebutuhan mendasar yang mengharuskan pembuatan tabel relasional baru atau relasi foreign key baru:
1. **AI Bukan Eksekutor Langsung:** Agent AI menyusun draf migrasi dalam bentuk file deklaratif (misal: Prisma Schema diff atau file SQL migration bernomor).
2. **Sandbox Dry-Run:** Mesin CI/CD secara otomatis menjalankan migrasi tersebut pada database klona (*ephemeral test database*).
3. **Data Loss & Performance Check:** Script pemeriksa memastikan tidak ada perintah destruktif (`DROP COLUMN`, `DROP TABLE`, `TRUNCATE`).
4. **Persetujuan Manusia:** Diff skema ditampilkan secara visual kepada Founder/Lead Architect. Migrasi ke database produksi hanya berjalan setelah persetujuan eksplisit diberikan.

---

## 10. Skenario End-to-End Peningkatan Kemampuan

### Skenario Nyata: Penambahan Format "Misi Temukan Kosakata Baru"

```
[1. Kebutuhan Masuk]
   Guru literasi mengusulkan: "Anak perlu menandai 2 kata sulit dari bacaan,
   lalu memilih arti yang paling sesuai dari pilihan makna."

[2. Intent Compilation]
   AI Agent menyusun dokumen spesifikasi deklaratif:
   - Tipe: SDUI_MISSION_STEP + RUBRIC_SPEC
   - Komponen: ContextWordMatch (sudah terdaftar di Allowlist Catalog)
   - Payload: JSON yang memuat teks paragraf, kata target, dan opsi makna.

[3. Validasi & Pengujian]
   Validation Gate memeriksa dokumen:
   - Skema Zod cocok 100%.
   - Tidak ada skrip eksternal berbahaya.
   - Waktu penyelesaian dipatok 4 menit (di bawah batas waktu layar).
   - Simulator me-render antarmuka tanpa error.

[4. Review & Aktivasi]
   Kurator Pedagogi menerima notifikasi di dashboard:
   - "Ada 1 format aktivitas kosakata baru siap dipublikasikan."
   - Kurator menekan tombol [Setujui & Publikasikan].
   - Status diubah menjadi 'ACTIVE' dan diunggah ke Redis cache.

[5. Eksekusi Runtime]
   Siswa yang membuka misi literasi hari ini langsung melihat aktivitas
   pencocokan kata baru pada ponsel/tablet mereka secara instan.
   -> ZERO DEPLOYMENT, ZERO DOWNTIME, ZERO SOURCE CODE MODIFICATION!
```

---

## 11. Mitigasi Antipola & Jebakan Keamanan (Inner-Platform Effect)

Membangun sistem yang terlalu dinamis sering kali menjebak pengembang ke dalam masalah arsitektur klasik. Berikut adalah batas pertahanan yang ditetapkan untuk WaliKelas.id:

### 1. Menghindari "Inner-Platform Effect"
- **Bahaya:** Menciptakan bahasa pemrograman rumit di dalam JSON (misal membuat ekspresi logika bertingkat `{"if": {"and": [...]}}`).
- **Pencegahan:** Spesifikasi deklaratif hanya boleh berisi *data konfigurasi* dan *pemilihan komponen*. Jika suatu logika memerlukan percabangan algoritma baru yang rumit, hal tersebut wajib diimplementasikan di source code TypeScript kernel melalui sprint reguler.

### 2. Mencegah Prompt Injection Sebagai Celah Eskalasi Hak Akses
- **Bahaya:** Masukan dari pengguna luar memanipulasi Intent Compiler untuk menerbitkan spesifikasi yang merusak hak akses data.
- **Pencegahan:** 
  - Intent Compiler hanya dapat dipicu oleh peran terverifikasi (Founder, Admin, Kurator Terdaftar).
  - Skema output dipaksa (*enforced*) menggunakan Zod parser ketat. Properti yang berada di luar skema akan dibuang secara otomatis (*strip unknown keys*).

### 3. Fail-Safe Rendering Guarantee
- Jika klien menerima spesifikasi JSON yang tidak lengkap atau versi komponen belum didukung oleh versi PWA lokal, aplikasi klien tidak boleh mengalami crash (*white screen*). 
- Klien wajib mengaktifkan `ErrorBoundary` dan menampilkan modul fallback yang aman.

---

## 12. Panduan Implementasi Teknis & Struktur Modul

Modul *Capability Kernel & Intent Compiler* diorganisasikan ke dalam repositori layanan backend sebagai berikut:

```text
apps/api/src/modules/dynamic-engine/
├── compiler/
│   ├── intent-compiler.service.ts      # LLM Prompt Orchestrator & Context Provider
│   ├── prompts/
│   │   ├── mission-compiler.prompt.ts  # Prompt sistem khusus translasi misi
│   │   └── rubric-compiler.prompt.ts   # Prompt penyusunan kriteria penilaian
│   └── dto/
│       └── compile-intent.dto.ts
│
├── validation-gate/
│   ├── schemas/
│   │   ├── sdui-step.schema.ts         # Zod schema antarmuka langkah misi
│   │   ├── rubric-spec.schema.ts       # Zod schema kriteria rubrik
│   │   └── dynamic-field.schema.ts     # Zod schema atribut data kustom
│   ├── policy-checker.service.ts       # Pemeriksa kata terlarang & batas layar
│   └── sandbox-runner.service.ts       # Headless dry-run simulator
│
├── registry/
│   ├── capability-registry.service.ts  # Manajemen CRUD spesifikasi & SemVer
│   ├── capability-cache.service.ts     # Sinkronisasi state Redis
│   └── audit-logger.service.ts         # Pencatat jejak perubahan dan diff
│
└── runtime-kernel/
    ├── sdui-generator.service.ts       # Penghasil payload antarmuka untuk klien
    ├── rubric-evaluator.service.ts     # Pengevaluasi jawaban berbasis spesifikasi
    └── dynamic-entity.service.ts       # Parser atribut schematized JSONB
```

---

## 13. Kesimpulan & Manfaat bagi Ekosistem WaliKelas.id

Dengan menerapkan spesifikasi arsitektur ini:
1. **Kecepatan Iterasi Produk Meningkat:** Tim pedagogi dan guru dapat menambahkan puluhan variasi misi membaca naratif, cerita fabel baru, dan petunjuk belajar dalam hitungan menit tanpa menunggu antrean developer.
2. **Kestabilan dan Keamanan Terjaga:** Kernel perangkat lunak tetap terlindungi dari bug regresi dan serangan injeksi karena kode inti bersifat statis dan terlindungi pengujian otomatis.
3. **Kepatuhan Perlindungan Anak Tetap Mutlak:** Batasan screen time, isolasi data pribadi, dan ketiadaan chatbot bebas tetap terkunci secara permanen di tingkat fondasi kode sumber.
4. **Kesiapan Menghadapi Review Investor:** Membuktikan kepada reviewer akselerator (YC/Techstars) bahwa platform memiliki arsitektur yang sangat terukur (*highly scalable, extensible architecture*) dengan biaya pemeliharaan operasional yang rendah.
