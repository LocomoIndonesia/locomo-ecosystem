# Catatan Rilis & Panduan UAT (User Acceptance Test)
**Platform:** Governance Index — Identity & Profile Edition
**Versi:** v1.1.9-Gov
**Tanggal Rilis:** 16 April 2026

---

## 📌 Catatan Rilis (Release Notes) v1.1.9-Gov

Pembaruan pada versi v1.1.9-Gov berfokus pada **Transformasi Identitas (Rebranding)** platform menjadi **Governance Index** serta peningkatan fitur **Manajemen Identitas Admin** untuk standar operasional yang lebih profesional dan personal.

### Fitur & Pembaruan Utama:

1. **Transformasi Identitas (Full Rebranding):**
   - Transisi seluruh elemen visual dari "Locomo Research" menjadi **Governance Index**.
   - Pembaruan logo *branding* pada Landing Page (GOVERNANCE INDEX), Sidebar Admin, dan Navbar.
   - Sinkronisasi identitas sistem pada basis data agar selaras dengan entitas baru klien.

2. **Manajemen Profil Admin (Identity Manager):**
   - **Modernisasi Halaman Profil**: Halaman profil kini terintegrasi penuh ke dalam layout Admin (Sidebar tetap aktif), memberikan alur kerja yang lebih efisien bagi administrator.
   - **Custom Profile Photo**: Fitur unggah foto profil kustom dengan antarmuka *Ultra-Luxe Minimalist*.
   - **Manajemen Alias**: Penambahan field *Alias* untuk kebutuhan personalisasi komunikasi administrator di masa mendatang.
   - **Enhanced Security UI**: Perombakan formulir pembaruan kata sandi dengan gaya visual *premium glassmorphism* dan validasi yang lebih jelas.

3. **Restrukturisasi Navigasi & UI Fixes:**
   - **Manajemen Responden**: Penambahan menu baru "Manajemen Responden" pada sidebar utama untuk memisahkan alur kerja data responden.
   - **Optimasi Integrasi Navbar**: Perbaikan tautan *Profile Settings* pada dropdown navbar yang kini terhubung secara instan menggunakan `wire:navigate`.
   - **UI Structural Recovery**: Perbaikan struktur *nesting* pada modul *Setting Manager* untuk memastikan stabilitas navigasi antar tab pengaturan.

4. **Pembaruan Infrastruktur (Backend):**
   - **Database Schema**: Penambahan kolom `alias` dan `profile_photo_path` pada tabel `users`.
   - **System Seeder**: Pembaruan sistem pengisian data awal (*seeding*) untuk menjamin konsistensi branding pada instalasi baru maupun pembaruan sistem.

---

## 🔐 Akun Uji Coba (Dummy Accounts)

Berikut adalah akun yang telah disiapkan di *database* yang dapat digunakan untuk melakukan pengujian:

### 1. Akun Admin (Panel Manajemen Utama)
Gunakan akun ini untuk masuk ke portal Dasbor Admin dan mencoba fitur Profil baru.
* **Email:** `admin@governance-index.com` (Placeholder baru) atau `admin@index.survey`
* **Password:** `Admin2026`

### 2. Akun Responden
* **Email:** `responden@index.survey`
* **Password:** `Responden2026`

---

## 🚀 Skenario UAT (User Acceptance Test) Flow

Mohon mengikuti alur uji coba sistem (*Test Cases*) berikut untuk memvalidasi perubahan pada versi v1.1.9-Gov.

### Skenario A: Validasi Rebranding & Identitas Visual
_Tujuan: Memastikan transisi identitas ke "Governance Index" sudah merata._

- [ ] **A1. Beranda Publik:** Buka halaman depan. Pastikan logo teks menampilkan **GOVERNANCE INDEX** dan *footer copyright* menampilkan **GOVERNANCE INDEX ENGINE**.
- [ ] **A2. Navbar Admin:** Login sebagai Admin dan lihat pojok kanan atas. Pastikan logo teks menampilkan **Governance.OS**.
- [ ] **A3. Sidebar Admin:** Pastikan teks branding di atas sidebar menampilkan **Governance** dan **Governance Index** (menggantikan Locomo).

### Skenario B: Pengujian Fitur Manajemen Profil Admin
_Tujuan: Memvalidasi fitur unggah foto, alias, dan pembaruan password._

- [ ] **B1. Akses Halaman Profil:** Klik dropdown profil di navbar, pilih **Profile Settings**.
  - *Ekspektasi:* Halaman terbuka tanpa *reload* penuh (via `wire:navigate`) dan tetap berada di dalam *layout* admin (sidebar tidak hilang).
- [ ] **B2. Unggah Foto Profil:** Coba unggah foto baru pada bagian *Profile Photo*.
  - *Ekspektasi:* Foto terunggah dan ditampilkan seketika dalam bingkai lingkaran yang elegan.
- [ ] **B3. Update Alias & Info:** Isi field *Alias* dan simpan perubahan.
  - *Ekspektasi:* Muncul notifikasi "Saved" yang halus (toast/message) dan data tersimpan ke database.
- [ ] **B4. Ganti Password:** Coba ganti password pada bagian *Update Password*.
  - *Ekspektasi:* Tampilan form konsisten dengan tema premium dan validasi error (jika ada) tampil dengan jelas.

### Skenario C: Navigasi Modul Baru
- [ ] **C1. Menu Manajemen Responden:** Klik menu **Manajemen Responden** di sidebar.
  - *Ekspektasi:* Mengarah ke halaman manajemen responden (rute `admin/respondents`).

---

> [!IMPORTANT]
> **Catatan Teknis untuk Deployment:**
> Pastikan menjalankan perintah `php artisan deployment/update_v1_1_9.sh` di server target untuk menerapkan perubahan skema database dan sinkronisasi branding identitas baru pada tabel `settings`.
