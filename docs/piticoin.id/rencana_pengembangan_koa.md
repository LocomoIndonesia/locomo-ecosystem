# Rencana Pengembangan & Persiapan: Slot KOA V2 (piticoin.id)

Dokumen ini berisi rangkuman persiapan dan rencana kerja untuk memigrasikan game kartu KOA di PitiCoin.id dari simulasi client-side mock (8x4 grid) ke server-side deterministic RNG (5x5 grid).

## 1. Persiapan Environment & Hak Akses
- Memeriksa kontainer Docker:
  - `piticoinid-app-1`, `piticoinid-db-1`, `piticoinid-vite-1` semuanya berjalan dengan normal.
- Mengatasi masalah permission berkas koordinat JSON di `public/games/kartu-koa/assets/atlas/` yang saat ini dimiliki oleh `root:root` (menyebabkan error `Permission denied` saat auto-segmentation dijalankan). Kepemilikan berkas harus diubah menjadi `dev360_03:dev360_03`.

## 2. Rencana Implementasi Backend (Laravel)
- **KoaGameController**:
  - Validasi bet (Silver) sesuai saldo pengguna.
  - Pessimistic locking (`lockForUpdate()`) pada tabel `wallets` dan `liquidity_pool_system` untuk mencegah eksploitasi ganda (*race conditions*).
  - Pembatasan kemenangan maksimal per putaran sebesar 35% dari saldo Pool Gunung Mas.
  - Simulasi pencarian match grid 5x5 dan kejatuhan kartu secara beruntun (*cascading steps*) di server.
  - Implementasi Free Spins (K.O.AA) dan Tension Mode (COKI).
  - Integrasi API Claim Gold dengan memotong saldo Perak (*burn engine*).
- **Rute API** di `routes/web.php`:
  - `GET /api/game/koa/state`
  - `POST /api/game/koa/spin`
  - `POST /api/game/koa/claim`

## 3. Rencana Implementasi Frontend (PixiJS & Vanilla JS)
- Transisi grid dari 8x4 ke 5x5 layout pada `game.js`.
- Penerapan visual **Pola V** pada 11 slot Hand (glowing overlay emas/perunggu) dan redup (*muted opacity*) pada 14 slot Discard Pile.
- Mengubah fungsi `spin()` dan `claimGold()` di `ui.js` / `game.js` agar melakukan request HTTP ke server-side Laravel API, dengan penanganan token CSRF dan proteksi double-click.

## 4. Verifikasi dan Pengujian
- Pengujian unit dan integrasi transaksi finansial di Laravel.
- Verifikasi visual layout grid 5x5 portrait/landscape di mobile browser.
- Verifikasi keamanan transaksi terhadap eksploitasi *race conditions* dan *XSS/CSRF*.
