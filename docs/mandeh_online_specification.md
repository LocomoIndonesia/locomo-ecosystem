# Spesifikasi Lengkap & Dokumentasi Arsitektur: Mandeh.Online v2

Dokumen spesifikasi ini berfungsi sebagai panduan arsitektur sistem, alur logika bisnis, skema database, dan interaksi antarmuka untuk pengembangan platform **Mandeh.Online v2**. Sistem ini mengusung filosofi **Agent-First (Generative UI)** dan dirancang sebagai ekosistem sosial pariwisata terpadu (*social tourism ecosystem*).

---

## 🧭 1. Arsitektur Ekosistem & Filosofi Antarmuka

### A. Filosofi Agent-First (Generative UI)
Mandeh.Online v2 tidak mengadopsi navigasi menu hierarkis konvensional yang kaku. Sebagai gantinya, sistem memadukan teknologi AI/asisten aktif dengan antarmuka yang dinamis:
- **Generative UI (Contextual Cards)**: Halaman depan dan dashboard dimuat secara adaptif menggunakan komponen responsif (*Response Cards*). Sistem mendeteksi profil, peran, lokasi, dan aktivitas pengguna, lalu meng-generate antarmuka tugas, survei, atau widget interaktif yang dibutuhkan secara instan tanpa mematikan alur pengguna (single page/inline experience).
- **Conversational Actions**: Pengguna dapat berinteraksi dengan asisten untuk memicu aksi sistem (seperti check-in, pengiriman laporan, atau pengisian jajak pendapat).

### B. Diagram Arsitektur Data Lokal
Seluruh kontrol data, autentikasi, level, dan poin reputasi dikelola secara mandiri di database lokal Mandeh.Online v2 (tidak menggunakan SSO eksternal).

```
                  ┌─────────────────────────────────────────┐
                  │            Client Application           │
                  │             mandeh.online.v2            │
                  │     (Local Database & Auth Engine)      │
                  └──────┬───────────────────────────┬──────┘
                         │                           │
                         ▼                           ▼
        ┌────────────────────────────────┐   ┌────────────────────────────────┐
        │     Armada Darat & Laut        │   │          Armada Udara          │
        │  - Kuota: Maks 50 Anggota      │   │  - Kuota: Maks 100 Anggota     │
        │  - Check-in GPS (Daftar Tugas) │   │  - Virtual Link & Livestream   │
        └───────────────┬────────────────┘   └───────────────┬────────────────┘
                        │                                    │
                        └───────────────┬────────────────────┘
                                        ▼ (Points Reputasi ≥ 80)
                  ┌─────────────────────────────────────────┐
                  │       Graduation Gate & Muscab Voting   │
                  │   (AES-256-CBC Encrypted & Anonymous)   │
                  └─────────────────────────────────────────┘
```

---

## 👥 2. Peran Pengguna & Struktur Keanggotaan

Sistem membagi pengguna ke dalam beberapa peran utama dengan hak akses dinamis yang dapat diatur oleh **Admin Manager**:

1. **Pengguna Terdaftar (Wisatawan/Warga)**: Akun individu biasa setelah mendaftar secara lokal. Dapat mengakses forum pariwisata, melihat profil bisnis, serta memberikan interaksi (review/rating).
2. **Calon Anggota (Vanguard)**: Pengguna terverifikasi yang mendaftarkan diri ke program pelatihan LDK HPI (Pramuwisata Penggerak).
3. **Admin Manager (Community Orchestrator)**: Pengelola komunitas yang menentukan tugas harian, nilai poin/bonus, mendistribusikan reward/badge, menjadwalkan survei/voting, serta berwenang melantik anggota Vanguard menjadi pengurus resmi.
4. **Asosiasi Bisnis / Pelaku Pariwisata**: Pengguna terverifikasi yang terhubung ke entitas bisnis (misal: Sopir Boat, Pengelola Wisma, Staff Restoran).

---

## 🔄 3. Detail Alur Logika Bisnis (User Flow)

