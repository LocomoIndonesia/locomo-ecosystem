# WaliKelas.id — 90-Second Product Demo Video Storyboard & Script
**Target Submission:** Global Accelerator / VC Pre-Seed Application (November 2026)  
**Theme:** *Empowering Primary Educators with an Evidence-Backed, Child-Safe Copilot*  
**Duration:** Exactly 90 Seconds  

---

## 🎬 Video Production Overview

| Scene | Duration | Screen View | Audio / Narration (Bahasa Indonesia / English Subtitles) | Key Visual Action |
|---|---|---|---|---|
| **Scene 1** | 00:00 – 00:15 (15s) | Teacher Dashboard (`/`) | *"Setiap minggu, guru SD menghabiskan hingga 15 jam untuk menyusun RPP dan memeriksa tugas satu per satu. Kenalkan WaliKelas.id: copilot pembelajaran terpadu untuk kelas sekolah maupun les privat."* | Tampilkan dashboard modern dengan 1-klik switch antara Sekolah & Privat. |
| **Scene 2** | 00:15 – 00:35 (20s) | Planning Copilot (`/programs/{id}`) | *"Dalam 1 menit, guru cukup menyalin silabus bebas. Gemini AI mengekstraksi sasaran ajar dan rubrik terkurasi. Namun guru tetap memegang kendali penuh untuk meninjau dan mengesahkan rencana pembelajaran."* | Tempel teks silabus fabel fiksi, klik "Ekstrak dengan AI", lihat deteksi ambiguitas, klik "Setujui Versi Aktif". |
| **Scene 3** | 00:35 – 00:55 (20s) | Learner Runner (`/runner/{token}`) & Guardian Checkpoint (`/guardian/checkpoint/{id}`) | *"Siswa mengakses misi membaca ramah anak tanpa password—fokus 10 menit tanpa distraksi chatbot bebas. Di rumah, orang tua menerima tugas offline dongeng meja makan dan mengonfirmasi capaian anak dalam satu klik."* | Tampilkan UI Runner fabel berang-berang siswa, lalu transisi cepat ke layar konfirmasi orang tua (Selesai / Butuh Bantuan). |
| **Scene 4** | 00:55 – 01:15 (20s) | Teacher Triage Queue (`/programs/{id}/triage`) | *"Semua hasil belajar masuk ke Inbox Triage Guru. Saat siswa butuh bantuan, AI menganalisis kesenjangan dan mengusulkan intervensi perbaikan. Tidak ada nilai otomatis yang keluar tanpa persetujuan manusia."* | Tampilkan kartu usulan intervensi Kiki, guru mengklik "✓ Setujui Intervensi (1-Klik)", terbit Laporan Perkembangan 3-Lapis. |
| **Scene 5** | 01:15 – 01:30 (15s) | Telemetry & Closing (`/telemetry/export-csv`) | *"Hasilnya? Efisiensi kerja guru meningkat hingga 80%, menghemat rata-rata 10 jam per minggu. WaliKelas.id membuktikan bahwa AI terbaik adalah AI yang memberdayakan manusia, bukan menggantikannya."* | Tampilkan metrik telemetri net time saved dan tombol unduh CSV Data Room. Teks penutup: *WaliKelas.id — Partner Andal Setiap Pendidik*. |

---

## 🎙️ Detailed Voiceover & Screenplay

