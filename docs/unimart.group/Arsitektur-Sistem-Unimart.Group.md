# Grand Design

Berdasarkan seluruh dokumen rancangan, arsitektur *blueprint*, dan *roadmap* ekosistem digital yang telah Anda himpun, **Unimart.id (Universal Market)** diposisikan sebagai "Jalan Tol Digital" sekaligus "Sistem Saraf Digital" bagi para pelaku usaha (Caffe, Grosir P\&D, Hotel, dll). Ini bukan sekadar aplikasi kasir biasa, melainkan platform **SaaS (Software as a Service) Multi-Tenant Enterprise** yang mengotomatisasi kasir, inventaris, pemasaran (via WiFi Hotspot), hingga layanan pelanggan (via AI).

Berikut adalah rancangan arsitektur sistem komprehensif untuk **Unimart.Group** menggunakan **Laravel 13** dan **PostgreSQL**, yang mengakomodasi seluruh visi yang Anda sebutkan:

### **1\. Tumpukan Teknologi Inti (Core Tech Stack)**

* **Backend Framework**: **Laravel 13** (Menangani logika bisnis utama, Multi-Tenancy, Antrean/Jobs, dan API backend).  
* **Database Relasional**: **PostgreSQL** (Kuat dalam data spasial/PostGIS, indeks JSONB, dan Row-Level Security).  
* **In-Memory Cache & Real-time**: **Redis** (Untuk *Dynamic Stock ATP / Available-to-Promise*, *Session Management*, dan antrean *Queue* berkecepatan tinggi).  
* **Real-time WebSocket**: **Laravel Reverb** (Memberikan notifikasi instan ke layar kasir atau *kitchen display* saat ada pesanan/pembayaran masuk tanpa harus me-refresh halaman).  
* **AI Engine**: **Google Gemini 2.5 Flash / Pro** via **Laravel AI SDK** (Untuk Asisten Chatbot 24/7 dan RAG \- *Retrieval-Augmented Generation* dari katalog toko).

### **2\. Arsitektur Multi-Tenant & Keamanan Data (PostgreSQL)**

Mengingat platform ini melayani banyak entitas bisnis berbeda dari satu pintu, sistem akan menggunakan pendekatan **Single Database dengan Foreign Key Isolation & Row-Level Security (RLS)**:

* **Scoped Singletons**: Setiap *request* yang masuk (berdasarkan *subdomain* toko atau login sesi) akan diidentifikasi *Tenant ID*\-nya oleh Laravel.  
* **Isolasi Ketat**: Data tidak akan pernah bocor antar-toko. PostgreSQL RLS akan diaktifkan untuk memastikan *Query* sekecil apa pun tidak bisa menembus batas kepemilikan data antar *tenant*, menjamin keamanan audit finansial tiap pemilik bisnis.

### **3\. Modul "Generative UI" & Fleksibilitas Alur Bisnis**

Karena Caffe, Grosiran P\&D, dan Hotel memiliki proses bisnis yang unik, Unimart mengadopsi arsitektur **Generative UI** menggunakan fitur **JSONB PostgreSQL**:

* **Form Dinamis**: Skema tampilan aplikasi (tombol kasir, input kasbon, nomor meja untuk Caffe, atau satuan Dus/Pak/Pcs untuk Grosir) disimpan dalam format JSON.  
* **Adaptasi Instan**: Jika sebuah bisnis ingin mematikan fitur "Pajak Layanan" atau menambah input "Nama Sopir" (untuk grosir), perubahan cukup dilakukan pada JSON konfigurasinya. Laravel dan Frontend (misalnya React/Vue.js via Inertia.js) akan langsung merender UI baru tanpa perlu merombak *source code* (bebas *hardcode*).

### **4\. Ekosistem Integrasi Utama (The Ecosystem Integration)**

#### **A. iZone Hotspot & Captive Portal (Gerbang Digital)**

Fasilitas WiFi di toko tidak lagi menjadi beban biaya, melainkan alat pemasaran.