### A. Alur Menjadi Calon Anggota (Vanguard Candidate)
Untuk masuk ke ekosistem pelatihan HPI, pengguna harus melewati alur berikut:
1. **Registrasi Individu**: Calon pendaftar membuat akun secara lokal di `mandeh.online`.
   *   **Validasi Standar UI/UX Interaktif:** 
       *   Input nomor HP dan email dicek secara real-time ke database via endpoint `/check-uniqueness` untuk menjamin keunikan.
       *   Terdapat indikator kekuatan sandi visual (password strength indicator) dan penegakan kompleksitas password secara dinamis.
       *   Dilengkapi ikon mata (eye icon) untuk show/hide password serta pencocokan konfirmasi password yang divalidasi langsung.
   *   **Pemilihan Wilayah (Dropdown Bertingkat):** 
       *   Dropdown pilihan Provinsi $\rightarrow$ Kabupaten $\rightarrow$ Kecamatan $\rightarrow$ Kelurahan/Desa terintegrasi secara bertingkat (cascading).
       *   Menggunakan proxy internal `/api/wilayah/...` yang mem-bypass batasan CORS terhadap API eksternal `emsifa` dengan implementasi caching selama 30 hari demi meminimalkan load-time.
       *   Pencarian data dilakukan secara elegan melalui komponen dropdown Select2 kustom buatan sendiri berbasis Alpine.js.
   *   **Kode Pos (Opsional):** Field kode pos bersifat opsional (`nullable` baik di skema database maupun backend validation) dikarenakan tiadanya API publik kode pos yang stabil dan mencakup seluruh desa.
2. **Pengajuan Onboarding Vanguard**: Di dashboard gamifikasi, pengguna mengajukan diri menjadi peserta LDK dengan memilih asal (Lokal/Perantau) serta Divisi Armada:
   *   **Armada Darat & Laut (Maks. 50 Orang)**: Fokus pada aksi lapangan di area Mandeh.
   *   **Armada Udara (Maks. 100 Orang)**: Fokus pada publikasi digital, livestreaming, dan WiFi Hub.
3. **Verifikasi Admin**: Admin meninjau data pengajuan. Setelah disetujui:
   *   Sistem menghasilkan **Tracking ID** unik (`VGD-YYYYMMDD-[RANDOM_4_DIGIT]`).
   *   Role pengguna berubah menjadi `Vanguard`.
   *   Dashboard mereka akan otomatis meng-generate papan misi, survei terjadwal, dan check-in harian.

### B. Alur Partisipasi, Gamifikasi & Reputasi
Setiap aktivitas yang dilakukan oleh Calon Anggota akan memberikan imbal balik langsung:
- **Daftar Tugas & Check-in**:
  *   Pengguna dapat mengakses halaman **Daftar Tugas** (`/vanguard/tugas`) untuk melihat presensi harian (check-in GPS koordinat), daftar kegiatan/agenda aktif, serta checklist resmi kegiatan organisasi.
  *   Menyelesaikan tugas/check-in memberikan Poin Reputasi instan dan memperbarui Log Riwayat Aktivitas.
- **Leveling**: Akumulasi poin akan meningkatkan Level pengguna.
- **Koleksi Badge/Reward**: Event tertentu memberikan lencana khusus yang dipajang di profil gamifikasi.
- **Graduation Gate**: Ketika Poin Reputasi mencapai $\ge 80$, sistem menandai akun `is_vanguard_graduated = true`, mengaktifkan hak suara digital mereka untuk Muscab HPI.

### C. Alur Voting, Jajak Pendapat (Survey), & Pelantikan
Mengadopsi pola dari sistem **Smart-Citizen**, voting/survei digunakan sebagai wadah demokrasi komunitas:
1. **Broadcast Pemilihan & Survei**: Admin dapat merancang pemilihan/survei baru di panel kontrol dan memilih secara spesifik (*checklist*) grup pengguna mana saja yang menjadi target (misal: hanya `Armada Darat & Laut` atau hanya `Vanguard yang Telah Lulus`).
2. **Hitung Mundur (Countdown)**: Selama waktu mulai survei/kegiatan belum berjalan (`start_time > now()`), antarmuka pengguna akan menampilkan hitung mundur waktu. Admin masih bisa merubah data selama masa ini. Begitu `now() >= start_time`, data terkunci demi integritas suara.
3. **Privacy Wall & Enkripsi**: Pilihan suara dienkripsi dengan `AES-256-CBC` dan divalidasi tanda tangan digitalnya via `SHA-256` untuk mencegah manipulasi data serta menjamin anonimitas pemilihan ketua HPI.
4. **Laporan Pengawasan**:
   *   Calon Vanguard yang telah dilantik (inaugurated HPI members) berhak mengakses menu **Laporan Pengawasan** (`/vanguard/laporan-pengawasan`).
   *   Halaman ini berfungsi sebagai log pelaporan temuan atau informasi pengawasan penting dari lapangan yang segera harus diperhatikan oleh organisasi. Menggunakan kembali database `tic_revitalization_logs` dengan UI yang telah disesuaikan.
