---
title: "Master Context — Riset & Seleksi Produk AI untuk Pasar Kabupaten/Kota"
version: "1.0"
as_of: "2026-10-10"
language: "id-ID"
status: "Exploratory research / belum ada produk final yang dipilih"
owner_context: "Founder/tim pengembangan Unimart Group dan ide produk terkait"
intended_audience: ["Founder", "Tim Produk", "Tim Engineering", "AI Agent", "Calon Mentor dan Investor (setelah verifikasi)"]
source_documents:
  - "IBOT.LOCOMO-Plann-Marketing.md"
  - "Rancangan_Strategis_Unimart_Group.pdf"
  - "TIMELINE-SUBMISSION-UNIMART-2026-2027.md"
source_scope: "Ketiga dokumen terlampir dan rangkaian diskusi pengguna–asisten sampai 10 Oktober 2026"
external_verification: "Belum dilakukan untuk versi ini; jadwal program, statistik, regulasi, dan lanskap kompetisi perlu dicek sebelum dipublikasikan"
---

# MASTER CONTEXT: RISET PORTOFOLIO IDE PRODUK DIGITAL BERBASIS AI
## Strategi niche, validasi kebutuhan lokal, defensibility, dan kesiapan pendanaan

> **STATUS KEPUTUSAN UTAMA:** Tim **belum memilih produk akhir**. Seluruh konsep di bawah merupakan kandidat eksplorasi, tesis atau usulan; **bukan** klaim bahwa sistem sudah dibangun, produk terbukti digunakan, atau investor sudah tertarik. Jangan memperlakukan rekomendasi dalam percakapan terdahulu sebagai keputusan founder yang final.

## 0. Petunjuk untuk AI agent lain (baca lebih dulu)

Dokumen ini sengaja ditulis sebagai *knowledge base / context handoff*. Agent yang membaca dokumen ini harus:

1. Memahami **tujuan utama**: menemukan **produk AI yang spesifik, bernilai, dapat diuji pada kebutuhan kabupaten/kota (daerah tingkat II), berpotensi berkembang lintas daerah, dan memiliki tesis pendanaan**. Target bukan meluncurkan semua modul Unimart.
2. Membaca label **[SUMBER]**, **[DISKUSI]**, **[IDE BARU]**, **[HIPOTESIS]**, **[USULAN]**, **[BELUM TERBUKTI]** secara literal. Jangan menyamakan ketiganya.
3. Tidak mengasumsikan satu nama merek/arsitektur/segmen/MVP sudah ditetapkan. Unimart, Locomo/iBot, atau nama lain dapat berfungsi sebagai platform, merek, modul, atau proposal berbeda, dan hubungan mereknya **belum diputuskan**.
4. Tidak menyatakan angka merchant, minat bayar, pendapatan, conversion, traction, akurasi AI, atau daya tarik investor sebagai fakta tanpa data pilot.
5. Tidak menganggap AI boleh mengeksekusi perubahan uang, pesanan, harga, iklan, data personal, atau pesan pelanggan secara otomatis tanpa izin dan audit.
6. Bila diminta meneruskan riset, mulai dengan **perbandingan masalah nyata, niche, willingness-to-pay, kemampuan distribusi, unit economics, defensibility, serta kecepatan memperoleh bukti**, bukan menambah fitur tanpa batas.
7. Membaca *decision register* pada §15 sebelum menyarankan roadmap atau menulis pitch deck; gunakan daftar pertanyaan terbuka pada §16.

### Konvensi penandaan sumber dan status

- **[SUMBER–LOC]**: didukung `IBOT.LOCOMO-Plann-Marketing.md`.
- **[SUMBER–STR]**: didukung `Rancangan_Strategis_Unimart_Group.pdf` (dokumen September 2026, 21 halaman).
- **[SUMBER–FUND]**: didukung `TIMELINE-SUBMISSION-UNIMART-2026-2027.md` (tanggal acuan 26 September 2026).
- **[DISKUSI]**: dinyatakan founder atau dianalisis dalam percakapan; belum tentu dibuktikan melalui riset lapangan.
- **[IDE BARU]**: ide eksplorasi founder yang disampaikan pada 10 Oktober 2026.
- **[USULAN]**: elaborasi/opsi desain dari asisten; bukan keputusan.
- **[HIPOTESIS]**: asumsi yang membutuhkan pembuktian.
- **[BELUM TERBUKTI]**: status tanpa bukti pilot atau verifikasi eksternal.

---

## 1. Ringkasan eksekutif

Founder ingin melakukan riset lebih lanjut untuk menentukan produk digital berbasis AI yang paling layak dibangun dan diajukan untuk pendanaan. Fokus awal adalah kabupaten/kota Indonesia, terutama pasar yang relatif kurang terlayani oleh solusi yang berorientasi pada kota besar. **Asumsi bahwa pasar daerah masih “terbuka” perlu diuji per kategori dan kompetitor**, bukan dijadikan klaim faktual. Founder memiliki akses konteks operasional usaha lokal dan pengalaman membangun perangkat lunak bisnis; keunggulan akses distribusi tersebut perlu dibuktikan dalam akuisisi pilot.

Ide yang kini hidup dalam portofolio riset:

1. **Unimart Voice-Native Business Operations**: AI dan suara untuk menjalankan serta mengontrol aktivitas F&B/grosir/outlet, termasuk pesanan, stok, koordinasi staf, rekonsiliasi, dan pemberitahuan owner.
2. **Locomo / iBot Commerce Assistant**: memahami kebutuhan pembeli, katalog merchant, menemukan penawaran yang cocok, dan membantu proses sampai pesanan selesai.
3. **AI Local Commerce Intelligence Network**: jaringan permintaan–penawaran lokal dengan agent yang mencocokkan kebutuhan dan merchant, menindaklanjuti peluang penjualan, serta mencatat hasil transaksi terverifikasi.
4. **AI Merchant Growth & Creator Referral Agent**: pemasaran, pengelolaan kampanye, referral/kreator, percakapan pelanggan, penjualan, atribusi, dan status pembayaran.
5. **Unified Payment Verification / Outlet Add-on**: QRIS dinamis, status pembayaran yang dapat dilihat staf sesuai hak akses, audit dan rekonsiliasi, tanpa bergantung pada screenshot.
6. **Personalized Education with Professional Teacher Oversight** **[IDE BARU]**: pembelajaran adaptif berbantuan AI, disertai pemantauan perkembangan oleh tenaga pengajar profesional.
7. **Smart Store / Shelf Finder Add-on** **[IDE BARU]**: katalog dan pemandu pelanggan minimarket/swalayan untuk menemukan lokasi produk hingga lorong/rak, layaknya pencarian lokasi buku di perpustakaan/toko buku.

**Kesimpulan sementara:** pilihan akhir **belum ditetapkan**. Tesis commerce network sempat diprioritaskan untuk dielaborasi dalam diskusi, tetapi founder kemudian secara eksplisit membuka kembali proses penelitian semua ide. Ini **bukan** keputusan untuk membatalkan voice, menggabungkan EdTech ke Unimart, atau membangun semua produk sekaligus.

Satu prinsip bersama: **AI seharusnya menciptakan atau menyelesaikan hasil kerja nyata dan terukur**—bukan sekadar antarmuka chat, STT/TTS, pembuat caption, atau integrasi API yang mudah ditiru. Defensibility diharapkan bersumber dari kedalaman workflow, data yang diperoleh secara sah, hubungan distribusi, bukti performa, ketergantungan pada hasil, dan network effects yang benar-benar terukur.

---

## 2. Asal-usul konteks: ringkasan tiga lampiran

### 2.1 Dokumen Locomo / iBot [SUMBER–LOC]

