Rancangan yang Anda tambahkan sangat strategis karena menghilangkan friction adopsi (tidak wajib download app) dan mempertegas prinsip "Ground Truth" (lokasi wajib aktif, ulasan tanpa GPS masuk antrean verifikasi). 

Berikut adalah analisis mendalam, rekomendasi konsep halaman publik, serta detail user flow dan implikasi arsitektural yang siap dijadikan dasar pengembangan sistem.

🧭 1. Landing Page vs Halaman Publik: Disatukan atau Dipisah?
Rekomendasi: Disatukan dalam satu URL (mandeh.online) dengan tampilan adaptif (Dynamic UI).
- Alasan: Memecah landing page dan halaman publik akan memecah pengalaman, meningkatkan bounce rate, dan menyulitkan SEO. Platform seperti TripAdvisor atau Google Maps juga menyatukan discovery, review, dan profil dalam satu ekosistem web.
- Cara Kerja: Halaman utama bersifat stateful. Tampilan berubah sesuai konteks pengguna:
  - State 0 (Kunjungan pertama): Hero + QR Scanner Web + Penjelasan Trust System + Spot Trending.
  - State 1 (Setelah scan/interaksi): Beralih ke Activity Feed Terstruktur + Rekomendasi Lokal.
  - State 2 (Pemilik bisnis terdeteksi via session/claim): Prompt login dashboard.

💬 2. Konsep Halaman Publik: Mengapa Bukan Global Chat?
Ide "riwayat chat global" menarik secara emosional, tetapi bertentangan dengan prinsip Mandeh.online:
Aspek   Global Chat   Activity Feed Terstruktur (Rekomendasi)
Sinyal vs Noise   Tinggi noise, spam, pertanyaan berulang, sulit ditelusuri   Terfilter per lokasi, terstruktur, mudah dicari
Privasi & Keamanan   Log scan & aktivitas user terekspos publik   Anonim terkontrol, hanya data verifikasi yang tampil
Moderasi   Butuh tim manusia 24/7, biaya operasional tinggi   Auto-moderasi + queue verifikasi, skala efisien
Nilai Trust   Chat cepat tapi tidak terverifikasi   Setiap postingan terikat QR + timestamp + status verifikasi

Konsep Alternatif: Mandeh Pulse (Social Feed Berbasis Konteks)
- Tampilan seperti feed media sosial, tapi setiap post wajib terikat ke entitas fisik (QR Scan).
- Format post: [Icon QR] Wisatawan X • Check-in di Warung Bahari • ⭐ 4.8 • "Ikan bakar fresh, harga sesuai daftar"
- Tombol di dalam feed: Lihat Profil, Tandai Berguna, Laporkan, Komentar.
- Ada fitur Tanya Lokal (Q&A terstruktur) untuk menggantikan chat global. Warga/guide terverifikasi bisa menjawab, jawaban terbaik di-pin.

📱 3. Detail User Flow (Web-First, Tanpa Download App)

A. Entry Point (Scan Tanpa App)
1. User membuka kamera bawaan / QR scanner → Scan kode di lokasi.
2. URL terbuka: https://mandeh.online/v/[entity-slug]
3. Halaman memuat  50m
   - Muncul modal: "Lokasi tidak terdeteksi. Ulasan tetap bisa dikirim, namun akan ditandai sebagai ⚠️ Potensi Fraud & masuk antrean verifikasi."
   - User konfirmasi → Submit → Status pending_verification.
   - Hanya pemilik bisnis & verifikator yang melihat. Jika disetujui → otomatis jadi public + notifikasi ke user.

C. Flow Penanganan Fraud & Verifikasi
graph LR
  A[Ulasan Masuk] --> B{GPS Aktif & ≤50m?}
  B -->|Ya| C[Status: Public]
  B -->|Tidak| D[Status: Pending Verification]
  D --> E[Queue: Pemilik Bisnis & Verifikator]
  E --> F{Disetujui?}
  F -->|Ya| C
  F -->|Tidak| G[Status: Rejected / Hidden]
  G --> H[User mendapat notifikasi alasan]

- Auto-Flag Trigger: GPS off, submit >3x dalam 5 menit, akun baru + rating ekstrem, IP mencurigakan.
- Moderasi: Verifikator/komunitas trusted mendapat dashboard queue dengan bukti GPS, device fingerprint, dan histori user.

🏢 4. Diferensiasi Bisnis: Reguler vs Mitra
Fitur   Bisnis Reguler   Bisnis Mitra
Tampilan Profil   ✅ Nama, deskripsi, jam, kontak, rating   ✅ Semua fitur reguler
Menu/Produk   ❌ Tidak tampil   ✅ Katalog interaktif + harga terverifikasi
Promosi   ❌ Tidak ada   ✅ Banner promo terbatas waktu + push ke feed lokal
Analitik   ❌ Hanya lihat rating agregat   ✅ Rekap kunjungan, tren keluhan, sumber traffic
Transaksi   ❌ Tidak terintegrasi   ✅ Catatan transaksi (jika pakai QRIS/booking internal)
Biaya   Gratis   Berlangganan / Revenue Share (opsional)