5. **Pelantikan (Inauguration)**: Berdasarkan hasil voting atau keputusan akhir, Admin dapat melakukan pelantikan resmi. Hal ini mengubah tipe pengguna dari `Vanguard` menjadi `Pengurus HPI` / `Anggota Resmi HPI` secara permanen.

---

## 🛡️ 4. Desain & Pembaruan Antarmuka (UI/UX Highlights)

### A. Layout Maritim Premium & Header
*   **Initials Avatar:** Jika pengguna belum memiliki foto avatar di tabel `users` (dideteksi otomatis), header akan memunculkan representasi avatar berupa inisial huruf dari nama depan dengan gradien warna maritim yang premium.
*   **Icon-Only Logout:** Teks "Keluar" atau "Logout" di header layout maritim, sidebar dashboard, dan dashboard warga dihilangkan dan diganti dengan icon logout murni untuk tampilan minimalis yang elegan.
*   **Identitas Pengguna:** Menampilkan nama panggilan (first name) di sisi kiri inisial avatar di header.

### B. Halaman Eksplorasi & Scan QR Code
*   **Scanner Placeholder:** Placeholder pencarian di bagian tengah halaman welcome diubah menjadi `"Cari atau scan QR Code..."` untuk memfasilitasi integrasi scan fisik.
*   **Modal Detail Entitas (Generative UI Overlay):**
    *   Mengklik atau memindai entitas bisnis/wisata membuka modal overlay glassmorphism yang responsif.
    *   Modal memuat: kategori bisnis (restoran, penginapan, dsb), rating bintang, data alamat, nomor telepon, serta tiga aksi interaktif: **Check-in**, **Ulasan**, dan **Rating**.
    *   Dilengkapi form ulasan interaktif dengan pilihan rating bintang langsung di dalam modal (tanpa pindah halaman) serta tautan cepat ke profil lengkap entitas.

### C. Dashboard Pelaku Wisata & Warga (UX & Map Picker Integration)
*   **Registrasi Bisnis Berbasis Modal**: Menghapus form pendaftaran bisnis inline dari dashboard utama Pelaku Wisata dan Warga Lokal. Tampilan dashboard disederhanakan dengan hanya menyajikan tabel/grid daftar bisnis serta tombol pemicu **"Tambah Bisnis Baru"** untuk memunculkan modal dialog pendaftaran (`showRegModal`).
*   **Peta Interaktif Leaflet (Coordinate Picker)**: Mengintegrasikan Leaflet map picker (`#map-picker`) di dalam modal registrasi. Koordinat lintang dan bujur tidak lagi dimasukkan secara manual, melainkan di-set otomatis saat pengguna mengeklik lokasi pada peta atau menyeret marker penunjuk lokasi.
*   **Deteksi Geolocation Otomatis**: Tombol **"Gunakan GPS Saya"** memanfaatkan browser HTML5 Geolocation API untuk mendeteksi koordinat aktual perangkat pengguna secara langsung, memposisikan penunjuk peta ke titik tersebut, dan memperbarui input koordinat.
*   **Input Koordinat Read-Only**: Kolom input untuk `lat` dan `lng` diatur menjadi `readonly` untuk memastikan data posisi GPS diverifikasi oleh peta dan sistem perangkat, menghindari kesalahan ketik manual dari pengguna.
*   **Penyelarasan Ukuran Peta (Invalidate Size)**: Menggunakan Alpine.js `$nextTick` dan fungsi `setTimeout` untuk memanggil `mapPicker.invalidateSize()` segera setelah modal terbuka guna mencegah ubin peta (tiles) pecah atau tidak memuat akibat container yang awalnya berukuran nol.

---

## 💼 5. Arsitektur Data Pelaku Wisata: Hybrid Dynamic-JSON & Sistem Referral

