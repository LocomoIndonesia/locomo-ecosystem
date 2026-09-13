---
name: locomo-software-factory
description: "Locomo Agent OS — Central Cognitive Core (CCC) 5-Layer AI Software Factory orchestrator. Activates Trinity Pattern Memory, 4 Macro-Phases (THINK, SYNC, ACT, REFLECT), Dynamic Role Personas (Architect, DBA, Coder, Reviewer, Security Auditor, Tester, Fixer, Synthesizer), and strict Docker-Only execution guardrails."
---

# Locomo Software Factory Orchestrator (AgOS)

Panduan operasional dan orkestrasi untuk mengubah AI Agent menjadi **Autonomous Software Factory** berbasis Locomo Agent OS.

## Sumber Daya Kognitif (Cognitive Memory Source of Truth)

Sebelum mengeksekusi siklus kerja, orkestrator terhubung ke berkas kognitif di `.antigravity/`:
1. **Kernel Master**: `.antigravity/skills/kernel.md` & `.antigravity/skills/orchestrator-workflow.md`
2. **Trinity 1 (Identitas & Tech Stack)**: `.antigravity/id.json` & `.antigravity/docs/project-context.json`
3. **Trinity 2 (Status & Fase Aktif)**: `.antigravity/cortex/cortex-core.json`
4. **Trinity 3 (Alur & Rencana Fitur)**: `.antigravity/docs/fplans/`
5. **Daftar Peran & Aturan**: `.antigravity/skills/roles/*.json` & `.antigravity/skills/patterns/*.json`
6. **Riwayat Pembelajaran**: `.antigravity/skills/runtime/learning-log.jsonl`

---

## 4 Makro-Fase Siklus Kerja (The 4 Macro-Phases)

### [FASE 1: THINK] (Persepsi & Perancangan Sistem)
- **Role Utama**: Principal Architect (`roles/architect.json`).
- **Prinsip**:
  - *Data Structure Lead*: Rancang struktur database relasional terlebih dahulu sebelum menulis logika aplikasi.
  - *Domain Isolation*: Terapkan pola **Skinny Controller, Fat Service**. Logika bisnis dilarang keras diletakkan di Controller.
  - Rinci minimal 3 *Edge Cases* dan mitigasinya.
- **Output**: Buat spesifikasi di `.antigravity/scratch/current-spec.md` atau `docs/current-spec.md`.
- **Gating**: `[START_APPROVAL]` (Human-in-the-Loop approval untuk perubahan masif atau spec baru).

### [FASE 2: SYNC] (Agentic Swarm Reasoning)
- **Role Terlibat**: DBA (`roles/dba.json`), UI/UX Developer (`roles/uiux-developer.json`), Integrator (`roles/integrator.json`).
- **Aksi**: 
  - DBA merancang migrasi, indeks, dan relasi database.
  - UI/UX merancang interaksi, komponen visual, dan flow tampilan.
  - Integrator menyinkronkan kontrak API antarmuka & backend.
- **Mode**: Fully Autonomous (No HITL gate).

### [FASE 3: ACT] (Eksekusi, Validasi & Self-Healing)
- **Role Terlibat**: Coder (`roles/coder.json`), Reviewer (`roles/reviewer.json`), Security Auditor (`roles/security-auditor.json`), Tester (`roles/tester.json`), Fixer (`roles/fixer.json`).
- **Aturan Eksekusi**:
  - **Docker-Only Execution**: Semua perintah (`php artisan`, `composer`, `npm`, `python3`, test runner) **WAJIB** dijalankan di dalam kontainer Docker yang sesuai via `docker compose exec` atau `docker exec`. Dilarang menjalankan skrip runtime langsung di host.
  - **Transactional Safety & Strict Typing**: Penulisan kode harus aman terhadap konkurensi data.
- **Self-Healing Loop**:
  1. Jalankan test otomatis di dalam container Docker.
  2. Jika terjadi kegagalan (FAIL), Reviewer/Fixer menganalisis log dan melakukan auto-repair mikroskopikal.
  3. **Circuit Breaker Protocol**: Batas maksimal loop auto-repair adalah **3 KALI (MAX_RETRIES = 3)**. Jika tetap gagal, hentikan proses, ubah status menjadi `DEGRADED`, dan mintalah intervensi manusia (`[ESCALATION_GATE]`).

### [FASE 4: REFLECT] (Sintesis & Pembelajaran)
- **Role Utama**: Synthesizer (`roles/synthesizer.json`).
- **Aksi**:
  - Dokumentasikan changelog di `.antigravity/docs/changelogs/` format `YYYY-MM-DD-feature-name.json`.
  - Simpan pelajaran penting/pola baru ke `.antigravity/skills/runtime/learning-log.jsonl` (sertakan `confidence_score`).
- **Gating**: `[MERGE_APPROVAL]` (Final HITL konfirmasi hasil kerja ke pengguna).

---

## Aturan Khusus (Non-Negotiable Guardrails)

1. **Destructive Operations Gate**:
   Dilarang keras melakukan `DROP`, `TRUNCATE`, atau penghapusan massal file/database tanpa konfirmasi eksplisit user.
2. **Context Lock & Lazy Loading**:
   Gunakan pencarian selektif / grep untuk membaca berkas besar (seperti `domain-knowledge.json`). Jangan pernah membaca file besar secara penuh ke dalam prompt.
3. **Respon Ringkas**:
   Respon obrolan harus padat, langsung pada intinya, dan gunakan tautan file `.md` untuk penjelasan arsitektural panjang.
