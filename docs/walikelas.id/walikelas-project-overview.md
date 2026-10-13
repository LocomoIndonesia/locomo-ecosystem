# WaliKelas.id — Project Overview & Arsitektur Fundamental

> Dokumen gabungan konsep dan fondasi penyelesaian masalah WaliKelas.id.
> Disintesis dari seluruh berkas di `docs/walikelas.id/`:
> *WaliKelas.ID Development Proposal*, *Analisis Platform walikelas.ID* (Yayasan Wali Kelas Indonesia, 10 Des 2024), dan *Laporan Investasi & Rancangan Strategis* (versi AI).
> Dokumen ini sengaja **tidak membahas arsitektur teknis**. Fokusnya adalah gagasan, masalah yang dituju, dan cara platform menyelesaikannya.

---

## 1. Inti Gagasan dalam Satu Paragraf

WaliKelas.id adalah **ekosistem pendidikan digital** yang menjadi jembatan antara **siswa, guru, dan orang tua**. Namanya diambil dari peran wali kelas, yaitu sosok yang mengenal murid secara utuh dan menghubungkan sekolah dengan rumah. Platform ini meniru peran itu dalam bentuk digital. Ia berevolusi dari *marketplace guru privat* menjadi **infrastruktur pendidikan yang memadukan Agent AI dan Guru Manusia**. AI mengambil beban akademik dan administratif yang berulang. Guru manusia memegang hal yang tidak bisa digantikan mesin: karakter, moral, dan kepedulian.

> **Prinsip pokok:** *AI mengajar dan mengurus administrasi, manusia membimbing dan menjaga karakter.*

---

## 2. Masalah yang Ingin Diselesaikan

Seluruh masalah di bawah ini bermuara pada satu paradoks, yang disebut di proposal sebagai *paradoks pendidikan Indonesia*. Kebutuhan akan pengajar berkualitas sangat tinggi, tetapi guru dan lulusan pendidikan justru kurang sejahtera dan kurang terberdayakan.

### 2.1 Sisi Siswa & Orang Tua
| Masalah | Penjelasan |
|---|---|
| **Kesenjangan akses** | Kualitas guru dan sumber belajar sangat timpang antara kota dan daerah terpencil. |
| **Pembelajaran seragam** | Metode massal (*one-size-fits-all*) tidak menjawab perbedaan kecepatan belajar, gaya belajar, dan kepribadian anak. |
| **Hasil belajar rendah** | Kemampuan membaca, matematika, dan sains siswa Indonesia (PISA) masih di bawah rata-rata global. |
| **Perkembangan anak tidak terlihat utuh** | Orang tua hanya melihat nilai. Kondisi emosi, minat, dan karakter anak tidak terpetakan. |
| **Orang tua sulit memantau** | Tidak ada alat sederhana untuk memahami kemajuan belajar anak. |
| **Banjir informasi negatif** | Anak terpapar konten buruk (pornografi, judi online, hoaks) tanpa perlindungan yang memadai. |

### 2.2 Sisi Guru & Pendidik
| Masalah | Penjelasan |
|---|---|
| **Beban administrasi** | RPP, Silabus, Prota, Promes, dan soal menyita hingga ±40% waktu kerja, sehingga waktu untuk mendampingi siswa berkurang. |
| **Tanpa rekam jejak & reputasi** | Guru honorer dan swasta tidak punya wadah terverifikasi untuk menunjukkan kualitas dan portofolio. Akibatnya nilai tawar profesional mereka lemah. |
| **Kesejahteraan rendah** | Insentif tidak sebanding dengan tanggung jawab, sehingga motivasi mengajar menurun. |
| **Minim pengembangan berkelanjutan** | Pelatihan pedagogi dan teknologi sulit diakses. |
| **Lulusan pendidikan sulit terserap** | Ribuan lulusan keguruan kesulitan mendapat pekerjaan yang layak, padahal daerah membutuhkan guru. |

### 2.3 Tingkat Ekosistem
- **Literasi digital** siswa, guru, dan orang tua masih rendah.
- **Penelitian pendidikan** kekurangan data nyata tentang pola mengajar dan efektivitas metode belajar.
- **Teknologi pendidikan yang parsial** hanya menyentuh permukaan dan tidak menjawab inti masalah.

---

## 3. Visi, Misi, dan Nilai

**Visi:** Membangun ekosistem masa depan pendidikan Indonesia yang inklusif, inovatif, dan berkelanjutan.