Untuk mewujudkan ekosistem sosial pariwisata yang kaya namun tetap ramping saat fase MVP, sistem memisahkan pendaftaran individu dari entitas bisnis, dengan struktur relasi dinamis berbasis JSON:

```
    ┌──────────────┐         1 : N         ┌────────────────┐
    │  Individual  ├──────────────────────>│  Entity        │
    │  User Table  │ (Owner Association)   │  (Business)    │
    └──────┬───────┘                       └───────┬────────┘
           │ (Referral / Upline Link)              │
           ├───────────────────────────────────────┤ (Referral Link)
           │                                       │
           ▼                                       ▼
    [upline_id (User)]                      [upline_id (User)]
```

### A. Mekanisme Pendaftaran Bisnis & Registrasi Otomatis (Referral)
1. **Pendaftaran Bisnis Terintegrasi**: Pengguna terdaftar dan terverifikasi dapat menambahkan bisnis baru (`Entity`).
2. **Registrasi Otomatis Pengguna Baru**: Saat mendaftarkan bisnis, pembuat bisnis wajib menentukan siapa Pemilik atau Pengemudi bisnis tersebut. Jika pemilik/pengemudi tersebut belum terdaftar di sistem:
   *   Pengguna pembuat bisnis mengisi data pendaftaran untuk pengguna baru tersebut (Nama, Email/No. Handphone, dll.).
   *   Sistem akan mendaftarkan pengguna baru tersebut secara otomatis sebagai pemilik/pengemudi bisnis.
   *   Pengguna baru yang didaftarkan secara otomatis akan memiliki `upline_id` yang mengarah ke ID pengguna pembuat bisnis tersebut (Sistem Referral).
3. **Penautan Referral Bisnis**: Bisnis (`Entity`) yang didaftarkan juga akan menyimpan `upline_id` pembuat bisnis tersebut untuk melacak kontribusi referral komunitas pariwisata.

### B. Generasi QR Code Bisnis
- Setiap kali entitas bisnis baru berhasil didaftarkan, sistem akan otomatis menghasilkan **QR Code** unik berdasarkan `qr_token` bisnis tersebut.
- QR Code ini digunakan untuk penanda fisik (misal: ditempel di boat, café, atau penginapan) agar memudahkan wisatawan memindai kode saat check-in atau melakukan penilaian (*interaction*).

### C. Menu Dashboard Upline: "Bisnis (Didaftarkan & Dimiliki)"
- Pengguna yang bertindak as *Upline* (pembuat bisnis/pendaftar pemilik bisnis lain) memiliki akses khusus ke menu **Bisnis** di dashboard mereka.
- Menu ini menampilkan:
  - Seluruh bisnis yang mereka daftarkan sendiri beserta detail pemiliknya (downline).
  - Bisnis yang mereka miliki secara langsung.
  - Gambar QR Code dari masing-masing bisnis yang dapat diunduh atau ditampilkan langsung.

---

## 📂 6. Skema Database Lokal

### A. Tabel Pengguna & Keanggotaan
- `users`: `id` (UUID), `name`, `email`, `password_hash`, `role` (enum), `level` (int), `total_points` (int), `trust_score` (int), `device_fingerprint` (string), `upline_id` (UUID, nullable, fk users.id), `timestamps`.
- `vanguard_participants`: `id` (UUID), `user_id` (foreign key), `tracking_id` (string), `origin` (enum: lokal/perantau), `division` (enum: darat_laut/udara), `is_vanguard_graduated` (boolean), `is_inaugurated` (boolean).

### B. Tabel Misi & Presensi harian
- `vanguard_missions`: `id`, `admin_id` (foreign key), `title`, `description`, `points_reward`, `bonus_points`, `badge_id` (nullable), `start_time` (timestamp), `end_time` (timestamp).
- `vanguard_checkins`: `id`, `user_id` (fk), `gps_lat`, `gps_lng`, `status` (verified/rejected), `method` (enum: gps/virtual), `checked_in_at` (timestamp).
- `vanguard_mission_reports`: `id`, `mission_id` (fk), `user_id` (fk), `report_text`, `proof_url`, `status` (pending/approved/rejected), `reviewed_at`.
- `tic_revitalization_logs`: `id`, `user_id` (fk), `title`, `reporter_name`, `description`, `priority` (enum), `status` (enum), `created_at`, `updated_at`. *(Tabel ini digunakan kembali untuk menampung Laporan Pengawasan)*.

