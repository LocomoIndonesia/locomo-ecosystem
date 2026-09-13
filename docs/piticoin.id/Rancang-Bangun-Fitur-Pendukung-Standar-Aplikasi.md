# Rancang Bangun Fitur Pendukung Standar Aplikasi (Standard Supporting Features)
**Pusat Profil, Pusat Bantuan, Tata Kelola Hukum (ToS & Privasi), dan Kepatuhan Digital PitiCoin.id**  
*Tanggal: 13 September 2026*  
*Arsitek: Locomo AI Agent OS (Antigravity)*  

---

## 1. Urgensi & Filosofi Fitur Pendukung

Sebuah platform digital berskala enterprise dan gateway ekonomi seperti **PitiCoin.id** tidak hanya membutuhkan *core gameplay* (staking, misi, dan pembagian komisi), melainkan juga wajib dilengkapi dengan **standar fitur pendukung komprehensif** untuk:
1. **Kepatuhan Regulasi & Hukum**: Mematuhi UU Perlindungan Data Pribadi (UU PDP No. 27/2022) dan standarisasi e-commerce/financial hub.
2. **Kepercayaan Pengguna (Trust & Legitimacy)**: Memberikan kejelasan aturan main, transparansi staking perak, dan kepastian perlindungan data.
3. **Penyempurnaan Identitas & Personalisasi**: Memberikan kebebasan visual kepada pengguna untuk berekspresi sesuai faksi dan personanya di dunia ekspedisi Gunung Mas.

---

## 2. Matriks Modul Fitur Pendukung Standar

```
docs/piticoin.id/
├── 1. Profile & Avatar Customizer    -> Upload Foto / Pilihan Preset Avatar Faksi & Persona
├── 2. Help Center & FAQ Hub           -> Kategori Panduan, FAQ Interaktif, & Jalur Kontak CS
├── 3. Syarat & Ketentuan (Terms)      -> Regulasi Koin, Staking Escrow, Anti-Fraud, & Batasan Klaim
├── 4. Kebijakan Privasi (Privacy)     -> Kepatuhan UU PDP 2022, Pemrosesan Data, & Hak Penghapusan
├── 5. Cookie Consent & Preferences    -> Floating Consent Banner, Kategori Cookie, & Local Storage
└── 6. Keamanan Akun & Manajemen Sesi  -> Ganti Password, WhatsApp Binding, & Kill Other Sessions
```

---

## 3. Rincian Desain & Arsitektur Masing-Masing Fitur

### 3.1 Manajemen Profil & Avatar Karakter (Profile & Avatar Customizer)
- **Kebutuhan**: Pengguna dapat mengubah foto profil mereka agar tampil di leaderboard ekspedisi, obrolan saudagar, dan kartu profil publik.
- **Dua Opsi Pemilihan Avatar**:
  1. **Upload Kustom**:
     - Format didukung: JPG, PNG, WebP (maksimal 2 MB).
     - Pipeline backend: Crop square (1:1), kompresi otomatis, simpan ke `storage/app/public/avatars/`, simpan URL ke `users.avatar_url`.
     - Validasi keamanan: Sanitasi MIME type untuk mencegah script injection (.php/.svg beracun).
  2. **Galeri Preset Persona Gunung Mas**:
     - Pilihan ikon berlisensi sesuai faksi: *Saudagar Pesisir (Biru Laut)*, *Penjelajah Gunung (Emas/Obsidian)*, *Pendeka Silat (Merah Marun)*, *Pedagang Pasar (Hijau Zamrud)*.
- **Skema Database**:
  - `users.avatar_url` (nullable string, URL gambar kustom atau identifier preset misal `preset:saudagar_01`).

---

### 3.2 Pusat Bantuan & FAQ Interaktif (Help Center)
- **Rute**: `/help` dan `/faq`
- **Fitur Utama**:
  1. **Quick Search Bar**: Pencarian cerdas teks artikel bantuan secara instan.
  2. **Kategori Panduan**:
     - *Perburuan Harta Karun*: Cara kerja Koin Perak (Staking), Koin Emas (Reward), dan gas fee mikro 5%.
     - *Ekspansi Jaringan*: Cara membagikan link referral dan mendaftarkan merchant Unimart POS / guru WaliKelas.
     - *Dompet & Pencairan*: Cara deposit, transfer P2P, dan penarikan saldo Rupiah.
     - *Akun & Keamanan*: Kendala OTP WhatsApp, ganti kata sandi, dan lisensi akun.
  3. **Direct Escalation (Bantuan Langsung)**:
     - Tombol *"Konsultasi dengan Saudagar Pembina"* (membuka modal pesan internal yang sudah ada).
     - Tombol *"WhatsApp Support Resmi"* dengan pesan pembuka otomatis.

---

