# Tab 1

# DOKUMEN BLUEPRINT

# **🎮 DOKUMEN BLUEPRINT PRODUKSI: KOA SLOT MINI-GAME (V2)**

## **📁 BAGIAN 1: ASSET PIPELINE v2 & ENGINE PARSING MATRIX**

Aset tidak lagi dimuat sebagai gambar tunggal raksasa, melainkan diurai secara modular berdasarkan sub-sistem menggunakan file format generasi baru .webp untuk performa kompresi GPU maksimal dan dipetakan oleh metadata .json.

### **1\. Struktur Direktori Produksi (public/games/kartu-koa/assets/)**

Plaintext  
assets/  
└── atlas/  
    ├── cards/         \# 30 Kartu KOA Valid \+ Simbol Mekanis (cards.webp, cards.json)  
    ├── ui/            \# Navigasi, Progress Bar, Multiplier, HUD (ui.webp, ui.json)  
    ├── frame/         \# Bingkai Premium, Hand/Discard divider (frame.webp, frame.json)  
    ├── effects/       \# Glow, Particles, Lighting, Explosion (effects.webp, effects.json)  
    ├── coins/         \# Animasi Koin Perak & Emas pecah/terbakar (coins.webp, coins.json)  
    └── background/    \# Latar Belakang dinamis Portrait & Landscape (bg.webp, bg.json)

### **2\. Standar Spesifikasi Metadata Sprite (cards.json)**

Setiap *texture atlas* wajib menyertakan koordinat presisi serta pencatatan anchor point pusat agar render engine (PixiJS/Phaser) dapat memanggil id kartu secara instan:

JSON  
{  
  "frames": {  
    "card\_hiu":   {"frame": {"x":0, "y":0, "w":72, "h":180}, "sourceSize": {"w":72, "h":180}, "anchor": {"x":0.5, "y":0.5}},  
    "card\_babi":  {"frame": {"x":74, "y":0, "w":72, "h":180}, "sourceSize": {"w":72, "h":180}, "anchor": {"x":0.5, "y":0.5}},  
    "sym\_koa":    {"frame": {"x":148, "y":0, "w":90, "h":180}, "sourceSize": {"w":90, "h":180}, "anchor": {"x":0.5, "y":0.5}},  
    "sym\_wild":   {"frame": {"x":240, "y":0, "w":90, "h":180}, "sourceSize": {"w":90, "h":180}, "anchor": {"x":0.5, "y":0.5}},  
    "sym\_lock":   {"frame": {"x":332, "y":0, "w":72, "h":180}, "sourceSize": {"w":72, "h":180}, "anchor": {"x":0.5, "y":0.5}}  
  },  
  "meta": {  
    "image": "cards.webp",  
    "format": "RGBA8888",  
    "size": {"w": 1024, "h": 1024}  
  }  
}

## **⚙️ BAGIAN 2: LOGIKA BACKEND & DETERMINISTIC RNG LIFECYCLE**

Game ini menggunakan metode **Deterministic RNG dengan Visual Mapping**. Frontend tidak menentukan nasib permainan; frontend adalah "aktor" yang menjalankan skenario yang sudah ditulis secara instan oleh backend server.

### **Siklus Eksekusi Spin (Milidetik Pertama):**

Plaintext  
\[User Klik SPIN\]   
       │  
       ▼  
\[Validasi Saldo Perak\] ──(Tidak Cukup)──\> Halt & Tampilkan Modal "Isi Ulang Perak"  
       │ (Cukup, Potong Bet)  
       ▼  
\[Backend Audit Engine\] ──\> Check KTA NFT Buff \+ Item Pendukung yang Aktif  
       │  
       ▼  
\[Kalkulasi Cap Limit\]  ──\> Ambil Data Live Pool Gunung Mas (Maks Kemenangan \= 35% Pool)  
       │  
       ▼  
\[RNG Math Roll\]        ──\> Roll Angka Hasil Akhir (0 s/d Batas Maks Kemenangan)  
       │  
       ▼  
\[Reverse Pattern Map\]  ──\> Cari / Bangun Susunan Template Runtuhan Kartu yang sesuai Nilai RNG  
       │  
       ▼  
\[Kirim Payload JSON\]   ──\> Kirim Array 2D Pola Runtuhan ke Frontend Client (PixiJS) untuk Dianimasikan

## **🎴 BAGIAN 3: MEKANIKA CASCADING GRID 5x5 & STATE MACHINE**

Antarmuka utama menggunakan Grid 5x5 (25 kartu) yang memisahkan fungsi visualisasi kartu, namun menyatukan fungsi mekanis runtuhan (*cascading rules*).

### **1\. Pembagian Ruang Grid Visual**

* **11 Slot (Tengah & Bawah):** Diberikan rona cahaya *glowing overlay* terang, merepresentasikan susunan *Hand* (Kartu di tangan pemain).  
* **14 Slot (Atas & Samping):** Diberikan efek *shadow/muted opacity*, merepresentasikan *Discard Pile* (Kartu buangan/pelengkap sistem).

