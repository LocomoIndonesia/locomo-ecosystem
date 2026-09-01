# Catatan Rilis & Panduan UAT (User Acceptance Test)
**Platform:** Locomo Research — Book Edition
**Versi:** v1.1.8-Ag
**Tanggal Rilis:** 14 April 2026

---

## 📌 Catatan Rilis (Release Notes) v1.1.8

Pembaruan pada versi v1.1.8 ("Book Edition") berfokus pada **modernisasi antarmuka pengguna (UI/UX)** ke standar premium (*Glassmorphism* & *Editorial Aesthetic*) serta memastikan stabilitas menyeluruh (100% *Test Pass*) pada *core system*.

**Fitur & Pembaruan Utama:**
1. **Modernisasi Tampilan Detail Post (Portal Publik):** Perombakan total pada halaman detail artikel dan profil riset dengan estetika baca layaknya buku premium. Typografi dipertajam (kombinasi font *Outfit* dan *Inter*), padding diperbaiki, dan fitur *glassmorphism* diimplementasikan untuk memberikan pengalaman *immersive*.
2. **Perbaikan Estetika Demografik:** Penyempurnaan penyajian data agregasi respon pada informasi post, memisahkan profil global responden dan detail demografik setiap pertanyaan ke dalam desain *accordion* yang terlihat premium.
3. **Penyempurnaan Alur Autentikasi (Lupa Password & Reset):** Halaman *Lupa Password*, *Reset Password*, dan *Konfirmasi Email* telah direkonstruksi ulang agar senada dengan UI Login interaktif bertemakan "Deep-Sea" *Glassmorphism*.
4. **Stabilitas Core (PHP 8.4)**: Penyelesaian *bugs property* pada *engine* modul kuesioner (`SurveyWizard`) agar sepenuhnya *compatible* dan andal berjalan di PHP versi terbaru (8.4).

---

## 🔐 Akun Uji Coba (Dummy Accounts)

Berikut adalah akun yang telah disiapkan di *database* yang dapat digunakan oleh Klien untuk melakukan pengujian:

### 1. Akun Admin (Panel Manajemen Utama)
Gunakan akun ini untuk masuk ke portal Dasbor Admin (mengelola indeks, menambahkan pertanyaan, melihat agregasi pengguna).
* **Email:** `admin@index.survey`
* **Password:** `Admin2026`

### 2. Akun Responden (Portal Pengisian Kuesioner)
Pilih salah satu dari akun ini untuk menguji bagaimana responden (masyarakat/sasaran) akan mengisi kuisioner penelitian.
* **Akun 1:** `responden@index.survey`
* **Akun 2:** `siti.rahmania@index.survey`
* **Akun 3:** `budi.santoso@index.survey`
* **Password untuk semua akun responden:** `Responden2026`

*(Catatan: Anda juga dapat mendaftar dengan membuat akun baru di halaman Registrasi untuk mencoba fitur pembuatan akun)*

---

## 🚀 Skenario UAT (User Acceptance Test) Flow

Mohon mengarahkan Klien untuk mengikuti alur uji coba sistem (*Test Cases*) berikut ini guna memvalidasi fungsionalitas aplikasi.

### Skenario A: Uji Coba Landing Page & Portal Publik (Guest Mode)
_Pengguna tidak perlu login_

- [ ] **A1. Akses Halaman Utama (Portal):** Buka beranda sistem. Validasi bahwa informasi list indeks/survei riset terlihat jelas dan banner publik tampil modern.
- [ ] **A2. Akses Post Detail (Reading Mode):** Klik salah satu tulisan pengumuman indeks atau survei pada beranda publik.
  - *Ekspektasi:* Memasuki tampilan **Book Edition** (seperti membaca buku premium). Perhatikan apakah teks mudah dibaca dan jarak header ke konten tidak terdapat celah berlebih yang mengganggu.
- [ ] **A3. Navigasi Login:** Klik tombol **Masuk** atau cobalah masuk ke fitur pengisian survei tanpa login.
  - *Ekspektasi:* Anda diarahkan ke halaman login yang dirancang dengan efek kotak kaca bertema *Deep-Sea* yang responsif.
- [ ] **A4. Lupa Kata Sandi:** Pada halaman Login, klik "Lupa Password".
  - *Ekspektasi:* Muncul layar responsif dan *smooth* (tanpa *loading bar* yang tersendat) untuk memasukkan alamat email.

### Skenario B: Uji Coba Panel Responden (User)
_Tujuan: Memvalidasi proses pendaftaran, beranda user, pengisian survei dari awal sampai akhir._

- [ ] **B1. Proses Login Responden:** Masukkan Email `responden@index.survey` dan Password `Responden2026`.
- [ ] **B2. Dashboard Responden:** Saat berhasil login, pantau menu Dashboard. 
  - *Ekspektasi:* Sidebar (untuk desktop/layar lebar) atau Off-Canvas Bottom Nav (di device HP) berjalan dengan *smooth*, memberikan rekap survei yang belum/sudah diselesaikan.
- [ ] **B3. Proses Pengerjaan Survei (Survey Wizard):** Pilih salah satu riset aktif dan klik "Isi Kuesioner" / "Mulai Survey".
  - *Ekspektasi:* Pengisian soal harus mulus pindah antar *step* pertanyaannya, dan setiap jawaban terekam sementaranya berjalan lancar tidak nge-*lag*. Coba keluar di pertengahan survei dan kembali ulang untuk melihat fitur *Auto-Resume*.
- [ ] **B4. Hasil/Skor Responden:** Masuk ke submenu *Riwayat / Laporan*.
  - *Ekspektasi:* Riwayat dari hasil pengerjaan indeks tampil elegan menampilkan *score*/nilai yang didapatkan oleh Responden setelah menjawab (jika jenis indeks memberikan nilai).

### Skenario C: Uji Coba Panel Admin (Administrator)
_Tujuan: Memvalidasi kemampuan admin melihat data masuk dan menata data demografik & sistem penelitian._

- [ ] **C1. Login Admin:** Logout dari akun responden, lalu Login kembali memakai `admin@index.survey` dan `Admin2026`.
- [ ] **C2. Dashboard Metrik Global:** Lihat dashboard admin awal.
  - *Ekspektasi:* Admin dapat melihat agregasi visual ringkas (total pengguna, total respon masuk, index berjalan).
- [ ] **C3. Kelola Demografik Pertanyaan (Fitur Baru):** Buka modifikasi Indeks / Kuesioner (jika ada data).
  - *Ekspektasi:* Memvalidasi bagaimana data agregasi pertanyaan dan Profil Responden dapat dikelompokan dan dibaca dengan intuitif oleh Admin dalam tampilan yang lebih berestetika, dan bahwa demografik profil individual dari pertanyaan bisa tersimpan paralel.
- [ ] **C4. Penarikan / Akses Data:** Pastikan data pengisian baru dari "responden" uji coba sebelumnya (di Skenario B) sudah masuk pada tab Jawaban/Responds di Panel Admin.

> [!TIP]
> **Catatan Untuk Klien/Tester:** Apabila Klien mengaksesnya menggunakan *Smartphone* (HP), pastikan mereka meninjau secara vertikal (Portrait) pada Skenario B (Responden) untuk menjajal *Mobile-First Bottom Navigation* yang eksklusif dibuat khusus untuk mode responden.
