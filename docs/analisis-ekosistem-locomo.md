# ANALISIS KOMPREHENSIF EKOSISTEM DIGITAL LOCOMO.ID
**Platform dan Infrastruktur Kedaulatan Digital Komunitas**
*Penyusun: AntiGravity AI (Agy)*  
*Tanggal: 10 Juni 2026*  

---

## 1. PENDAHULUAN & FILOSOFI SISTEM

Locomo OS merupakan sebuah infrastruktur kedaulatan digital terdistribusi yang dibangun dengan filosofi **Federated Architecture (Arsitektur Terfederasi)**. Sistem ini menolak sentralisasi data tunggal demi menghindari titik kegagalan tunggal (Single Point of Failure), belajar dari kerentanan infrastruktur data nasional. 

Setiap komunitas (nagari, RT/RW, kampus, kawasan wisata) memegang kedaulatan atas datanya sendiri melalui node/instance lokal yang saling terhubung menggunakan protokol standar ekosistem Locomo. Seluruh layanan dalam ekosistem ini dirancang untuk **mengubah modal sosial informal yang ada di masyarakat menjadi data digital yang terstruktur, tepercaya, dan bernilai balik bagi komunitas.**

---

## 2. INVENTARISASI WORKSPACE & STRUKTUR AKTUAL

Saat ini, workspace utama terletak pada direktori `/home/dev360_03/locomo.id`. Hasil pemetaan struktur direktori menunjukkan pembagian berikut:

```
/home/dev360_03/locomo.id/
├── docs/                      # Pusat dokumentasi spesifikasi & log audit
│   ├── dev-logs/
│   ├── sistem.operasi/        # Dokumen acuan filosofis dan fungsional dari User
│   ├── current-spec.md
│   ├── issue-log.md
│   ├── uat_governance_v1_1_9.md
│   └── uat_locomo_v1_1_8.md
├── locomo-web/                # Skeleton proyek Laravel 13 (Fresh Installation)
├── mandeh.id/                 # [Rencana] Wadah modul Identity Layer (Stateless SSO) - Kosong
├── mandeh.online/             # [Rencana] Target migrasi instansi pertama wisata - Kosong
└── tapak.pandeka/             # [Rencana] Wadah platform reputasi & mading pemuda - Kosong
```

### Catatan Penting pada Workspace:
1. **`locomo-web`**: Berisi skeleton Laravel 13 yang bersih dengan dependensi bawaan (Laravel Framework, Tinker, Pint, Sail, dan Pest). Belum terdapat logika bisnis atau rute khusus selain bawaan framework.
2. **`mandeh.online` / `mandeh.id` / `tapak.pandeka`**: Saat ini masih berupa folder kosong yang siap diisi/diintegrasikan.

---

## 3. ANALISIS SOURCE PROJECT EKSTERNAL (`mande-project`)

Proyek `mandeh.online` sebelumnya telah dibuat di luar workspace ini, tepatnya di `/home/dev360_03/mande-project`. Berdasarkan analisis mendalam terhadap struktur dan kode di direktori tersebut, berikut temuan teknisnya:

### A. Struktur Arsitektur Monolitik Modular
Proyek ini mengadopsi pola **Modular Monolith** dengan membagi logika bisnis ke dalam sub-folder `app/Modules/`. Beberapa modul utama yang sudah ada:
* **`Auth`**: Manajemen login menggunakan JWT (`PHPOpenSourceSaver\JWTAuth`).
* **`Business`**: Logika pengelolaan usaha wisata lokal.
* **`Complaint` & `Review`**: Manajemen ulasan multi-dimensi dan penanganan keluhan warga/wisatawan.
* **`QR`**: Pengelolaan kode QR yang bertindak sebagai gerbang fisik-digital.
* **`Emergency` & `Info`**: Penanganan info darurat dan sebaran fasilitas publik.

