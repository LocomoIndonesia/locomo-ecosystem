# AGENTS.md — Locomo Agent OS v12 (Zero-Touch Auto-Boot)

Setiap sesi baru di workspace ini WAJIB auto-boot via Central Brain Gateway, tanpa perintah manual.

## 1. Identifikasi Proyek
- Baca `.locomo` di root workspace untuk `project_code` + `gateway_url` (default `http://localhost:8000`).
- Jika `.locomo` hilang, gunakan nama folder sebagai `project_code` dan daftarkan ulang via `POST /project/init`.

## 2. Sinkronisasi Kognitif (via Central Brain Gateway)
- Status Kognitif: `GET /memory/cortex?project_code={code}`
- Changelog: `POST /memory/changelog` `{"project_code": "...", "message": "..."}`
- Error Tracing: `POST /memory/error` dengan format sama.
- Seluruh state kognitif terpusat di `~/.locomo.agent/docs/{project_code}/`. Dilarang membuat brain folder lokal di target project.

## 3. Tiered Execution (Windows & WSL)
1. Tier 1 (Primary): Docker Container (`docker compose exec <svc> <cmd>`)
2. Tier 2 (Secondary): WSL Shell (`php`, `composer`, `python3`, `npm`)
3. Tier 3 (Fallback): Windows Host (`dotnet`, `powershell`) bila perlu ekosistem Windows.

## 4. Guardrails & Multi-Agent Swarm
- Skinny Controller, Fat Service. Relational-first.
- Circuit breaker: maks 3x auto-repair.
- Destruktif (`DROP`, `TRUNCATE`, hapus file) wajib konfirmasi eksplisit user.
- Multi-Agent Swarm: Orchestrator mengoordinasikan delegasi tugas ke Worker Agents (`agy`, `opencode`, dll).

