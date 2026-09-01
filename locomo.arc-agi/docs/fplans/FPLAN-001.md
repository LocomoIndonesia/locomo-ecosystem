# FLOW PLAN: FPLAN-001 (Inisialisasi & Setup Proyek)

**Fokus Utama:** Melakukan bootstrap tumpukan teknologi, inisiasi database, serta menyusun pondasi dasar proyek.

---

## 📋 STATUS ALUR KERJA

* **Status Keseluruhan:** `IN_PROGRESS`
* **Tanggal Dibuat:** 28 Juni 2026
* **Versi Dokumen:** 1.0.0

---

## 🚀 LANGKAH KERJA BERTAHAP

### 🔴 Step 1: Setup Kontainer & Docker Scaffolding
* **Deskripsi:** Menyiapkan lingkungan Docker Compose yang mencakup web server, runtime bahasa, dan database engine yang disepakati.
* **Kriteria Keberhasilan (Acceptance Criteria):**
  - File `docker-compose.yml` terancang di root.
  - Perintah `docker compose up -d` berjalan tanpa eror.
  - Akses port database dan web server terbuka dan merespons.
* **Status:** `PENDING`

### 🔴 Step 2: Inisiasi Database & Migrasi Awal
* **Deskripsi:** Merancang skema tabel awal, konfigurasi koneksi database, dan menjalankan berkas migrasi pertama.
* **Kriteria Keberhasilan (Acceptance Criteria):**
  - Koneksi database terverifikasi berhasil.
  - Tabel bawaan sistem (users, sessions, password_resets) terbuat secara otomatis.
* **Status:** `PENDING`

### 🔴 Step 3: Setup Autentikasi & Guardrails Keamanan
* **Deskripsi:** Mengimplementasikan modul autentikasi dasar (seperti JWT atau Session-based SSO) dan memverifikasi kepatuhan terhadap guardrails keamanan di `id.json`.
* **Kriteria Keberhasilan (Acceptance Criteria):**
  - Endpoint registrasi dan login merespons dengan benar.
  - Token JWT atau session terbuat dan tervalidasi dengan aman.
  - Log audit mendokumentasikan ketiadaan fungsi destruktif (seperti `eval()`).
* **Status:** `PENDING`

---

## 🔒 CATATAN & BATASAN EKSEKUSI

* **Aturan Pengujian:** Setiap langkah wajib lulus pengujian otomatis sebelum merilis status `PASS`.
* **Circuit Breaker:** Jika terjadi kegagalan build/pengujian pada langkah aktif, coba perbaiki mandiri maksimum 3 kali. Setelah itu, hentikan eksekusi dan hubungi *The Orchestrator*.