**Tesis yang tertulis:** Locomo memudahkan pemilik usaha memasukkan atau mengoreksi katalog lewat percakapan, serta memudahkan pembeli menjelaskan kebutuhan kemudian memperoleh pilihan barang/jasa yang relevan berdasarkan informasi yang tersedia (harga, lokasi, stok, jadwal). Contoh sumber meliputi menambahkan minyak goreng di toko Lumpo, mencari gula dalam batas anggaran, atau jasa pengelasan di Bayang.

Prinsip pokok dari sumber:

- Tidak wajib memiliki katalog sempurna saat onboarding; data dapat dilengkapi bertahap melalui percakapan.
- Produk sama dapat berbeda harga dan stok antar-outlet.
- Agent mengoordinasikan pertanyaan, penawaran, konfirmasi, dan status pesanan, tetapi **keputusan perubahan dan komitmen penting tetap di tangan pihak berwenang**.
- Teks, tombol, dan suara merupakan pilihan interaksi; hasil STT harus dapat diperiksa/dikoreksi.
- Personalisasi pelanggan dan penggunaan riwayat mensyaratkan izin/kontrol pengguna; data internal seperti harga modal, pemasok, dan kontak penanggung jawab perlu dibatasi.
- Program referral dijelaskan sebagai mekanisme undangan dan pertumbuhan ekosistem; **dokumen tidak membuktikan bahwa mesin atribusi komisi atau payout telah dibangun**.

**Interpretasi untuk riset:** kandidat awal *two-sided commerce coordination*, berpotensi berkembang menjadi demand matching; sumber belum memuat bukti market size, transaksi, atau product-market fit.

### 2.2 Rancangan Strategis Unimart Group [SUMBER–STR]

**Tesis tertulis:** *voice-native business operating system* untuk usaha offline yang bergantung pada pemilik. Segmentasi awal yang direkomendasikan dokumen adalah F&B independen di kota/kabupaten lapis kedua dengan kira-kira 1–5 outlet dan 3–20 pekerja, kemudian grosir/retail.

Pokok penting dokumen:

- **Masalah inti:** pemilik tidak mempunyai sistem yang cukup dipercaya untuk menjalankan usaha tanpa selalu hadir.
- **Closed-loop voice operations:** ucapan -> intent terstruktur -> resolusi entitas -> cek izin -> konfirmasi -> tindakan deterministik -> event -> pengakuan/eskalasi/penyelesaian -> audit.
- **Contoh vertikal F&B:** pelayan mengucap order; dapur menerima; kekurangan bahan memicu pemberitahuan/substitusi; pelanggan memutuskan; status kembali ke dapur/owner.
- **Business Memory Agent:** konteks harus berbentuk entity, event, state, dan relasi yang dapat diaudit, bukan tumpukan chat.
- **Kontrol:** voice-first *bukan* voice-only; approval untuk tindakan berisiko; minimisasi data; operasi lokal/offline bila dibutuhkan.
- **Teknologi:** MVP cloud dengan klien Android/Windows dan koneksi real-time; local/hybrid/private edge merupakan tahap lanjut apabila kebutuhan latency, privasi, offline, dan biaya terbukti.
- **Go-to-market:** satu kabupaten, wawancara/observasi merchant, design partners, pilot terukur, lalu ekspansi.
- **Revenue:** langganan software, tier AI, onboarding; perangkat/add-on/partner revenue pada fase lanjut.
- **Moat yang diklaim sebagai calon sumber:** workflow graph, istilah/satuan lokal, correction data, distribusi lokal, edge deployment, kepercayaan operasional.
- **North-star versi dokumen:** tindakan bisnis yang berhasil diselesaikan per outlet per minggu; bukan jumlah transkripsi.

**Hal yang tidak boleh dikaburkan:** dokumen September 2026 menempatkan **F&B closed-loop voice** sebagai MVP dan positioning awal, bukan AI sales network. Proposal commerce network adalah pengembangan hipotesis kemudian, dan berbeda dari keputusan dalam dokumen asal.

### 2.3 Timeline Submission dan Pendanaan 2026–2027 [SUMBER–FUND]

Dokumen bertanggal acuan **26 September 2026** menyusun jalur aplikasi untuk produk **Unimart Core voice-native B2B SaaS**, termasuk Founder Institute Bali, Y Combinator Winter 2027, Techstars, dan program infrastruktur cloud. Ia memisahkan:

- **Project A:** Unimart Core / voice operations (pitch utama SaaS).
- **Project B:** Merchant Settlement Network / ide berbasis Stellar, hanya bila ada alasan teknis dan kepatuhan jelas.
- **Project C:** Onchain Merchant Passport & Loyalty, hanya bila benar-benar perlu on-chain dan memilih satu ekosistem.
- **Project D:** Unimart Edge AI, bila kebutuhan teknis terbukti.

Dokumen menganjurkan demo 60–90 detik, wawancara, design partners, outlet pilot, retention, *weekly successful actions*, willingness-to-pay, serta ukuran pasar bottom-up. Juga menekankan agar token, blockchain, compute marketplace, dan super-app **tidak dipaksakan ke core SaaS**.

**Peringatan tanggal:** beberapa tanggal aplikasi Oktober 2026 dalam lampiran telah lewat pada 10 Oktober 2026; lainnya mungkin dekat. Semua deadline, investment terms, kriteria, dan status intake adalah **catatan historis dari dokumen**, bukan status yang sudah diverifikasi pada 10 Oktober. **Rencana fundraising harus diperbarui setelah keputusan produk.**

---

## 3. Kronologi evolusi gagasan dan alasan challenge

| Fase gagasan | Fokus | Konsekuensi kritik/pembelajaran | Status sekarang |
|---|---|---|---|
| Unimart general ecosystem | Berbagai layanan digital merchant | Terlalu banyak fitur dan segmen; positioning dan pemasaran lemah | Jangan menjadi pitch utama tanpa wedge |
| Unimart Voice & AI POS | STT/TTS untuk transaksi dan koordinasi staf | Voice merupakan **cara interaksi**, bukan alasan ekonomis membeli produk, kecuali menyelesaikan masalah operasional konkret | Kandidat viable untuk diuji, bukan dibatalkan |
| Unimart closed-loop F&B | Owner control, kitchen, order, inventory | Kuat bila ada pengurangan kesalahan/waktu dan retensi berbayar; memerlukan pilot operasional mendalam | Tesis produk dalam dokumen September |
| Ide tim: AI marketing / referral / channel commerce | Kreator, merchant, iklan, cross-channel, pembayaran | Berorientasi pendapatan tetapi sangat luas; API/integrasi bukan moat | Kandidat diuji ulang |
| Locomo demand-to-supply | Pelanggan mengutarakan kebutuhan, agent menemukan merchant | Potensi mengubah demand menjadi order; marketplace cold start menjadi tantangan | Landasan konsep jaringan lokal |
| Local Commerce Intelligence Network | Demand graph, product/merchant graph, match, attribution | Dapat memiliki network effects jika kepadatan matching dan transaksi benar-benar terjadi | Pernah diprioritaskan untuk elaborasi, **belum dipilih final** |
| Personalized education | Pembelajaran adaptif + pengawasan pengajar profesional | Niche berbeda, pembayar dan regulasi berbeda; perlu pembuktian outcome pendidikan | **Ide baru, riset terbuka** |
| Smart Shelf Finder | Pencarian lokasi produk di rak minimarket/swalayan | Masalah sempit dan demonstratif; bergantung pada data planogram/rak yang akurat | **Ide baru, riset terbuka** |

### Kontroversi inti yang harus tetap terbaca oleh agent