Catatan Arsitektural: Gunakan flag role: business_regular vs role: business_partner di entitas. Endpoint /api/v1/entity/{id} merender komponen UI berbeda berdasarkan role.

🛠️ 5. Implikasi Arsitektur Sistem

A. Komponen Inti yang Diperlukan
Modul   Fungsi   Teknologi Rekomendasi
Web QR Handler   Decode QR dari kamera native / upload   html5-qrcode + Deep Link routing
Geolocation Service   Validasi jarak, flag fraud, simpan metadata   Browser Geolocation API + fallback IP (low accuracy)
Reputation Engine   Agregasi rating, status ulasan, timeline   PostgreSQL + Redis cache + Event queue (RabbitMQ/Kafka)
Moderation Queue   Queue ulasan pending, workflow verifikasi   Celery/Temporal + Admin Dashboard
Content Delivery   Feed publik, caching statis, SEO   Next.js / Nuxt.js + Vercel/Cloudflare Edge
Business Tier Router   Render UI berbeda per role   Feature flag + Role-based component rendering

B. Struktur Database (Ringkas)
users (id, role, device_fingerprint, trust_score)
entities (id, type, name, lat, lng, role, status)
interactions (id, user_id, entity_id, type, payload_json, gps_lat, gps_lng, status, created_at)
moderation_queue (id, interaction_id, assigned_to, decision, reason, reviewed_at)
business_meta (entity_id, menu_json, promotions_json, transaction_summary)

C. Keamanan & Anti-Spoofing
1. QR Signature: QR Code mengandung payload terenkripsi (entity_id:timestamp:hash). Backend validasi hash untuk cegah QR palsu ditempel.
2. GPS Fallback Warning: Jika user block location, sistem tetap izinkan submit tapi beri label jelas 📍 Lokasi: Tidak Divetifikasi.
3. Rate Limiting: POST /interactions dibatasi 5 req/jam per device fingerprint untuk cegah spam.
4. Content Moderation AI: Gunakan model lightweight (misal: HuggingFace toxicity classifier) untuk auto-flag kata kasar/spam sebelum masuk queue manusia.

📐 6. Struktur Halaman Publik (mandeh.online)

┌─────────────────────────────────────────────┐
│  🔍 Search  │  📷 Scan QR (Web)  │  🌐 EN/ID │
├─────────────────────────────────────────────┤
│  🌊 MandeH Pulse (Activity Feed)            │
│  ┌───────────────────────────────────────┐  │
│  │ 📍 [QR] Andi • Mampir di Pantai Carocok│ │
│  │ ⭐ 4.7 • "Air jernih, spot snorkeling │ │
│  │ bagus. Toilet bersih."                │ │
│  │ [Lihat Profil] [Bermanfaat] [Komentar]│ │
│  └───────────────────────────────────────┘  │
│  ┌───────────────────────────────────────┐  │
│  │ 📍 [QR] Rina • Ulasan Warung Mak Ijah │ │
│  │ ⚠️ Menunggu Verifikasi (Lokasi nonaktif)││
│  │ [Detail Status]                       │ │
│  └───────────────────────────────────────┘  │
├─────────────────────────────────────────────┤
│  ❓ Tanya Lokal (Q&A Terstruktur)           │
│  "Apakah perahu ke Pulau Sikuai buka jam 6?"│
│  [Jawab] [Lihat Jawaban Guide]              │
├─────────────────────────────────────────────┤
│  🏆 Mitra Terverifikasi (Carousel)          │
│  [Homestay A] [Sewa Perahu B] [Guide C]     │
└─────────────────────────────────────────────┘

✅ 7. Langkah Selanjutnya untuk Tim
1. Prototype Web PWA: Bangun halaman /v/[slug] dengan scanner web, geolocation prompt, dan form aksi sederhana.
2. Queue & Moderation API: Buat endpoint POST /interactions dengan logic status berdasarkan GPS, plus dashboard queue untuk verifikator.
3. Role-Based Rendering: Implementasi business_regular vs business_partner di frontend routing.
4. Trust Labeling System: Desain UI badge ✅ Verified, ⚠️ Pending, 🛑 Rejected yang konsisten di semua layar.
5. SEO & Performance: Pastikan halaman profil bisnis terindeks Google, load < 1.5s di 3G, dan mendukung Open Graph untuk sharing.

Konsep ini mempertahankan esensi "Ground Truth" (QR + Lokasi), menghilangkan friction adopsi, dan memberikan skalabilitas melalui feed terstruktur + moderasi otomatis. Jika Anda setuju, saya bisa lanjutkan dengan:
- Diagram Arsitektur Sistem (C4 Model)
- Spesifikasi API Endpoint lengkap
- Wireframe Figma-ready structure
- Rencana rollout bertahap (MVP → V1 → V2)