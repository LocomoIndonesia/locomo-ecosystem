# Analisis Proses Bisnis, Arsitektur Fundamental, & User Flow PitiCoin.id
**Gerbang Pembayaran Sentral & Pintu Masuk Jaringan Ekosistem Locomo.id**  
*Tanggal Kalibrasi: 12 September 2026*  
*Arsitek Sistem: Locomo AI Agent (Antigravity)*

---

## 1. Visi Fundamental & Filosofi PitiCoin.id

**PitiCoin.id** BUKANLAH sebuah aplikasi operasional monolitik (segalanya dikerjakan di satu tempat). Platform ini adalah **Hub (Gerbang Utama), Mesin Pencatat Referral (Jaringan), dan Gerbang Pembayaran Sentral (Payment Gateway)** untuk **Federated Ecosystem Locomo.id**.

Pengguna di PitiCoin memiliki **satu identitas utama** (sebagai Saudagar / Pengguna Ekosistem). Misi utama mereka di dalam platform ini adalah **memperluas jaringan** dan mendistribusikan layanan ekosistem kepada masyarakat sekitar.

Melalui narasi **"Perburuan Harta Karun Gunung Mas"**, platform ini mendorong dan memberikan insentif (Koin Emas & Perak) kepada pengguna untuk:
1. **Membangun Jaringan Pengguna (Karavan)**: Mengajak orang lain bergabung melalui link/kode referral.
2. **Mendaftarkan Bisnis (Ekspansi Unimart)**: Mendaftarkan usaha/toko (baik miliknya sendiri maupun milik orang lain) agar masuk ke dalam ekosistem Unimart.group.
3. **Mendaftarkan Pendidik (Ekspansi WaliKelas)**: Mendata dan mendaftarkan guru, tentor, atau ustaz lokal agar terhubung ke platform WaliKelas.id.
4. **Berbelanja Produk Digital (PPOB)**: Melakukan transaksi harian seperti beli pulsa, token listrik, dan tagihan air.
5. **Membeli Item Perbekalan Game**: Mendukung gamifikasi dengan membeli item-item untuk mempercepat atau memaksimalkan misi.
6. **Menjadi Agen Validasi & Keuangan**: Mengelola pembayaran ekosistem, melakukan survei fisik merchant, dan bertransaksi P2P.

---

## 2. Arsitektur Terdistribusi (Data Dispatcher & Payment Hub)

Untuk meluruskan batas domain aplikasi, arsitektur operasional Locomo.id dipisahkan sebagai berikut:

### 2.1 PitiCoin Sebagai "Formulir Pendaftaran & Tracker Jaringan"
Di dalam dashboard PitiCoin, **tidak ada fitur ganti persona** dan **tidak ada fitur manajemen kasir/stok barang**. 
Jika pengguna ingin mendaftarkan sebuah warung tetangga, mereka membuka menu **"Ekspansi Bisnis"** di PitiCoin. Form yang diisi murni untuk pendataan (Nama Toko, Pemilik, GPS). 
Setelah *submit*, PitiCoin bertugas melempar (dispatch) data tersebut melalui API ke layanan tujuan (Unimart), sembari **mengunci ID Referral** si pendaftar agar selamanya mendapatkan komisi dari warung tersebut.

### 2.2 Operasional Berada di Layanan Masing-Masing
Jika pengguna adalah seorang pemilik UMKM yang baru saja mendaftarkan tokonya melalui PitiCoin, maka untuk aktivitas operasional (membuka POS kasir, mencatat barang masuk, merekap penjualan harian), pengguna tersebut **wajib membuka aplikasi Unimart.group secara terpisah**. Hal yang sama berlaku untuk platform WaliKelas dan Tapak Pandeka.

### 2.3 PitiCoin Sebagai Gerbang Pembayaran Sentral & Marketplace
Seluruh transaksi moneter, pembelian paket langganan (subscription), PPOB, pembelian item game, dan perputaran koin di ekosistem Locomo berpusat di PitiCoin.
- **Langganan Ekosistem**: Bayar tagihan bulanan Kasir POS Unimart atau profil WaliKelas.
- **Transaksi PPOB**: Pembelian produk digital sehari-hari (Pulsa, Paket Data, PLN, BPJS).
- **In-Game Purchases**: Pembelian item pendukung misi Perburuan Harta Karun.
Seluruhnya dapat dibayar menggunakan Saldo Rupiah (Fiat), Koin Emas, maupun Koin Perak (untuk item tertentu).