- **Voice vs kebutuhan riil:** kritik tim bahwa STT/TTS tidak otomatis menyelesaikan masalah adalah valid. Tetapi bila voice mengurangi waktu dan kesalahan pemrosesan order, voice bisa menjadi diferensiasi fungsional pada workflow khusus. Jangan menghapusnya tanpa uji.
- **AI generalis vs agent spesialis:** “AI yang dapat melakukan apa pun” sulit didiferensiasi; tugas dengan outcome spesifik lebih mudah dipasarkan dan diukur.
- **Marketplace vs merchant agent:** agent milik merchant yang membela kepentingannya berbeda secara insentif dengan agent jaringan milik pembeli yang mencari penawaran terbaik. Struktur data, monetisasi, dan privasinya bergantung pada pilihan ini.
- **Fitur vs moat:** link-in-bio, chatbot, posting terjadwal, QRIS checkout, dan API integrasi bisa menjadi komponen berguna; **tidak boleh diklaim sebagai moat mandiri**.
- **Pendanaan vs luas fitur:** investor umumnya membutuhkan bukti masalah, traction, retention, economics, ukuran pasar masuk akal, dan kemampuan tim. Proposal ini harus diuji, bukan menyatakan pasti menarik investor.

---

## 4. Prinsip riset produk: definisi peluang yang layak

### 4.1 Sasaran founder [DISKUSI]

- **Prioritas tujuan:** menemukan produk yang memiliki peluang pendanaan, dapat menjadi perusahaan yang bertumbuh, dengan dasar kebutuhan nyata.
- **Wedge geografis:** kebutuhan lokal di daerah tingkat II—kabupaten/kota, termasuk lingkungan Pesisir Selatan sebagai konteks pilot yang dikenal.
- **Keterbatasan yang dicari solusinya:** digital adoption gap, sumber daya manusia terbatas, proses manual, distribusi produk, akses pelanggan, kendala pencatatan/pembayaran; masing-masing belum tentu dominan pada setiap segmen.
- **Teknologi:** AI sebagai agent/asisten spesialis yang menjalankan proses, bukan hanya memberi jawaban; voice dapat menjadi opsi.
- **Defensibility:** ingin diferensiasi yang tahan terhadap pesaing yang dapat menyalin UI/API.

### 4.2 Kriteria seleksi produk [USULAN]

Tiap ide harus menjawab dengan bukti:

1. **Pengguna dan pekerjaan spesifik:** siapa pengguna harian, siapa pembayar, siapa mengambil keputusan pembelian?
2. **Penderitaan yang terukur:** seberapa sering masalah terjadi dan berapa biaya/waktu/kegagalan akibat masalah itu?
3. **Urgensi:** apakah pengguna mencari solusi sekarang atau hanya menganggap fitur “menarik”?
4. **Willingness-to-pay:** apakah dapat membayar setelah pilot, berapa dan dengan model apa?
5. **Distribusi di kabupaten:** dapatkah diperoleh 10 pelanggan awal tanpa iklan besar? Apakah perlu pemasangan/pendampingan?
6. **Time-to-value:** berapa hari untuk mendapatkan hasil nyata pertama?
7. **Moat yang dapat mengakumulasi:** data koreksi, workflow, hubungan institusional, switching cost, performa, jaringan aktif, bukan sekadar kode.
8. **Scalability:** apa yang berubah ketika pindah dari satu kabupaten ke kabupaten lain? Apakah support bertambah linear?
9. **Regulasi dan trust:** izin data personal, pembayaran, perlindungan anak, konten, standar pendidikan, dan kontrak institusi.
10. **Investment fitness:** ukuran pasar bottom-up, repeatability, gross margin, tingkat retensi, kecepatan pertumbuhan, dan kekuatan team narrative.

---

## 5. Kandidat A — Unimart Voice-Native Operations (F&B/grosir)

**Asal:** [SUMBER–STR] + [DISKUSI].

**Problem thesis:** owner usaha offline sulit mendelegasikan operasi karena data dari staf, order, stok, dan pembayaran terpecah/tidak dipercaya.

**Pengguna/pembayar:** owner usaha F&B independen (beberapa pekerja/outlet), staf kasir/pelayan/dapur sebagai end user; ekspansi mungkin grosir.

**Value proposition:** tindakan bisnis (pesanan, koreksi, status dapur, stok, peringatan owner) selesai dan dapat diaudit dengan input yang ringan, termasuk suara jika bermanfaat.

**Eksperimen MVP:** satu alur `order → kitchen acknowledgment → perubahan stok/substitusi → konfirmasi → selesai`; owner alert; fallback manual; audit; pengukuran sebelum/sesudah.

**Metrik:** waktu order-to-kitchen, missed orders, correction rate, % order terekam, weekly completed actions, owner hours away, retention, paid conversion.

**Moat potensial:** event graph operasional, relasi produk/satuan lokal, alur lapangan spesifik, kepercayaan dan data koreksi; masih belum terbukti.

**Risiko:** kebisingan, akurasi bahasa/logat, perubahan perilaku staf, ketergantungan koneksi, biaya support, perlu integrasi POS/perangkat, voice fatigue.

**Kandidat pitch:** *AI operations control for owner-operated local businesses*; bukan “aplikasi voice universal”.

**Kaitan dengan ide lain:** dapat menjadi produk terpisah atau modul infrastruktur jika commerce network membutuhkan data transaksional. Tidak otomatis harus digabungkan.

---

## 6. Kandidat B — Local Commerce Intelligence Network / Locomo

**Asal:** [SUMBER–LOC] + [DISKUSI] + [USULAN].

### 6.1 Product thesis

> Jaringan lokal yang menangkap **permintaan pelanggan yang sah**, menghubungkannya dengan **penawaran merchant yang diketahui**, dan membantu agent mengoordinasikan proses hingga **transaksi yang dapat diverifikasi**.

Bukan hanya katalog atau marketplace daftar produk. Hipotesis nilai ada pada *demand-to-fulfillment orchestration*.

**Aktor:** buyer; merchant; merchant staff; kreator/afiliasi (fase lanjut); operator jaringan; mitra pembayaran/fulfillment.

### 6.2 Komponen usulan

1. **Merchant Passport / Smart Profile**: data profil, verifikasi identitas/kontak, cabang, kategori, katalog, jam/area layanan, CTA, link checkout, persetujuan penggunaan data. Contoh URL ilustratif `unimart.group/toko-abc`; bukan route produk yang sudah aktif.
2. **Demand Capture**: explicit buyer request, pencarian, formulir, order inquiry, kebutuhan belum terpenuhi; preferensi tidak boleh diekstraksi diam-diam dari pesan pribadi.
3. **Product & Merchant Graph**: varian, alias lokal, satuan/kemasan, harga, stok beserta waktu pembaruan, lokasi layanan, kemampuan pemenuhan.
4. **Opportunity Matching**: filter deterministik eligibility lalu ranking oleh relevansi, lokasi, harga, freshness, kapasitas, respons; alasan rekomendasi perlu transparan.
5. **Sales Execution Agent**: menyiapkan penawaran, mengklarifikasi, menghubungi pihak dengan izin, mengingatkan follow-up, menyusun order, eskalasi ketika tidak terpenuhi.
6. **Verified Commerce Ledger**: demand ID, offer, order, status pembayaran dari provider, delivery/fulfillment, refund, komisi dan atribusi; tidak berarti platform menampung dana.
7. **Local Market Intelligence**: permintaan belum terpenuhi dan peluang produk yang diagregasi, dengan privasi, batas akses, ukuran sampel, dan anti-inference.

### 6.3 Bentuk agent dan insentif

- **Merchant-side agent**: bertindak untuk tujuan merchant, menjaga rahasia harga modal, data pelanggan, keputusan diskon.
- **Buyer-side agent**: mencari penawaran terbaik sesuai kebutuhan pembeli; wajib transparan jika ranking dipengaruhi komisi/sponsorship.
- **Network orchestrator**: mengatur protokol matching, fairness, transaksi, privacy boundary, dispute.

**Pertanyaan masih terbuka:** apakah titik masuk (wedge) adalah merchant-led network (merchant membawa pelanggan) atau demand-led network (agent pencarian pelanggan terlebih dahulu)? Dalam diskusi, merchant-led sempat direkomendasikan untuk mengurangi cold start, **namun belum diputuskan founder**.