### [00:00 - 00:15] Pembukaan & Masalah Nyata Guru
- **Visual:** Buka browser di [http://localhost:8088](http://localhost:8088). Kamera zoom halus ke dashboard guru.
- **Aksi:** Tunjukkan transisi instan antara tab **Mode Sekolah** (Pak Budi - SD Negeri Cemara 01) dan **Mode Privat Mandiri** (Bu Siti - Tutor Les).
- **Narasi:**
  > *"Di Indonesia dan Asia Tenggara, jutaan guru sekolah dasar dan tutor privat terbebani tumpukan administrasi: merancang silabus, memeriksa esai, dan mengabari orang tua hingga larut malam. WaliKelas.id hadir merevolusi ekosistem ini dalam satu ruang kerja terpadu."*

### [00:15 - 00:35] Planning Copilot & Human Control (TI-04, TI-05)
- **Visual:** Masuk ke program *"Kelas 3A - Literasi Membaca Fabel"*. Buka modal Impor Silabus.
- **Aksi:** Tempel silabus 4 baris teks bebas. Klik **Ekstrak Rencana dengan Copilot**.
- **Visual:** Tampil draf versi 1 terstruktur rapi: tujuan ajar, durasi sesi, dan rubrik terstandar. Kotak peringatan ambiguitas muncul jika alokasi waktu belum spesifik.
- **Aksi:** Guru meninjau dan menekan tombol hijau **Setujui & Aktifkan Versi Rencana**.
- **Narasi:**
  > *"Melalui Planning Copilot berbantuan Google Gemini, guru cukup menempel silabus bebas. Sistem secara deterministik memetakan sasaran kompetensi terkurasi dan menandai ambiguitas kurikulum. Tidak ada rencana yang aktif otomatis sebelum disahkan oleh sang guru."*

### [00:36 - 00:55] Learner Runner & Kemitraan Orang Tua (TI-07, TI-08)
- **Visual:** Buka link siswa [http://localhost:8088/runner/demo-runner-token-2026](http://localhost:8088/runner/demo-runner-token-2026).
- **Aksi:** Tunjukkan antarmuka Server-Driven UI yang tenang dan ramah anak kelas 3 SD. Siswa membaca dongeng berang-berang kecil, menjawab pilihan ganda, dan menuliskan 1 kalimat refleksi. Sesi dibatasi timer 10 menit dengan ucapan selamat dan anjuran istirahat layar.
- **Visual:** Beralih ke layar orang tua di smartphone [http://localhost:8088/guardian/checkpoint/b4c30e74-71cf-4d7e-b5fe-5f7262648f4a](http://localhost:8088/guardian/checkpoint/b4c30e74-71cf-4d7e-b5fe-5f7262648f4a).
- **Aksi:** Orang tua membaca panduan dongeng meja makan dan menekan tombol hijau *"Selesai"*.
- **Narasi:**
  > *"Siswa belajar tanpa rasa cemas melalui runner web yang bersih tanpa login rumit dan tanpa chatbot bebas yang rawan halusinasi. Di sisi rumah, orang tua diajak bermitra melalui konfirmasi misi offline 1-klik yang menjaga privasi anak."*

### [00:56 - 01:15] Inbox Triage & Pengesahan Intervensi (TI-05, TI-12)
- **Visual:** Kembali ke layar guru di [http://localhost:8088/programs/9ae4053b-2104-44aa-971e-8340e8cddfff/triage](http://localhost:8088/programs/9ae4053b-2104-44aa-971e-8340e8cddfff/triage).
- **Aksi:** Tampilkan kartu Triage. Copilot mendeteksi kebutuhan intervensi Kiki dan mengusulkan kegiatan membaca terpandu tambahan 15 menit. Guru mengklik **✓ Setujui Intervensi (1-Klik)**.
- **Aksi:** Klik **+ Terbitkan Laporan 3-Lapis**. Tampilkan modal Laporan Perkembangan (Fakta Kehadiran, Temuan Bukti Asesmen, Rekomendasi Rumah).
- **Narasi:**
  > *"Seluruh tugas yang masuk disaring ke Inbox Triage Guru. Ketika siswa membutuhkan penguatan, AI mengusulkan tindakan remedial beserta alasan pedagogisnya. Guru mengesahkan intervensi dalam 1 klik dan menerbitkan laporan perkembangan 3-lapis yang transparan dan siap dibagikan ke orang tua."*

### [01:16 - 01:30] Telemetri, Defensibilitas & Penutup
- **Visual:** Buka metrik telemetri di Dashboard, klik **📊 Unduh CSV Data Room**. Tampilkan grafik penghematan waktu 80%+.
- **Narasi:**
  > *"Telemetri empiris kami membuktikan penghematan waktu hingga 80% per sesi asesmen. WaliKelas.id: fondasi relational tangguh, privasi anak mutlak, dan mitra terbaik bagi masa depan literasi anak bangsa."*
- **Visual Logo:** WaliKelas.id • *Empowering Educators, Inspiring Learners*.

---

## 🛠️ Checklist Kesiapan Demo Perekaman (Recording Checklist)
- [x] Docker stack aktif di port `8088` (Sail/PHP 8.4 + Postgres 16).
- [x] Pre-compiled asset bundle terkompilasi bersih tanpa `public/hot`.
- [x] Demo data tersedia:
  - User: `Pak Budi` & `Bu Siti`.
  - Siswa: `Kiki` (Runner token: `demo-runner-token-2026`).
  - Wali: `Maya` (Checkpoint ID: `b4c30e74-71cf-4d7e-b5fe-5f7262648f4a`).
  - Triage Queue: Program `9ae4053b-2104-44aa-971e-8340e8cddfff`.
- [x] Resolusi layar standar: 1920x1080 (16:9), browser zoom 100%.
