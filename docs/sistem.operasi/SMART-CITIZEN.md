Berikut adalah dokumen acuan teknis untuk pengembangan sistem **Smart-Citizen**, sebuah platform pemilihan ketua RT dan RW digital yang dirancang khusus untuk lingkungan **Galaxy Pulo Sirih Timur, Bekasi**. Narasi ini disusun secara objektif sebagai fondasi pengembangan arsitektur sistem oleh tim pengembang.
# Smart-Citizen: Arsitektur Sistem Pemilihan Digital
**Lokasi Implementasi:** Galaxy Pulo Sirih Timur, Bekasi
## I. Konsep Fundamental Sistem
Sistem Smart-Citizen dibangun dengan mengedepankan kedaulatan data warga dan efisiensi birokrasi tingkat akar rumput. Berikut adalah pilar utama sistem:
 * **Antarmuka Agent-First:** Mengadopsi filosofi *user experience* (UX) di mana sistem berperan sebagai asisten aktif. Pengguna tidak melakukan navigasi menu yang kompleks, melainkan berinteraksi melalui *Response Cards* yang menyajikan informasi secara kontekstual sesuai tahapan pemilihan.
 * **Verifikasi Integritas Ganda:** Keamanan identitas dipastikan melalui pencocokan data primer (Nama, NIK, dan Nomor KK) terhadap basis data internal panitia, diperkuat dengan tanda tangan digital sebagai bukti otentikasi mandiri dari warga.
 * **Mekanisme Partisipasi Proaktif:** Mengintegrasikan WhatsApp Gateway sebagai mesin penggerak partisipasi. Sistem melakukan pengingat otomatis (nudge) secara berkala untuk memastikan setiap warga menggunakan hak suaranya tanpa perlu intervensi manual dari panitia di hari pemilihan.
 * **Anonimitas Suara (The Privacy Wall):** Arsitektur database memisahkan secara fisik antara catatan identitas pemilih dengan log pilihan suara. Hal ini menjamin kerahasiaan pilihan warga sembari tetap mempertahankan transparansi jumlah suara yang masuk.
## II. User Flow Detail (Alur Warga)
Alur berikut merupakan representasi urutan logika yang dialami oleh warga dari tahap pendaftaran hingga hasil pemilihan final.
### 1. Tahap Onboarding & Registrasi Identitas
 * **Input Data:** Warga mengakses platform dan memasukkan Nama Lengkap, NIK, Nomor KK, serta nomor WhatsApp aktif.
 * **Pernyataan Digital:** Warga wajib memberikan tanda tangan elektronik pada kolom yang tersedia sebagai pernyataan resmi bahwa identitas yang diberikan adalah benar.
 * **Status Verifikasi:** Data dikirimkan ke sistem untuk divalidasi oleh panitia secara internal. Warga menerima kartu status "Dalam Proses Verifikasi" pada dashboard mereka.
### 2. Tahap Edukasi & Masa Tenang
 * **Eksplorasi Kandidat:** Warga yang telah terverifikasi dapat mengakses profil lengkap kandidat. Informasi disajikan dalam bentuk kartu interaktif yang berisi profil singkat, visi, misi, dan materi kampanye digital.
 * **Hitung Mundur (Countdown):** Dashboard utama menampilkan waktu tersisa menuju jam pembukaan pemilihan. Seluruh interaksi dipandu oleh pesan-pesan informatif dari sistem (agent-first).
### 3. Tahap Pemilihan (Hari-H)
 * **Aktivasi Bilik Suara:** Pada jam yang ditentukan, sistem mengirimkan notifikasi serentak melalui WhatsApp kepada seluruh warga terverifikasi bahwa pemilihan telah dimulai.
 * **Penggunaan Hak Suara:** Warga masuk ke dashboard, memilih salah satu kandidat, dan melakukan konfirmasi akhir. Setelah suara terkirim, akses pemilihan pada akun tersebut otomatis dikunci oleh sistem.
 * **Otomasi Pengingat (Smart Reminder):** Setiap jam, sistem secara otonom memindai data pemilih. Warga yang terverifikasi namun belum menggunakan hak suaranya akan menerima pengingat personal melalui WhatsApp.