**Misi:**
1. **Meningkatkan kualitas belajar**: pembelajaran privat yang bermutu dan terjangkau untuk semua kalangan.
2. **Memberdayakan tenaga pendidik**: pelatihan, evaluasi, dan pengurangan beban administrasi.
3. **Mendukung riset & literasi**: membuka data teragregasi dan wadah publikasi karya.
4. **Melindungi generasi muda**: menyaring konten negatif dan mempromosikan konten edukatif.

**Selaras dengan SDG 4** (pendidikan berkualitas yang inklusif dan merata).

---

## 4. Konsep Fundamental: Sistem *Dual-Core*

Ini inti arsitektur konseptual WaliKelas.id. Pendidikan dibagi menjadi dua peran yang saling melengkapi.

```
                    ┌─────────────────────────────┐
                    │        WALIKELAS.ID         │
                    └──────────────┬──────────────┘
          ┌────────────────────────┴────────────────────────┐
          ▼                                                 ▼
 ┌──────────────────────┐                      ┌───────────────────────┐
 │   CORE 1: AGENT AI   │                      │ CORE 2: GURU MANUSIA  │
 │   "Tenaga Pengajar   │                      │   "Wali Kelas"        │
 │   & Asisten Admin"   │                      │   Penjaga Karakter    │
 ├──────────────────────┤                      ├───────────────────────┤
 │ • Mengajar akademik  │                      │ • Bimbingan moral     │
 │ • Adaptif per anak   │                      │ • Konsultasi emosi    │
 │ • Tersedia 24/7      │                      │ • Pendampingan berkala│
 │ • Menyusun dokumen   │                      │ • Evaluasi & kehadiran│
 │   ajar untuk guru    │                      │   nyata               │
 │ • Memetakan psikologi│                      │ • Teladan & empati    │
 │ • Laporan otomatis   │                      │                       │
 └──────────────────────┘                      └───────────────────────┘
          └────────────────────────┬────────────────────────┘
                                   ▼
              Siswa berkembang secara akademik DAN karakter;
              Guru berdaya, terhormat, dan sejahtera.
```

**Mengapa dua inti?**
- AI unggul dalam **skala, kesabaran, personalisasi, dan konsistensi**, tetapi tidak punya otoritas moral dan kehangatan manusia.
- Manusia unggul dalam **keteladanan, empati, dan penilaian karakter**, tetapi terbatas waktu dan tenaga bila terbebani administrasi.
- Dengan membagi peran, waktu guru dikembalikan untuk hal yang paling berharga: **mendampingi manusia, bukan mengurus dokumen**.

---

## 5. Solusi untuk Setiap Pemangku Kepentingan

### 5.1 Untuk Siswa
- **Tutor AI Personal 24/7**: mengajar Calistung, Matematika, Sains, dan Bahasa Asing sesuai kecepatan dan gaya belajar anak.
- **Asesmen awal & pemetaan karakter**: tes diagnostik yang dilandasi literatur psikologi perkembangan anak (kepribadian, gaya belajar, kecerdasan majemuk).
- **Pendampingan wali kelas manusia** secara berkala untuk karakter dan emosi.
- **Perlindungan digital**: penyaringan konten negatif dan edukasi keamanan digital.

### 5.2 Untuk Orang Tua
- **Laporan perkembangan otomatis lewat WhatsApp**. Orang tua tidak perlu belajar aplikasi baru dan cukup membaca pesan. Ini prinsip *zero-barrier*.
- **Gambaran utuh anak**: akademik, emosi, minat, dan karakter, bukan sekadar angka.
- **Suara orang tua**: memberi ulasan anonim terhadap guru.

### 5.3 Untuk Guru & Pendidik
- **Asisten AI persiapan mengajar**: RPP, Silabus, Prota, Promes, LKS, dan bank soal sesuai kurikulum nasional disusun dalam hitungan menit.
- **Paspor Reputasi Digital**: profil profesional terverifikasi berisi riwayat mengajar, sertifikasi, dan indikator reputasi.
- **Rating & ulasan anonim 360°** dari siswa, orang tua, dan kepala sekolah. Ulasan disaring AI agar objektif.
- **Pasar modul ajar**: guru memonetisasi karya (RPP, modul, media ajar) kepada sesama guru.
- **Pengembangan kompetensi**: evaluasi berbasis data untuk public speaking, penguasaan materi, dan pedagogi.
- **Peluang penghasilan** bagi lulusan pendidikan dan guru muda, termasuk di daerah.