* **Infrastruktur**: Menggunakan **MikroTik RouterOS** (sebagai *Hotspot Gateway*) di lokasi toko dan **FreeRADIUS** terpusat di server cloud.  
* **Alur (Laravel \+ Zilleali/mikrotik-laravel)**: Saat pelanggan terhubung ke WiFi toko, mereka diarahkan (di-redirect) ke halaman *Captive Portal* Laravel. Halaman login WiFi ini langsung disulap menjadi **Katalog Produk / QR Menu Real-time**. Pelanggan memesan dari HP mereka, dan otomatis mendapat akses internet sesuai syarat toko.  
* **Radius CoA (Change of Authorization)**: Memungkinkan Laravel memutus atau menyesuaikan bandwidth pelanggan secara dinamis langsung dari Cloud.

#### **B. Integrasi Pembayaran (QRIS Gateway & Upload Bukti Manual)**

Alur kasir tidak boleh terhambat. Pendekatannya bersifat *Hybrid*:

* **Automatis (QRIS Payment Gateway)**: Integrasi menggunakan *Midtrans* atau *Xendit* (melalui paket seperti `glennraya/xendivel`). Saat pelanggan memindai QRIS dinamis, *Webhook* dari Midtrans akan ditangkap oleh Laravel, diverifikasi, dan Reverb akan membunyikan notifikasi instan di tablet kasir ("Pembayaran Berhasil\!").  
* **Semi-Manual (Upload Bukti Bayar)**: Untuk *tenant* yang belum ter-integrasi QRIS resmi, atau pelanggan yang transfer bank manual, tersedia fitur **Upload Bukti Pembayaran** (foto kamera/galeri).  
  * *AI Verification (Opsional)*: Gemini Vision AI bisa digunakan untuk membaca teks (OCR) pada foto struk transfer yang diunggah pelanggan guna mencocokkan nominal dengan tagihan secara otomatis, mencegah struk palsu.

#### **C. Asisten Kasir Pintar (Gemini LLM & WhatsApp Gateway)**

* **Evolution API (WhatsApp Webhooks)**: Sebuah Node.js *service* yang menempel ke WhatsApp. Mengubah nomor WA toko menjadi asisten otomatis.  
* **Cara Kerja**: Ketika pelanggan mengirim WA bertanya, "Apakah kamar hotel masih ada?" atau "Berapa harga menu kopi susu?", pesan diteruskan ke backend Laravel. **Laravel AI SDK** menggunakan Gemini LLM \+ *Vector Search* (Katalog toko yang di-embed ke vektor database) untuk merespons layaknya admin profesional (bebas halusinasi karena dibatasi hanya pada data katalog toko tersebut).

### **5\. Manajemen Perangkat Keras & Kasir Portabel (Hardware Layer)**

Untuk mendukung operasional lapangan, sistem dibangun ramah terhadap perangkat *mobile* (Tablet Android/iPad):

* **Sinkronisasi Dual-Database**: Saat operasional sangat sibuk, *stok* dikurangi seketika di memori **Redis (ATP)** agar kasir tidak menjual barang yang sudah habis (cegah overselling), kemudian disinkronisasikan ke **PostgreSQL** secara *asynchronous* (Job Queue) sebagai *Audit Trail* permanen.  
* **Cetak Faktur (Printer Thermal Bluetooth)**:  
  * Aplikasi frontend akan menggunakan **Web Bluetooth API** (atau melalui native wrapper Android) untuk mengirim *raw commands* (ESC/POS) langsung ke printer thermal portabel kasir tanpa perlu instalasi driver yang rumit.  
  * **Custom Receipt Generator**: Pemilik bisnis disediakan *dashboard* *drag-and-drop* sederhana (di-handle lewat JSON konfigurasi di backend). Mereka bisa mengatur:  
    1. *Header*: Logo Toko, Nama, Alamat, Pesan Sambutan.  
    2. *Body*: Layout tabel barang pesanan, pajak, diskon.  
    3. *Footer*: Pesan "Terima kasih", Password WiFi dinamis, atau Link Sosial Media.

### **Kesimpulan Alur Layanan (PaaS) Unimart**