### 6.4 Hipotesis network effects

- **Cross-side**: peningkatan *relevant active supply* menaikkan peluang pemenuhan permintaan; permintaan valid menambah insentif merchant bergabung.
- **Data quality**: koreksi alias, stok, hasil match, fulfillment meningkatkan resolver/ranking bila kualitas feedback baik.
- **Distribution**: merchant/kreator mengajak pelanggan; efek baru terjadi apabila pesanan dan pembelian berulang meningkat.

Ukuran kualitas jaringan yang disarankan: demand match rate, fulfillment rate, time to match, verified matched orders/week, repeat purchase, contribution margin per fulfilled order, *category-by-locality liquidity*; jangan hanya menghitung akun terdaftar atau GMV bruto.

### 6.5 Cold start dan strategi akuisisi

Mulai pada **satu geografi kecil + satu kebutuhan dengan frekuensi tinggi**, misalnya grosir-ke-warung atau kebutuhan rumah tangga berulang; ini *hipotesis awal* bukan hasil data lapangan. Manfaat mandiri merchant sejak hari pertama: profil, katalog, direct inquiry, follow-up, laporan sederhana, payment verification. Jaringan lintas merchant aktif bertahap hanya ketika izin dan kepadatan transaksi cukup.

### 6.6 Risiko besar

- Merchant tidak mau berbagi pelanggan atau demand dengan pesaing.
- Harga/stok tidak mutakhir sehingga match gagal.
- Pembeli lebih nyaman tetap di WhatsApp/platform lain.
- Margin transaksi tidak cukup setelah support, PSP, refund dan komisi kreator.
- Kesetaraan pemeringkatan vs merchant yang membayar; potensi konflik insentif agent.
- Privacy serta penyalahgunaan data permintaan, spamming, scraping dan fake demand.
- Kebutuhan pengantaran dan *last-mile*, berpotensi menjadi operasi logistik alih-alih SaaS.

### 6.7 Eksperimen falsifikasi

- Rekrut 10–20 merchant dalam satu kategori/wilayah; uji 100 permintaan buyer nyata melalui concierge/manual matching sebelum AI penuh.
- Catat apakah buyer mau menerima penawaran dari merchant yang belum pernah dikenal.
- Ukur % request feasible, offer accepted, paid, fulfilled dan repeat; tandai alasan kegagalan.
- Bandingkan merchant dengan smart profile saja vs smart profile + network leads untuk mengisolasi **incremental** sales.
- Stop/pivot jika nilai per order tidak menutup CAC/fulfillment/support meskipun match rate meningkat.

---

## 7. Kandidat C — AI Merchant Growth + Creator Referral Network

**Asal:** [DISKUSI] usulan tim dan elaborasi kemudian.

**Masalah yang dihipotesiskan:** seller/kreator mengelola pemasaran, posting, percakapan, referral, pesanan dan pembayaran di kanal yang terfragmentasi.

**Fitur calon:** product feed, campaign brief, generasi kreatif, link referral, landing product, CRM, follow-up yang diizinkan, atribusi berbasis order valid, statistik konversi, komisi dan payout via mitra berizin.

**Titik penting:** “otomatis publish seluruh medsos/marketplace” **bukan asumsi feasible**. Akses API, izin aplikasi, konten, batas otomatisasi, layanan iklan, penyelesaian checkout, dan kebijakan platform berbeda-beda. MVP sebaiknya memakai channel terbatas dan export/share/manual approval ketika integrasi belum tersedia.

**Dua interpretasi pembayaran yang sempat dibahas:**

- *Unified payment/reconciliation*: banyak kanal penjualan tetapi status dan rekonsiliasi terpusat.
- *Unified revenue/commission*: merchant dan kreator melihat transaksi, fee, atribusi dan hak pendapatan dari banyak program.

**Belum ditetapkan** apakah Unimart berperan sebagai platform transaksi sendiri, penyedia dashboard lintas platform, atau keduanya. **Unified dashboard tidak berarti custody dana**. Hindari menjalankan transfer/split settlement sendiri tanpa skema mitra dan dasar izin yang sesuai.

**Pembeda yang perlu diuji:** atribusi yang dapat diverifikasi, jaringan merchant-kreator yang menghasilkan konversi, data outcome kampanye, dan kemampuan cross-merchant matching; bukan generator link saja.

**Metrik:** active selling creators, qualified visits, assisted orders, paid/fulfilled orders, attribution disputes, campaign contribution margin, merchant retention, repeat campaigns.

**Risiko:** atribusi multi-touch, fraud/referral abuse, payout komisi, platform policy changes, ROI iklan, ketergantungan kanal pihak ketiga, insentif kreator yang tidak selaras.

---

## 8. Kandidat D — Payment Verification & Merchant Control Add-on

**Asal:** [DISKUSI terkait Unimart POS/QRIS dan ketakutan screenshot pembayaran]; bukan fokus utama tiga lampiran.

**Problem thesis:** staf toko tidak selalu dapat melihat status pembayaran QRIS/transfer milik owner; bukti screenshot bisa dipalsukan; verifikasi manual menyebabkan keterlambatan atau kerugian. Angka kerugian contoh yang muncul dalam diskusi adalah **anekdot, bukan statistik pasar tervalidasi**.

**MVP:** staff membuat payment request beramount pasti; sistem menghasilkan QRIS dinamis via PSP/mitra; webhook diterima dan diverifikasi server; kasir hanya menerima status terverifikasi dengan order ID yang cocok; owner punya audit, settlement mismatch alert, kontrol role multi-outlet.

**Wedge spesifik:** toko yang memiliki kasir, banyak transaksi QRIS, dan owner tidak selalu berada di tempat. Masalah ini mungkin lebih akut dan lebih mudah divalidasi daripada network commerce penuh.

**Moat potensial:** data rekonsiliasi, workflow merchant, audit, integrasi dengan proses outlet dan distribusi; QRIS generation itu sendiri bukan moat.

**Metrik:** false acceptance, payment confirmation latency, waktu antrean, rekonsiliasi mismatch, paid retention, nilai kerugian yang dapat dicegah **dengan pembandingan valid**.

**Risiko:** ketersediaan mitra berizin, webhook delay/outage, biaya payment gateway, dispute, status expired, refund, keamanan nominal dan penyalahgunaan staff.

**Strategi produk:** bisa menjadi **produk add-on mandiri** atau payment primitive bersama bagi kandidat A/B/C; jangan menjadikannya kewajiban semua proyek.

---

## 9. Kandidat E — Personalized Education + Professional Teacher Monitoring [IDE BARU]

### 9.1 Gagasan inti founder

Layanan pendidikan personal berbasis AI, dengan pemantauan perkembangan oleh tenaga pengajar profesional. Belum ada definisi final tentang jenjang peserta, mata pelajaran, kurikulum, model hubungan guru, model pembayaran, atau bentuk sertifikasi. Jangan mengarang bahwa layanan ini sudah terintegrasi dengan sekolah atau sudah memiliki guru tersertifikasi.

### 9.2 Masalah dan pengguna yang perlu dibedakan [HIPOTESIS]

- **Siswa**: kesulitan pada kompetensi tertentu dan membutuhkan latihan/umpan balik dengan ritme personal.
- **Orang tua**: kesulitan memantau pemahaman dan progres nyata di luar nilai ujian.
- **Guru/tutor**: tidak cukup waktu mendiagnosis miskonsepsi dan memberi tindak lanjut berbeda kepada tiap murid.
- **Sekolah/lembaga kursus**: ingin intervensi pembelajaran yang terbukti, tanpa menambah beban administrasi berlebihan.

Potensi pembayar berbeda: B2C orang tua, B2B lembaga bimbel/sekolah, atau program institusi/pemerintah daerah. Model procurement dan risiko berbeda drastis.

### 9.3 Tiga konfigurasi produk yang layak diuji [USULAN]

