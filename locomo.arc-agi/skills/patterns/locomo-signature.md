# LOCOMO OS ARCHITECTURE MANIFESTO & SIGNATURE SKILLS
**Version:** 2.0.0 (Enterprise AgOS Edition)
**Context:** Standardisasi Pengembangan Sistem Operasi Multi-Agent Locomo.OS (Locomotive: Inspirasi Indonesia).
**Orchestrator/Architect:** Ronald Poetra (Zaid)
**Target Agent:** AntiGravity IDE (Semua Role WAJIB mematuhi Manifesto ini).

---

## 1. THE LOCOMO OS MINDSET (CORE PHILOSOPHY)
* **The Operating System Paradigm:** Sistem Locomo OS (AntiGravity) bukan sekadar kerangka kerja, melainkan sebuah kernel dinamis (AgOS). Semua sistem yang Anda (Agen AI) buat harus modular. Fitur harus bisa dicabut atau diganti di masa depan tanpa merusak inti sistem.
* **Agnostic Data Structure:** Rancang struktur sedinamis mungkin. Gunakan konsep skema dinamis (seperti JSONB properties) jika berurusan dengan forms atau data turunan spesifik agar inti produk tidak *hardcoded*.
* **Modular Monolith & Layering:** DILARANG KERAS menempatkan *business logic* kompleks pada Presentation Layer (Controller/View).
    * *Controller/Routes:* Hanya untuk validasi request ringan, *routing*, dan JSON response format (Skinny Controller).
    * *Service Classes / Actions:* Pemegang otoritas penuh terhadap *business logic*, kalkulasi, dan manipulasi data. (Fat Service).

## 2. ROMs (READ-ONLY MEMORIES) - THE SOURCE OF TRUTH
Tumpukan teknologi dan lingkungan tidak lagi *hardcoded* di sini. Sebelum mengeksekusi tugas apapun, Anda (Agen AI) **DIWAJIBKAN** membaca dan menginternalisasi konstanta pada direktori `skills/roms/`.

Anda wajib memeriksa ketiga file referensi konstan berikut setiap saat:
1. **`skills/roms/tech-stack.md`** -> Untuk mematuhi batasan *Framework*, Tipe Data (UUID/JSONB), dan Filosofi UX ("AntiGravity Aesthetic").
2. **`skills/roms/environment.md`** -> Untuk mengetahui di *path* direktori mana Anda berdiri, aturan mengeksekusi Terminal (seperti aturan *Docker Sail* lokal vs *cPanel production*), dan pipeline rilis.
3. **`skills/roms/business-rules.md`** -> Untuk memastikan injeksi Tanda Tangan Digital pada file code, resiliensi sistem (seperti penanganan multi-request file *upload*), dan aturan pengikatan manusia dalam loop keamanan (*Human-In-The-Loop*).

## 3. SYSTEM SECURITY & HITL (HUMAN-IN-THE-LOOP) PROTOCOL
Meskipun didesain mandiri, Agen AI tetap harus memiliki *Safe Guards* (Pembatas Kritis) demi stabilitas operasional Software Factory Locomo OS.
* **Scope Isolation:** Jangan mengakses/mengubah file di luar direktori Application Core (ditentukan di `environment.md`).
* **Environment Credentials Protection:** Dilarang mencetak variabel *credential* `.env` rahasia (seperti DB_PASSWORD, API keys) secara eksplisit di terminal output atau disalin ke dokumen riwayat.
* **MANDATORY HITL GATE:** Hentikan proses dan tunggu input eksplisit pengguna (`[HITL_APPROVAL_REQUIRED]`) pada kondisi berikut:
    - Anda diminta mengubah struktur arsitektur dasar yang telah teruji (*Code refactor core engine*).
    - Menjalankan migrasi destruktif (seperti `reset`/`fresh`) di server production.
    - Menghapus direktori berskala besar.

Jika instruksi bersifat tidak deterministik atau terlalu berisiko, buat *Artifact Plan*, jelaskan analisis Anda, dan dapatkan persetujuan. Locomo OS mengedepankan keamanan deterministik di atas kecepatan serampangan.