### B. Pemetaan Basis Data (Migrations)
Skema basis data `mande-project` memiliki beberapa tabel krusial yang saling berhubungan:
1. **`users`**: Menggunakan UUID sebagai Primary Key. Menyimpan atribut autentikasi lokal, `phone_number` (untuk notifikasi WhatsApp), `role` (`warga`, `wisatawan`, `business_owner`, `layanan_publik`, `sponsor`), `trust_score` (default: 50), dan `device_fingerprint`.
2. **`entities`** (sebelumnya `businesses`): Menyimpan data bisnis terdaftar dengan atribut `owner_id`, `name`, `slug`, `type` (`restoran`, `hotel`, `transportasi`, dll), `lat`/`lng` (koordinat geospasial), dan `qr_token` unik.
3. **`interactions`**: Mencatat check-in, ulasan, atau laporan keluhan. Memiliki field `rating_score`, `payload_json` (ulasan multi-dimensi), `gps_lat`/`gps_lng`, `status` (`pending_verification`, `public`, `rejected`), dan `device_fingerprint`.
4. **`moderation_queue`**: Menampung ulasan dengan status `pending_verification` yang memerlukan persetujuan pemilik bisnis atau tokoh pemuda setempat.
5. **`business_meta`**: Menyimpan katalog menu digital (`menu_json`), promosi (`promotions_json`), dan ringkasan transaksi (`transaction_summary`) khusus untuk bisnis berstatus Mitra (Partner).
6. **`forum_questions`**: Log forum Q&A terstruktur ("Ask Local").

### C. Logika Validasi Proximity ("Haversine Presence Check")
Salah satu fitur unggulan pada `InteractionController` lama adalah pengecekan jarak ulasan secara geospasial menggunakan `HaversineService`.
* Jika koordinat pengirim ulasan berjarak **$\le$ 100 meter** dari koordinat fisik `entity`, status ulasan otomatis diset menjadi `public` (Verified Presence).
* Jika jarak **> 100 meter**, ulasan ditandai sebagai `pending_verification` dan dilemparkan ke tabel `moderation_queue`.
* Jika GPS dimatikan, ulasan ditandai sebagai `Unverified` (Potensi Fraud) dan disembunyikan dari publik sebelum dimoderasi.

---

## 4. DETIL SPESIFIKASI LAYANAN EKOSISTEM LOCOMO

Berdasarkan dokumen internal pada `docs/sistem.operasi/`, berikut adalah rancangan spesifikasi rinci untuk setiap subsistem dalam ekosistem `locomo.id`:

```
┌──────────────────────────────────────────────────────────────────┐
│                       LOCOMO OS (KERNEL)                         │
│             Micro Frontend Canvas • Plugin Registry              │
├──────────────────────────────────────────────────────────────────┤
│                       MANDEH.ID (AUTH)                           │
│        Stateless JWT IDP • QR Identity Single Source of Truth    │
└──────────────┬───────────────────┬───────────────────┬───────────┘
               ▼                   ▼                   ▼
      ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
      │  MANDEH.ONLINE  │ │  TAPAK.PANDEKA  │ │  SMART-CITIZEN  │
      │  Wisata & Usaha │ │ Reputasi Pemuda│ │   Demokrasi RT  │
      └─────────────────┘ └─────────────────┘ └─────────────────┘
```

### 4.1 Locomo OS (Kernel Engine)
* **Peran**: Lapisan fondasi ("kernel") yang bersifat agnostik dan tidak terlihat langsung oleh pengguna akhir.
* **Arsitektur**: Menggunakan **Micro Frontend** (misal via Webpack Module Federation) dengan shell aplikasi terpadu berbentuk **Global Canvas**.
* **Fitur Utama**:
  * **Chat as OS**: Perintah bahasa alami disalurkan ke mesin deteksi intensi (*Intent Detection*) untuk memanggil modul layanan yang relevan secara dinamis.
  * **Generative UI**: Komponen antarmuka (misal *Response Card*) dimuat secara instan tanpa perlu memindah-mindahkan halaman web konvensional.
  * **Plugin Registry**: Pendaftaran plugin menggunakan standar kontrak manifest JSON yang ketat (`plugin_id`, `permissions`, `ui_components`, `chat_intents`, `api_base`).

### 4.2 Mandeh.ID (Identity Layer)
* **Peran**: Pusat otentikasi tunggal (SSO) bagi seluruh layanan di ekosistem Locomo.
* **Mekanisme**: **Stateless JWT Authentication**. Identity Provider (Mandeh.ID) menerbitkan *Access Token* (masa aktif 15 menit) dan *Refresh Token* (masa aktif 30 hari). Setiap instance layanan memegang Public Key Mandeh.ID dan memverifikasi token secara lokal tanpa perlu memanggil server pusat berulang kali (tahan terhadap downtime).
* **QR Identity**: Penyatuan profil fisik-digital menggunakan QR Code untuk memicu alur geospasial instan pada merchant/lokasi.