**E1. AI Tutor + Teacher Review:** siswa mendapat latihan diagnostik dan tutor interaktif; guru meninjau dashboard mingguan, mengoreksi rekomendasi, serta melakukan intervensi pada siswa yang membutuhkan. Wedge: bimbel atau tutor kecil yang sudah memiliki siswa.

**E2. Remedial Intelligence for Schools:** sistem mendeteksi gap kompetensi per topik, mengusulkan latihan/remedial, memonitor penguasaan pascaintervensi, dan memberi laporan ringkas kepada guru. Bukan “chatbot belajar semua pelajaran”.

**E3. Teacher Copilot for Personal Learning Plans:** guru menyusun target, rubrik, bank tugas, dan evaluasi adaptif; AI membantu administrasi dan distribusi materi, guru tetap pengambil keputusan pedagogis.

### 9.4 Workflow contoh

`baseline assessment → competency map → learning plan → short practice → formative feedback → teacher review → intervention → reassessment`.

**Peran tenaga pengajar profesional:** menetapkan tujuan pedagogis; menyetujui/meninjau soal penting; mengoreksi miskonsepsi dan keluaran berisiko; menilai kasus sulit; melakukan intervensi; menjaga hubungan dengan murid dan orang tua. Jangan menjanjikan setiap interaksi AI diawasi real-time jika kapasitas guru tidak mendukung.

### 9.5 Kandidat moat

- **Verified learning progression graph**: pemetaan topik–miskonsepsi–intervensi–penguasaan yang diuji longitudinal, bukan hanya jumlah chat siswa.
- **Teacher-in-the-loop outcome data**: umpan balik guru profesional dan hasil belajar yang dinilai ulang secara konsisten.
- **Institutional workflow embedding**: terpasang dalam ritme penugasan, remedial, dan komunikasi sekolah/bimbel, dengan switching cost wajar.
- **Local curriculum/context expertise**: kualitas bank latihan, bahasa daerah/konteks, validasi ahli—asal benar-benar memiliki kualitas dan hak konten.

Moat tersebut memerlukan data belajar berkualitas dan evaluasi yang sah; penggunaan LLM atau adaptive quiz saja mudah ditiru.

### 9.6 Metrik dan uji pasar

- Baseline vs reassessment pada kompetensi yang sama dengan instrumen valid.
- Time-to-mastery, completion rate, teacher review time, intervensi terlaksana, pemahaman jangka menengah.
- Parent/teacher willingness to pay, retention siswa dan institusi, biaya tenaga pengajar per siswa, eskalasi keselamatan.
- Desain pilot: satu jenjang + satu mata pelajaran + satu jenis kesulitan (misalnya numerasi dasar), bekerja dengan beberapa tutor/guru, bandingkan metode sekarang vs metode didukung AI tanpa mengklaim kausalitas dari sampel kecil.

### 9.7 Risiko khusus

- **Perlindungan data anak dan consent wali**, prinsip minimisasi, kebijakan penyimpanan, keamanan akses.
- **AI hallucination dan pedagogical harm**, rekomendasi keliru, penyederhanaan berlebihan, jawaban yang menggantikan proses belajar.
- **Kualitas dan akuntabilitas guru**, lisensi/hak materi, biaya tenaga profesional, jadwal monitoring.
- **Regulasi pendidikan, procurement sekolah, dan kesenjangan perangkat/internet**.
- **Outcome education lambat dibuktikan** dibanding kesalahan transaksi di toko; risiko evaluasi lemah.

**Tesis investor yang layak diuji:** *AI-assisted personalized remediation with accountable human educators for underserved schools/tutoring providers*, bukan platform e-learning generik.

---

## 10. Kandidat F — Smart Shelf Finder / In-Store Product Navigation [IDE BARU]

### 10.1 Gagasan inti founder

*Add-on* untuk swalayan atau minimarket: pelanggan dapat mencari barang melalui katalog dan mendapatkan petunjuk **posisi rak/lorong** tempat barang berada, analog dengan pencarian buku berdasarkan rak di perpustakaan atau toko buku. Belum ada keputusan penggunaan indoor positioning hardware, integrasi POS, atau AI computer vision.

### 10.2 Problem thesis [HIPOTESIS]

Pelanggan membutuhkan waktu mencari produk, sering bertanya kepada staf, atau gagal menemukan barang yang sebenarnya tersedia. Staff menghabiskan waktu menjawab pertanyaan lokasi. Toko kehilangan potensi konversi ketika pelanggan menyerah. Semua ini perlu dibuktikan melalui observasi toko.

### 10.3 Produk minimal (tanpa infrastruktur berat) [USULAN]

- **QR entrance / kiosk / web mobile**; tidak wajib install app.
- Pencarian berdasarkan nama, merek, sinonim/alias, ukuran/kemasan; voice opsional.
- Hasil: nama produk, foto, harga bila tersedia, availability freshness, dan petunjuk `Lantai 1 → Lorong B → Rak 03 → Sisi kanan → Tingkat 2`.
- Peta toko 2D dengan lokasi zona/rak, bukan indoor GPS presisi.
- *Staff shelf editor*: staf mengatur posisi SKU, zona dan rak lewat scan barcode/form; ada timestamp dan approval.
- *Not found* flow: produk tidak tersedia, pindah rak, atau bisa meminta bantuan staf.

### 10.4 Teknik inti dan realitas implementasi

**Masalah terbesar bukan mencari produk dengan AI**, melainkan menjaga **akurasi pemetaan SKU → shelf slot** ketika display/promo/restocking berubah. Akurasi peta fisik adalah sumber kebenaran.

Arsitektur MVP yang lebih realistis:

`SKU master → catalog synonym search → planogram/shelf-slot mapping → 2D aisle map → navigation instructions → customer feedback → shelf correction workflow`.

Indoor beacon, RFID per produk, kamera CV real-time, atau AR dapat menjadi R&D selanjutnya jika ROI terbukti; jangan diwajibkan pada produk awal.

### 10.5 Calon pelanggan dan monetisasi

- Pembayar utama: toko swalayan/minimarket independen, supermarket lokal, jaringan toko regional, toko bangunan/perlengkapan rumah yang memiliki banyak SKU.
- Monetisasi: SaaS per toko atau per jumlah SKU/luas area; biaya setup peta/rak; add-on analitik pencarian. Hindari asumsi bahwa pembeli akhir mau membayar untuk arah rak.
- Jalur distribusi: penyedia POS, pemasok sistem stok, integrator lokal, asosiasi retail, jaringan minimarket setempat.

### 10.6 Moat potensial

- **Shelf location accuracy infrastructure** dan proses pembaruan yang murah serta rutin.
- Dataset alias dan pencarian produk per toko, mapping planogram yang konsisten, integrasi inventory/POS jika disetujui.
- Kontrak jaringan toko atau channel partner instalasi/support.
- Performa operasional yang terbukti lebih baik dari menanyakan staf.

**Catatan:** fitur pencarian lokasi rak tetap dapat direplikasi. Moat lebih mungkin timbul dari *data freshness, workflow integration, deployment repeatability,* dan hubungan pelanggan B2B.

### 10.7 Eksperimen dan risiko

- Lakukan *timed shopper study*: 20–50 tugas pencarian produk di 2–3 toko berbeda, dengan dan tanpa pemandu.
- Ukur median search time, find-success rate, staf-interruption rate, wrong-location rate, update effort per SKU move, shopper adoption.
- Pantau seberapa cepat rak berubah, biaya pemetaan awal, dan ketergantungan pemilik menyediakan data SKU.
- Risiko: toko kecil terlalu mudah dinavigasi sehingga fitur tidak bernilai; pengelola enggan membayar; perubahan display terlalu sering; lokasi rak bocor/tidak akurat; koneksi lemah.