### 5.4 Untuk Sekolah, Yayasan & Pemerintah
- **Dashboard analitik** pemetaan kualitas pembelajaran dan karakter siswa.
- **Data teragregasi** untuk kebijakan dan penelitian pendidikan.
- **Kemitraan** untuk digitalisasi dan pemerataan kualitas.

### 5.5 Untuk Peneliti & Komunitas
- Sumber data untuk penelitian pola mengajar dan efektivitas metode.
- **Wadah literasi & publikasi** bagi guru dan siswa (artikel ilmiah, fiksi, esai).
- Forum, ruang karya, dan akses ke program bantuan atau beasiswa.

---

## 6. Cakupan Layanan Belajar

Lima kategori layanan awal:
1. **Calistung**: membaca, menulis, berhitung.
2. **Matematika**: pendekatan interaktif.
3. **Bahasa Asing**: Inggris, Mandarin, Arab.
4. **Bimbingan Ujian**: ujian sekolah, masuk perguruan tinggi, sertifikasi.
5. **Pendidikan Agama**: mendukung pendekatan kurikuler maupun tematik (akhlak, pembinaan spiritual) sesuai nilai komunitas pengguna.

Cakupan akan diperluas sesuai kebutuhan pasar, antara lain pembelajaran adaptif, ujian online, kelas virtual, mentoring karier, dan konseling psikologis untuk siswa dan guru.

---

## 7. Perjalanan Pengguna (Konseptual)

**Guru:** daftar dan verifikasi → terbit profil reputasi → menyiapkan ajar dengan AI dalam hitungan menit → mengajar dan menjadi wali kelas → menerima ulasan anonim dan penghasilan.

**Siswa & Orang Tua:** asesmen awal dan pemetaan karakter → belajar bersama tutor AI → pendampingan berkala wali kelas → ulasan anonim via WhatsApp → laporan rutin (mingguan/bulanan) ke orang tua dan sekolah.

---

## 8. Keberlanjutan: Bagaimana Misi Ini Dibiayai

Misi sosial ditopang model bisnis agar tidak bergantung pada donasi semata:

| Pilar | Gagasan |
|---|---|
| **Komisi sesi belajar** | Persentase kecil dari setiap sesi bimbingan siswa–wali kelas (15–20% pada laporan investasi; 10–20% pada proposal awal). |
| **Langganan AI Co-Pilot guru** | Dasar gratis (*freemium*), fitur analitis dan template premium berlangganan (±Rp 29 ribu–79 ribu/bulan). |
| **Bagi hasil marketplace modul ajar** | Biaya platform ±20% dari transaksi antar-guru. |
| **B2B/B2G dashboard analitik** | Lisensi untuk sekolah swasta, yayasan, dan dinas pendidikan. |
| **Pendukung** | Kemitraan/sponsor, iklan edukatif tersegmentasi, serta donasi dan crowdfunding untuk beasiswa siswa kurang mampu. |

Logika pertumbuhannya: **guru menjadi agen pertumbuhan organik** karena platform menyelesaikan masalah kerja mereka. Kebutuhan guru akan dokumen ajar dan kebutuhan orang tua akan laporan karakter menciptakan penggunaan harian yang melekat.

---

## 9. Posisi & Pembeda

| Pembanding | Fokus mereka | Pembeda WaliKelas.id |
|---|---|---|
| Ruangguru | Skala besar, konten umum | Personalisasi, pemantauan karakter, pemberdayaan guru |
| Zenius | Video terstruktur, persiapan ujian | Interaksi guru–siswa, manajemen kelas, umpan balik langsung |
| Khan Academy | Materi luas dan gratis | Pendampingan langsung dan penilaian |
| Udemy | Kursus keterampilan bebas | Pendidikan formal dan terstruktur |

**Pembeda utama:** WaliKelas.id tidak hanya "menjual konten" atau "mempertemukan guru". Ia **menaruh guru sebagai pusat pemberdayaan** (reputasi, penghasilan, waktu), **memperlakukan anak sebagai individu utuh** (akademik + karakter), dan **melibatkan orang tua** dengan cara yang sesederhana pesan WhatsApp.

---

## 10. Tahapan Perjalanan

