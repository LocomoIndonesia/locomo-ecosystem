# SPECIFICATION: Remote WSL Build through Windows Path (POC)

## 1. Objective
Tujuan dari spesifikasi ini adalah untuk memvalidasi apakah AntiGravity dapat melakukan alur kerja pengembangan lengkap (Read, Write, Build, Test) pada proyek yang berada di dalam WSL Ubuntu, namun diakses melalui jalur UNC Windows (`\\wsl$\Ubuntu\...`), dengan mengeksekusi perintah melalui wrapper `wsl` dan `sail`.

## 2. Technical Context
*   **OS:** Windows 11 (Host)
*   **WSL Distribution:** Ubuntu
*   **Project Path (Windows):** `\\wsl$\Ubuntu\home\dev360_03\index.survey`
*   **Project Path (WSL):** `/home/dev360_03/index.survey`
*   **Project Root:** `locomo-research`
*   **Build Tool:** Laravel Sail (Docker)

## 3. Scope of POC
1.  **Environment Check:** Menjalankan perintah `wsl whoami` dan `wsl pwd` untuk memastikan komunikasi terminal ke WSL berfungsi.
2.  **Docker/Sail Connectivity:** Menjalankan `./vendor/bin/sail artisan --version` di dalam direktori `locomo-research` via `wsl`.
3.  **Cross-Platform File Sync:** Membuat file dummy `test-poc.txt` melalui AntiGravity (jalur Windows) dan memverifikasi keberadaannya melalui `wsl ls`.

## 4. Execution Protocol (Locomo Standard)
Semua perintah terminal WAJIB mengikuti format:
`wsl -d Ubuntu -e sh -c "cd /home/dev360_03/index.survey/locomo-research && ./vendor/bin/sail <command>"`
Atau jika path pemetaan otomatis berfungsi:
`cd locomo-research && ./vendor/bin/sail <command>` (untuk dijalankan di terminal yang mendukung).

## 5. Success Criteria
*   [ ] AntiGravity dapat membaca file dalam workspace.
*   [ ] AntiGravity dapat menulis file dalam workspace.
*   [ ] Terminal dapat menjalankan perintah `sail` tanpa error koneksi.
*   [ ] Hasil build/test dapat ditangkap oleh AntiGravity.

## 6. Security & Constraints
*   Patuhi `locomo-signature.md`.
*   [REQUIRE_HUMAN_APPROVAL] diperlukan sebelum menjalankan perintah destruktif (misal: `sail artisan migrate:fresh`).