### 4. Tahap Penutupan & Hasil
 * **Lockdown Sistem:** Begitu waktu pemilihan berakhir, sistem secara otomatis menutup seluruh akses voting.
 * **Notifikasi Abstain:** Warga yang tidak memberikan suara hingga batas waktu berakhir akan menerima pemberitahuan otomatis bahwa hak suara mereka dinyatakan hangus atau abstain.
 * **Tabulasi Hasil:** Sistem menyajikan rekapitulasi suara akhir secara transparan yang dapat diakses oleh warga sebagai bentuk pertanggungjawaban publik.
## III. Spesifikasi Arsitektur Data
Untuk mendukung skalabilitas dan keamanan, sistem akan menggunakan pendekatan modular monolith atau microservices dengan tumpukan teknologi yang mencakup Laravel (PHP), Python untuk pemrosesan data, dan Docker untuk manajemen infrastruktur.
| Komponen | Spesifikasi Logika |
|---|---|
| **Data Pemilih** | Disimpan dalam tabel terenkripsi yang hanya dapat diakses untuk kebutuhan validasi internal. |
| **Vault Suara** | Tabel terpisah yang menyimpan ID unik suara tanpa referensi ke identitas pribadi (Anonymous IDs). |
| **Messaging Engine** | Integrasi API WhatsApp dengan sistem penjadwalan (*Task Scheduling*) untuk pengingat setiap jam. |
| **Audit Trail** | Log aktivitas sistem yang mencatat waktu pendaftaran dan waktu pengambilan suara untuk validasi integritas pemilihan. |
Dokumen ini berfungsi sebagai acuan fundamental bagi pengembangan teknis lebih lanjut dalam ekosistem **Smart-Citizen** di Galaxy Pulo Sirih Timur, Bekasi.

___
Berikut adalah kelanjutan dokumen acuan teknis **Smart-Citizen** untuk profil **Admin (Panitia)** dan **Kandidat**, yang dirancang khusus untuk tata kelola pemilihan di **Galaxy Pulo Sirih Timur, Bekasi**.
# Smart-Citizen: Tata Kelola & Manajemen Kampanye
**Lokasi Implementasi:** Galaxy Pulo Sirih Timur, Bekasi
## I. Konsep Fundamental Pengelolaan (Orchestration)
Pengelolaan sistem Smart-Citizen tidak menggunakan model kontrol konvensional, melainkan menggunakan prinsip **Governance Orchestration**:
 * **Validator Autonomy:** Admin berfungsi sebagai verifikator akhir yang mencocokkan data input warga (Nama, NIK, KK) dengan *Master Data* internal tanpa perlu melihat dokumen fisik asli, menjaga privasi warga namun tetap memastikan validitas.
 * **Candidate Centralization:** Untuk menjaga ketertiban data dan integritas konten, seluruh profil kandidat diinisiasi secara terpusat oleh Admin. Kandidat hanya memiliki hak akses untuk mengelola narasi kampanye mereka.
 * **Automated Oversight:** Admin memantau jalannya pemilihan melalui *Live Pulse Dashboard* yang menunjukkan statistik partisipasi secara *real-time* dan status pengiriman notifikasi WhatsApp otomatis tanpa menyentuh data kerahasiaan pilihan warga.
## II. User Flow Detail: Admin (Panitia Pemilihan)
Admin berperan sebagai moderator sistem yang memastikan ekosistem pemilihan berjalan sesuai jadwal dan aturan yang ditetapkan.
### 1. Persiapan & Pengaturan Sistem (Initialization)
 * **Setup Jadwal:** Admin menetapkan jendela waktu pemilihan (Waktu Buka dan Tutup Bilik Suara Digital).
 * **Inisiasi Kandidat:** Admin memasukkan data dasar kandidat (Nama, Nomor Urut, dan Foto Profil Resmi) serta memberikan akses akun kepada kandidat yang bersangkutan.
 * **Impor Data Pemilih (DPT):** Admin mengunggah daftar Nama, NIK, dan No. KK warga Galaxy Pulo Sirih Timur ke dalam basis data internal sebagai referensi validasi otomatis.