---

## 3. Matriks Jaringan & Layanan Eksternal

| Target Layanan Terpisah | Siapa yang Didaftarkan? | Fungsi Utama di Layanan Tujuan (Operasional) | Peran PitiCoin (Hub & Finansial) |
| :--- | :--- | :--- | :--- |
| **Unimart.group** | UMKM, Toko, Warung (Milik sendiri / orang lain) | Kasir pintar (POS), manajemen stok, akuntansi harian, landing page merchant. | Menyediakan form registrasi awal, mengunci referral agen, menjadi gateway pembayaran sewa bulanan POS. |
| **WaliKelas.id** | Guru, Tentor Privat, Ustaz | Manajemen profil pendidik, penjadwalan les, absensi kelas virtual. | Mendata profil dasar guru, mendistribusikan komisi ke agen yang mendaftarkan. |
| **Tapak Pandeka** | Pelajar, Mahasiswa, Pemuda | Portofolio karya digital, e-sertifikat, forum diskusi OSIS. | Autentikasi SSO (Mandeh.ID) dan validasi struktur jaringan siswa ke sekolah/mentornya. |

---

## 4. Model Ekonomi Sirkular (Mandalomics V2) & Mekanika Koin

*(Konsep Koin Emas & Perak tetap berlaku sebagai bahan bakar ekosistem)*

1. **Starter Kit (Injeksi Awal)**: Pengguna baru membeli lisensi untuk mendapatkan Koin Perak (modal staking) dan hak mendaftarkan downline/bisnis.
2. **Staking Perak**: Untuk mengambil tugas/misi ekspansi, pengguna mengunci Koin Perak. Jika sukses, Perak kembali utuh + Reward Koin Emas.
3. **Burn & Pencairan Emas**: Koin Emas dapat digunakan untuk **membayar langganan layanan Unimart/WaliKelas, membeli PPOB, atau membeli Item Game (semua dengan diskon khusus)**, atau dicairkan ke rekening bank dalam bentuk Rupiah (Fiat).

---

## 5. Rancangan User Flow Terpusat

```mermaid
flowchart TD
    %% Gerbang Masuk & Identitas
    A[Pengguna Membuka piticoin.id] --> B[Gerbang Login / Register (SSO)]
    B --> C[Tautkan Kode Referral / Upline]
    C --> D[Pembelian Starter Kit & Injeksi Perak]
    D --> E[Dashboard Utama: Markas Saudagar]
    
    %% Core Loop di Dalam PitiCoin
    E --> F1[Manajemen Jaringan: Lihat Downline & Bonus]
    E --> F2[Dompet Keuangan: Top-up, Withdraw, Histori Koin]
    E --> F3[Tugas & Misi: Staking Perak untuk Aksi Ekosistem]
    
    %% Jembatan Ekspansi (Data Dispatcher)
    E --> G[Menu Ekspansi & Pendaftaran Layanan]
    G --> H1[Form Daftar UMKM Baru]
    G --> H2[Form Daftar Guru/Pendidik]
    G --> H3[Form Daftar Pelajar]
    
    %% Distribusi Data (API)
    H1 -.->|Kirim Data & Kunci ID Referral| API_UM[(Database Unimart.group)]
    H2 -.->|Kirim Data & Kunci ID Referral| API_WK[(Database WaliKelas.id)]
    H3 -.->|Kirim Data & Kunci ID Referral| API_TP[(Database Tapak Pandeka)]
    
    %% Payment Gateway & Marketplace
    E --> I[Gerbang Tagihan & Bursa Digital]
    API_UM -.->|Kirim Tagihan Langganan| I
    API_WK -.->|Kirim Tagihan Langganan| I
    
    I --> J1[Bayar Langganan Layanan (Unimart/WaliKelas)]
    I --> J2[Bursa PPOB: Beli Pulsa, Token PLN, dll]
    I --> J3[Bursa Perbekalan: Beli Item Game Misi]
    
    J1 --> K[Bayar pakai Koin Emas / Fiat]
    J2 --> K
    J3 --> K
```

---

## 6. Desain Misi Dinamis & Eksekusi Jaringan (Tasks)

Misi yang disediakan di PitiCoin berfokus pada aktivitas menggerakkan ekosistem, validasi, dan transaksi, **bukan** aktivitas operasional aplikasi lain.