**Tesis investor potensial:** *lightweight in-store discovery and planogram operations for fragmented retail*, **jika** terbukti multi-outlet, repeatable, dan memiliki SaaS economics; jika tidak, tetap dapat menjadi bisnis add-on B2B yang sehat tanpa memaksakan narasi venture-scale.

---

## 11. Tambahan ide yang masih berdekatan dengan kemampuan tim [USULAN]

Ini adalah alternatif penelitian, **bukan usulan untuk dibangun bersamaan**.

| Ide tambahan | Wedge pengguna | Masalah sempit | Sinyal nilai | Hubungan dengan kandidat |
|---|---|---|---|---|
| **AI Reorder Agent untuk warung–distributor** | Warung/grosir | Kekurangan barang dan pemesanan ulang manual | Penurunan stockout, reorder selesai | Turunan commerce network; repeat transactions |
| **AI Quote-to-Order untuk jasa lokal** | Bengkel, las, servis, percetakan | Inquiry sulit dikualifikasi dan penawaran terlambat | Konversi inquiry menjadi booking/DP | Vertikal Locomo services |
| **Payment Confidence Layer** | Multi-outlet ritel/kasir | Bukti bayar palsu dan owner-only status | Pembayaran tervalidasi, rekonsiliasi | Turunan payment add-on |
| **Assisted Catalog Digitizer** | Merchant katalog berantakan | Input/normalisasi foto, satuan, harga | Waktu onboarding dan data accuracy | Infrastruktur commerce/shelf finder |
| **Teacher Intervention Queue** | Guru/bimbel | Sulit menentukan murid mana butuh bantuan | Waktu guru dan mastery progress | Wedge EdTech yang lebih sempit |
| **Shelf Audit Copilot** | Swalayan dengan display sering berubah | Lokasi SKU dan label rak tidak konsisten | Shelf accuracy + waktu audit | Pelengkap Shelf Finder |

**Prinsip tambahan:** jika ingin ide yang sangat sulit direplikasi secara fungsional, carilah masalah yang mengharuskan **data operasional unik + proses koreksi dan verifikasi + kepercayaan institusi + hasil nyata**. Tidak ada software feature tunggal yang dapat dijamin tidak bisa ditiru.

---

## 12. Perbandingan terstruktur seluruh kandidat

**Penilaian berikut kualitatif dan bersifat awal, belum hasil market research.** “Daya tarik” bukan prediksi peluang memperoleh dana.

| Kandidat | Pembayar potensial | Hasil paling cepat dibuktikan | Beban implementasi awal | Tantangan moat | Tantangan utama |
|---|---|---|---|---|---|
| A. Voice Ops | Owner F&B/outlet | Pesanan lebih cepat, lebih sedikit salah | Sedang–tinggi | Workflow + distribusi | Adopsi staf/perangkat |
| B. Local Commerce Network | Merchant + fee order | Matched fulfilled order | Tinggi untuk jaringan; MVP concierge lebih rendah | Network density + outcome data | Cold start, fulfillment, trust |
| C. Growth/Referral Agent | Merchant / kreator via fee | Qualified conversions | Sedang | Attribution + network | Kebijakan platform, fraud |
| D. Payment Add-on | Owner toko/outlet | Payment confirmation tanpa screenshot | Sedang | Workflow embedded + audit | PSP integration dan compliance |
| E. Personalized EdTech | Orang tua / bimbel / sekolah | Teacher workflow; belajar butuh waktu | Sedang untuk pilot, tinggi bila skala | Verified learning graph + institusi | Bukti outcome, minors/privacy |
| F. Shelf Finder | Manajemen retail | Waktu menemukan produk | Rendah–sedang untuk toko tunggal | Data posisi rak selalu akurat | Planogram maintenance + WTP |

### Pola evaluasi prioritas [USULAN]

Gunakan skor 1–5 untuk masing-masing dimensi (bukti diperlukan):

- Intensitas masalah **20%**.
- Kemampuan pengguna membayar **15%**.
- Kecepatan validasi dan time-to-value **15%**.
- Keunggulan distribusi founder **15%**.
- Defensibility data/workflow/network **15%**.
- Unit economics dan skala lintas daerah **15%**.
- Risiko regulasi, keamanan dan dependensi **5%** (skor lebih tinggi bila risiko terkendali).

**Jangan memberi skor final sekarang**. Wawancara dan eksperimen dapat mengubah urutan kandidat secara radikal. Buat *evidence dossier* satu halaman untuk tiap kandidat sebelum ranking.

### Dua tipe venture yang berbeda

- **B2B workflow SaaS** (Voice Ops, Payment Add-on, Shelf Finder, Teacher Copilot): pembayar jelas, nilai dapat diuji di beberapa pelanggan, distribusi/retensi menjadi krusial.
- **Multi-sided network** (Commerce Network, Creator Commerce): ada potensi network effects, tetapi mahal/rumit memperoleh likuiditas awal dan menjaga insentif stakeholder.

Tidak ada alasan apriori bahwa network selalu lebih baik daripada B2B SaaS untuk fundraising. Investor dapat tertarik kepada keduanya jika bukti pertumbuhan dan ekonomi usaha kuat.

---

## 13. Arsitektur bersama: reusable AI primitives, bukan satu super-app

Bila tim ingin bereksperimen dengan beberapa MVP, bangun *reusable internal toolkit* secukupnya, **bukan platform besar sebelum validasi**.

### 13.1 Komponen teknis bersama [USULAN]

```text
Channels (web / PWA / Android / chat / optional voice)
                   |
           Auth + Tenant + Roles
                   |
       Intent Router / Agent Orchestrator
                   |
  Domain tools (commerce OR education OR retail navigation)
                   |
      Policy, Approval, Audit, State Machines
                   |
   PostgreSQL + Redis + Events + Integration Adapters
                   |
      Verified outcomes + Analytics + Evaluation
```

**Pisahkan domain data:** pendidikan anak jangan dicampur dengan profil pelanggan commerce; dataset dan consent berbeda. Domain-specific data ownership dan tenant isolation harus didefinisikan untuk setiap MVP.

### 13.2 Prinsip agent

- LLM dipakai untuk interpretasi, perencanaan, dan bahasa; sistem terprogram memvalidasi hak, persetujuan, jumlah uang, serta perubahan state.
- Tiap tool memiliki JSON schema, allowlist tindakan, idempotency key, audit/event ID, dan failure handling.
- Agent boleh **merekomendasikan** tindakan berisiko; publish, refund, transfer, promosi berbayar, perubahan harga global, komunikasi masif dan keputusan pendidikan penting tunduk pada approval sesuai risiko.
- Memisahkan `proposal`, `approved`, `executing`, `confirmed`, `failed`, `compensated`, agar “agent mengatakan sudah” tidak menggantikan status sistem.
- Gunakan evaluasi berbasis tugas sungguhan (completion, errors, reversals, cost per successful action) sebelum klaim agent otonom.
- Tidak perlu multi-agent LLM kompleks di MVP; satu orchestrator dengan tools deterministik sering lebih mudah diaudit.

### 13.3 Evolusi stack

Stack yang pernah digunakan founder dalam konteks pengembangan: Laravel/PHP, PostgreSQL, Redis, Docker; stack tersebut **opsi awal**, bukan keputusan teknis untuk semua produk. Untuk pencarian: mulai full-text/trigram, kemudian embeddings/pgvector bila diperlukan. Edge/offline hanya setelah kebutuhan pilot membenarkan kompleksitas.

---

## 14. Ekonomi bisnis, pendanaan, regulasi, dan pengukuran

### 14.1 Model monetisasi per kandidat

