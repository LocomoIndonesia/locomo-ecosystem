# Rencana Rancang Bangun Arsitektur Sistem Ekosistem Terintegrasi PitiCoin.id
**Dokumen Arsitektur Komprehensif: Pemetaan Hub Dashboard, Mitigasi Kelemahan Bisnis, Baitul Mal, Lelang Digital, Reputasi Multidimensi, dan Konsensus Juri Misi**

*Versi: 2.0.0-Enterprise*  
*Tanggal: 14 September 2026*  
*Arsitek: Locomo AI Agent OS (Antigravity CCC Engine)*  
*Dokumen Acuan:*
- `docs/piticoin.id/Analisis-Kelemahan-dan-Pengembangan-Bisnis-PitiCoin.md`
- `docs/piticoin.id/Rancangan-Fitur-Tambahan-PitiCoin.md`
- `docs/piticoin.id/Rencana-Implementasi-Fitur-Baitul-Mal-Dan-Ekosistem-PitiCoin.md`
- `docs/piticoin.id/Rencana-Implementasi-Fitur-Pendukung-Standar-PitiCoin.md`

---

## 1. Latar Belakang & Identifikasi Kesenjangan (Architectural Gaps)

Berdasarkan evaluasi terhadap implementasi kode terkini dan umpan balik pengguna:
1. **Pemisahan Navigasi & Kesenjangan Dashboard (Dashboard Disconnect)**:
   - Modul yang telah diimplementasikan (Profil Persona, Manajemen Sesi Multi-Perangkat, Pusat Bantuan/FAQ, serta Legalitas Syarat Ketentuan dan Kebijakan Privasi UU PDP) belum terpetakan dengan baik pada antarmuka utama (`Dashboard.tsx`).
   - Dashboard saat ini masih berupa halaman tunggal linier tanpa navigasi *Hub App Bar*, dermaga menu cepat (*Quick Access Hub*), dan *Footer* standar kepatuhan hukum.
2. **Kelemahan Bisnis & Kerentanan Eksploitasi (Vulnerabilities)**:
   - *Inflasi Koin Emas & Perak*: Pemasukan koin dari Hourly Angpao dan hadiah misi belum diimbangi wadah serap moneter (*Treasury & Staking Sink*).
   - *Sybil Attack & Verifikasi Malas*: Sistem verifikasi acak 5 orang rentan disusupi peternak akun palsu atau pengguna yang sekadar klik valid tanpa cek bukti.
   - *Manipulasi Reputasi (Circle-Jerk Farming)*: Perlunya algoritma redaman (*decay algorithm*) agar sesama akun tidak bisa saling dongkrak reputasi.
   - *Pasar Lelang Dingin (Cold-Start)*: Perlunya lelang Dutch (harga turun bertahap) dan Smart Escrow anti-sniping untuk aset digital/fisik UMKM.

---

## 2. Arsitektur Solusi 5-Layer PitiCoin.id

```
+-----------------------------------------------------------------------------------+
|                        1. PRESENTATION & HUB LAYER (INERTIA/REACT)                |
|  [Top App Bar] -> [Quick Access Hub Dock] -> [Dynamic Mountain Ticker & Escrow]   |
|  [Dashboard Hub Navigation: Profile | Help/FAQ | Baitul Mal | Auction | Legal]    |
+-----------------------------------------------------------------------------------+
                                         |
+-----------------------------------------------------------------------------------+
|                     2. API GATEWAY & CONTROLLER ROUTING LAYER                     |
|  /dashboard  |  /profile  |  /help  |  /terms  |  /baitul-mal  |  /auction  | /pos|
+-----------------------------------------------------------------------------------+
                                         |
+-----------------------------------------------------------------------------------+
|                    3. DOMAIN SERVICE LAYER (FAT SERVICE PATTERN)                  |
|  +---------------------+  +---------------------+  +----------------------------+  |
|  |   BaitulMalService  |  |  ReputationService  |  |  MissionVerificationService|  |
|  +---------------------+  +---------------------+  +----------------------------+  |
|  +---------------------+  +---------------------+  +----------------------------+  |
|  |    AuctionService   |  |   WebPushService    |  |    DynamicGasFeeService    |  |
|  +---------------------+  +---------------------+  +----------------------------+  |
+-----------------------------------------------------------------------------------+
                                         |
+-----------------------------------------------------------------------------------+
|              4. INTEGRITY & SECURITY GUARDRAILS (GR-001 s/d GR-003)               |
|  - Zero-Sum Financial Ledger (Baitul Mal & Auction Smart Escrow)                  |
|  - Anti-Sybil Defense: Honeypot Validation Trap + Verifier Staking Slash          |
|  - Anti-Sniping Engine (Overtime +3 menit pada penawaran detik terakhir)          |
|  - Strict MIME & UU PDP 27/2022 Personal Data Cryptographic Masking              |
+-----------------------------------------------------------------------------------+
                                         |
+-----------------------------------------------------------------------------------+
|                    5. PERSISTENCE LAYER (POSTGRESQL 15 + CACHE)                   |
|  users | wallets | ledgers | baitul_mal_* | auctions | mission_verifications      |
+-----------------------------------------------------------------------------------+
```