### 2. Moderasi & Verifikasi Pemilih
 * **Review Antrean:** Sistem menyajikan kartu respon (*Response Card*) berisi data pendaftar baru yang mencakup Nama, NIK, No. KK, dan Tanda Tangan.
 * **Validasi Identitas:** Admin mencocokkan data tersebut dengan database internal. Jika data sinkron, Admin memberikan status **"Verified"**. Jika tidak, Admin menolak dengan menyertakan alasan yang akan dikirim otomatis ke WhatsApp warga.
### 3. Pemantauan Hari-H (Real-Time Monitoring)
 * **Live Participation Pulse:** Admin memantau grafik persentase warga yang telah menggunakan hak suara vs yang belum.
 * **Monitoring Notifikasi:** Admin memastikan mesin *WhatsApp Nudge* bekerja setiap jam untuk mengirimkan pesan pengingat kepada warga yang belum memilih.
### 4. Sertifikasi Hasil
 * **Tabulasi Final:** Setelah waktu pemilihan berakhir, Admin melakukan validasi akhir terhadap total suara yang masuk.
 * **Publikasi:** Admin memicu perintah sistem untuk menerbitkan rekapitulasi hasil akhir kepada seluruh warga secara transparan di dashboard Smart-Citizen.
## III. User Flow Detail: Kandidat
Kandidat adalah subjek pemilihan yang diberikan ruang khusus untuk berkomunikasi dengan konstituen secara digital.
### 1. Aktivasi Profil
 * **Login Akses:** Kandidat masuk menggunakan akun yang telah dibuatkan oleh Admin.
 * **Finalisasi Data:** Kandidat melengkapi profil mereka dengan narasi mendalam yang meliputi Visi, Misi, dan Program Kerja unggulan.
### 2. Manajemen Narasi Kampanye
 * **Posting Konten:** Kandidat dapat mengunggah materi kampanye tambahan (seperti poster digital atau video singkat pesan untuk warga) yang akan muncul secara eksklusif di dashboard warga.
 * **Update Berkala:** Selama masa kampanye, kandidat dapat memperbarui informasi atau menambahkan berita terbaru terkait kegiatan sosialisasi mereka di lingkungan Galaxy Pulo Sirih Timur.
### 3. Analisis Jangkauan (Insights)
 * **Engagement Monitoring:** Kandidat dapat melihat statistik anonim mengenai berapa kali profil mereka dilihat oleh warga atau berapa banyak warga yang telah membaca detail visi-misi mereka.
 * *Catatan:* Kandidat tidak memiliki akses ke identitas warga yang melihat profil mereka demi menjaga objektivitas dan privasi.
## IV. Spesifikasi Arsitektur Admin & Kandidat
| Komponen | Spesifikasi Logika |
|---|---|
| **Integrasi DPT** | Mekanisme *matching logic* antara input registrasi warga dengan database internal panitia. |
| **Candidate Sandbox** | Ruang isolasi konten di mana kandidat hanya bisa mengedit data mereka sendiri tanpa akses ke modul sistem lainnya. |
| **Live Dashboard** | *Websocket connection* untuk pembaruan data partisipasi warga secara instan tanpa *refresh* halaman. |
| **Audit Logs** | Pencatatan setiap aksi admin (verifikasi/tolak) untuk menghindari penyalahgunaan wewenang. |
Dokumen ini melengkapi narasi sistem **Smart-Citizen** sebagai acuan utama dalam perancangan arsitektur teknis dan UI/UX bagi seluruh pemangku kepentingan di Galaxy Pulo Sirih Timur, Bekasi.