1. **Akuisisi (iZone & QR Menu)**: Pelanggan duduk di kafe/masuk grosir \-\> Connect WiFi / Scan Barcode \-\> Muncul Halaman Unimart Toko (Generative UI sesuai tipe bisnis).  
2. **Transaksi (Order & Pay)**: Pelanggan memilih barang \-\> Bayar via QRIS Midtrans atau Upload Bukti Transfer \-\> Pesanan masuk.  
3. **Operasional (POS & Dapur)**: Tablet Android Kasir berbunyi (via Laravel Reverb) \-\> Dapur mulai proses \-\> Kasir menekan tombol "Cetak" (Koneksi Bluetooth ke Printer Thermal mencetak struk *custom*).  
4. **Manajemen & Pertumbuhan (Owner)**: Pemilik bisnis memantau *Dashboard* Real-time secara *remote*. Malam harinya, WhatsApp Bot (Gemini AI) tetap bekerja melayani reservasi pelanggan untuk esok hari.

# Caffe (FnB)

Berikut adalah rancangan pembaruan arsitektur MVP Unimart:

### **1\. Penyesuaian Arsitektur Teknis untuk MVP (cPanel Environment)**

Karena keterbatasan cPanel (tidak ada *daemon* proses jangka panjang seperti WebSocket server atau Redis), kita melakukan strategi "Graceful Fallback":

* **Pengganti Redis (Cache & Queue):** Kita akan mengubah `CACHE_DRIVER` ke `file` dan `QUEUE_CONNECTION` ke `database`. Antrean pesan (seperti pengiriman WhatsApp) akan disimpan di tabel `jobs` PostgreSQL, dan dijalankan menggunakan fitur **Cron Job cPanel** (menjalankan `php artisan schedule:run` setiap menit).  
* **Pengganti Laravel Reverb (Real-time KDS & Kasir):**  
  * **AJAX Short Polling:** Untuk layar Dapur (KDS) dan Kasir yang selalu terbuka, kita gunakan metode *polling* ringan (misal: mengambil data ke server setiap 5-10 detik) menggunakan fitur framework frontend (seperti `setInterval` di Vue/React atau `wire:poll` jika menggunakan Livewire).  
  * **Web Push Notification:** Menggunakan **Service Worker (Web Push API)**. Saat pesanan masuk, server mengirimkan *push notification* yang akan memunculkan *pop-up* di browser Chrome kasir/koki meskipun mereka sedang membuka tab lain.  
  * **WhatsApp Notification:** Jika kasir/koki sedang tidak di depan layar, notifikasi via WA Gateway (Evolution API) akan dikirim ke nomor grup dapur atau nomor kasir.  
* **Pengganti `pgvector` untuk AI Gemini:** Karena `pgvector` belum didukung, kita **tidak memerlukan Vector Search**. *Google Gemini 1.5 Flash* memiliki kapasitas *Context Window* hingga 1 juta hingga 2 juta token (setara ribuan halaman buku). Saat ada pelanggan bertanya via WA, backend Laravel cukup menarik *seluruh data katalog menu* dalam format JSON/Teks biasa dan menyisipkannya langsung ke dalam *Prompt* Gemini. Untuk skala MVP Kafe, ini sangat efisien dan tetap bebas halusinasi.

### **2\. Fitur Rekonsiliasi Tutup Kasir & Multi-Payment (Shift Management)**

Ini adalah fitur yang paling disukai pemilik bisnis (Owner) karena mengunci "kebocoran halus". Sistem akan membedakan uang fisik dan uang digital secara tegas.

**Struktur Data & Alur:**

* Sistem mencatat transaksi berdasarkan `payment_method` (Tunai, QRIS Midtrans, Transfer Manual, Debit/Kredit, Kasbon).  
* **Alur Tutup Kasir (End of Day/Shift):**  
  1. Saat Kasir menekan tombol **"Tutup Kasir"**, sistem menampilkan rekapitulasi:  
     * *Saldo Awal Kasir*: Rp 500.000 (Tunai)  
     * *Total Penerimaan Tunai*: Rp 1.200.000  
     * *Total Pengeluaran Kas (Beli Es/Gas)*: \- Rp 100.000  
     * **Ekspektasi Uang di Laci Kasir**: **Rp 1.600.000**  
  2. Kasir **diwajibkan menghitung uang fisik secara manual** dan memasukkan nominalnya ke sistem (misal Kasir menginput: Rp 1.580.000).  
  3. Sistem mencatat ada **Selisih (Minus) Rp 20.000**.  
  4. Sistem juga menampilkan laporan terpisah: *Total Pendapatan QRIS/Transfer Bank \= Rp 2.500.000 (Langsung masuk rekening Owner, aman dari jangkauan kasir).*  