---

## 3. Rincian Dekomposisi Modul & Alur Bisnis

### A. Dashboard Hub Navigation & App Mapping (Prioritas Langsung)
Mengubah `Dashboard.tsx` dari halaman statis menjadi **Command Center Hub**:
1. **Top Hub Bar (Header Terintegrasi)**:
   - Logo PitiCoin & Status Konektivitas Jaringan.
   - Status Identitas Pengguna (Avatar Persona, Tier Reputasi, Faksi).
   - Akses Cepat Tombol Profil, Notifikasi Drawer, Pusat Bantuan, dan Log Out.
2. **Feature Matrix Quick Access Grid (Dermaga Modul)**:
   - 🏛️ **Baitul Mal**: Kotak Amal & Staking Pengendali Inflasi.
   - ⚖️ **Pasar Lelang**: Bursa Nilai English/Dutch Auction.
   - 🛡️ **Juri Verifikasi**: Portal Konsensus 5 Verifikator & Misi Publik.
   - 🏪 **POS Kasir Saudagar**: Gateway Transaksi & QR Code Merchant.
   - 📜 **Pusat Bantuan & FAQ**: Direktori panduan, pencarian instan & WhatsApp CS.
   - 👤 **Profil Persona**: Kustomisasi avatar, preferensi audio SFX, dan manajemen sesi.
3. **Legal & Compliance Footer**:
   - Tautan terstruktur: Syarat & Ketentuan (`/terms`), Kebijakan Privasi UU PDP (`/privacy`), Regulasi Token Perak vs Emas, serta Lisensi Ekosistem Locomo.

---

### B. Modul Baitul Mal & Kas Sosial Koin Emas (Terkalibrasi Sesuai Arahan)

Halaman khusus publik/anggota (`/baitul-mal`) yang dirancang dengan prinsip **Transparansi Total (Akuntabilitas Moneter)** dan **Privasi Bermartabat (UU PDP No. 27/2022)**:

#### 1. Arsitektur Antarmuka Halaman Khusus (`/baitul-mal`)
Halaman ini memiliki 4 zona informasi utama:
1. **Statistik Kas Transparan (Financial Transparency Counter)**:
   - *Total Kas Terkumpul (All-Time)*: Akumulasi seluruh donasi Koin Emas yang masuk.
   - *Kas Siap Salur (Current Active Pool)*: Saldo Koin Emas yang saat ini tersedia di kas.
   - *Total Tersalurkan*: Akumulasi dana yang telah dinikmati oleh para mustahik.
   - *Jumlah Penerima Manfaat*: Total penjelajah/warga terbantu.
2. **Form Donasi/Sedekah Koin Emas Interaktif**:
   - Pilihan nominal cepat (100, 500, 1.000, 5.000, 10.000 Koin Emas) atau input kustom.
   - Input pesan doa/harapan (*Prayer & Blessing Message*).
   - Opsi visibilitas: *Tampilkan Inisial Masked* vs *Hamba Allah (Anonim Penuh)*.
3. **Live Ticker Donatur (Wall of Muhsinin) dengan Masking Otomatis**:
   - Menampilkan deretan kontribusi terkini secara *real-time*.
   - **Aturan Masking Privasi**:
     - Nama: Disamarkan secara otomatis (contoh: `Ahmad Pratama` $\rightarrow$ `Ah*** Pr*****`, atau `Hamba Allah`).
     - Nomor WhatsApp: Disamarkan (contoh: `081234567890` $\rightarrow$ `0812****7890`).
   - Dilengkapi fitur interaktif *"Aamiin-kan Doa"* oleh komunitas untuk mempererat ikatan sosial.
4. **Histori Periode Distribusi & Penerima Manfaat (Batch Distribution Transparency)**:
   - Dikelompokkan per **Periode Distribusi** (contoh: *Periode 01: Berkah Awal Musim Gunung Mas - September 2026*).
   - Menampilkan metadata periode: Tanggal penyaluran, total koin emas disalurkan, dan jumlah penerima.
   - **Tabel Daftar Penerima Manfaat per Periode**:
     - Nama Penerima (Dimasking demi martabat, misal: `Su***** - Faksi Manusia`).
     - Kategori Mustahik (contoh: *Penjelajah Pemula Kurang Saldo*, *Dhuafa Binaan Saudagar*, *Korban Bencana Ekspedisi*).
     - Jumlah Koin Emas yang diterima masing-masing mustahik.
     - Tanggal & Catatan Penyaluran.