### 4.3 Mandeh.Online (Instance #1: Wisata & Reputasi)
* **Peran**: Model uji coba (*Proof of Concept*) pertama untuk kawasan wisata Mandeh, Pesisir Selatan.
* **Fitur Inti**:
  * **Ground Truth Rating**: Ulasan dinilai per kategori (Transparansi Harga, Kualitas Layanan, Keamanan).
  * **Live Pulse Stream**: Menggantikan global chat dengan feed linimasa aktivitas check-in dan ulasan yang terikat dengan pemindaian QR fisik.
  * **Dispute & Moderation**: Penanganan fraud ulasan melalui koordinasi tokoh pemuda setempat.
  * **Diferensiasi Layanan**: Akun Bisnis Reguler (profil pasif gratis) vs Bisnis Mitra (halaman interaktif, katalog menu lengkap, banner promosi dinamis).

### 4.4 HakCampus.id (Pustaka Ilmu & LMS)
* **Peran**: Kedaulatan distribusi karya akademik (kreator-first).
* **Fitur Inti**:
  * **Multi-Format Publisher**: Mengintegrasikan teks, slide, audio, video penjelasan, dan kuis ke dalam format bab terpadu.
  * **Sewa Berbasis Waktu**: Akses berbayar berjangka waktu (7, 30, 90 hari) untuk mengurangi hambatan biaya bagi pelajar.
  * **DRM Gradual**: Watermarking digital identitas penyewa di berkas unduhan pada Fase 1, diikuti enkripsi penuh di Fase 2.
  * **Contextual LMS**: LMS yang menyusun struktur konten otomatis menyesuaikan RPP dan silabus nyata yang digunakan guru di kelas sekolah setempat.
  * **Strategi Biaya AI**: 80% fitur operasional dijalankan tanpa AI/LLM. 15% bantuan konsep sederhana menggunakan LLM murah (GPT-4o-mini/Haiku), dan 5% analisis mendalam menggunakan LLM besar dengan caching ketat.

### 4.5 Tapak Pandeka (Reputasi & Mading Pemuda)
* **Peran**: Rekam jejak portofolio dan kontribusi pemuda yang divalidasi komunitas (bukan self-claim).
* **Fitur Inti**:
  * **Sistem Gamifikasi**: Akumulasi Poin, Lencana (Badges) berdasarkan keterampilan nyata, dan Leveling (`Anak Nagari` $\rightarrow$ `Penggerak` $\rightarrow$ `Pembina` $\rightarrow$ `Pandeka Nagari`).
  * **Point Decay**: Penurunan poin otomatis jika tidak aktif selama 90 hari demi menjaga aktualitas data kompetensi.
  * **Misi Mikro**: Penugasan berdurasi 5 menit (misal: melaporkan fasilitas publik rusak, input UMKM) untuk memperbarui data nagari.
  * **Mode Antarmuka**: Adaptasi UI berdasarkan kategori umur (Remaja 13-15, Pelajar 16-18, Pemuda Aktif 19-25, Pembina 26-30).

### 4.6 Smart-Citizen (Demokrasi & Tata Kelola)
* **Peran**: Modul pemilihan ketua RT/RW dan tata kelola warga berbasis digital di Galaxy Pulo Sirih Timur, Bekasi.
* **Fitur Inti**:
  * **The Privacy Wall**: Pemisahan database fisik antara identitas terverifikasi pemilih dengan data pilihan suara untuk menjaga kerahasiaan penuh.
  * **WhatsApp Nudge**: Pengiriman pengingat berkala ke nomor warga yang belum mencoblos secara otomatis setiap jam selama masa pemilu.
  * **Verifikasi Tanda Tangan**: Pencocokan DPT internal panitia dengan validasi tanda tangan elektronik warga pada saat pendaftaran.

### 4.7 PitiCoin (Urat Nadi Finansial)
* **Peran**: Lapisan transaksi ekonomi tertutup (Closed-Loop) di dalam ekosistem.
* **Fase Rilis**:
  * **Fase 0**: Poin loyalitas ekosistem (hadiah ulasan Mandeh, penyelesaian misi Tapak Pandeka).
  * **Fase 1**: Dompet digital terkelola untuk transaksi sewa materi HakCampus atau pembayaran iuran RT Smart-Citizen (memerlukan regulasi PJSP Bank Indonesia).
  * **Fase 2**: Ekonomi komunitas tertutup yang matang (transfer antar-warga, pembayaran merchant fisik lokal).

---

