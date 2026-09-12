# ANALISIS STRUKTUR & RENCANA PENYESUAIAN LOCOMO AGENT OS
**Peningkatan Orkestrasi Multi-Project, Docker-Centric Runtime, dan Panduan Portabilitas**
*Penyusun: AntiGravity AI (Agy)*  
*Tanggal: 10 Juni 2026*  

---

## 1. PENDAHULUAN & TUJUAN RESTRUKTURISASI

Sistem operasi manajemen pemikiran Agen AI yang sebelumnya dikenal sebagai **Locomo OS (Central Cognitive Core / CCC)** ditingkatkan menjadi **Locomo Agent OS**. 

Untuk menyelaraskan dengan visi baru User, analisis ini diperluas secara khusus untuk:
1. **Kemampuan Pengelolaan Multi-Project**: Mengelola sub-proyek (`locomo-web`, `mandeh.online`, `mandeh.id`, `tapak.pandeka`) di bawah satu workspace tunggal secara dinamis tanpa pencampuran konteks kognitif.
2. **Docker-Only Execution**: Memastikan seluruh kakas bantu (PHP, Python, Graphify, Ollama, Composer, NPM, dll.) dijalankan secara eksklusif menggunakan kontainerisasi Docker.
3. **Panduan Reusability & Deployment**: Panduan inisialisasi awal dan instalasi bersih (*clean sweep*) saat memindahkan Locomo Agent OS ke workspace baru.
4. **Default Booting Protocol**: Aturan wajib di mana Agen secara otomatis menggunakan protokol Locomo Agent OS sebagai perilaku baku setiap kali percakapan dimulai.
5. **Penyelarasan Konteks Graphify & pgvector Sync**: Mengintegrasikan peta pikiran Graphify per-proyek dan sinkronisasi otomatis ke memori pgvector via Python API.

---

## 2. RE-ANALISIS KEMAMPUAN ORKESTRASI & MULTI-PROJECT

Pada arsitektur lama, Agen terjebak pada asumsi satu workspace = satu aplikasi Laravel. Dalam ekosistem terpadu `locomo.id`, terdapat beberapa proyek paralel dengan tumpukan teknologi bervariasi.

### Tantangan Multi-Project:
- **Pencemaran Context Window**: Jika Agen memindai seluruh direktori secara membabi buta, AST (Abstract Syntax Tree) dan referensi antar-proyek akan bercampur (misal: class `User` di `mandeh.online` tertukar dengan class `User` di `locomo-web`).
- **Path Resolution**: Perintah Artisan atau Sail yang dijalankan di root workspace akan mengalami error jika tidak diarahkan secara dinamis ke sub-folder proyek yang dituju.

### Strategi Peningkatan Multi-Project:
1. **Dynamic Target Selector**: Menambahkan variabel `active_project` pada `.antigravity/cortex/cortex-core.json`.
2. **Context Separation**: Mempartisi file dokumentasi proyek pada `.antigravity/docs/` menggunakan sub-direktori atau namespace per proyek (misal: `docs/projects/mandeh.online.json`, `docs/projects/tapak.pandeka.json`).
3. **Dynamic Path Mapping**: Memodifikasi `.antigravity/skills/roms/environment.md` agar `Application Core Path` didefinisikan secara dinamis berdasarkan nilai `active_project`.

### Distingsi Workspace vs Project:
- **Workspace (`locomo.id`)**: Merupakan folder wadah utama yang mewakili ekosistem terintegrasi.
- **Projects**: Sub-direktori independen di dalam workspace yang mewakili aplikasi/layanan nyata:
  1. `locomo-web` (Learning Platform & LMS)
  2. `mandeh.online` (Reputasi Wisata & Toko Lokal)
  3. `mandeh.id` (Identity Provider / SSO Gateway)
  4. `tapak.pandeka` (Log Reputasi & Mading Pemuda)
- *Aturan*: Pendaftaran kode proyek di database `os_projects` dilarang menggunakan nama workspace `locomo.id`. Hanya sub-project riil yang diizinkan untuk didaftarkan.

---

## 3. DOCKER-ONLY RUNTIME ENVIRONMENT POLICY

