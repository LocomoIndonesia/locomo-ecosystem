# Grand Design Arsitektur: Locomo Agent OS

**Versi Kernel:** 9.2.0-enterprise  
**Fokus Utama:** Universal AI Orchestration System & AI Software Factory  
**Penyusun:** AntiGravity AI (Berdasarkan Analisis & Infrastruktur Eksisting)  

---

> [!NOTE]
> Dokumen ini memetakan seluruh kemampuan, struktur, dan protokol dasar dari **Locomo Agent OS** saat ini. Dokumen ini dirancang untuk menjadi fondasi (*blueprint*) dalam melakukan penyesuaian (upgrade) menuju konsep **SDLC Era AI (Google AI Playbook)**.

---

## 1. Visi & Filosofi Sistem

**Locomo Agent OS** (sebelumnya Central Cognitive Core / CCC) adalah sebuah sistem operasi manajemen pemikiran Agen AI yang bertindak sebagai *Master Controller / State Machine* untuk mengelola siklus hidup pengembangan perangkat lunak (SDLC) secara otonom dan kolaboratif (AI Software Factory). Sistem ini beroperasi pada ekosistem **Locomo.id Federated Ecosystem** yang terdiri dari multi-proyek berskala *enterprise*.

Filosofi utama Locomo Agent OS:
1. **Docker-Centric Runtime**: Seluruh eksekusi berjalan 100% di dalam kontainer Docker. Tidak ada eksekusi *native* di *host*.
2. **Multi-Project Isolation**: Pemisahan konteks kognitif yang tegas antar sub-proyek dalam satu *workspace* terpadu.
3. **Agentic Swarm Reasoning**: Orkestrasi multi-agen (Architect, Coder, Reviewer, Security Auditor, dll) dengan sistem *State Machine* yang terukur.
4. **Local RAG & Global Inference**: Pemanfaatan *Graphify* (AST Scanner) dan *pgvector* untuk pemetaan memori kontekstual secara lokal, dipadukan dengan mesin inferensi eksternal.

---

## 2. Arsitektur Kognitif 5-Lapis (The 5-Layer Architecture)

Sistem Locomo Agent OS terstruktur dalam 5 lapisan (*layer*) hierarkis di dalam direktori `.antigravity/`:

1. **`skills/` (The Engine / Immutable)**: 
   Mesin orkestrasi universal yang agnostik terhadap *persona*. Berisi aturan *State Machine* (`orchestrator-workflow.md`), protokol utama (`kernel.md`), definisi *roles* (agen), dan *Read-Only Memories (ROMs)* terkait *tech-stack* dan lingkungan (*environment*).
2. **`docs/` (The Cartridge / Mutable)**:
   Wadah identitas spesifik *workspace* dan sub-proyek. Memuat `project-context.json` (informasi bisnis/stakeholder), pemetaan fitur, dan pengetahuan domain.
3. **`brain/` (Project Brain / Persistent)**:
   Lokasi memori aktif dan persisten sistem yang dihasilkan selama eksekusi berjalan (seperti *vector embeddings*, rekam jejak keputusan, dsb).
4. **`scratch/` (Project Sandbox / Temporary)**:
   Ruang isolasi utama untuk *file* sementara, pengujian, dan draf kode sebelum disahkan.
5. **`cortex/` (Central Cognitive Core / CCC)**:
   Pusat kesadaran eksekutif agen. Menyimpan data deterministik (*state*) melalui file `cortex-core.json`, yang mendikte keputusan orkestrasi dan perpindahan fase. Mengacu pada struktur ini, Agen **wajib mematuhi Authorization Gate** sebelum berpindah fase operasional.

---

## 3. Protokol Sistem & Booting Sequence

Locomo Agent OS diikat oleh protokol baku yang wajib dieksekusi secara otomatis saat inisialisasi sesi (*Onboarding*).

### A. System Onboarding (`jalankan locomo agent os`)
1. **Auto-Detection**: Membaca parameter `"env_status"` pada `cortex-core.json`.
2. **Host Check**: Pengecekan status *Docker Engine* dan ketersediaan *Volume* secara dinamis.
3. **Driver Spin Up**: Menginisiasi `docker compose up -d` di folder `.antigravity/drivers` (menyalakan *pgvector*, *semantic-api*, dan *Ollama*).
4. **Context Sync & HITL Gate**: Jika terdapat proyek baru (`active_project`), Agen wajib meminta *Human-in-the-Loop* (HITL) persetujuan untuk meregistrasi dan menyinkronkan *AST (Graphify)* ke *database pgvector*.
5. **Booting Report**: Menampilkan visual tag mode **`[OS_MODE: COGNITIVE]`** atau **`[OS_MODE: DRIVERLESS]`**, status proyek, fase kerja, dan prioritas tugas (Task Pipeline).

### B. System Shutdown (`matikan locomo agent os`)
Siklus aman untuk mematikan infrastruktur (*Docker compose down*) dan mereset status `cortex-core.json` kembali ke *idle*.

---

