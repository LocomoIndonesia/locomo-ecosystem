# Laporan Implementasi: Pemisahan Navigasi Tab Dashboard, Bottom Nav E-Wallet, dan Fitur Scan Barcode PitiCoin.id

Dokumen ini merangkum perbaikan arsitektur antarmuka (UI/UX) pada aplikasi **PitiCoin.id** sesuai arahan terbaru, yaitu pemisahan antara tab konten dashboard atas dengan *bottom navigation bar*, penyesuaian penamaan tab, penambahan tab *Reward*, serta integrasi fitur dompet digital modern (Mutasi, Wallet, Profil, dan Scan Barcode).

---

## 1. Masalah yang Diselesaikan (Problem Statement)
Sebelumnya, tab selektor di bagian atas dashboard (*Misi, Jaringan, Gateway & Pasar*) dan *bottom navigation dock* memanipulasi state yang sama (`activeTab`). Akibatnya:
- Navigasi bawah dan atas menjadi redundan dan menampilkan konten yang sama.
- Tab atas masih menggunakan label *Gateway & Pasar*.
- Belum tersedia tab khusus untuk *Reward* dan loyalti.
- Belum ada halaman terpisah untuk fungsi dompet digital inti (*Mutasi Transaksi*, *Wallet/Dompet*, *Profil Pengguna*).
- Belum tersedia tombol *Scan Barcode / QRIS* di tengah *bottom bar* sebagaimana standar aplikasi dompet digital (*DANA, GoPay, OVO*).

---

## 2. Rincian Perubahan Implementasi

### A. Dekopel Navigasi (Decoupled Navigation Architecture)
State navigasi dipisahkan menjadi dua tingkatan hierarki:
1. **Top Tabs (`activeTab`)**:
   - Khusus mengontrol konten internal pada halaman utama (*Beranda*):
     - **Misi (`QUESTS`)**: Daftar misi harian, staking escrow berjalan, dan horizon waktu.
     - **Jaringan (`NETWORK`)**: Pohon jaringan Saudagar (Level 1 & Level 2), link referral undang rekan, dan komisi Karavan.
     - **Layanan (`SERVICES`)**: Katalog layanan ekosistem terintegrasi (Unimart POS Juragan Pro, WaliKelas.id LMS, Tapak Pendekar, PPOB Digital Goods, Bursa Lelang P2P, dan Game Harta Karun Gunung Mas).
     - **Reward (`REWARDS`)**: *Tab Baru* untuk milestone streak absensi 7 hari (klaim Koin Emas), klaim bagi hasil royalti karavan jaringan, tingkatan lisensi, serta kupon/voucher diskon merchant ekosistem.
2. **Bottom Navigation (`activeBottomNav`)**:
   - Mengontrol tampilan halaman penuh:
     - **Beranda (`HOME`)**: Dashboard utama PitiCoin.id beserta 4 tab konten atas di atas.
     - **Mutasi (`MUTASI`)**: Catatan ledger transaksi masuk/keluar, filter (*Semua, Perak, Emas, Masuk, Keluar*), dan audit double-entry ledger.
     - **Scan (`SCAN`)**: Tombol aksi melayang (*Floating Action Button*) di tengah untuk pemindaian QRIS/Barcode.
     - **Wallet (`WALLET`)**: Tampilan kartu dompet metal obsidian (*Sovereign Vault*), rincian saldo estimasi Rupiah, quick actions (Top Up, Transfer, Lebur Emas, Bursa P2P), dan rekening pencairan bank terverifikasi.
     - **Profil (`PROFILE`)**: Kartu identitas Saudagar, ID & WhatsApp, progress EXP level, link referral undangan, pengaturan PIN transaksi, dan tombol logout.

---

### B. Fitur Scan Barcode & QRIS (Digital Wallet UX)
Tombol *Scan* dirancang dengan gaya *elevated circular button* di tengah *dock*:
- **Aura & Gradient**: Menggunakan gradien emas `from-amber-600 via-primary-container to-yellow-300` dengan efek glow `shadow-[0_0_20px_rgba(255,215,0,0.6)]`.
- **Modal Interaktif 2-Mode**:
  1. **Mode Pindai Kode**:
     - Viewfinder kamera dengan 4 sudut bercahaya emas.
     - Animasi sinar laser (*scan laser beam*) yang bergerak vertikal naik-turun.
     - Tombol simulasi *Flash* dan unggah gambar barcode dari galeri.
  2. **Mode Barcode QR Saya**:
     - Menampilkan kode QR unik dan ID Saudagar (`PTC-XXXXXXXX`) pengguna untuk menerima pembayaran atau transfer saldo Perak/Emas langsung dari rekan saudagar.
     - Tombol cepat salin ID Saudagar.

---

### C. Sinkronisasi Data Mutasi Ledger
Pada `app/Http/Controllers/DashboardController.php`, sistem menambahkan data `ledger_entries` langsung ke Inertia props:
```php
$ledgerEntries = \App\Models\LedgerEntry::where('wallet_id', $user->id)
    ->with('ledger')
    ->orderBy('created_at', 'desc')
    ->take(50)
    ->get();
```
Halaman **Mutasi** kini membaca catatan transaksi nyata dari tabel `ledger_entries` dengan *fallback* riwayat `escrows` sehingga pengguna selalu melihat informasi mutasi yang rapi dan transparan.

---

## 3. Hasil Validasi & Kompilasi
- **TypeScript & Vite Build**: Kompilasi dijalankan menggunakan `docker compose exec app npm run build` dan berhasil tanpa error (`✓ built in 1.28s`).
- **Bundle Output**: Berkas produksi diperbarui di `public/build/assets/Conqueror-C9uSYVbX.js`.
- **Controller Test**: Pengujian controller melalui Tinker berhasil merender halaman dengan data user aktif.