Locomo Agent OS menetapkan aturan mutlak: **Dilarang mengeksekusi kakas bantu atau runtime (PHP, Python, Node, Ollama) langsung pada mesin host (cPanel, bare-metal native, WSL Host).** Semua proses wajib dibungkus di dalam kontainer Docker yang berjalan di dalam ekosistem.

### Pemetaan Perintah Docker-Centric:
* **PHP & Laravel Artisan**:
  * *Native (DILARANG)*: `php artisan migrate`
  * *Docker-Centric*: `docker compose exec -w /var/www/html/[sub-project-name] laravel.test php artisan migrate` atau menggunakan Sail: `./vendor/bin/sail artisan migrate` (menargetkan kontainer Sail internal).
* **Python Semantic API & Bridge**:
  * *Native (DILARANG)*: `python main.py`
  * *Docker-Centric*: Dijalankan di dalam kontainer `semantic-api` (berbasis `./api/Dockerfile`). Panggilan eksternal dibungkus via API Endpoint atau `docker compose exec semantic-api python [script]`.
* **Ollama & Model Inference**:
  * *Native (DILARANG)*: `ollama run qwen`
  * *Docker-Centric*: Dijalankan lewat kontainer `ollama-local` (port 11434).
* **Graphify (AST Scanner)**:
  * *Native (DILARANG)*: Running graphify script on host node.
  * *Docker-Centric*: Dijalankan menggunakan service image khusus atau di dalam container python bridge yang memiliki volume mapping ke workspace.

Aturan ini dikunci di dalam manifest berkas `skills/roms/environment.md`.

---

## 4. STANDAR PROTOKOL DEFAULT ONBOARDING CHAT

Apabila folder `.antigravity/` terdeteksi di root workspace, Agen AI (AntiGravity) memiliki kewajiban baku untuk **menginisiasi percakapan** menggunakan protokol standar Locomo Agent OS. 

### Alur Onboarding (Wajib pada First Chat):
1. **Auto-Detection**: Mengecek keberadaan direktori `.antigravity/`.
2. **Check & Booting Container**: Memastikan status kontainer drivers via `docker compose ps` (Fase One-Time Check).
3. **Session Declaration**: Mengirimkan pesan deklarasi status dengan tag visual:
   * **`[OS_MODE: COGNITIVE]`** (jika Docker berjalan & API Driver aktif).
   * **`[OS_MODE: DRIVERLESS]`** (jika Docker mati / tidak terpasang).
4. **Booting Report**: Menyajikan ringkasan status dari `cortex-core.json` dan `project-context.json` berupa:
   * Nama Proyek Aktif
   * Status Kesehatan Sistem (Health)
   * Fase Pengembangan Aktif (Current Phase)
   * Tugas Prioritas Utama (Top Priorities)

*Aturan ini mengikat Agen sebagai System Protocol default.*

---

## 5. PENYELARASAN KONTEKS GRAPHIFY & SINKRONISASI PGVECTOR

Untuk memastikan bahwa AI memahami struktur file dan hubungan logika dengan cepat tanpa token overhead, Locomo Agent OS mengintegrasikan **Graphify** sebagai standar mind map.

### 5.1 Analisis Struktur Graphify: Tunggal vs Terpisah
Dilakukan kajian perbandingan terhadap struktur data Graphify pada multi-proyek:
* **Struktur Tunggal (Workspace Wide)**: Seluruh file dalam `locomo-web`, `mandeh.online`, dll disatukan ke satu `graph.json`. Ini menyebabkan pencampuran file dengan nama kelas yang sama dan penurunan performa penelusuran.
* **Struktur Terpisah (Per-Project - DIREKOMENDASIKAN)**: Setiap sub-project memiliki peta Graphify sendiri (misal: `graph-locomo-web.json`, `graph-mandeh-online.json`).
  * *Keputusan*: **Locomo Agent OS menggunakan pola terpisah.** File peta tersimpan di `.antigravity/drivers/api/graphify-out/graph-[project-code].json`.

