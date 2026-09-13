# Rencana Implementasi & Analisis Arsitektur: Fitur Pendukung Standar PitiCoin.id
**Tata Kelola Profil Persona, Pusat Bantuan, Legalitas Digital, Privasi UU PDP, dan Keamanan Sesi**  
*Tanggal: 13 September 2026*  
*Arsitek: Locomo AI Agent OS (Antigravity)*  
*Dokumen Rujukan: `docs/piticoin.id/Rancang-Bangun-Fitur-Pendukung-Standar-Aplikasi.md`*

---

## 1. Eksekutif Ringkasan & Hasil Analisis Arsitektur

Berdasarkan tinjauan mendalam terhadap dokumen rancang bangun serta basis kode aktif `piticoin.id` (Laravel 13 + Inertia React + Tailwind v4 Obsidian-Gold):

### A. Kondisi Saat Ini (Current State & Architectural Gaps)
1. **Halaman Profil (`Profile/Edit.tsx`)**:
   - Masih menggunakan template bawaan Laravel Breeze standar berwarna putih (`bg-white`), tidak selaras dengan tema gelap Obsidian-Gold PitiCoin.
   - `ProfileUpdateRequest` dan form masih memvalidasi `email`, padahal arsitektur identitas PitiCoin menggunakan `whatsapp` dan `username`.
   - Belum memiliki fitur upload avatar kustom maupun seleksi galeri avatar preset faksi/persona Gunung Mas.
   - Belum menampilkan metrik reputasi, rating, lisensi faksi, dan level Saudagar.
2. **Kepatuhan Hukum & Legalitas (`/terms` & `/privacy`)**:
   - Belum tersedia rute publik dan halaman antarmuka Syarat & Ketentuan (ToS) dan Kebijakan Privasi (UU PDP No. 27/2022).
   - Penjelasan status Koin Perak (utility internal token) dan Koin Emas (reward poin loyalitas) belum terformalisasi di antarmuka publik.
3. **Pusat Bantuan & FAQ Interaktif (`/help`)**:
   - Belum ada direktori FAQ interaktif pencarian instan dan jalur eskalasi bantuan langsung (Konsultasi Saudagar & WhatsApp Support).
4. **Preferensi Kuki (`CookieConsentBanner.tsx`)**:
   - Belum ada floating banner persetujuan kuki esensial vs opsional dengan status penyimpanan di `localStorage`.
5. **Keamanan Akun & Manajemen Sesi**:
   - Driver sesi database (`SESSION_DRIVER=database`) telah aktif dan tabel `sessions` tersedia di PostgreSQL, namun antarmuka untuk melihat sesi aktif lintas perangkat dan fitur *Revoke/Kill Other Sessions* belum dibuat.
6. **Harmonisasi Guardrails (Sesuai `.antigravity/id.json`)**:
   - Seluruh mutasi profil, upload media, dan terminasi sesi wajib dikemas dalam `ProfileService` (Pola *Skinny Controller, Fat Service* - GR-003).
   - Validasi file MIME type ketat untuk mencegah eksekusi skrip berbahaya (GR-001).

---

## 2. Arsitektur Solusi & Dekomposisi Modul

```
piticoin.id/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ProfileController.php          <- Refactor: Skinny controller delegating to Service
│   │   │   ├── HelpCenterController.php       <- NEW: Controller untuk /help & /faq
│   │   │   ├── LegalController.php            <- NEW: Controller untuk /terms & /privacy
│   │   │   └── SessionSecurityController.php  <- NEW: List & Revoke active sessions
│   │   └── Requests/
│   │       ├── ProfileUpdateRequest.php       <- Fix: Ganti email dengan name/whatsapp/sfx
│   │       └── AvatarUploadRequest.php        <- NEW: Validasi upload gambar (MIME, max 2MB)
│   └── Services/
│       ├── ProfileService.php                 <- NEW: Logika bisnis profil, avatar crop/store, faksi
│       └── SessionSecurityService.php         <- NEW: Query tabel sessions & Auth::logoutOtherDevices
├── database/migrations/
│   └── 2026_09_13_000001_add_avatar_and_preferences_to_users.php <- NEW: avatar_url & audio_sfx
├── resources/js/
│   ├── Components/
│   │   ├── CookieConsentBanner.tsx            <- NEW: Glassmorphism cookie banner
│   │   ├── NotificationDrawer.tsx             <- NEW: Drawer notifikasi aktivitas & WebPush scaffold
│   │   └── AvatarPickerModal.tsx              <- NEW: Modal galeri preset persona & custom upload
│   └── Pages/
│       ├── Help/
│       │   └── Index.tsx                      <- NEW: Searchable FAQ & CS escalation
│       ├── Legal/
│       │   ├── Terms.tsx                      <- NEW: Syarat & Ketentuan Layanan
│       │   └── Privacy.tsx                    <- NEW: Kebijakan Privasi UU PDP 2022
│       └── Profile/
│           ├── Edit.tsx                       <- Redesign: Full Obsidian-Gold Dark Theme
│           └── Partials/
│               ├── UpdateProfileInformationForm.tsx <- Redesign: Persona, Faksi, WA, Rating
│               ├── AvatarCustomizer.tsx             <- NEW: Visual avatar selector
│               ├── SessionManagerForm.tsx           <- NEW: List device aktif & revoke button
│               └── AudioPreferencesForm.tsx         <- NEW: SFX Misi & KOA switch
```

---