* **Notifikasi Owner:** Begitu kasir selesai menekan submit, sebuah rangkuman Laporan Tutup Kasir otomatis dikirim ke WhatsApp Owner, sehingga Owner tahu persis kondisi keuangan hari itu tanpa perlu datang ke toko.

### **3\. Manajemen Karyawan Berbasis Peran (RBAC \- Role-Based Access Control)**

Dengan menggunakan *Spatie Laravel-Permission* (atau skema bawaan Laravel), Owner dapat mengundang karyawannya untuk masuk ke dalam ekosistem Caffe miliknya.

* **Pemisahan Akses (Generative UI):** Berdasarkan Role (Peran) yang login, UI yang di-render oleh JSON Generative UI akan berbeda:  
  * **Role Owner:** Akses penuh. Bisa melihat laporan omzet, selisih kasir harian, mengubah harga menu, menambah/menghapus karyawan, memantau *rating/review*.  
  * **Role Kasir:** Hanya melihat layar POS (Point of Sale), memproses pembayaran, menginput uang keluar (*petty cash*), upload bukti transfer manual, dan melakukan Tutup Kasir. Tidak bisa mengubah harga menu atau menghapus riwayat transaksi.  
  * **Role Koki/Dapur:** Hanya melihat layar pesanan yang masuk (KDS). Tugasnya hanya menekan tombol "Pesanan Dimasak" dan "Pesanan Selesai".  
  * **Role Waiter (Pelayan):** Hanya bisa melihat katalog menu untuk membantu tamu memesan (input pesanan dari meja ke sistem) menggunakan HP/Tablet mereka sendiri.

**Alur Penambahan Karyawan:**

1. Owner masuk ke menu "Manajemen Tim".  
2. Owner menambah akun baru (memasukkan Nama, No HP/WA, PIN login).  
3. Owner memilih Role (misal: "Koki").  
4. Karyawan login di HP mereka masing-masing menggunakan No HP dan PIN yang dibuatkan Owner. Layar mereka otomatis hanya menampilkan fitur sesuai fungsi kerja mereka.

# Intruksi Khusus Agent Coding

\# Dokumen Arsitektur & Konteks Sistem: Unimart.Group (Caffe MVP)  
\*\*Versi:\*\* 1.0 (MVP Edition)    
\*\*Target Lingkungan:\*\* Shared Hosting cPanel (Apache, PHP 8.4, PostgreSQL)    
\*\*Tujuan Dokumen:\*\* Konteks referensi utama untuk agen AI (Antigravity) dalam pengembangan \*codebase\* Laravel.

\---

\#\# 1\. Ringkasan Eksekutif  
\*\*Unimart.Group\*\* adalah platform \*B2B Enterprise SaaS (Software as a Service)\* Multi-Tenant yang berfungsi sebagai "Sistem Saraf Digital" untuk entitas bisnis lokal (terutama Caffe/F\&B pada fase MVP ini).   
Sistem ini tidak hanya berfungsi sebagai POS (Point of Sale), tetapi mengotomatisasi seluruh alur operasional: dari akuisisi pelanggan via \*Captive Portal Hotspot\*, pemesanan mandiri (QR Menu), manajemen dapur (KDS), rekonsiliasi laci kasir, hingga integrasi Asisten AI via WhatsApp.