- **Fase fondasi (2024):** riset kebutuhan → perancangan → pengembangan → soft launch (Des 2024) → rencana grand launch awal semester genap (Jan 2025).
- **Fase evolusi AI:**
  1. **MVP & pilot**: profil guru, asisten RPP, rating anonim via WhatsApp, asesmen karakter. Target: kepuasan guru 90%, keterlibatan orang tua 85%.
  2. **Komersialisasi**: marketplace modul ajar, tutor suara AI (Calistung dan bahasa asing), akselerasi akuisisi guru.
  3. **Layanan nasional**: standar reputasi pendidik nasional, kemitraan dengan pemda dan dinas pendidikan.
- **Target ambisi:** 10.000 guru dan 100.000 siswa di fase awal, lalu 100.000+ guru dan 1 juta siswa. Tujuan akhirnya menjadikan **Paspor Reputasi Pendidik** standar nasional.

---

## 11. Risiko & Cara Menjaganya

| Risiko | Pendekatan |
|---|---|
| Ulasan anonim disalahgunakan | Penyaringan sentimen AI sebelum masuk ke skor reputasi. |
| Guru enggan beradaptasi teknologi | Antarmuka sederhana berbasis WhatsApp dan template sekali klik. |
| Kebenaran pedagogis hasil AI | AI dibekali kurikulum resmi dan literatur psikologi terverifikasi, dengan guru manusia sebagai pengawas. |
| Persaingan platform besar | Fokus pada ceruk yang belum digarap: personalisasi, karakter, dan pemberdayaan guru. |
| Adopsi lambat | Insentif pengguna awal dan kemitraan komunitas pendidikan lokal. |
| Perubahan regulasi dan privasi data | Kepatuhan perlindungan data dan kemitraan dengan pemerintah. |

---

## 12. Dampak yang Dituju

1. **Pemerataan akses**: anak di daerah terpencil mendapat kualitas belajar yang setara dengan harga terjangkau.
2. **Peningkatan kualitas mengajar**: guru dilatih, dievaluasi, dan dibebaskan dari beban administratif.
3. **Pengembangan anak seutuhnya**: akademik, emosional, sosial, dan karakter.
4. **Pemberdayaan dan kesejahteraan guru**: reputasi yang diakui dan penghasilan tambahan.
5. **Transformasi digital pendidikan**: model yang bisa ditiru institusi lain.
6. **Generasi aman secara digital**: terlindungi dari konten negatif.

---

## 13. Catatan Sintesis & Hal yang Perlu Diselaraskan

Dokumen sumber dibuat pada waktu dan sudut pandang berbeda. Beberapa hal perlu diputuskan agar satu visi:

- **Peran inti:** proposal 2024 menempatkan *pertemuan fisik guru–siswa* sebagai layanan inti. Laporan investasi menempatkan *Agent AI sebagai pengajar* dan guru manusia sebagai *wali kelas karakter*. Dokumen ini memakai versi terbaru (Dual-Core) sebagai arah utama.
- **Cakupan fitur:** proposal awal luas (marketplace barang, bantuan sosial, penelitian, blockchain, iklan). Laporan investasi lebih fokus (reputasi guru, AI co-pilot, laporan WhatsApp, modul ajar). Perlu ditentukan mana yang inti dan mana yang tahap lanjut.
- **Komisi:** 10–20% (proposal) dan 15–20% (laporan investasi) perlu satu angka resmi.
- **Garis waktu:** jadwal peluncuran 2024–2025 sudah lampau dan perlu pembaruan terhadap kondisi saat ini.
- **Skala target:** pendanaan Rp 5–7 miliar (proposal awal) dan target pengguna (laporan investasi) perlu dihubungkan ke satu rencana keuangan.
- **Pendidikan agama:** dua dokumen merumuskannya berbeda (spesifik Islam vs. inklusif sesuai nilai komunitas). Perlu ditegaskan posisinya.

---

## 14. Rangkuman

> **WaliKelas.id** menjawab satu pertanyaan: *bagaimana setiap anak Indonesia mendapat pendidikan yang personal dan berkarakter, sementara setiap guru Indonesia mendapat waktu, penghargaan, dan penghidupan yang layak?*
>
> Jawabannya: biarkan **AI memikul pekerjaan yang berulang dan berskala**, biarkan **guru manusia menjadi wali kelas yang hadir untuk karakter dan hati anak**, dan hubungkan semuanya, termasuk orang tua, lewat saluran yang paling dekat dengan keseharian mereka.

*Sumber: `Analisis_Platform_walikelas.ID.pdf`, `WaliKelas.ID_Development_Proposal.pdf`, `walikelas-investor-report.md/.pdf`.*