### **2\. Matriks Combo Multiplier & Transisi State Game**

| Fase Runtuhan | Aturan Kombinasi Pecah (Mato) | Multiplier Putaran Biasa | Multiplier Putaran Gratis (K.O.AA) | Efek Sistem / Trigger Visual |
| :---- | :---- | :---- | :---- | :---- |
| **Pecah 1** | Minimal 3 Kartu Kembar Cocok | **x1** | **x2** | Efek suara hancur, kartu baru turun dari atas. |
| **Pecah 2** | Combo Beruntun Runtuhan ke-2 | **x2** | **x4** | Animasi panel multiplier menyala berapi. |
| **Pecah 3** | Combo Beruntun Runtuhan ke-3 | **x4** | **x6** | Kilat petir menyambar di sekeliling bingkai grid. |
| **Pecah 4+** | Combo Beruntun Runtuhan ke-4 | **x5** | **x10** | **Suntikkan Simbol KOA (Scatter)** ke dalam runtuhan. |

### **3\. Logika State Khusus: COKI & K.O.AA**

* **STATE: COKI (Tension Mode)**  
  * *Syarat:* Muncul tepat **2 Simbol KOA** di layar setelah visual runtuhan berhenti.  
  * *Mekanika Engine:* Kunci kolom (*Lock Reel*) tempat 2 simbol KOA berada menggunakan aset sym\_lock. Jalankan *Respin* gratis 1x pada kolom sisa.  
  * *Efek UX:* Musik beralih ke tabuhan Gandang Tasa intensitas tinggi, kecepatan drop kartu melambat (*slow-drop effect*), layar menggelap fokus ke area slot yang terkunci.  
* **STATE: K.O.AA (Free Spin Mode)**  
  * *Syarat:* Mendapatkan **3 atau lebih Simbol KOA** (baik dari putaran langsung atau hasil Respin COKI).  
  * *Mekanika Engine:* Transisi ke layar Free Spin sebanyak **12 Putaran Gratis**. Saldo Koin Perak tidak ditarik sama sekali selama mode ini. Seluruh kemenangan dikonversi langsung menjadi **Kuota Koin Emas Tambang** di panel indikator.

## **💰 BAGIAN 4: IMPLEMENTASI MANDALOMICS & ENGINE PEMBAKARAN (BURN)**

Sesuai dengan grand design PitiCoin.id, sistem ini bebas inflasi (Anti-Ponzi) karena didukung oleh perputaran omset bisnis riil (*Real World Asset / Fiat-Backed*).

Plaintext  
      ┌────────────────────────────────────────────────────────┐  
       │               POOL GUNUNG MAS (Koin Emas)               │  
       └───────────────────────────┬────────────────────────────┘  
                                   │  
                     Maksimal 35% Jackpot per Siklus  
                                   │  
                                   ▼  
                       KUOTA EMAS (Siap Diklaim)  
                                   │  
                     \[ Klik Tombol KLAIM EMAS \]  
                                   │  
            Engine Memeriksa & Membakar Saldo Perak (100:1)  
                                   │  
         ┌─────────────────────────┴─────────────────────────┐  
         ▼                                                   ▼  
  \[ SALDO PERAK \]                                    \[ BRANKAS EMAS \]  
(Dihancurkan/Burned)                               (Aset Likuid Bertambah)

1. **Aturan Nilai Konversi:**  
   $$\\text{1 Koin Emas} \= \\text{Rp 1.000} \\quad \\Vert \\quad \\text{100 Koin Perak} \= \\text{1 Koin Emas}$$  
2. **Mekanisme Validasi Klaim:**  
   Saat user menekan tombol **\[KLAIM EMAS\]**, *smart contract engine* menjalankan logika:  
   $$\\text{Syarat Pembakaran} \= \\text{Jumlah Kuota Emas} \\times 100$$  
   *Jika Saldo Perak User \>= Syarat Pembakaran:* Potong/hancurkan (*burn*) Perak selamanya dari sistem. Pindahkan kuota emas menjadi saldo riil di **Brankas Koin Emas Pengguna** (siap di-withdraw ke Rupiah).  
   *Jika tidak:* Gagalkan klaim dan minta pengguna mengumpulkan Perak valid via Turn Over (TO) putaran slot kembali.

## **📱 BAGIAN 5: DESAIN MOBILE-FIRST & RESPONSIVE LAYOUT MATRIX**

Untuk menjamin aplikasi terasa seperti aplikasi *native app* premium di browser mobile, layout diatur menggunakan dimensi dinamis (100dvh) tanpa memunculkan scrollbar browser.

### **1\. Layout Viewport Matrix**