\#\# 2\. Tumpukan Teknologi (Tech Stack) MVP  
Karena target deployment MVP adalah \*\*cPanel\*\*, agen AI (Antigravity) \*\*WAJIB\*\* mematuhi batasan infrastruktur berikut:  
\* \*\*Backend:\*\* Laravel 13.x  
\* \*\*Database:\*\* PostgreSQL (Menggunakan fitur JSONB, namun \*\*TIDAK ADA\*\* ekstensi \`pgvector\`).  
\* \*\*Cache & Session:\*\* \`file\` atau \`database\` (Mengingat Redis belum tersedia).  
\* \*\*Queue:\*\* \`database\` driver (Dijalankan via Cron Job cPanel \`schedule:run\` setiap menit).  
\* \*\*Real-time / Broadcast:\*\* Tidak menggunakan Laravel Reverb/WebSockets. Gunakan \*\*AJAX Short Polling\*\* (interval 5-10 detik) untuk Kitchen Display System (KDS) dan layar Kasir. Gunakan \*Web Push Notification\* (Service Worker) sebagai pelengkap.  
\* \*\*AI Engine:\*\* Google Gemini 1.5 Flash via \`laravel/ai\` SDK. Karena tidak ada vector DB, gunakan teknik \*Massive Context Window\* (menyisipkan seluruh JSON katalog ke dalam sistem prompt).  
\* \*\*Frontend:\*\* Blade Templating \+ Alpine.js / Livewire / Vue (Inertia) sesuai preferensi, ramah mobile (Mobile-First Design).

\#\# 3\. Arsitektur Multi-Tenancy & Keamanan  
\* \*\*Pendekatan:\*\* Single Database, Shared Schema dengan isolasi \*\*Foreign Key (\`tenant\_id\`)\*\*.  
\* \*\*Keamanan:\*\* Wajib mengaktifkan \*\*Row-Level Security (RLS)\*\* pada PostgreSQL atau mengaplikasikan \*Global Scope\* di Eloquent Laravel secara ketat agar data (Order, Menu, Shift) tidak pernah bocor antar Caffe.  
\* \*\*Role-Based Access Control (RBAC):\*\* Menggunakan Spatie Permission.  
  \* \`Owner\`: Akses dashboard laporan, menu, kelola staf.  
  \* \`Cashier\`: Buka/Tutup shift, POS, terima pembayaran, input uang keluar.  
  \* \`Chef\`: Akses layar Kitchen Display System (KDS) saja.  
  \* \`Waiter\`: Akses katalog menu untuk membantu tamu memesan.

\#\# 4\. Alur Bisnis Inti (F\&B / Caffe)

\#\#\# A. Gerbang Digital (Akuisisi via MikroTik)  
\* Integrasi dengan RouterOS MikroTik (via API). Saat pelanggan masuk ke WiFi Caffe, mereka diarahkan (RFC7710) ke Captive Portal Laravel.  
\* Halaman Captive Portal langsung berubah menjadi \*\*Katalog Menu Digital / Self-Order\*\*.

\#\#\# B. Alur Pemesanan (Generative UI Concept)  
Sistem mendukung 2 alur yang dikendalikan oleh konfigurasi JSON (Generative UI):  
1\. \*\*Dine-In (Pesan di Meja):\*\* Pelanggan input No. Meja \-\> Pilih Menu \-\> Checkout \-\> Status transaksi: \*Unpaid\*.  
2\. \*\*Counter Order / Fast Food:\*\* Kasir input pesanan \-\> Langsung Bayar \-\> Status transaksi: \*Paid\*.

\#\#\# C. Kitchen Display System (KDS)  
\* Pesanan yang masuk otomatis muncul di layar Dapur via AJAX Polling.  
\* \*\*SLA Tracking:\*\* Frontend menghitung durasi sejak \`created\_at\`. Jika waktu tunggu \> 15 Menit, blok antrean berubah warna menjadi \*\*Merah / Berkedip\*\*.  
\* Chef menekan tombol "Selesai" untuk mengupdate status dari \`cooking\` ke \`ready\`.

\#\#\# D. Manajemen Kasir & Rekonsiliasi Shift  
Fitur anti-bocor untuk Owner:  
\* \*\*Buka Shift:\*\* Sistem memuat saldo sistem hari sebelumnya, kasir bisa \*override\* modal uang tunai laci (Starting Cash).  
\* \*\*Petty Cash (Uang Keluar):\*\* Fitur mencatat pengeluaran mendadak dari laci kasir (misal: beli es batu).  
\* \*\*Tutup Shift:\*\* Kasir wajib menghitung uang fisik secara buta (Blind Close). Sistem akan mencocokkan: \`Saldo Awal \+ Tunai Masuk \- Petty Cash\`. Selisih akan dilaporkan via WA ke Owner. Pendapatan QRIS terpisah dan tidak masuk perhitungan laci.

\#\#\# E. Pembayaran & Cetak Struk  
\* \*\*QRIS Gateway:\*\* Integrasi webhook (Midtrans/Xendit).  
\* \*\*Manual Transfer:\*\* Upload bukti foto. (Bisa divalidasi manual oleh kasir).  
\* \*\*Cetak Struk:\*\* Frontend terhubung langsung ke Printer Thermal Bluetooth portabel via Web Bluetooth API (mengirim ESC/POS raw commands). Header/Footer struk dikonfigurasi via JSON.

\#\# 5\. Struktur Database Utama (Konteks Skema)

\*(Instruksi untuk Antigravity: Buat migration berdasarkan struktur logis ini)\*

1\. \`tenants\`: \`id\`, \`name\`, \`domain\`, \`wifi\_radius\_secret\`, \`settings\` (JSONB \- untuk layout struk & UI).  
2\. \`users\`: \`id\`, \`tenant\_id\`, \`name\`, \`role\`, \`pin\` (untuk login cepat kasir/waiter).  
3\. \`shifts\`: \`id\`, \`tenant\_id\`, \`user\_id\`, \`opened\_at\`, \`closed\_at\`, \`starting\_cash\`, \`actual\_ending\_cash\`, \`system\_ending\_cash\`, \`notes\`.  
4\. \`expenses\` (Petty Cash): \`id\`, \`tenant\_id\`, \`shift\_id\`, \`amount\`, \`description\`.  
5\. \`products\` / \`menus\`: \`id\`, \`tenant\_id\`, \`name\`, \`price\`, \`stock\` (integer, sinkronisasi ketat anti-overselling via DB lock/transactions), \`category\`, \`image\_url\`.  
6\. \`orders\`: \`id\`, \`tenant\_id\`, \`shift\_id\`, \`order\_number\`, \`table\_number\`, \`status\` (\`pending\`, \`cooking\`, \`ready\`, \`completed\`), \`payment\_status\` (\`unpaid\`, \`paid\`), \`payment\_method\` (\`cash\`, \`qris\`, \`transfer\`), \`total\_amount\`.  
7\. \`order\_items\`: \`id\`, \`order\_id\`, \`product\_id\`, \`quantity\`, \`price\`, \`notes\`.

\#\# 6\. Asisten AI (WhatsApp Bot 24/7)  
\* \*\*Gateway:\*\* Menggunakan webhook ke sistem API WhatsApp (Evolution API / Cloud API).  
\* \*\*Logic:\*\* Webhook masuk ke Laravel \-\> Route ke Job Queue (Database driver) \-\> Proses via \`laravel/ai\` SDK.  
\* \*\*Prompt Engine:\*\* Tarik data menu Caffe (\`products\` dimana \`tenant\_id \= X\`), format ke JSON string, masukkan ke parameter context Gemini. Berikan instruksi: "Anda adalah asisten kasir Caffe X. Jawab ketersediaan stok dan harga berdasarkan data JSON ini. Jangan berhalusinasi."

\---

\#\# 7\. DIREKTIF KHUSUS UNTUK AI CODER ("ANTIGRAVITY")  
Saat menerima instruksi untuk membangun fitur berdasarkan arsitektur ini, Anda (Antigravity) wajib mematuhi aturan berikut:  
1\. \*\*No External Daemons:\*\* Jangan pernah mengusulkan \`php artisan reverb:start\`, koneksi Redis, atau daemon supervisor panjang. Ingat, aplikasi ini berjalan di \*\*cPanel\*\*. Gunakan Cron Jobs Laravel (\`schedule:run\`) dan pendekatan \*stateless\* (AJAX/File Cache).  
2\. \*\*Database Transactions:\*\* Saat memproses \`order\` (terutama memotong \`stock\`), selalu gunakan \`DB::transaction()\` dan \`lockForUpdate()\` untuk mencegah \*race condition\* karena kita tidak memakai Redis ATP.  
3\. \*\*Generative UI:\*\* Hindari \*hardcode\* konfigurasi tampilan. Gunakan kolom JSONB di database (misal tabel \`tenants\` kolom \`settings\`) untuk menyimpan field form dinamis, header/footer struk, dan mengaturnya via backend.  
4\. \*\*Security First:\*\* Pastikan setiap \*Query\* ke database selalu di-scope menggunakan \`where('tenant\_id', current\_tenant\_id())\` atau menggunakan Global Scope Traits. Isolasi data adalah harga mati.  
5\. \*\*KISS (Keep It Simple, Stupid):\*\* Fokus pada penyelesaian MVP. Kode harus modular, mudah dipelihara, dan mengikuti \*clean stack\* Laravel 13\.  
