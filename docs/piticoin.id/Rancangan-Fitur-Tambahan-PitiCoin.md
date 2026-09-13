# Rancangan Strategis & Konsep Fitur Tambahan PitiCoin.id (Locomo Ecosystem)

Dokumen ini berisi elaborasi konsep, ide, dan rancangan strategis untuk empat fitur utama yang akan ditambahkan ke dalam ekosistem **PitiCoin.id**.

---

## 1. Fitur WebPush Notifications
**Konsep Dasar:**  
Pengiriman notifikasi secara *real-time* langsung ke peramban (browser) pengguna, baik di desktop maupun perangkat seluler (PWA), tanpa mengharuskan aplikasi terbuka secara aktif.

**Use-Cases (Skenario Penggunaan):**
- **Sistem Lelang:** Notifikasi saat ada penawaran (*bid*) baru yang lebih tinggi pada barang yang sedang diincar (Outbid Alert).
- **Verifikasi Misi:** Peringatan kepada 5 pengguna acak yang terpilih untuk memverifikasi sebuah misi khusus.
- **Angpao/Harta Karun:** Pengingat "Hourly Angpao" (Angpao Per Jam) atau munculnya titik perburuan harta karun baru.
- **Transaksi:** Konfirmasi pembayaran atau transfer PitiCoin berhasil.

**Rancangan Teknis:**
- **Service Worker:** Menggunakan standar Web Push API (berjalan di background browser).
- **Backend/Pusher:** Integrasi menggunakan paket seperti `minishlink/web-push` di Laravel untuk mengenkripsi payload, atau menggunakan WebSockets (Soketi/Laravel Reverb) yang dikombinasikan dengan Push API.
- **VAPID Keys:** Implementasi *Voluntary Application Server Identification* (VAPID) untuk autentikasi server pengirim push notification.

---

## 2. Fitur Rating & Reputasi Multidimensi
**Konsep Dasar:**  
Meninggalkan sistem *rating* tradisional (bintang 1-5) dan beralih ke **Skor Kepercayaan Multidimensi (Multi-dimensional Trust Score)** yang lebih komprehensif, dinamis, dan sulit dimanipulasi.

**Variabel Penentu Skor (Bobot Algoritma):**
1. **Apresiasi Jaringan (20%):** Validasi dari sesama anggota ekosistem Locomo (Endorsements). Semakin tinggi reputasi pemberi apresiasi, semakin besar poin yang didapat.
2. **Transaksi Client (30%):** Rasio keberhasilan transaksi (jual/beli/lelang), zero-dispute rate, dan kepuasan pihak lawan transaksi.
3. **Riwayat Check-in (20%):** Konsistensi dan validitas lokasi pengguna (Proof-of-Location). Penting untuk mencegah akun *bot* atau *spoofing* GPS.
4. **Tingkat Penyelesaian Misi (30%):** *Completion Rate* (Jumlah misi sukses dibagi jumlah misi yang diambil). 

**Mekanisme Strategis (Gamifikasi):**
- **Sistem Tiering:** Pengguna dikelompokkan ke dalam Tier Reputasi (contoh: *Pemula, Pengembara, Saudagar, Sultan*).
- **Privilege:** Reputasi tinggi memberikan akses ke Pasar Lelang VIP, prioritas verifikator misi khusus (reward lebih besar), dan *fee* transaksi yang lebih rendah.

---

## 3. Sistem Verifikasi Misi Khusus (Decentralized Verification)
**Konsep Dasar:**  
Mencegah kecurangan (fraud) pada misi-misi krusial (high-value missions) dengan menerapkan sistem verifikasi konsensus terdesentralisasi (mirip konsep *Proof-of-Work/Stake* dengan *Human-in-the-loop*).

**Alur Kerja (Workflow):**
1. **Submit Misi:** Pengguna "A" mengunggah bukti misi (foto, kuesioner, bukti fisik).
2. **Seleksi Acak (The Jury System):** Sistem algoritma memilih minimal **5 pengguna aktif** secara acak.
   - *Syarat Verifikator:* Berada di luar jaringan (bukan *mutual friend/follower*), memiliki skor reputasi minimal tertentu, dan tidak sedang dalam status *banned*.