### 6.1 Kategori Misi Ekspansi (Akuisisi Layanan)
- **Misi:** "Daftarkan 1 Warung Kelontong di kecamatan Anda ke ekosistem Unimart."
- **Penyelesaian:** Jika warung berhasil terdaftar (API Unimart merespon sukses), sistem PitiCoin merilis *Reward Koin Emas* ke pengguna.

### 6.2 Kategori Misi Transaksional & Keuangan
- **Misi:** "Bantu perpanjang masa aktif POS Kasir Unimart milik salah satu downline Anda" atau "Beli Token PLN minimal Rp50.000 untuk pelanggan."
- **Penyelesaian:** Mendapat bonus komisi cashback harian.

### 6.3 Kategori Misi Validasi Lapangan (Agen)
- **Misi:** "Validasi eksistensi UMKM 'Toko Makmur' di titik GPS terlampir."
- **Penyelesaian:** Sistem men-generate misi berdasarkan data toko yang didaftarkan pengguna lain. Pengguna mendatangi lokasi, memfoto plang toko, dan *submit*.

### 6.4 Mandatory Daily Check-in & Time-Based
Fitur *Check-in* Harian untuk mendapatkan *Angpao Perak* dan membuka daftar misi harian tetap dipertahankan guna menjaga aktivitas konstan di platform (Daily Active Users).

---

## 7. Katalog Layanan Ekosistem, PPOB, & Bursa Perbekalan

Seluruh proses pembayaran di bawah ini terpusat di PitiCoin dan dapat menggunakan Koin Emas (yang akan memicu sistem insentif/diskon untuk deflasi token).

### 7.1 Langganan Layanan Ekosistem (Subscriptions)
| Layanan / Modul | Target | Biaya Bulanan | Keterangan |
| :--- | :--- | :--- | :--- |
| **Unimart POS Kasir Pintar** | UMKM / Toko | Rp 29.000 (atau 25 Emas) | Tagihan muncul di dasbor PitiCoin pemilik toko. Agen upline mendapat komisi Emas. |
| **Unimart Web Toko** | UMKM / Toko | Rp 49.000 (atau 40 Emas) | Pembayaran domain/hosting terpusat di PitiCoin. |
| **WaliKelas Tutor Suite** | Guru / Bimbel | Rp 39.000 (atau 30 Emas) | Menjaga agar profil guru tetap aktif. |

### 7.2 Bursa Produk Digital (PPOB)
Fungsi esensial untuk mendorong utilitas riil platform sebagai aplikasi harian masyarakat:
- **Pulsa & Paket Data**: Seluruh operator.
- **Token Listrik (PLN)**: Pembelian prabayar.
- **Tagihan Rutin**: Pembayaran Pascabayar, PDAM, BPJS, dan Internet Rumah.
*(Note: Pembelian menggunakan Koin Emas mendapatkan potongan admin).*

### 7.3 Bursa Perbekalan Game (In-Game Items)
Untuk memperkuat gamifikasi "Perburuan Harta Karun Gunung Mas", pengguna dapat membeli item pendukung menggunakan Koin Perak/Emas:
- **Kompas Emas**: Membuka akses ke Misi Rahasia yang bernilai tinggi (Premium Quest).
- **Tiket Ekspedisi VIP**: Meningkatkan *drop rate* (probabilitas sukses) saat menyelesaikan misi.
- **Tiket Pengampunan Streak (Freeze)**: Mencegah hangusnya streak check-in harian apabila pengguna terlewat satu hari.
- **Energi Potion**: Mempercepat proses cooldown misi.

---

## 8. Ringkasan Teknis (Batas Sistem)

1. **Database Terpisah:** `piticoin.id` memiliki database mandiri untuk tabel `users`, `wallets`, `transactions`, `quests`, `referrals`, `ppob_transactions`, `inventory_items`.
2. **API Integrasi Ekosistem & Pihak Ketiga:**
   - PitiCoin -> Unimart (`POST /api/register-merchant`)
   - Unimart -> PitiCoin (`POST /api/billing/create-invoice`)
   - PitiCoin -> Provider PPOB (Digiflazz/Tripay) untuk pemrosesan pulsa/token.
3. **Stateless Identity:** Pengguna hanya perlu login satu kali melalui **Mandeh.ID** (SSO/JWT Token) untuk berpindah dari dasbor PitiCoin ke Unimart POS tanpa perlu login ulang.