#### 2. Skema Database Terkalibrasi
- `baitul_mal_pools`: Saldo kas aktif, total terkumpul, total tersalurkan.
- `baitul_mal_donations`:
  - `user_id` (foreign key ke users)
  - `amount_gold` (integer / bigint)
  - `prayer_message` (text, doa/harapan donatur)
  - `is_anonymous` (boolean, flag Hamba Allah)
  - `masked_name` & `masked_phone` (string ter-cache untuk render cepat)
- `baitul_mal_periods` (Periode Distribusi):
  - `period_name` (string, misal "Distribusi Batch #1 September 2026")
  - `distribution_date` (timestamp)
  - `total_gold_distributed` (bigint)
  - `total_beneficiaries_count` (integer)
  - `notes` (text)
- `baitul_mal_distributions`:
  - `period_id` (foreign key ke `baitul_mal_periods`)
  - `beneficiary_user_id` (foreign key ke users)
  - `beneficiary_category` (string enum: `BEGINNER_EXPLORER`, `DHUAFA_COMMUNITY`, `EMERGENCY_AID`)
  - `amount_gold` (bigint)
  - `masked_beneficiary_name` (string)
  - `disbursed_at` (timestamp)

#### 3. Logika Mutasi & Invariant Finansial
- **Debit Donatur**: `wallets.gold_balance` dideduct seketika dalam transaksi database (`DB::transaction`).
- **Kredit Baitul Mal**: `baitul_mal_pools.active_balance` dan `total_collected` diincrement.
- **Pencatatan Audit Ledger**: Dicatat di tabel `financial_ledgers` dengan referensi `BAITUL_MAL_DONATION`.
- **Eksekusi Penyaluran**: Pemotongan kas `baitul_mal_pools.active_balance` dan penambahan langsung ke `wallets.gold_balance` penerima manfaat disertai pencatatan `financial_ledgers` bertipe `BAITUL_MAL_AID`.

---

### C. Modul Reputasi Multidimensi & Mitigasi Sybil Attack
- **Algoritma Skor**:
  $$\text{Score} = (W_{net} \times 0.2) + (W_{trx} \times 0.3) + (W_{chk} \times 0.2) + (W_{mis} \times 0.3)$$
- **Pelemahan Manipulasi (Decay & Diminishing Return)**:
  - Validasi apresiasi dari pengirim yang sama dalam 7 hari mengalami diskon logaritmik $\frac{1}{\log_2(n+1)}$ untuk mencegah *reputation farming*.
- **Tiering**:
  - `PEMULA` (0 - 249)
  - `PENGEMBARA` (250 - 499)
  - `SAUDAGAR` (500 - 799)
  - `SULTAN` (800 - 1000)

---

### D. Modul Verifikasi Misi Khusus (5 Juri Desentralisasi & Honeypot)
- **Seleksi Acak 5 Juri**:
  - Sistem memilih 5 verifikator di luar aliansi/paguyuban dan bukan penerima *endorsement* aktif.
- **Honeypot Validation Trap**:
  - 10% misi verifikasi disisipi data palsu buatan sistem (*honeypot*).
  - Jika juri memilih `VALID` pada *honeypot*, sistem otomatis memberlakukan **Slashing** (pengurangan skor reputasi drastis -100 poin dan skorsing juri selama 7 hari).
- **Staking Juri**:
  - Verifikator menaruh pasak (*stake*) 50 Perak. Jika vote sejalan dengan konsensus mayoritas ($\ge 3$ suara), pasak kembali + reward insentif. Jika vote kalah secara sengaja, pasak hangus.

---

### E. Modul Pasar Lelang Digital (Digital Auction Engine) & Kalibrasi Model Bisnis
Berdasarkan tinjauan strategis terhadap utilitas ekosistem, posisi pasar lelang dikalibrasi agar memiliki batasan subjek dan objek yang jelas:

#### 1. Siapa yang Melelang? (Pelaku Lelang)
Diterapkan **Model Hybrid Bertahap** untuk mencegah penipuan dan mengatasi *Cold-Start Problem*:
- **Fase 1: Platform & Merchant Resmi (Official / System Drops)**
  - *Pelelang*: Sistem PitiCoin / Admin Root Level 0 dan Saudagar Merchant terverifikasi.
  - *Tujuan*: Menjamin ketersediaan barang berkualitas tanpa risiko penipuan logistik atau barang fiktif antar pengguna.
- **Fase 2: Peer-to-Peer Terbatas (Verified Saudagar P2P)**
  - *Pelelang*: Hanya dibuka untuk pengguna ber-Tier **Saudagar / Sultan** dengan reputasi $\ge 500$ dan lisensi aktif.
  - Pengguna biasa (Free/Guest Tier) hanya bertindak sebagai **Peserta Lelang (Bidder)**, bukan penjual liar.

