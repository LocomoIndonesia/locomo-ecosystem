# Laporan Pembenahan Header: Game Kartu KOA (Slot KOA V2)

Pembenahan area Header game kartu KOA telah berhasil diselesaikan berdasarkan masukan dan referensi berkas layout `koa-slot.html`.

## Ringkasan Perubahan:
1. **Penyatuan Visual Header ke Kanvas PixiJS**:
   - Memperbesar tinggi kanvas PixiJS menjadi **`652.5px`** untuk menampung area header setinggi `180px` di bagian teratas.
   - Menggambar tumpukan koin emas (`element_0` dari `coin_treasure-atlas`), banner kayu saldo (`element_102`), dan peti emas (`element_34`) secara dinamis menggunakan Asset Atlas Pipeline langsung pada kanvas.
   - Menggambar judul logo KOA emas (`element_239` dari `typografi_atlas`) di atas grid board.
2. **HUD Info Profil & Saldo Transparan**:
   - Menggabungkan elemen profil (avatar, username, lencana VIP) dan kapsul ekonomi (KOA Coin, KOA Gold) ke dalam satu kontainer overlay HTML `.game-header-panel`.
   - Mengatur latar belakang HUD menjadi transparan agar visual koin emas PixiJS di bawahnya terlihat jelas tanpa tabrakan teks statis.
   - Tombol klik klaim emas dihubungkan ke kapsul kanan `personal-gold-quota-trigger` (KOA GOLD) dan area transparan di atas peti emas PixiJS.
3. **Penyelarasan Letak Grid & Multipliers**:
   - Menggeser seluruh elemen slot kartu KOA (grid 5x5) ke bawah sejauh `180px` (`y = 180 + 22.5 + row * 90`).
   - Menyelaraskan letak 4 lingkaran pengali di kanvas PixiJS agar sejajar dengan posisi grid baru (`y = 290, 390, 490, 590`).

## Hasil Pengujian:
- Seluruh unit & integration test Laravel berhasil lulus 100% (`php artisan test`):
  `Tests: 23 passed (70 assertions)`
