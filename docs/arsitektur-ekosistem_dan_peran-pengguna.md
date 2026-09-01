# Rencana Implementasi: Ekosistem Pariwisata Terstruktur & Pasukan Khusus Vanguard

Dokumen ini menjelaskan rancangan komprehensif alur kerja, struktur peran, dan pembagian tugas untuk **Pasukan Khusus Vanguard (Pramuwisata Penggerak)** dalam ekosistem **mandeh.online** terintegrasi.

---

## 🧭 Arsitektur Ekosistem & Peran Pengguna

Ekosistem `mandeh.online` mengintegrasikan tiga pilar utama untuk menjaga akuntabilitas lapangan dan transparansi informasi:

```
                  ┌─────────────────────────────────────────┐
                  │          Identity Provider (IdP)        │
                  │                 mandeh.id               │
                  └────────────────────┬────────────────────┘
                                       │ (OAuth2 SSO Token)
                                       ▼
                  ┌─────────────────────────────────────────┐
                  │            Client Application           │
                  │             mandeh.online.v2            │
                  └──────┬───────────────────────────┬──────┘
                         │                           │
                         ▼                           ▼
        ┌────────────────────────────────┐   ┌────────────────────────────────┐
        │     Armada Darat & Laut        │   │          Armada Udara          │
        │  - Presensi GPS (≤100m)        │   │  - Presensi Livestream/Virtual │
        │  - Tugas Fisik Docks & Pantai  │   │  - Kampanye Medsos & Web       │
        │  - Revitalisasi Fisik TIC      │   │  - Moderasi & Setup WiFi Hub   │
        └───────────────┬────────────────┘   └───────────────┬────────────────┘
                        │                                    │
                        └───────────────┬────────────────────┘
                                        ▼ (Points Reputasi ≥ 80)
                  ┌─────────────────────────────────────────┐
                  │         Graduation Gate & E-Voting      │
                  │        (AES-256-CBC + SHA-256 ISO)      │
                  └─────────────────────────────────────────┘
```

---

## 🔄 Rincian Alur Kerja (User Flow)

### 1. Alur Pendaftaran & Seleksi Divisi
1. **Registrasi SSO (`mandeh.id`):**
   * Calon mendaftar akun baru melalui pintu gerbang terpusat di `mandeh.id` dengan memilih tipe akun **Vanguard**.
   * Setelah sukses, token stateless JWT diterbitkan dan pengguna diarahkan kembali ke `mandeh.online.v2`.
2. **Pengisian Form Onboarding:**
   * Di `mandeh.online.v2`, sistem mendeteksi bahwa akun Vanguard belum memiliki data partisipan.
   * Pengguna diarahkan ke form onboarding untuk melengkapi data:
     * **Asal Partisipan:** *Lokal* (warga asli Pessel) atau *Perantau*.
     * **Pemilihan Divisi Armada:**
       * **Armada Darat & Laut:** Berfokus pada pelayanan wisatawan langsung di lapangan, panduan fisik, keselamatan pantai, dan pemeliharaan objek wisata.
       * **Armada Udara:** Berfokus pada dokumentasi digital, pemetaan drone, kampanye media sosial, pengelolaan hotspot WiFi komunitas, dan moderasi konten.
3. **Generasi Tracking ID:**
   * Setelah formulir disubmit, sistem men-generate ID Pelacakan Unik dengan format: `VGD-YYYYMMDD-[RANDOM_4_DIGIT]` (contoh: `VGD-20260614-7291`).
   * Status keanggotaan diset sebagai `pending_verification`.

### 2. Alur Verifikasi Admin (Moderation Queue)
1. **Antrean Pendaftaran:**
   * Setiap calon Vanguard yang baru mendaftar akan masuk ke antrean moderasi di dashboard Admin.
2. **Review Dokumen & Kualifikasi:**
   * Admin meninjau data calon, memvalidasi status keanggotaan HPI (Himpunan Pramuwisata Indonesia) Cabang Pesisir Selatan jika ada.
3. **Persetujuan (Approval):**
   * Admin menyetujui pendaftaran melalui dashboard moderasi dengan 1-klik.
   * Status Vanguard berubah menjadi `active`, dan mereka menerima **10 Poin Reputasi Awal**. Pengguna kini dapat mengakses Papan Misi (*Mission Board*) di dashboard mereka.

### 3. Siklus Tugas Harian (Daily Presensi & Mission Board)
Vanguard diwajibkan mengumpulkan poin reputasi dengan menyelesaikan presensi harian dan misi khusus divisi:

#### A. Presensi Harian (Check-in Loop)
*   **Armada Darat & Laut:** Wajib melakukan check-in melalui browser handphone di lokasi wisata. Sistem memverifikasi koordinat GPS secara real-time. Jika jarak $\le$ 100m dari koordinat Hub (misal: Kantor TIC/Dermaga), check-in disetujui (**+10 Poin**).
*   **Armada Udara:** Melakukan check-in presensi virtual/livestream melalui dashboard pendukung (**+10 Poin**).

#### B. Papan Misi Khusus Divisi
Misi dirancang spesifik untuk mengoptimalkan kompetensi masing-masing divisi:

| Divisi | Contoh Tugas / Misi | Deskripsi Tugas | Poin Reward | Bukti Verifikasi |
| :--- | :--- | :--- | :---: | :--- |
| **Armada Darat & Laut** | **Simulasi Pelayanan Tamu** | Melatih simulasi pemanduan langsung sesuai standar kode etik HPI. | 30 | URL Foto/Video simulasi |
| | **Revitalisasi Fisik TIC** | Berpartisipasi membersihkan atau memperbaiki fasilitas TIC Mandeh. | 25 | Log Aksi + Foto sebelum & sesudah |
| | **Patroli Kebersihan & Keamanan** | Melakukan penyisiran sampah dan monitoring pungli di Pantai Carocok. | 20 | Foto kondisi lokasi + Geotag |
| **Armada Udara** | **Kampanye Media Sosial** | Membuat konten infografis/video Reels promosi wisata nagari berdaulat. | 35 | URL Link Postingan Medsos |
| | **Instalasi Hotspot WiFi Hub** | Mengatur router nirkabel di titik kumpul wisatawan untuk program O2O. | 30 | Log Konfigurasi IP & Foto Router |
| | **Moderasi Konten Komunitas** | Meninjau laporan ulasan mencurigakan di antrean *fraud* sistem. | 15 | ID Tiket Moderasi yang diselesaikan |

4. **Pengiriman Laporan Tugas:**
   * Vanguard mengunggah deskripsi pekerjaan dan menyertakan tautan bukti (*Proof URL*) ke dashboard.
5. **Kurasi Laporan:**
   * Laporan diverifikasi oleh Admin/Ketua Pemuda Nagari. Setelah disetujui, poin reward ditambahkan secara dinamis ke profil Vanguard terkait.

### 4. Alur Kelulusan & E-Voting Aman (Graduation Gate)
Sistem menerapkan tata kelola demokrasi digital tertutup untuk memilih pemimpin atau kebijakan komunitas:
1. **The Graduation Gate:**
   * Setiap kali poin Vanguard bertambah, sistem memicu fungsi `recalculatePoints()`.
   * Jika total Poin Reputasi $\ge$ 80, status kelulusan diset menjadi `is_vanguard_graduated = true`.
2. **Aktivasi Hak Suara (E-Voting):**
   * Hanya Vanguard yang sudah lulus (*graduated*) yang dapat membuka halaman pemilihan [vanguard/evoting](file:///home/dev360_03/locomo.id/mandeh.online.v2/app/Modules/Vanguard/Routes/web.php#L20-L21).
3. **Ballot Casting yang Aman & Anonim:**
   * Pilihan suara dienkripsi menggunakan standar industri **AES-256-CBC** sebelum disimpan ke database (`vanguard_votes`).
   * Tanda tangan digital (*Signature*) di-generate menggunakan **SHA-256** dengan garam berupa kombinasi *Pilihan + Tracking ID + APP_KEY* untuk menjamin bahwa satu orang hanya mengirim satu suara tanpa membocorkan pilihan fisiknya.
   * Partisipan ditandai `voted_at = now()` untuk mengunci akses agar tidak dapat memilih dua kali.

---

## 📂 Rancangan Database & Komponen Kode

### A. Skema Database (Evolusi Tabel Vanguard)
Rancangan tabel PostgreSQL yang telah diimplementasikan di [2026_06_14_000001_create_vanguard_tables.php](file:///home/dev360_03/locomo.id/mandeh.online.v2/app/Modules/Vanguard/Migrations/2026_06_14_000001_create_vanguard_tables.php):
*   `vanguard_participants`: Menyimpan profil, divisi (`darat_laut` atau `udara`), poin reputasi, status kelulusan, dan status voting.
*   `vanguard_checkins`: Mencatat tanggal presensi harian dan metode verifikasi.
*   `vanguard_missions`: Daftar tugas master yang tersedia.
*   `vanguard_mission_reports`: Laporan tugas yang dikirim beserta status persetujuan admin.
*   `vanguard_votes`: Menyimpan surat suara terenkripsi (`encrypted_ballot`) dan tanda tangan pengesahan (`signature_hash`).

### B. Komponen Backend Laravel (Modules)
*   **`VanguardOnboardingController`:** Mengelola form registrasi divisi dan pembuatan tracking ID unik.
*   **`VanguardDashboardController`:** Menyediakan feed misi aktif sesuai divisi, presensi harian, dan pengiriman laporan misi.
*   **`VanguardVotingController`:** Mengatur pembatasan gerbang kelulusan (Point Gate) dan mengenkripsi surat suara secara anonim.
*   **`TicRevitalizationController`:** Command center untuk memantau log perbaikan infrastruktur TIC.

---

## 🔬 Rencana Verifikasi (Verification Plan)

### A. Pengujian Otomatis (Automated Tests)
Kita akan menulis Unit & Integration Test untuk menguji logika bisnis utama:
1. **Test Onboarding:** Menjamin user biasa yang terdaftar sebagai Vanguard tidak dapat mendaftar ulang jika sudah memiliki profil.
2. **Test Point Gate:** Memastikan user dengan poin < 80 ditolak saat mengakses halaman e-voting.
3. **Test Cryptography Integrity:** Menguji bahwa data di kolom `encrypted_ballot` pada tabel `vanguard_votes` tidak dapat dibaca secara plain text di database, namun dapat didekripsi dengan benar oleh sistem menggunakan kunci aplikasi.

### B. Verifikasi Manual (Manual Walkthrough)
1. **Registrasi:** Menjalankan alur pendaftaran user tipe Vanguard di SSO `mandeh.id` dan memastikan perpindahan ke form divisi berjalan mulus.
2. **Dashboard Selektif:** Menguji masuk sebagai akun Armada Udara dan memastikan papan misi hanya menampilkan tugas "Kampanye Media Sosial" atau "WiFi Hub" (tugas Armada Darat & Laut tidak boleh muncul).
3. **Simulasi Presensi GPS:** Menguji tombol check-in pada perangkat mobile dengan simulasi koordinat GPS $\le$ 100m dan > 100m untuk memvalidasi pemberian poin.