### C. Tabel Voting & Survey Bertarget
- `vanguard_surveys`: `id`, `title`, `description`, `target_roles` (array / json checklist), `start_time`, `end_time`.
- `vanguard_votes`: `id`, `survey_id` (fk), `encrypted_ballot` (text), `signature_hash` (string), `created_at`. (Tanpa kolom `user_id` untuk privasi penuh).
- `vanguard_vote_participations`: `id`, `survey_id` (fk), `user_id` (fk), `voted_at`. (Hanya mencatat partisipasi agar user tidak memilih dua kali).

### D. Tabel Entitas & Asosiasi Bisnis
- `entities`: `id` (UUID), `owner_id` (fk users), `upline_id` (UUID, nullable, fk users.id), `name`, `slug`, `type` (enum), `description`, `price_range`, `lat`, `lng`, `qr_token`, `qr_code_path` (string, nullable), `timestamps`.
- `business_meta`: `id`, `entity_id` (fk entities), `payload_json` (json untuk menampung menu, promo, dll).
- `entity_associations`: `id`, `entity_id` (fk), `user_id` (fk), `role_in_business` (enum: driver, manager, staff).

---

## 🔬 7. Rencana Verifikasi (Verification Plan)

### A. Pengujian Otomatis (Automated Tests)
1. **Onboarding & Access Guard Test**: Memastikan hanya peserta HPI tervalidasi yang dapat mengakses `/vanguard/tugas` (Daftar Tugas) dan hanya anggota resmi terlantik (`is_inaugurated = true`) yang dapat mengakses `/vanguard/laporan-pengawasan` (Laporan Pengawasan).
2. **Referral Creation Test**: Menguji bahwa ketika mendaftarkan bisnis dengan pemilik baru, pengguna baru tersebut berhasil dibuat dengan `upline_id` yang benar.
3. **Upline Business Scope Test**: Menguji bahwa query menu "Bisnis" hanya memuat entitas yang dimiliki secara langsung atau yang di-referral-kan (`upline_id = current_user_id`).
4. **QR Code Auto Generation Test**: Memastikan ketika data `Entity` disimpan, sistem memicu pembuatan token QR unik dan path file gambarnya disimpan.
5. **Targeted Broadcast & Privacy Wall Test**: Memastikan voting/survei hanya ditampilkan kepada role penerima yang dicentang oleh admin, dan verifikasi bahwa tabel `vanguard_votes` tidak memiliki kaitan data langsung dengan tabel `users` (anonimitas).
6. **Countdown Mutability Lock Test**: Memastikan tugas atau survei terkunci rapat setelah hitung mundur selesai (`now() >= start_time`), dan mencegah admin melakukan modifikasi apa pun.
7. **Dashboard Render Test (Business Owner & Warga)**: Memastikan halaman dashboard Pelaku Wisata (`/dashboard/business`) dan Warga (`/dashboard/warga`) berhasil dimuat dan dirender dengan benar.

### B. Verifikasi Manual (Manual Walkthrough)
1. **Simulasi Registrasi Bisnis Upline**: Mengisi form pendaftaran armada boat baru -> memasukkan identitas driver baru -> memverifikasi bahwa pengemudi baru tersebut menerima email aktivasi dan memiliki tautan upline ke akun pembuat.
2. **Dashboard Bisnis Upline**: Masuk dengan akun upline -> mengakses menu "Bisnis (Didaftarkan & Dimiliki)" -> memeriksa daftar bisnis downline beserta tampilan QR Code bisnis tersebut.
3. **Verifikasi Peran Admin Manager**: Membuat tugas baru dengan bonus reputasi, menyetujui laporan presensi Vanguard, dan memverifikasi perolehan level-up di home UI Vanguard.
4. **Akses Tugas & Laporan Pengawasan**: Menguji pengiriman check-in harian berbasis GPS dan pengiriman temuan pengawasan baru, lalu memvalidasi kemunculannya di tabel log secara instan.
5. **Peta Interaktif & Lokasi GPS**: Menguji fungsionalitas peta Leaflet di dalam modal dialog dashboard dengan mengeklik peta untuk merubah koordinat, menggeser marker, serta menekan tombol "Gunakan GPS Saya" untuk verifikasi akurasi pendeteksian koordinat perangkat.
