# Blueprint Desain UI Mobile-First & Tablet-Adaptive: Unimart.group

**Status Dokumen**: Proposal & Spesifikasi Desain  
**Target Platform**: Smartphone (iOS/Android PWA) & Tablet (Maksimal 10.5 inci)  
**Proyek Terkait**: `unimart.group`  
**Tanggal**: 2026-09-03  

---

## 1. Analisa Aktivitas Development Terakhir

### 1.1 Evaluasi Fitur Google Sign-In & Autentikasi
Pada iterasi terakhir, tim telah menyelesaikan fondasi integrasi identitas dan otentikasi:
- **Controller Backend**: Pembuatan [GoogleAuthController.php](file:///home/dev360_03/locomo.id/unimart.group/app/Http/Controllers/Auth/GoogleAuthController.php) yang mengelola alur OAuth Socialite (`redirectToGoogle`, `handleGoogleCallback`).
- **Linking Akun & Registrasi Progresif**: Otomatis menghubungkan `google_id` jika email terdaftar, atau menyimpan profile payload ke sesi (`session('google_register')`) untuk diarahkan ke wizard pendaftaran bisnis [owner-register.blade.php](file:///home/dev360_03/locomo.id/unimart.group/resources/views/auth/owner-register.blade.php).
- **Rute Web**: Penyediaan endpoint `/auth/google/redirect` dan `/auth/google/callback` pada [routes/web.php](file:///home/dev360_03/locomo.id/unimart.group/routes/web.php).

### 1.2 Temuan & Problem Statement UX Saat Ini
Meskipun fungsionalitas logika telah berjalan, interface yang ada saat ini didesain dengan paradigma **Desktop-First Retrofit** (dibuat untuk desktop lalu disesuaikan secara parsial menggunakan `@media (max-width: 768px)`):
1. **Sidebar Konvensional**: Dashboard Staff & Owner masih menggunakan bilah samping kiri (sidebar) 230px. Pada layar HP, sidebar ini menutupi seluruh layar saat dibuka dan memutus alur kerja.
2. **Tabel Data Horizontal**: Tampilan pesanan, daftar menu, dan staf menggunakan elemen `<table>` lebar yang memicu horizontal scroll yang kaku di layar sentuh.
3. **POS Kasir Split-Pane**: Pada layar HP, layout kasir terbagi secara vertikal dengan tombol checkout yang sering tenggelam di bawah lipatan layar (*below the fold*), memperlambat kecepatan transaksi kasir.
4. **Ergonomi Jangkauan Jempol (*Thumb Zone*)**: Tombol aksi krusial diletakkan di bagian atas layar (header), padahal kasir dan pemilik kafe mengoperasikan HP dengan satu tangan dari bagian bawah.

---

## 2. Paradigma Desain: Mobile-First & Thumb-Friendly

### 2.1 Kaidah Ergonomi Mobile (Thumb Zone Layout)
```
+---------------------------------------+
|  [Header: Nama Kafe + Status + Avatar]| -> Hard to reach (Status only)
+---------------------------------------+
|                                       |
|          AREA KONTEN UTAMA            |
|       (Katalog / Kartu Ringkasan)     | -> Natural viewing area
|                                       |
+---------------------------------------+
| [Quick Action / Floating Bottom Bar]  | -> Easy Thumb Access
+---------------------------------------+
|   [🏠 Home | 📋 Pesanan | 💳 POS | ⚙️]   | -> Primary Thumb Zone (Bottom Nav)
+---------------------------------------+
```

### 2.2 Standard Desain UI Mobile-First Unimart
1. **Viewport & Safe Area**: Mendukung viewport notch & home bar modern (`viewport-fit=cover`, `padding-bottom: env(safe-area-inset-bottom)`).
2. **Touch Targets**: Semua tombol sentuh memiliki tinggi minimal `48px` dengan jarak antar-elemen minimal `8px` untuk mencegah salah klik.
3. **Bottom Navigation Bar**: Menggantikan Sidebar desktop untuk navigasi utama di smartphone dan tablet potret.
4. **Bottom Sheet (Drawer Geser Naik)**: Digunakan untuk form detail, keranjang pesanan kasir, modal pembayaran QRIS/Tunai, dan filter kategori.
5. **Card List vs Table**: Konversi seluruh tampilan data tabel menjadi kartu interaktif dengan status badge yang jelas.

---

## 3. Rencana Restrukturisasi Halaman Utama

| Halaman | Kondisi Lama | Konsep Baru Mobile-First |
| :--- | :--- | :--- |
| **Auth (Login/Register)** | Card tengah desktop dengan banyak padding. | Full-screen mobile layout, Clean Input, Big Google Sign-In button di bagian bawah, numeric OTP/PIN keypad friendly. |
| **POS Kasir (`owner.pos`)** | Split-pane kanan-kiri desktop, produk grid kecil. | Katalog grid 2 kolom di mobile (3-4 kolom di tablet), Bottom Sticky Cart Bar dengan counter & total, Bottom-sheet checkout drawer. |
| **Staff Dashboard (`staff.dashboard`)** | Sidebar 230px + Topbar + tabel pesanan horizontal. | App Bar atas ringkas, Bottom Tab Bar (Shift, Orders, Expense), Card list order realtime dengan tombol aksi 1-tap (*Prepare*, *Done*, *Pay*). |
| **Owner Dashboard (`owner.dashboard`)** | Statistik grid desktop, grafik lebar. | KPI Carousel geser horizontal, Quick action pills (Buka Kasir, Cek Omset, Tambah Menu), Feed aktivitas kafe terkini. |
| **Menu & Meja (`owner.menus`, `owner.tables`)** | Tabel CRUD konvensional. | Visual card list menu dengan toggle switch status ketersediaan (Tersedia / Habis), Floating Action Button (FAB) `+ Tambah Menu`. |

---

## 4. Rincian Implementasi CSS & Pola Komponen (Design Tokens)

### 4.1 Token Ukuran & Breakpoint
```css
:root {
    --touch-target-min: 48px;
    --nav-bottom-height: 64px;
    --safe-bottom: env(safe-area-inset-bottom, 16px);
    --safe-top: env(safe-area-inset-top, 12px);
    --radius-sheet: 24px 24px 0 0;
    --radius-card: 16px;
}

/* Breakpoints:
   - Mobile: < 640px (Default Mobile-First)
   - Tablet: 640px - 1024px
   - Desktop: > 1024px
*/
```

### 4.2 Struktur Navigasi Bawah (Bottom Navigation Bar)
```html
<nav class="bottom-nav">
    <a href="/staff/dashboard?tab=orders" class="nav-tab active">
        <svg>...</svg>
        <span>Pesanan</span>
        <span class="badge-count">3</span>
    </a>
    <a href="/caffe/alias/pos" class="nav-tab">
        <div class="fab-center-btn"><svg>...</svg></div>
        <span>Kasir</span>
    </a>
    <a href="/staff/dashboard?tab=shift" class="nav-tab">
        <svg>...</svg>
        <span>Shift</span>
    </a>
</nav>
```

---

## 5. Roadmap Eksekusi Refactoring
1. **Fase A (Auth & Onboarding)**: Penyesuaian layout `owner-login`, `owner-register`, dan `staff-login` ke full viewport mobile dengan integrasi Google Sign-In responsif.
2. **Fase B (POS & Checkout Mobile)**: Re-desain `owner/pos.blade.php` dengan grid produk sentuh dan bottom-sheet cart.
3. **Fase C (Dasbor Staf & Operasional Kasir/Barista)**: Transformasi `staff/dashboard.blade.php` ke bottom navigation dan card-based orders.
4. **Fase D (Dasbor Pemilik & Manajemen Menu/Meja)**: Redesain ringkas untuk monitoring omset via HP.