3. **Notifikasi:** 5 verifikator menerima *WebPush Notif* bahwa ada misi yang harus divalidasi.
4. **Proses Verifikasi (Blind Validation):** Verifikator melihat bukti tanpa mengetahui identitas pengguna "A" (anonim) untuk mencegah bias, lalu memilih `VALID` atau `REJECT`.
5. **Konsensus:** Jika mencapai konsensus (misal: 3 dari 5 memilih VALID), misi dianggap berhasil.
6. **Reward Verifikator:** 5 orang tersebut mendapatkan insentif (PitiCoin/XP) sebagai imbalan telah menjaga integritas ekosistem. Jika ada verifikator yang sengaja menyabotase (voting berlawanan dengan mayoritas secara terus-menerus), skor reputasinya akan turun secara drastis (*Slashing*).

---

## 4. Pasar Lelang Digital (Digital Auction Market)
**Konsep Dasar:**  
Bursa nilai dinamis (Dynamic Value Exchange) di dalam ekosistem Locomo. Tempat pengguna membakar (burn) atau mendistribusikan PitiCoin untuk mendapatkan aset langka (digital maupun fisik).

**Jenis Lelang yang Diterapkan:**
- **English Auction (Lelang Terbuka):** Harga mulai dari rendah dan naik berdasarkan penawaran (*bidding*) tertinggi sampai batas waktu habis. (Cocok untuk aset bernilai tinggi).
- **Dutch Auction (Lelang Menurun):** Harga mulai dari sangat mahal dan terus turun setiap jam/menit. Orang pertama yang menekan tombol "Beli" menjadi pemenangnya. (Menciptakan psikologi FOMO / *Fear Of Missing Out* yang kuat dan sangat menarik secara gamifikasi).

**Fitur & Mekanisme Kunci:**
1. **Smart Escrow:** Saat pengguna melakukan *bid*, jumlah PitiCoin tersebut akan dikunci (*frozen*). Jika ada yang menawar lebih tinggi, PitiCoin akan dikembalikan otomatis (dikombinasikan dengan WebPush Notif: *"Seseorang baru saja menawar lebih tinggi!"*).
2. **Anti-Sniping (Overtime):** Jika ada penawaran di 1 menit terakhir sebelum lelang ditutup, waktu penutupan otomatis diperpanjang 3 menit. Ini mencegah *bot* atau *sniper* yang mencuri barang di detik terakhir, serta memaksimalkan harga jual.
3. **Aset yang Dilelang:**
   - *Hak Eksklusif Misi*: Lelang hak untuk mengerjakan misi super-rahasia dengan *reward* raksasa.
   - *Aset Digital/NFT*: Lencana (*badge*) unik yang memengaruhi tier reputasi.
   - *Voucher Fisik*: Tiket diskon atau barang nyata dari merchant Saudagar di ekosistem Locomo.
4. **Tax & Burn Mechanism:** Setiap transaksi lelang akan dipotong biaya (fee) 2-5% yang akan *dibakar* (dihilangkan dari peredaran) atau dimasukkan ke dalam kas *Baitul Mal* ekosistem untuk menjaga inflasi PitiCoin.

---
*Rancangan ini siap untuk diturunkan menjadi ERD (Entity Relationship Diagram) dan skenario integrasi API.*

---

## 5. Fitur Baitul Mal & Kas Sosial Koin Emas (Treasury System)
**Konsep Dasar:**
Wadah serap moneter (*Treasury & Staking Sink*) sekaligus kotak amal digital untuk sedekah Koin Emas dari para pengguna. Tujuannya adalah menekan inflasi koin (sebagai counter-balance dari Angpao Harian) sekaligus memberikan perlindungan sosial (Bantuan Darurat) bagi pengguna ekosistem Locomo yang memenuhi kriteria (Mustahik).

**Fitur & Mekanisme Kunci:**
1. **Transparansi Ledger:** Saldo Baitul Mal (Uang Masuk dan Uang Keluar) direkam melalui skema Double-Entry Ledger, sehingga pengguna bisa melihat pergerakan Koin Emas secara *real-time* di Dashboard.
2. **Kepatuhan Privasi Data (UU PDP No. 27/2022):** Identitas donatur dan penerima santunan akan dilindungi dan disamarkan secara sistem. (Contoh: `Budi Santoso` menjadi `Bu*** Sa*****` dan nomor ponsel `081234567890` menjadi `0812****7890`).
3. **Anonimitas Penuh (Hamba Allah):** Pengguna dapat memilih opsi anonim penuh saat memberikan donasi Koin Emas, namun transaksinya tetap tercatat valid secara agregat di Ledger publik.
4. **Distribusi Terjadwal:** Penyaluran (Distribution) kepada Mustahik dilakukan per *batch* / periode (contoh: Distribusi Batch September 2026), sehingga alur keuangan dapat di-audit secara berkala.