| Produk | Pendapatan fase awal | Ekspansi monetisasi | Hal yang harus dihindari |
|---|---|---|---|
| Voice Ops | SaaS/outlet dan onboarding | AI usage, multi-outlet, edge | Harga tanpa menghitung support/AI |
| Commerce Network | Tools merchant atau fee transaksi terverifikasi | SaaS + matching service + creator tools | Mengakui GMV sebagai revenue |
| Growth/Referral | Merchant SaaS / campaign tool | Fee attributed orders | Payout/settlement tanpa mitra sah |
| Payment Add-on | SaaS/outlet dan setup | Rekonsiliasi, audit, multi-store | Bertindak seolah PJP tanpa izin |
| Education | Per siswa/kelas/guru atau institusi | Analisis kompetensi, teacher review | Mengabaikan biaya guru & hak data siswa |
| Shelf Finder | SaaS per toko + setup peta | Planogram audit, insight pencarian | Underprice pemetaan dan pembaruan rak |

### 14.2 Definisi metrik keuangan

- **GMV**: nilai bruto transaksi di jaringan, **bukan** pendapatan platform.
- **Revenue**: fee SaaS/layanan/transaksi yang berhak diakui, setelah memperhatikan kebijakan akuntansi.
- **Gross margin**: revenue dikurangi biaya langsung (AI inference, channel message, hosting, processing yang ditanggung, support langsung, payout vendor terkait).
- **Contribution margin**: setelah variabel akuisisi/fulfillment/support yang dapat diatribusikan menurut desain perhitungan.
- **CAC/payback**: biaya akuisisi per pelanggan berbayar dan periode pemulihan margin; khusus pasar kabupaten masukkan kunjungan lapangan dan pelatihan.
- **Retention**: cohort merchant/sekolah/toko yang tetap menggunakan dan membayar; bukan instalasi.

### 14.3 Jalur pendanaan [SUMBER–FUND sebagai histori, bukan keputusan kini]

Dokumen 26 September menyiapkan jalur Founder Institute, YC, Techstars, serta cloud programs; program on-chain dipisahkan secara ketat. **Bukan berarti semua program cocok untuk tesis EdTech atau Shelf Finder**, dan jadwal harus diperbarui. Sebelum pitching, diperlukan problem proof, demo, pilot, retensi, revenue/unit economics awal, potensi ekspansi, IP ownership dan legal entity.

**Product selection harus mendahului investor-fit narrative.** Jangan mengubah fitur menjadi blockchain/Web3 hanya karena suatu grant tersedia.

### 14.4 Isu tata kelola lintas kategori

- **Data pribadi:** kumpulkan hanya data yang dibutuhkan; consent/landasan pemrosesan; akses per role; retensi, penghapusan dan ekspor sesuai aturan berlaku; perlu kajian hukum Indonesia aktual.
- **Pembayaran:** gunakan bank/PSP/PJP berizin untuk QRIS, checkout dan settlement; webhook terverifikasi + rekonsiliasi; dana merchant/kreator jangan disimpan sendiri tanpa landasan legal yang tepat.
- **Pemasaran:** izin komunikasi, opt-out, anti-spam, kebijakan medsos/marketplace dan atribusi yang transparan.
- **Pendidikan:** perlindungan anak, peninjauan guru, kualitas materi, keselamatan, akses wali sesuai umur dan regulasi; pengawasan bukan pengganti pendidik.
- **Ritel fisik:** kebenaran posisi rak/availability dan risiko salah navigasi; data toko dan perubahan layout harus dijaga.

---

## 15. Decision register — apa yang sudah diputuskan dan belum

| Kode | Item | Status per 10 Okt 2026 | Bukti/konteks |
|---|---|---|---|
| DEC-01 | Tujuan adalah mencari produk AI yang layak pendanaan | **Arah founder terkonfirmasi** | Pernyataan eksplisit founder |
| DEC-02 | Target eksplorasi berangkat dari kebutuhan lokal kabupaten/kota | **Arah founder terkonfirmasi** | Pernyataan eksplisit founder |
| DEC-03 | Produk final adalah Unimart Voice Ops F&B | **BELUM** | Dokumen lama merekomendasikan ini, founder melanjutkan riset |
| DEC-04 | Produk final adalah Commerce Network | **BELUM** | Pernah dipilih untuk elaborasi, bukan build commitment |
| DEC-05 | EdTech menjadi modul Unimart | **BELUM; tidak diasumsikan** | Ide baru dengan pelanggan/regulasi berbeda |
| DEC-06 | Shelf Finder menjadi add-on Unimart atau produk terpisah | **BELUM** | Ide baru, belum ada pilot |
| DEC-07 | Merchant-led vs buyer/demand-led | **TERBUKA** | Ada rekomendasi awal merchant-led, founder belum mengunci |
| DEC-08 | Model pembayaran/custody/split settlement | **TERBUKA; wajib kajian legal** | Dua interpretasi pernah dibahas |
| DEC-09 | Voice harus menjadi fitur utama produk | **TERBUKA** | Voice bermanfaat jika outcome terbukti |
| DEC-10 | Moat berasal dari integrasi API | **DITOLAK sebagai tesis moat tunggal** | Challenge dan konsensus diskusi |
| DEC-11 | Perlu validasi niche, problem dan economics lebih dahulu | **Arah kerja disetujui secara prinsip** | Permintaan riset lanjut |
| DEC-12 | Target accelerator, deadline dan terms dalam dokumen lama | **HISTORIS; perlu re-verifikasi** | Sumber bertanggal 26 Sep 2026 |
| DEC-13 | Produk sudah memiliki traction/PMF | **TIDAK ADA BUKTI dalam sumber** | Jangan diinventarisasi sebagai pencapaian |
| DEC-14 | Seluruh fitur referral/payment/voice telah dibangun | **TIDAK TERSIMPUL DARI SUMBER** | Dokumen mayoritas desain/strategi |

---

## 16. Pertanyaan riset yang harus dijawab sebelum keputusan produk

### 16.1 Semua ide

1. Siapa **economic buyer**, user harian, dan pengambil keputusan implementasi?
2. Apa tugas saat ini, frekuensi, biaya masalah, dan solusi alternatif (termasuk tidak memakai software)?
3. Apa alasan membeli dalam 30–90 hari, bukan “nanti”?
4. Bisakah manfaat dibuktikan dengan demo atau pilot 2–8 minggu?
5. Bagaimana founder mendapatkan 5–10 design partners dengan izin mengamati workflow?
6. Apakah produk harus didampingi langsung; berapa biaya onboarding/support per daerah?
7. Data unik apa yang dapat dihasilkan **secara sah** dan berkualitas dari penggunaan berulang?
8. Mana yang menghalangi kompetitor menyalin bisnis—bukan menyalin UI?
9. Adakah unit economics sehat pada satu wilayah sebelum ekspansi?
10. Apa kondisi **kill/pivot** yang disepakati di awal?

### 16.2 Khusus commerce

- Dari mana demand pertama datang? Apa manfaat merchant sebelum ada demand network?
- Bagaimana mengatasi konflik kepentingan agent merchant dan agent buyer?
- Seberapa mutakhir harga/stok; siapa mengoreksi data salah?
- Bagaimana memastikan order dan payment benar-benar fulfilled dan attributed?
- Apakah merchant rela membayar untuk leads/order tambahan, dan berapa?
- Bagaimana perlindungan data pelanggan ketika peluang dibagikan lintas merchant?

### 16.3 Khusus EdTech

- Jenjang/mata pelajaran/kompetensi yang paling membutuhkan remedial?
- Siapa tenaga pengajar profesional dan berapa rasio guru terhadap siswa?
- Apakah pengawasan asinkron mingguan cukup atau harus sinkron?
- Bagaimana evaluasi kompetensi yang valid dan menjaga privasi anak?
- Apakah sekolah/bimbel/orang tua mau membayar untuk peningkatan yang terukur?

### 16.4 Khusus Shelf Finder

- Berapa SKU dan berapa luas toko minimum agar masalah mencari barang signifikan?
- Apakah data shelf-slot tersedia? Seberapa sering planogram berubah?
- Siapa bertanggung jawab memperbarui dan memeriksa lokasi SKU?
- Apakah customer bersedia scan QR; apakah staf merasakan penghematan?
- Apakah ROI masuk akal setelah biaya setup dan perubahan rak dihitung?