#### 2. Apa Produk yang Dilelang? (Katalog Objek Lelang)
Produk yang dilelang dibagi menjadi 2 pilar konkret:
- **Pilar A: Utilitas Ekosistem & Aset Digital (Internal Gamification)**
  1. *Hak Kontrak Misi Rahasia/Eksklusif*: Slot terbatas ekspedisi Gunung Mas berhadiah Koin Emas besar.
  2. *Slot Promosi & Banner Saudagar*: Merchant menawar slot tayang iklan produk UMKM mereka di dashboard utama PitiCoin.
  3. *Perlengkapan Ekspedisi & Kartu KOA Langka*: Item peningkat drop-rate emas atau penambah stamina ekspedisi.
- **Pilar B: Voucher Fisik & Produk UMKM Riil (Real-World Redemption)**
  1. *Voucher Diskon Merchant*: Voucher makan/minum, produk kerajinan, atau layanan dari Saudagar jaringan Locomo (Mandeh.Online / Unimart).
  2. *Mekanisme Penukaran*: Pemenang lelang menerima kode QR Voucher unik yang dapat dipindai di kasir fisik Saudagar melalui modul POS.

#### 3. Mekanisme Finansial & Deflasi (Tax & Burn)
- **Mata Uang Bid**:
  - *Koin Perak*: Digunakan untuk lelang aset digital gamifikasi dan slot promosi (membakar perak berlebih untuk menekan inflasi).
  - *Koin Emas*: Digunakan untuk lelang voucher riil merchant yang memiliki nilai tukar rupiah.
- **Smart Escrow**: Saldo penawar tertinggi dikunci di status `ESCROW_LOCKED`. Jika ada tawaran lebih tinggi, saldo otomatis di-refund seketika (*auto-refund*).
- **Anti-Sniping**: Tawaran pada sisa waktu $< 60$ detik otomatis memperpanjang lelang 3 menit.
- **Alokasi Pajak (5%)**: 50% dihanguskan (*burn* permanen) dan 50% disalurkan ke kas sosial **Baitul Mal**.

---

### F. Modul WebPush Notifications & Real-Time Alerts
- **Standar**: RFC 8292 VAPID push protocol via Service Worker (`public/sw.js`).
- **Use-Cases**:
  - Peringatan penawaran lelang terlampaui (*Outbid Notification*).
  - Tugas juri verifikasi baru diterima.
  - Harta karun / Angpao Per Jam siap diklaim.
  - Verifikasi keamanan sesi dari perangkat asing.

---

## 4. Tahapan Eksekusi (Roadmap Implementasi)

| Tahap | Fokus Utama | Target Deliverable |
|---|---|---|
| **Fase 1** | **Dashboard Hub Mapping & Navigasi Terpadu** | Refactor `Dashboard.tsx`: Top App Bar, Quick Access Hub Matrix, Footer Legal & Profil Links |
| **Fase 2** | **Baitul Mal & Staking Deflasi** | Migrasi `baitul_mal_tables`, `BaitulMalService`, API rute, & Halaman Antarmuka `/baitul-mal` |
| **Fase 3** | **Reputasi Multidimensi & Algoritma Anti-Farming** | Migrasi `user_reputations`, `ReputationService`, integrasi badge tier di profil & dashboard |
| **Fase 4** | **Juri Verifikasi Misi & Honeypot Trap** | Migrasi `mission_verifications`, `MissionVerificationService`, arsitektur seleksi acak 5 juri |
| **Fase 5** | **Pasar Lelang Digital (English/Dutch Auction)** | Refactor `AuctionController`, migrasi `auctions`, `AuctionService`, smart escrow & anti-sniping |
| **Fase 6** | **WebPush VAPID Notifications** | Migrasi `web_push_subscriptions`, service worker listener `sw.js`, `WebPushService` |

---

## 5. Rencana Verifikasi & Uji Ketahanan

1. **Uji Navigasi Dashboard**:
   - Memastikan semua tautan navigasi (`/profile`, `/help`, `/terms`, `/privacy`, `/baitul-mal`, `/auction`) dapat diakses tanpa hambatan dari Dashboard di semua ukuran layar (Mobile & Desktop).
2. **Uji Konsistensi Finansial (Zero-Sum Invariant)**:
   - Total Koin Emas didonasikan ke Baitul Mal = Total Koin Emas masuk ke kas `baitul_mal_boxes`.
   - Total Koin Emas penawar lelang terkunci = Total saldo escrow lelang aktif.
3. **Uji Pertahanan Honeypot**:
   - Simulasi verifikator yang menyetujui honeypot menerima penalti slashing reputasi dan skorsing juri.
4. **Uji Anti-Sniping**:
   - Penawaran pada sisa waktu $< 60$ detik otomatis memperpanjang *expiry timestamp* sebanyak 180 detik.