## 3. Rencana Implementasi Bertahap (Phased Roadmap)

### Fase 1: Skema Database & Service Layer Backend
1. **Migration**:
   - Tambahkan `avatar_url` (nullable string) dan `audio_sfx_enabled` (boolean default true) pada tabel `users`.
2. **ProfileService & SessionSecurityService**:
   - `ProfileService::updateAvatar()`: Memproses upload file (resize/crop square, simpan di disk public `avatars/`, sanitasi MIME) atau menetapkan string identifier preset (contoh: `preset:saudagar_pesisir`).
   - `ProfileService::updatePreferences()`: Menyimpan preferensi audio SFX & nama pengguna.
   - `SessionSecurityService::getActiveSessions()`: Mengambil data dari tabel `sessions`, membedakan sesi saat ini (`current_session`) dari IP address dan User Agent.
   - `SessionSecurityService::revokeOtherSessions()`: Menjalankan `Auth::logoutOtherDevices($password)`.

### Fase 2: Legalitas & Kepatuhan UU PDP (Terms, Privacy, Cookie Banner)
1. **LegalController & Routes**:
   - Buat rute publik `/terms` dan `/privacy`.
2. **Halaman Terms (`resources/js/Pages/Legal/Terms.tsx`)**:
   - Status Koin Perak (Internal utility simulation token) & Koin Emas (Reward loyalti).
   - Loss disclaimer klausul staking escrow Gunung Mas.
   - Larangan Sybil Attack & manipulasi multi-akun WhatsApp.
   - Pemisahan entitas federasi Locomo.id (PitiCoin vs Unimart vs WaliKelas).
3. **Halaman Privacy (`resources/js/Pages/Legal/Privacy.tsx`)**:
   - Kepatuhan UU No. 27 Tahun 2022.
   - Hak subjek data (akses salinan data, koreksi, dan hak penghapusan/right to erasure).
4. **Komponen Cookie Banner (`CookieConsentBanner.tsx`)**:
   - Banner melayang di bagian bawah dengan glassmorphism (`glass-panel`).
   - Opsi: *Setujui Semua* (`all`), *Hanya Esensial* (`essential`), dan tautan ke `/privacy#cookies`.

### Fase 3: Pusat Bantuan & FAQ Interaktif (`/help`)
1. **HelpCenterController & Data Provider**:
   - Kategori FAQ:
     - *Perburuan Harta Karun & Staking* (Escrow, gas fee mikro 5%, Koin Perak & Emas).
     - *Ekspansi Jaringan* (Referral karavan, downline level 1 & 2, pendaftaran merchant).
     - *Dompet & Pencairan* (Pencairan koin emas ke rekening bank, deposit saldo).
     - *Akun & Keamanan* (WhatsApp OTP, lisensi Saudagar).
2. **Antarmuka Help Center (`resources/js/Pages/Help/Index.tsx`)**:
   - Pencarian instan teks FAQ.
   - Accordion interaktif animasi halus.
   - Tombol eskalasi: *"Konsultasi Saudagar Pembina"* (membuka chat internal) & *"WhatsApp Support Resmi"*.

### Fase 4: Redesign Halaman Profil Persona & Manajemen Sesi
1. **Transformasi Antarmuka `Profile/Edit.tsx`**:
   - Mengganti gaya putih menjadi tema Obsidian Gold Dark Glassmorphic.
   - Header profil menampilkan kartu identitas: Avatar, Nama, Username, Faksi (Nisnas/Human/Android), Lisensi (Petualang/Saudagar), Level & XP Bar, dan Bintang Rating/Reputasi.
2. **Avatar Customizer**:
   - Tab 1: Galeri Preset Faksi (Saudagar Pesisir, Penjelajah Gunung, Pendeka Silat, Pedagang Pasar).
   - Tab 2: Upload Gambar Kustom (JPG, PNG, WebP max 2MB).
3. **Session Manager (`SessionManagerForm.tsx`)**:
   - Menampilkan daftar perangkat aktif yang login ke akun pengguna (Browser, OS, IP, Waktu Terakhir).
   - Tombol *"Keluarkan dari Semua Perangkat Lain"*.
4. **Preferensi Audio SFX & Keamanan Kata Sandi**:
   - Toggle audio efek ekspedisi.
   - Form ganti kata sandi berarsitektur aman.

### Fase 5: Modul Masa Depan (Future-Proof Integration)
1. **WebPush Notification Scaffold**:
   - Service worker listener (`public/sw.js`) dan tombol aktifkan notifikasi di header.
2. **P2P Random Mission Verification (5 Pengguna Acak)**:
   - Menyiapkan arsitektur konsensus verifikasi misi ekspansi luar jaringan oleh 5 pengguna acak dengan pembagian mikro-insentif perak.
3. **Integrasi Pasar Lelang Digital (`/auction`)**:
   - Memastikan navigasi menuju pasar lelang dapat diakses secara intuitif dari header dan help center.

---

## 4. Matriks Validasi Keamanan (SecureCoder & Guardrails)
- **File Upload Security**: Cek ketat MIME types (`image/jpeg`, `image/png`, `image/webp`), generate nama file UUID acak, jangan gunakan nama asli dari pengguna untuk mencegah path traversal.
- **Session Revocation**: Wajib meminta verifikasi kata sandi sebelum memutus sesi perangkat lain.
- **Data Protection**: Nomor WhatsApp dan data personal dilindungi sesuai klausul privasi UU PDP 2022.