### 3.3 Syarat & Ketentuan Layanan (Terms and Conditions)
- **Rute**: `/terms`
- **Klausul Wajib**:
  1. **Definisi Instrumen Finansial & Utilitas**:
     - Menegaskan bahwa Koin Perak adalah *utility token internal* untuk simulasi ekspedisi, bukan alat tukar resmi fiat di luar ekosistem.
     - Koin Emas adalah poin reward loyalitas yang dapat ditukarkan sesuai likuiditas pool.
  2. **Klausul Pelepasan Tanggung Jawab Staking (Loss Disclaimer)**:
     - Pengguna secara sadar mengunci Koin Perak ke dalam *escrow*. Jika misi gagal atau kedaluwarsa, koin hangus sesuai algoritma tanpa tuntutan ganti rugi.
  3. **Integritas Jaringan & Larangan Multi-Akun (Sybil Attack)**:
     - Larangan membuat akun palsu / nomor WhatsApp tiruan untuk manipulasi komisi. Akun yang terindikasi manipulasi akan dibekukan oleh Admin Level 0.
  4. **Pemisahan Entitas Layanan Terfederasi**:
     - PitiCoin berperan sebagai Payment & Network Hub. Masalah operasional barang/jasa di merchant Unimart menjadi tanggung jawab masing-masing entitas merchant.

---

### 3.4 Kebijakan Privasi (Privacy Policy)
- **Rute**: `/privacy`
- **Kepatuhan Hukum**: Menyelaraskan dengan **UU Perlindungan Data Pribadi (UU No. 27 Tahun 2022)** Republik Indonesia.
- **Poin-Poin Pokok**:
  1. **Data yang Dihimpun**: Nama lengkap, username unik, nomor WhatsApp aktif, log transaksi ledger internal, dan koordinat GPS (hanya jika mendaftarkan merchant baru).
  2. **Tujuan Pemrosesan**: Autentikasi federasi SSO Mandeh.ID, pencatatan hierarki referral upline/downline, dan keamanan finansial.
  3. **Keamanan & Penyimpanan Data**: Penyimpanan menggunakan enkripsi password Argon2id/Bcrypt, proteksi SSL/TLS, dan pencatatan transaksi *double-entry ledger*.
  4. **Hak Subjek Data (User Rights)**:
     - Hak memperoleh salinan data profil.
     - Hak memperbarui nomor WhatsApp dan identitas.
     - Hak mengajukan penutupan dan anonimisasi akun (*Right to Erasure / Deletion*).

---

### 3.5 Spanduk & Preferensi Kuki (Cookie Consent & Preferences)
- **Komponen**: `resources/js/Components/CookieConsentBanner.tsx`
- **Mekanisme Kerja**:
  1. **Deteksi Pengunjung**: Muncul otomatis di bagian bawah layar jika `localStorage.getItem('piticoin_cookie_consent')` belum bernilai.
  2. **Klasifikasi Kuki**:
     - *Kuki Esensial (Wajib)*: Session Laravel, proteksi token CSRF, status autentikasi. Tidak dapat dinonaktifkan.
     - *Kuki Preferensi & Kinerja (Opsional)*: Menyimpan setting tema visual (Dark/Gold Obsidian), status audio SFX kartu KOA, dan riwayat modal.
  3. **Pilihan Tombol**:
     - `[ Setujui Semua ]`: Mengizinkan seluruh kuki, simpan status `all`.
     - `[ Hanya Esensial ]`: Hanya simpan sesi dan keamanan, simpan status `essential`.
     - `[ Pelajari Selengkapnya ]`: Tautan langsung menuju `/privacy#cookies`.

---

### 3.6 Fitur Pendukung Tambahan (Good-to-Have Standards)
1. **Pusat Notifikasi & Riwayat Aktivitas**:
   - Ikon lonceng di navigasi atas dengan badge counter.
   - Menampilkan notifikasi instan saat:
     - Referral baru bergabung di karavan pengguna.
     - Misi berhasil diklaim dan saldo Koin Emas masuk.
     - Saudagar rujukan atau Admin mengirimkan pesan konsultasi.
2. **Pengaturan Keamanan & Sesi Aktif (Active Session Management)**:
   - Menampilkan daftar perangkat/browser yang sedang login beserta IP dan waktu aktif terakhir.
   - Tombol *"Keluarkan dari Semua Perangkat Lain"* untuk mencegah pembajakan akun.
3. **Pengalih Suara & Audio Efek Ekspedisi (Audio SFX Toggle)**:
   - Toggle on/off suara putaran kartu KOA dan suara koin emas di menu pengaturan.

---

## 4. Rencana Implementasi Bertahap

| Tahap | Modul Fitur | Deliverables |
| :--- | :--- | :--- |
| **Fase 1** | **Legal & Compliance Pages** | Pembuatan halaman publik `/terms`, `/privacy`, dan komponen `CookieConsentBanner.tsx`. |
| **Fase 2** | **Pusat Bantuan (Help Center)** | Pembuatan halaman `/help` dengan accordion FAQ dan integrasi tombol eskalasi CS WhatsApp. |
| **Fase 3** | **Redesign Halaman Profil & Avatar** | Pembaruan `/profile` dengan tema Dark Gold, picker avatar preset persona, dan upload custom avatar. |
| **Fase 4** | **Pusat Notifikasi & Manajemen Sesi** | Implementasi drawer notifikasi aktivitas user dan kontrol sesi login aktif. |



NB:
- fitur WebPush Notif
- Fitur Rating & Reputasi berdasarkan apresiasi jaringan maupun dari client yang bertransaksi, serta termasuk riwayat check-in + serta tingkat penyelesaian misi
- verifikasi misi dari pengguna di luar jaringan yang akan di kirim oleh sistem secara acak minimal 5 orang yang melakukan verifikasi
- pasar lelang digital