## 4. Workflows Orkestrasi & Mesin State (The Factory)

Siklus kerja pengembangan berjalan dalam dua dimensi yang saling beririsan: **4 Fase Makro (Kernel Loop)** dan **Alur State Machine Spesialis (Swarm Routing)**.

### 4.1 Empat Makro-Fase Siklus Kerja (The 4 Macro-Phases)
1. **[PHASE 1: THINK]**: Persepsi dan perencanaan. Pembuatan arsitektur dan spesifikasi (`current-spec.md`). Diakhiri dengan gerbang `[START_APPROVAL]` (HITL).
2. **[PHASE 2: SYNC]**: *Swarm Reasoning*. Kerja paralel para *role agent* untuk memastikan kontrak API, data, dan desain selaras.
3. **[PHASE 3: ACT]**: Eksekusi penulisan kode, validasi keamanan, dan perbaikan otomatis (*auto-healing*). Melewati `[STRICT_VALIDATION]`.
4. **[PHASE 4: REFLECT]**: Fase sintesis, pelaporan log (ke `docs/changelogs/`), dan persiapan penggabungan (*merge*).

### 4.2 Swarm Roles & Routing (Micro-Steps)
Setiap langkah (*state*) memicu pemuatan (*context switch*) *role* spesifik:
* **STEP 1 (The Architect)**: Merancang arsitektur, *database*, dan *tech-stack rules*.
* **STEP 2A (The Coder)**: Implementasi *backend*, logika *database*, dan *business logic*.
* **STEP 2B (The UI Developer)**: Fokus *frontend*, CSS, interaksi DOM, dan visual.
* **STEP 3A (The Reviewer)**: Melakukan audit kualitas kode berdasarkan `locomo-signature.md`.
* **STEP 3B (The Security Auditor)**: Memburu ancaman kerentanan (Red-Team) khususnya pada otentikasi dan *query* rentan.
* **STEP 4 (The Tester)**: Mengeksekusi pengujian (*Sail/Artisan test*). Memvalidasi eksekusi dalam kontainer.
* **STEP 5 (The Fixer)**: Memperbaiki *bug* secara bedah mikro berdasarkan log dari Tester/Reviewer. (Dibatasi **MAX_RETRIES = 3** sebelum jatuh ke mode *Degraded* / Eskalasi).
* **STEP 6 (The Gatekeeper)**: Validasi rilis final.

---

## 5. Manajemen Multi-Proyek & Sinkronisasi Konteks

Dalam merespons kompleksitas *workspace* yang menaungi berbagai proyek terpisah (`locomo-web`, `mandeh.online`, `mandeh.id`, dll), OS menetapkan batasan ketat:
* **Dynamic Target Selector**: Penggunaan variabel `active_project` dalam *Cortex Core* untuk memastikan perintah agen (seperti penelusuran *path* atau *testing*) secara dinamis diarahkan ke folder proyek spesifik.
* **Graphify Isolation**: File konfigurasi pemetaan abstrak kode (AST) bersifat terpisah per proyek (contoh: `graph-mandeh-online.json`) guna mencegah **Pencemaran Context Window**.
* **Database Vector Sync**: Representasi Graphify disinkronisasikan ke tabel `cognitive_memories` di *pgvector* melalui API Python (fastAPI).

---

## 6. Kepatuhan Mutlak & "Definition of Done"

Sistem menetapkan kualitas tanpa toleransi (Zero-Tolerance Compliance):
1. **Docker-Only**: Segala bentuk kakas (*Artisan, Python, NPM*) dilarang keras berjalan secara native. Wajib menggunakan `docker compose exec` atau *Sail* pada container terkait.
2. **Destructive Gateway**: Operasi seperti penghapusan file, modifikasi konfigurasi inti, atau perintah `DROP/TRUNCATE` *database* **Wajib memicu [DESTRUCTIVE_GATE] (Persetujuan User)**.
3. **Lazy Loading Context**: Demi menghindari *Token Overflow*, Agen tidak diperkenankan membaca file referensi besar secara massal, melainkan wajib menggunakan *grep/search* untuk mencari node data spesifik (misal: di `domain-knowledge.json`).
4. **Definition of Done (DoD)**:
   * Bersih dari isu keamanan.
   * *Automated Tests* berstatus `PASS`.
   * Digital Signature (`locomo-signature.json`) telah tertanam di kode.

---

## 7. Arah Pengembangan (Google AI Playbook Alignment)

Dokumen ini menjadi fondasi *As-Is* arsitektur. Pada tahapan selanjutnya, kita akan menyesuaikan orkestrasi ini dengan prinsip-prinsip dari **Google AI Playbook**, yang meliputi:
* **Evolusi Prompting & Perencanaan Strategis (Chain of Thought/ReAct)**
* **Iterative & Incremental Delivery dengan Validasi AI**
* **Continuous Feedback Loops yang lebih interaktif dan intuitif**
* **Integrasi Kakas AI Toolchain Terkini (mis: Peningkatan Otonomi Agen Spesifik)**

---
*End of Document. Prepared for User Review.*