### 5.2 Alur Sinkronisasi & Verifikasi Database (pgvector)
1. **Inisiasi Proyek**: Ketika `active_project` dipicu, Agen memanggil `/project/init` untuk meregistrasikan proyek ke tabel `os_projects`.
2. **Perbandingan & Peta Pikiran**: Agen memicu skrip `sync_context.py` di dalam kontainer `semantic-api` dengan parameter `--project [project_code]` dan `--graph [path_graph]`.
3. **Penyimpanan Vektor**: Modul FastAPI akan mengubah representasi node Graphify (Source File, Class, Method) menjadi embedding vektor menggunakan model Ollama Qwen dan menyimpannya di tabel `cognitive_memories`.
4. **Pemeriksaan Konsistensi Otomatis**: Setiap proses booting (Command: `jalankan locomo agent os`) akan memeriksa apakah jumlah rekaman memori proyek di database selaras dengan node pada file Graphify. Jika kosong atau out-of-date, sinkronisasi otomatis akan dijalankan.

### 5.3 Aturan Persetujuan Pengguna (HITL Gate)
* **Pendaftaran Proyek Baru**: Agen DILARANG mendaftarkan atau menyinkronkan proyek baru ke database secara otomatis. 
* **Protokol**: Jika `active_project` yang diset belum terdaftar di basis data, Agen wajib menampilkan pesan konfirmasi `[HITL_APPROVAL_REQUIRED]` di obrolan untuk meminta izin eksplisit dari pengguna sebelum menjalankan prosedur pendaftaran/sinkronisasi.

---

## 6. PANDUAN DEPLOYMENT & PORTABILITAS (WORKSPACE LAIN)

Agar Locomo Agent OS dapat dipasang pada proyek/workspace lain secara bersih tanpa membawa "sampah kognitif" dari proyek sebelumnya, ikuti panduan inisialisasi awal (*Clean Sweep*) berikut:

### 6.1 Langkah-Langkah Deployment Baru:
1. **Copying Core Engine**: Salin folder `.antigravity/` ke root workspace target (abaikan folder `.antigravity/brain/` dan `.antigravity/scratch/`).
2. **Execution Clean Sweep Script**: Jalankan perintah pembersihan (atau lakukan pembersihan manual):
   * Kosongkan berkas `cortex/decisions.jsonl`.
   * Kosongkan berkas `cortex/conflicts.jsonl`.
   * Hapus berkas memori dinamis di dalam folder `brain/*` (jika ada).
3. **Configuration Cartridge Docs**: Update dokumen di `.antigravity/docs/` (`project-context.json`, `tech-stack.json`, `domain-knowledge.json`, `feature-map.json`) sesuai dengan deskripsi teknis proyek baru.
4. **Update ROMs Environment**: Sesuaikan target path `OS Root` dan `Application Core Path` di `skills/roms/environment.md` mengarah ke direktori workspace yang baru.
5. **Docker Spin Up**: Jalankan perintah `cd .antigravity/drivers && docker compose up -d --build` untuk membangun database pgvector dan driver Ollama lokal khusus untuk database baru.

---

## 7. DETAIL PERUBAHAN FILE DI WORKSPACE

Untuk mengintegrasikan hasil re-analisis ini ke dalam file sistem `.antigravity`, perubahan spesifik berikut akan diterapkan:

### A. `.antigravity/SKILL.md` (Update System Protocol)
* Menyisipkan kewajiban default onboarding chat di baris awal.
* Menegaskan bahwa driverless mode pun tetap mewajibkan docker check jika memungkinkan.
* Menambahkan status sinkronisasi geospasial/vektor pada tahapan onboarding dengan gerbang HITL.

### B. `.antigravity/skills/roms/environment.md` (Docker & Multi-Project Path)
* Mengunci aturan **Docker-Only Execution** untuk PHP, Python, Graphify, dan Ollama.
* Mengubah struktur path menjadi modular dengan menambahkan pemetaan sub-proyek.

### C. `.antigravity/docs/project-context.json` & `.antigravity/cortex/cortex-core.json`
* Membagi data workspace (`locomo.id`) dengan array daftar proyek riil.
* Mengosongkan `active_project` default untuk menanti pilihan pengguna.

### D. `.antigravity/drivers/api/sync_context.py`
* Merombak skrip agar dinamis menerima argumen CLI (`--project` dan `--graph`).

---

## 8. RENCANA EKSEKUSI & VALIDASI

Setelah User memberikan persetujuan terhadap analisis khusus ini, Agen akan memperbarui berkas konfigurasi `.antigravity/` secara bertahap dan melakukan inisialisasi ulang (*clean boot*) untuk mengaktifkan **Locomo Agent OS**.
