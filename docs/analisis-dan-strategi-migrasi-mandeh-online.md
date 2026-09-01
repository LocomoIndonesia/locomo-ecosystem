# Analisis Mendalam & Strategi Migrasi Proyek: mandeh.online
**Evolusi Menuju Sistem Operasi Wisata Nagari Terpadu di Ekosistem locomo.id**

---

## 1. PENDAHULUAN

Dokumen ini disusun untuk mendokumentasikan hasil analisis mendalam terhadap proyek **`mandeh.online`** yang saat ini berada di direktori referensi `/home/dev360_03/mande-project/`. Analisis ini mencakup identifikasi fitur yang telah diimplementasikan (existing), rencana pengembangan fitur masa depan (planned), serta rencana strategis untuk memigrasikan proyek tersebut ke dalam workspace ekosistem terintegrasi `/home/dev360_03/locomo.id/` di bawah kendali **Locomo Agent OS**.

*Catatan Penting:* Berdasarkan keputusan terbaru, semua fungsi integrasi AI/Ollama dan basis data vektor (`pgvector`) pada `mandeh.online` **dihapus dan ditangguhkan (pending)** untuk penyesuaian hosting cPanel yang hanya mendukung database PostgreSQL standar.

---

## 2. ANALISIS FITUR EKSISTING (Dari mande-project)

Berdasarkan pemindaian AST dan struktur file pada direktori referensi, `mandeh.online` dibangun dengan pendekatan **Modular Monolith** di Laravel. Berikut adalah rincian modul yang telah diinisiasi:

### A. Modul Bisnis (`app/Modules/Business`)
* **Fungsi Utama:** Mengelola profil destinasi dan usaha lokal (Restoran, Homestay, Transportasi, Jasa Pemandu, dll.).
* **Entitas Data:**
  * `Business.php`: Model profil usaha dengan koordinat geospasial (`lat`, `lng`), rentang harga, tipe usaha, dan `qr_token` unik.
  * `Product.php` & `MenuItem.php`: Entitas menu kuliner dan produk lokal yang terkait dengan bisnis.
* **Controller & View:** Menangani visualisasi daftar produk (`products.blade.php`), menu (`menu.blade.php`), dan detail profil (`show.blade.php`).

### B. Modul Gateway QR (`app/Modules/QR`)
* **Fungsi Utama:** Menangani *deep link routing* ketika wisatawan memindai kode QR fisik di lokasi wisata.
* **Entitas Data:**
  * `QrCode.php` & `QrService.php`: Layanan verifikasi tanda tangan token QR untuk mencegah pemalsuan kode QR oleh pihak luar.
* **UX Flow:** Mengarahkan pemindaian kode QR langsung ke profil publik bisnis tanpa perlu mengunduh aplikasi native.

### C. Modul Ulasan & Reputasi (`app/Modules/Review`)
* **Fungsi Utama:** Mengumpulkan penilaian multi-dimensi dari wisatawan.
* **Logika Rating:** Rating tidak bersifat global tunggal, melainkan dipecah menjadi 3 parameter utama:
  1. **Transparansi Harga** (mencegah praktik "tembak harga").
  2. **Kualitas Pelayanan**.
  3. **Faktor Keamanan & Kenyamanan** (mencegah praktik pungli).

### D. Modul Pengaduan & Laporan Warga (`app/Modules/Report`)
* **Fungsi Utama:** Media advokasi wisatawan/warga untuk melaporkan masalah di lapangan (misal: penumpukan sampah, jalan rusak, pungli).
* **Controller:** `ReportController.php` dengan halaman pembuatan laporan (`create.blade.php`).

### E. Modul Administrasi & Moderasi (`app/Modules/Admin`)
* **Fungsi Utama:** Dashboard moderasi ulasan dan verifikasi data fisik.
* **Controller:** `ModerationController.php` untuk menyetujui ulasan yang masuk ke antrean verifikasi (*moderation queue*).

### F. Modul Autentikasi Lokal (`app/Modules/Auth`)
* **Fungsi Utama:** Menangani pendaftaran dan login lokal berbasis form tradisional (Email & Sandi).
* *Catatan Migrasi:* Modul ini **DILIKUIDASI** dan digantikan secara penuh oleh SSO Terfederasi `mandeh.id`.

### G. Pemeriksaan Kode Legacy: Penghapusan pgvector & Ollama
Berdasarkan pemeriksaan menyeluruh pada seluruh kode sumber `mande-project/app/`, ditemukan komponen-komponen terkait AI/Vektor berikut yang **wajib ditangani sebelum proses salin**:

1. **`LocomoCognitiveService.php` (DILIKUIDASI):**
   * *Lokasi:* `app/Services/LocomoCognitiveService.php`
   * *Status:* **Dihapus (tidak disalin)**. File ini adalah bridge HTTP yang menghubungkan Laravel ke API FastAPI kognitif untuk pencarian semantik dan penyimpanan memori.
2. **`IngestKnowledgeBase.php` (DILIKUIDASI):**
   * *Lokasi:* `app/Console/Commands/IngestKnowledgeBase.php`
   * *Status:* **Dihapus (tidak disalin)**. Perintah Artisan untuk mengunggah berkas Markdown dokumentasi ke memori kognitif.
3. **`ForumController.php` (DIREFAKTOR):**
   * *Lokasi:* `app/Http/Controllers/Api/ForumController.php`
   * *Status:* **Wajib direfaktor**. Fungsi pencarian semantik pada metode `search()` dan penyelarasan context di `store()` yang sebelumnya memanggil `$this->cognitive->queryMemory()` akan diubah menggunakan kueri SQL standar:
     ```php
     // Perubahan dari pencarian semantik ke kueri SQL standard LIKE
     $results = ForumQuestion::where('question', 'LIKE', '%' . $query . '%')
         ->limit(5)
         ->get();
     ```
4. **Skema Database Relasional Murni (cPanel-Ready):**
   * Pemeriksaan berkas migrasi di `database/migrations/` memastikan tidak ada penggunaan tipe data `vector` maupun dependensi SQL dari pgvector. Skema migrasi database relasional murni aman dan didukung penuh oleh PostgreSQL standar di hosting cPanel.

---

## 3. RENCANA FITUR BARU & STRATEGI SOSIAL (Growth Loop)

Untuk mewujudkan **Sistem Operasi Wisata Nagari** yang aktif dan mandiri, berikut adalah rincian rencana fitur baru yang akan dibangun setelah proses migrasi:

### A. Mandeh Pulse (Contextual Social Feed)
* **Konsep:** Aliran aktivitas sosial waktu nyata (real-time). Setiap check-in, ulasan, atau postingan foto keindahan alam dari wisatawan akan mengalir di halaman utama.
* **Threaded Chat:** Menghindari obrolan global acak (*global chat spam*). Diskusi dikunci secara konteks di bawah destinasi tertentu (misal: berdiskusi hanya di bawah thread "Dermaga A").

### B. The Trust Formula (Validasi Jarak GPS)
* **Logika Validasi:** Menggunakan koordinat GPS perangkat pengirim ulasan untuk mencegah ulasan palsu:
  * **Jarak ≤ 100m dari koordinat bisnis:** Diberi label ✅ **"Verified Presence"** (Mampir).
  * **Jarak > 100m:** Diberi label ⚠️ **"Passing/Remote Review"**.
  * **GPS Mati/Ditolak:** Diberi label 🚩 **"Unverified"** (Masuk antrean moderasi otomatis, tidak langsung tayang di feed publik).

### C. Dynamic Social Cards (Visual Payoff - Non-AI)
* **Mekanisme Baru:** Gambar ulasan cantik dihasilkan langsung oleh aplikasi Laravel menggunakan pustaka **PHP GD** atau **ImageMagick** bawaan server hosting (bukan menggunakan API Python kognitif/AI).
* **Tujuan:** Menghasilkan poster ulasan otomatis yang siap dibagikan wisatawan ke Instagram Story atau status WhatsApp secara native di hosting cPanel.

### D. Digital Badges (Gamifikasi)
* **Insentif Non-Finansial:** Memberikan lencana digital (seperti "Penjelajah Pasisia" atau "Duta Mandeh") setelah wisatawan mengunjungi dan memindai jumlah QR tertentu.

---

## 4. STRATEGI INTEGRASI SSO FEDERASI (mandeh.id)

Dalam workspace baru, `mandeh.online` tidak akan menyimpan kredensial kata sandi pengguna atau menangani pendaftaran akun secara lokal. Sistem dialihkan ke protokol SSO federasi Mandeh.ID:

```
┌───────────────┐          1. Redirect Authorization         ┌───────────────┐
│               │ ────────────────────────────────────────>  │               │
│ mandeh.online │                                            │   mandeh.id   │
│   (Client)    │ <────────────────────────────────────────  │  (SSO Server) │
└───────────────┘       2. Auth Code Redirect Callback       └───────────────┘
        │                                                            ▲
        │                                                            │
        └─────────────────  3. Token Exchange  ──────────────────────┘
                          (stateless JWT validation)
```