---

## 17. Rancangan riset komparatif 6 minggu [USULAN]

Tujuannya **memilih kandidat**, bukan membangun enam MVP.

| Minggu | Kegiatan | Output / decision gate |
|---|---|---|
| 1 | Definisikan 2–3 persona tiap kandidat, problem interview guide, dan daftar partner | Problem statements + hipotesis terukur |
| 2 | Wawancara/observasi merchant F&B, seller/creator, ritel, guru/orang tua/bimbel | Evidence notes; current alternatives; severity |
| 3 | Pilih tiga kandidat paling menjanjikan berdasarkan evidence; uji willingness-to-pay | Shortlist dengan alasan, bukan opini |
| 4 | Prototype/concierge test ringan untuk tiga kandidat | Measured first outcome dan biaya implementasi |
| 5 | Lakukan pilot terbatas pada satu atau dua kandidat | Data penggunaan, error, retention dini, unit economics awal |
| 6 | Review bukti, risk register, moat thesis; putuskan satu wedge 90 hari berikutnya | Go / Iterate / Pivot / Stop dan investment narrative awal |

**Contoh desain uji yang tidak mensyaratkan AI mahal:** commerce matching secara manual dengan formulir dan operator; EdTech berupa assessment dan review guru menggunakan prototype; Shelf Finder dengan QR + peta 2D dan spreadsheet shelf mapping; Voice Ops dengan workflow order terstruktur dan pengukuran waktu; payment add-on dengan sandbox PSP sebelum transaksi nyata.

### Aturan keputusan

- **Go** bila pengguna menjalankan workflow nyata berulang, outcome lebih baik dari baseline, dan ada sinyal membayar yang kredibel.
- **Iterate** bila problem nyata tetapi solusi/UX belum layak.
- **Pivot segment** bila nilai kuat hanya pada persona tertentu.
- **Stop** bila kebutuhan jarang terjadi, solusi manual memadai, CAC/support tak sehat, atau hambatan regulasi tidak sebanding.

---

## 18. Template satu halaman untuk menguji tiap ide

Salin dan isi terpisah untuk A–F:

```markdown
# PRODUCT EVIDENCE CARD — [NAMA IDE]
- Problem statement:
- Segmen/niche/geografi:
- Pengguna harian:
- Pembayar/pengambil keputusan:
- Alur kerja saat ini:
- Bukti masalah (kutipan, observasi, frekuensi, biaya):
- Alternatif yang digunakan saat ini:
- Outcome satu kalimat:
- MVP paling kecil:
- Keunggulan distribusi founder:
- Data/workflow moat hypothesis:
- Risiko regulasi dan trust:
- Harga dan willingness-to-pay evidence:
- Unit economics awal (asumsi vs aktual):
- North-star metric:
- Baseline, hasil uji, n, periode, confounders:
- Kompetitor/substitusi yang perlu dicek:
- Kondisi go / iterate / pivot / stop:
- Status dan owner riset:
```

---

## 19. Rekomendasi kerja berikutnya — bukan keputusan membangun

1. Buat **evidence backlog** untuk keenam kandidat, dengan hipotesis dan bukti terpisah.
2. Identifikasi **dua peluang yang bisa diuji sangat cepat** (misalnya Shelf Finder dan Payment Verification) sebagai pembanding terhadap **dua peluang yang dapat menjadi jaringan besar** (Commerce Network dan Personalized EdTech). Voice Ops dan Growth/Referral tetap masuk evaluasi karena sumber awal menunjukkan kedalaman use case.
3. Wawancarai calon pembayar sebenarnya, bukan hanya pengguna yang menyukai demo AI.
4. Jalankan *concierge pilot* agar masalah dan willingness-to-pay diuji sebelum arsitektur kompleks.
5. Gunakan matrix seleksi dengan skor berdasarkan data setelah enam minggu; jika bukti belum cukup, jangan memaksa keputusan.
6. Setelah memilih satu wedge, barulah revisi **product thesis, TAM/SAM/SOM bottom-up, competitive landscape, MVP, roadmap, pitch deck, accelerator target, dan funding timeline**.

**Penegasan:** arah eksplorasi founder dapat melahirkan **lebih dari satu usaha/produk di masa depan**, tetapi batas kapasitas engineering, pemasaran, dan akuisisi pelanggan mengharuskan satu eksperimen utama pada satu waktu.

---

## 20. Ringkasan untuk konteks prompt agent AI lain

> Founder sedang mengeksplorasi ide startup digital berbasis AI untuk pasar kabupaten/kota di Indonesia, dengan sasaran membangun produk yang menyelesaikan masalah niche secara terukur dan layak memperoleh pendanaan. Dokumen Unimart awal (Sep 2026) mengusulkan voice-native closed-loop operations untuk F&B/grosir dengan owner control. Dokumen Locomo menawarkan AI untuk onboarding merchant dan matching kebutuhan pelanggan–penawaran. Diskusi berikutnya membahas AI Merchant Growth, jaringan permintaan–penawaran lokal, referral kreator, smart merchant profile, payment verification, network effects, dan moat berbasis data/workflow/distribusi. Pada 10 Okt 2026 founder memperluas eksplorasi ke personalized AI education dengan monitoring tenaga pengajar profesional dan smart shelf finder untuk minimarket/swalayan. **Belum dipilih produk final.** Agent harus menilai pilihan secara netral berdasarkan customer pain, kecepatan pembuktian, demand/pay willingness, go-to-market kabupaten, unit economics, moat, regulation, dan scalability, bukan menambah fitur atau menganggap rekomendasi terdahulu telah diputuskan.

---

## 21. Sumber dan batas pembacaan

### Dokumen sumber primer yang dianalisis

1. **`IBOT.LOCOMO-Plann-Marketing.md`** — narasi merchant/customer, voice, catalog, customer matching, referral dan prinsip persetujuan/privasi. Tidak berisi hasil survei atau traction.
2. **`Rancangan_Strategis_Unimart_Group.pdf`** — 21 halaman, September 2026; voice-native operations, segmen F&B, closed-loop workflow, edge, monetisasi, moat, pilot, fundraising. Ada angka pasar dan proyeksi **dalam dokumen** yang tidak diverifikasi ulang pada laporan ini.
3. **`TIMELINE-SUBMISSION-UNIMART-2026-2027.md`** — perencanaan accelerator/funding per 26 September 2026, project A–D, checklist, deadline dan terms **historis**; bukan status live.
4. **Diskusi percakapan sampai 10 Oktober 2026** — kritik tim terhadap Unimart yang terlalu general, pembahasan AI merchant marketing/referral/payment, pergeseran ke Local Commerce Intelligence Network, dan dua ide baru pendidikan personal serta Shelf Finder.

### Batas evidensi

- Tidak ada survei kompetitor, wawancara merchant/guru, data transaksi pilot, financial model berbasis aktual, atau verifikasi realtime program pendanaan yang dilakukan untuk **versi 1.0** ini.
- Contoh angka atau target eksperimen yang pernah muncul dalam diskusi (seperti volume merchant, fee, GMV, sampel wawancara) **bukan realisasi**.
- Tidak ada klaim bahwa implementasi fitur tertentu di situs produksi telah diperiksa di laporan ini.
- Klaim pasar lokal relatif kurang terlayani merupakan **hipotesis founder**, membutuhkan pemetaan pesaing nasional/regional dan observasi lapangan.
- Setiap keputusan mengenai custody/settlement, data anak, sekolah, dan penggunaan platform eksternal memerlukan telaah kebijakan/regulasi mutakhir.

**Versi berikutnya yang disarankan:** v1.1 sesudah problem interview dan lanskap pesaing; v2.0 sesudah eksperimen MVP terpilih dan matriks evidence terisi.