Plaintext  
📱 MODE PORTRAIT (Vertical Stack)  
┌──────────────────────────────────────────┐  
│   \[ GOLD POOL: 🪙 10,000.00 EMAS \]        │  \--\> HUD Atas (Global Pool)  
├──────────────────────────────────────────┤  
│                                          │  
│              GRID SLOT KOA               │  
│                   5 x 5                  │  \--\> Centerpiece (Dominan 63% Layar)  
│                                          │  
├──────────────────────────────────────────┤  
│  🥈 Saldo Perak: 5,000                   │  \--\> Dashboard Ekonomi Personal  
│  🪙 Kuota Klaim: 25.00  \[ KLAIM EMAS \]   │  \--\> Tombol Aksi Pembakaran Crimson Red  
├──────────────────────────────────────────┤  
│   \[ 100 \] \[ 500 \] \[ 1K \]     (( SPIN ))  \--\> Footer Kontrol & Tombol Putar  
└──────────────────────────────────────────┘

📐 MODE LANDSCAPE (Side-by-Side Flex)  
┌───────────────────────────────────┬──────────────────────────────────────┐  
│                                   │    \[ GOLD POOL: 🪙 10,000.00 EMAS \]   │  
│                                   ├──────────────────────────────────────┤  
│           GRID SLOT KOA           │   🥈 Saldo Perak: 5,000              │  
│               5 x 5               │   🪙 Kuota Klaim: 25.00              │  
│        (Dominan Kiri 60%)         │   🏦 Brankas Vault: 120.00 EMAS      │  
│                                   ├──────────────────────────────────────┤  
│                                   │   \[ KLAIM EMAS \]        (( SPIN ))   │  
└───────────────────────────────────┴──────────────────────────────────────┘

### **2\. Panduan Kustom CSS untuk Efek Premium Adiktif:**

* **Efek Kaca Logam (Glassmorphism Panels):**  
* CSS

.panel-premium {  
  background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, rgba(0,0,0,0.35) 100%);  
  border: 1px solid rgba(255, 255, 255, 0.15);  
  backdrop-filter: blur(12px);  
  box-shadow: inset 0 1px 1px rgba(255,255,255,0.1);  
}

*   
*   
* **Efek Kilatan Cahaya Tombol Klaim (Shine Sweep Loop):**  
* CSS

@keyframes shine {  
  100% { left: 125%; }  
}  
.btn-burn-crimson::after {  
  content: '';  
  position: absolute;  
  top: 0; left: \-75%; width: 50%; height: 100%;  
  background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);  
  transform: skewX(-25deg);  
  animation: shine 4s infinite;  
}

*   
* 

## **🎯 NOTA UNTUK ANTIGRAVITY (LANGKAH EKSEKUSI CODING)**

Dokumen ini sudah divalidasi penuh oleh sistem **WaliKelas.ID**.

Sampaikan instruksi final ini kepada sistem AI Developer (Antigravity/Cursor) Anda untuk mulai menyusun kode **Tahap 1**: *Membangun struktur file parser JSON Atlas Loader, inisialisasi Canvas Grid 5x5 menggunakan aset dari folder atlas/cards/ dan atlas/ui/, serta penguncian viewport responsif di area 100dvh.* Mesin gamifikasi ini siap diproduksi untuk menjadi instrumen deflasi Koin Perak paling adiktif dan melestarikan budaya KOA Minang secara revolusioner\!

**Icon Atlas** (±100 ikon).  
**Coin & Treasure Atlas** (ekonomi game).  
**Background & Ornament Atlas** (identitas visual).  
**Typography / Sprite Font Atlas**.  
**Particle & Animation Atlas**.  
**Seluruh file JSON dan Asset Manifest**.  
**Implementasi PixiJS** yang mulai merakit semua atlas menjadi antarmuka permainan.

# Desgin UI Pipeline

# **Yang Menurut Saya Paling Penting: Visual Identity KOA**

Ada satu hal yang ingin saya usulkan sebelum kita melangkah lebih jauh.

Saat ini visual UI kita masih banyak mengambil inspirasi dari estetika **slot Asia bergaya emas**. Itu sudah terlihat premium, tetapi belum memiliki identitas yang benar-benar khas.

Karena KOA berasal dari permainan tradisional Minangkabau, saya menyarankan kita membangun **Visual Identity KOA** agar game ini langsung dikenali sebagai produk yang unik. Misalnya:

* Ornamen frame menggunakan motif Minangkabau seperti **Pucuak Rabuang**, **Aka Cino Sagagang**, atau **Awan Larat**, bukan ornamen naga Tiongkok.  
* Latar belakang memadukan tekstur hitam dengan ukiran emas bergaya rumah gadang.  
* Peti harta dan koin tetap bergaya premium, tetapi diberi aksen motif Minang agar tidak terasa generik.  
* Efek kemenangan dapat menggunakan kilau emas dengan pola ukiran yang muncul sesaat, bukan hanya ledakan cahaya biasa.

Dengan begitu, ketika pemain melihat satu tangkapan layar saja, mereka akan langsung tahu bahwa ini adalah **KOA Slot**, bukan sekadar reskin dari tema slot Asia yang sudah banyak beredar.

