# Rencana Implementasi: Fitur Baitul Mal (Donasi/Sedekah Koin Emas) & Ekosistem PitiCoin.id

Rencana ini merinci arsitektur, skema database, logika bisnis, dan tahapan implementasi fitur-fitur baru pada platform **PitiCoin.id**, dengan fokus utama pada **Baitul Mal (Donasi & Sedekah Koin Emas)** serta integrasi dengan **WebPush Notifications**, **Rating & Reputasi Multidimensi**, **Verifikasi Misi Acak (5 Verifikator)**, dan **Pasar Lelang Digital**.

---

## 1. Pertimbangan Kritis & Tata Kelola

### A. Mata Uang Donasi (Koin Emas vs Koin Perak)
Donasi/sedekah di Baitul Mal difokuskan pada **Koin Emas** (*Gold Balance* yang bernilai riil/memiliki cadangan likuiditas di `LiquidityPoolSystem`). Koin Emas yang disedekahkan tidak hangus (burn), melainkan dialokasikan ke Kas Sosial Baitul Mal untuk didistribusikan kepada mustahik yang memenuhi kriteria.

### B. Kriteria Penerima Manfaat (Mustahik)
Syarat pengguna yang berhak menerima santunan Baitul Mal:
- Tier pemula dengan saldo Koin Emas = 0 dan Koin Perak < batas minimal.
- Telah melakukan verifikasi akun (KYC ringan / nomor terdaftar).
- Memiliki masa aktif akun minimal (contoh: 7 hari) untuk mencegah bot / akun tuyul.
- Riwayat aktivitas positif (rajin check-in atau aktif belajar/misi) dan tanpa catatan pelanggaran (*zero penalty*).

---

## 2. Rincian Modul & Perubahan File

### Modul 1: Baitul Mal, Sedekah & Donasi Sosial (PitiCoin Treasury)
Fitur ini mengelola kas sosial, penerimaan donasi (P2P dan Kotak Sosial), serta penyaluran kepada penerima manfaat.

- **Migrasi Database (`database/migrations/2026_09_14_000001_create_baitul_mal_tables.php`):**
  - `baitul_mal_boxes`: Wadah/program donasi sosial (contoh: "Kotak Amal Ekspedisi Umum", "Bantuan Modal Saudagar Baru", "Dana Darurat Penjelajah").
  - `baitul_mal_donations`: Riwayat transaksi donasi (pengirim, jumlah gold, target kotak/P2P, pesan doa, status anonimitas).
  - `baitul_mal_beneficiaries`: Pendaftar/penerima manfaat yang diverifikasi (status, alasan pengajuan, jumlah santunan yang diterima).
  - `baitul_mal_distributions`: Pencatatan histori penyaluran koin emas dari kas Baitul Mal ke dompet mustahik.
- **Model Eloquent:**
  - `app/Models/BaitulMalBox.php`: Melacak `total_collected_gold` dan `total_distributed_gold`.
  - `app/Models/BaitulMalDonation.php`: Relasi ke User dan Double-entry Ledger.
  - `app/Models/BaitulMalBeneficiary.php`: Evaluasi pengajuan mustahik.
- **Service Layer (`app/Services/BaitulMalService.php`):**
  - `donateToBox(userId, boxId, amountGold, isAnonymous, message)`
  - `donateP2P(senderId, recipientId, amountGold, note)`
  - `assessBeneficiaryEligibility(userId)`
  - `disburseAid(boxId, beneficiaryId, amountGold, approvedBy)`

---

### Modul 2: WebPush Notifications
Mekanisme notifikasi peramban/PWA tanpa harus membuka tab browser.
- **Migrasi:** `database/migrations/2026_09_14_000002_create_web_push_subscriptions_table.php`
- **Service Layer:** `app/Services/WebPushService.php` (RFC 8292 VAPID)
- **Frontend Worker:** `public/sw.js`

---

### Modul 3: Rating & Reputasi Multidimensi
Menghitung skor kepercayaan dinamis dan mengelompokkan pengguna ke dalam Tier.
- **Migrasi:** `database/migrations/2026_09_14_000003_create_user_reputations_table.php`
- **Service Layer:** `app/Services/ReputationService.php`
  - Formula: $\text{Score} = (W_{net} \times 0.2) + (W_{trx} \times 0.3) + (W_{chk} \times 0.2) + (W_{mis} \times 0.3)$
  - Mekanisme *slashing* penalti jika terbukti curang.

---

### Modul 4: Verifikasi Misi Khusus (5 Pengguna Acak)
Konsensus juri desentralisasi dengan proteksi *Honeypot* dan *Stake*.
- **Migrasi:** `database/migrations/2026_09_14_000004_create_mission_verifications_table.php`
- **Service Layer:** `app/Services/MissionVerificationService.php`
  - Algoritma seleksi acak 5 orang di luar paguyuban/mutual.
  - Notifikasi serentak via WebPush.
  - Ambang konsensus $\ge 3$ suara sependapat.

---

### Modul 5: Pasar Lelang Digital (Digital Auction)
Pasar lelang terbuka (English) & menurun (Dutch) untuk pembakaran & perputaran koin emas.
- **Migrasi:** `database/migrations/2026_09_14_000005_create_auction_tables.php`
- **Service Layer:** `app/Services/AuctionService.php`
  - Smart Escrow penguncian koin emas saat bidding.
  - Overtime anti-sniping.

---

## 3. Rencana Pengujian (Verification Plan)
1. **Baitul Mal Ledger Invariant Test:** Memastikan integritas saldo kas sosial dan dompet pengguna seimbang secara matematis (Zero-Sum Invariant).
2. **Reputation Weight Calculation Test:** Memverifikasi akurasi kenaikan tier dan penalti slashing.
3. **Random Verifier Consensus Test:** Menguji pemilihan 5 juri acak dan keabsahan konsensus 3/5 suara.