## 5. STRATEGI MIGRASI & INTEGRASI KODE

Untuk memindahkan dan merapikan sistem dari `mande-project` ke dalam ekosistem yang terstandarisasi di workspace saat ini, langkah-langkah strategis berikut harus diterapkan:

### A. Penyesuaian Sistem Autentikasi (Migrasi ke Mandeh.ID)
1. **Pemisahan Auth Server**: Logika pendaftaran dan login lokal pada `AuthController` di `mande-project` harus dipindahkan menjadi bagian dari service independen di `mandeh.id`.
2. **Stateless Guard**: Konfigurasi `config/auth.php` di proyek `mandeh.online` baru harus diubah menggunakan driver custom JWT yang hanya memverifikasi tanda tangan token menggunakan Public Key Mandeh.ID. Model `User` tidak lagi menyimpan kata sandi jika masuk melalui Single Sign-On (SSO).

### B. Pembersihan & Standarisasi Kode (Standardisasi Locomo Signature)
* **Strict Typing**: Menambahkan deklarasi `declare(strict_types=1);` di setiap berkas PHP baru.
* **Restrukturisasi DB**: Menggunakan skema migrasi final hasil evolusi (Tabel `entities` menggantikan `businesses`, penggunaan kolom `device_fingerprint` dan tabel `business_meta`).
* **Modular Monolith Integrity**: Memisahkan kode endpoint API ke dalam folder modul yang rapi di bawah `app/Modules/Entity`, `app/Modules/Review`, dan `app/Modules/Moderation`.

### C. Refactoring View Layer ke Generative Canvas
* Mengubah tampilan dari template Blade tradisional `/prototypes` menjadi respons komponen dinamis (JSON state representation) agar kompatibel dengan React/Next.js frontend shell dari Locomo Kernel.

---

## 6. PETA JALAN PENGEMBANGAN EKOSISTEM (ROADMAP)

### A. Layanan Aktif saat ini / dalam Rencana Dekat:
1. **Mandeh.Online**: Wisata dan reputasi bisnis (Fase POC).
2. **HakCampus.id & LMS**: Penerbitan digital karya akademik.
3. **Tapak Pandeka**: Reputasi pemuda & mading sekolah.
4. **Smart-Citizen**: Demokrasi RT/RW.
5. **PitiCoin**: Sistem transaksi poin/ekonomi.

### B. Rencana Ekspansi Layanan (Lini Kehidupan Belum Tersentuh):
* **Pertanian & Perikanan**: Platform transparansi harga komoditas pertanian real-time tingkat nagari untuk menyeimbangkan posisi tawar petani terhadap tengkulak, disertai Buku Tani Digital.
* **Kesehatan Komunitas**: Kartu Sehat Digital tingkat keluarga yang terhubung dengan posyandu lokal untuk pemantauan gizi, pengingat vaksin otomatis via WA.
* **Infrastruktur & Laporan Warga**: Eskalasi bertingkat pelaporan masalah fasilitas fisik (jalan, jembatan, got) dengan SLA respons terikat.
* **Kedaruratan & Kebencanaan**: Peta jalur evakuasi digital nagari pesisir pantai dan check-in keselamatan mandiri pasca-bencana.
* **Warisan Budaya & Arsip Nagari**: Dokumentasi digital sejarah nagari, silsilah keluarga, dan pengetahuan adat yang diperbarui secara partisipatif oleh pemuda setempat (Nagari Chronicle).

---

## 7. PRINSIP KERJA & KESIMPULAN

Setiap pengembangan dalam ekosistem Locomo harus tunduk pada tiga aturan emas:
1. **Proof of Concept Sebelum Skala Besar**: Mulai dari penyelesaian masalah kecil yang nyata di lapangan (contoh: anti-nembak harga warung di Mandeh).
2. **Satu Batu Setiap Waktu**: Jangan membangun seluruh ekosistem sekaligus. Pastikan integrasi dasar `Mandeh.ID` dan `Mandeh.Online` berjalan sempurna sebelum membuka keran fitur HakCampus atau Tapak Pandeka.
3. **Replikabilitas sebagai DNA**: Logika verifikasi proximity dan reputasi didesain agar mudah disalin ke nagari atau wilayah baru di Indonesia hanya dengan mengubah konfigurasi parameter lokasi dan nama komunitas.

---
*Dokumen ini disusun untuk menyelaraskan pemahaman taktis dan strategis tim pengembang ekosistem Locomo.*