### Alur Kerja Teknis:
1. **Trigger:** Wisatawan mengklik tombol "Masuk" atau mencoba mengirimkan ulasan (Registration Gate terpicu).
2. **Redirect:** `mandeh.online` mengarahkan browser ke `http://mandeh.id.localhost/oauth/authorize` dengan parameter:
   * `client_id=mandeh-online-client`
   * `redirect_uri=http://mandeh.online.localhost/callback`
   * `response_type=code`
   * `state=[random_csrf_state]`
3. **Login di Mandeh.ID:** Pengguna memasukkan email dan sandi di form glassmorphism Mandeh.ID.
4. **Callback:** Mandeh.ID melakukan redirect balik ke `mandeh.online` membawa parameter `code` (stateless auth token) dan `state`.
5. **Token Exchange:** Backend `mandeh.online` mengirim request POST ke `/oauth/token` milik `mandeh.id` untuk menukar `code` dengan `access_token` dan `id_token`.
6. **Stateless Authentication:** Backend `mandeh.online` memverifikasi tanda tangan `id_token` (JWT RS256) menggunakan kunci publik dari endpoint `/oauth/jwks`, mengekstrak data profil (`sub`, `name`, `email`), dan menetapkan session pengguna tanpa menulis sandi ke database lokal.

---

## 5. RENCANA STRATEGIS PROSES MIGRASI (PORTING)

Langkah-langkah taktis untuk memindahkan kode proyek dari direktori lama ke workspace terintegrasi saat ini:

### Langkah 1: Inisialisasi Project Laravel Kosong
Kita akan membuat aplikasi Laravel baru di dalam folder `/home/dev360_03/locomo.id/mandeh.online` menggunakan composer via kontainer `php-fpm`.

### Langkah 2: Konfigurasi Database Workspace
1. Membuat logical database `mandeh_online_db` di dalam kontainer developer database `pgsql` (Port 5432).
2. Mengonfigurasi `mandeh.online/.env` untuk menggunakan PostgreSQL standar:
   ```env
   APP_URL=http://mandeh.online.localhost
   DB_CONNECTION=pgsql
   DB_HOST=pgsql
   DB_PORT=5432
   DB_DATABASE=mandeh_online_db
   DB_USERNAME=sail
   DB_PASSWORD=password
   ```

### Langkah 3: Penyalinan Modul & Struktur Modular
1. Menyalin modul-modul fungsional berikut dari direktori lama `/home/dev360_03/mande-project/app/Modules/` ke `/home/dev360_03/locomo.id/mandeh.online/app/Modules/`:
   * `Business/`, `QR/`, `Review/`, `Report/`, `Admin/`, `Info/`.
2. **DILARANG** menyalin modul `Auth/`. Kita akan menggantinya dengan middleware dan controller klien SSO OIDC kustom di `app/Http/Controllers/Auth/SSOClientController.php`.
3. **DILARANG** menyalin `app/Services/LocomoCognitiveService.php`.

### Langkah 4: Migrasi Skema Database & Jalankan Migrasi
1. Menyalin berkas migrasi tabel dari `mande-project/database/migrations/` ke `mandeh.online/database/migrations/` (fokus pada tabel `businesses`, `interactions`, `posts`, `products`, `menu_items`).
2. **Refaktor ForumController:** Salin `app/Http/Controllers/Api/ForumController.php` ke direktori baru, dan sesuaikan pencarian agar menggunakan kueri SQL `LIKE`.
3. Menjalankan migrasi database di dalam kontainer:
   `docker compose exec php-fpm php mandeh.online/artisan migrate`

### Langkah 5: Penyalinan Aset UI & Leaflet.js
1. Menyalin aset CSS/JS, pustaka **Leaflet.js**, dan prototipe tampilan (`resources/views/prototypes/`) ke direktori proyek baru.
2. Membawa file konfigurasi Tailwind dan Vite jika diperlukan untuk proses kompilasi aset frontend.

### Langkah 6: Pemindaian Kognitif (Locomo Agent OS Sync)
1. Menjalankan pemindaian peta proyek menggunakan skrip pembuat graph:
   `docker compose exec semantic-api python generate_project_graph.py /workspace/mandeh.online /app/graphify-out/graph-mandeh.online.json`
2. Memasukkan seluruh index relasi kode ke memori pgvector Locomo Agent OS:
   `docker compose exec semantic-api python sync_context.py --project mandeh.online --graph /app/graphify-out/graph-mandeh.online.json`
3. Memperbarui `active_project` pada `project-context.json` ke `"mandeh.online"` jika akan dikembangkan lebih lanjut.
