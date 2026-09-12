# Rancang Bangun Integrasi Parameter Sistem Misi PitiCoin.id
**Sistem Verifikasi Otomatis & Standarisasi Parameter Misi Berbasis Logika Ekosistem**  
*Tanggal: 13 September 2026*  
*Arsitek: Locomo AI Agent OS (Antigravity)*  

---

## 1. Latar Belakang & Masalah Utama

Pada implementasi awal, modul CRUD Misi di Dashboard Admin telah memungkinkan Admin membuat dan mengelola data misi (judul, deskripsi, nilai taruhan Koin Perak, hadiah Koin Emas, dan batas waktu). Namun:
1. **Ketiadaan Parameter Otomatis**: Belum ada jembatan data antara aktivitas pengguna di aplikasi (rekrut referral, daftarkan toko ke Unimart, transaksi PPOB, dll.) dengan status kemenangan/kekalahan taruhan di `escrow_transactions`.
2. **Beban Operasional Admin**: Jika resolusi kemenangan dilakukan secara manual oleh Admin, sistem tidak akan *scalable* ketika ribuan pengguna menjalankan ekspedisi secara simultan.
3. **Pemisahan Peran (Separation of Concerns)**:
   - **Admin**: Berfokus 100% pada aspek kreatif, narasi/lore ekspedisi Gunung Mas, strategi reward, dan copy konten.
   - **Sistem**: Bertanggung jawab 100% mengukur metrik capaian secara presisi, real-time, dan mengeksekusi distribusi koin via smart escrow logic.

---

## 2. Arsitektur Engine Misi (Parameterized Quest Engine)

```
┌─────────────────────────────────────────────────────────────┐
│                      ADMIN DASHBOARD                        │
│  - Form Buat/Edit Misi                                      │
│  - Pilih Preset Parameter: [ Rekrut Anggota ]               │
│  - Masukkan Target Threshold: [ 5 ]                         │
│  - Tulis Narasi/Lore Misi                                   │
└──────────────────────────────┬──────────────────────────────┘
                               │ Disimpan ke `quests.parameters`
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                 QUEST & ESCROW DATA STORE                   │
│  - `quests.parameters`: {"action": "RECRUIT", "target": 5}  │
│  - `escrow_transactions`: status=LOCKED, baseline_count=12  │
└──────────────────────────────┬──────────────────────────────┘
                               │
            ┌──────────────────┴──────────────────┐
            ▼                                     ▼
┌───────────────────────────────┐   ┌───────────────────────────────┐
│     ON-DEMAND PROGRESS        │   │    EXPIRED CHECK CRON JOB     │
│   (User Buka App / Klik Klaim)│   │     (Setiap 15 Menit)         │
│               │               │   │               │               │
│  QuestEvaluatorService        │   │  Cek Escrow yang Lewat        │
│  Hitung selisih metrik sistem │   │  `expires_at` tapi belum lolos│
│               │               │   │               │               │
│  Target tercapai?             │   │  Otomatis:                    │
│  - YA  -> Resolve SUCCESS     │   │  - GAGAL -> Resolve FAILED    │
│  - TIDAK -> Tampilkan Progres │   │    (Perak dipotong/hangus)    │
└───────────────────────────────┘   └───────────────────────────────┘
```

---

## 3. Katalog Standar Parameter Aksi Sistem (`action_type`)

Sistem menyediakan preset parameter yang dapat langsung dipilih oleh Admin melalui antarmuka dropdown:

| Parameter Code | Label di Dashboard Admin | Cara Sistem Mengukur (Data Query) | Satuan / Input Admin |
| :--- | :--- | :--- | :--- |
| `RECRUIT_MEMBERS` | **Rekrut Anggota Baru** | Menghitung jumlah record `users` dengan `upline_id = user_id` yang dibuat setelah waktu staking (`created_at >= escrow.created_at`). | Jumlah Anggota (misal: 3, 5, 10) |
| `REGISTER_MERCHANT_UNIMART` | **Daftarkan Toko / Bisnis Unimart** | Menghitung order/pendaftaran merchant di `service_orders` dengan `service_code = UNIMART_POS` atas nama user. | Jumlah Toko (misal: 1, 2) |
| `REGISTER_WALIKELAS_TEACHER`| **Daftarkan Guru / Pendidik WaliKelas** | Menghitung pendaftaran guru di `service_orders` dengan `service_code = WALIKELAS_PRO`. | Jumlah Pendidik (misal: 1) |
| `DIGITAL_PPOB_TRANSACTION` | **Transaksi Produk Digital (PPOB)** | Menghitung transaksi sukses pembelian pulsa/PLN/data di modul PPOB. | Frekuensi Transaksi (misal: 1, 3) |
| `DAILY_CHECKIN_STREAK` | **Check-in Harian Berturut-turut** | Menghitung record di `user_checkins` yang berurutan sesuai target hari. | Jumlah Hari (misal: 3, 7) |
| `EARN_AFFILIATE_COMMISSION`| **Kumpulkan Komisi Afiliasi (IDR)** | Menghitung akumulasi komisi baru di `affiliate_commissions` sejak staking. | Nominal Rupiah (misal: 50.000) |
| `MANUAL_PROOF_SUBMISSION` | **Verifikasi Bukti Lapangan / Khusus** | Pengguna mengunggah foto bukti fisik (survei warung/kegiatan), verifikasi 1-click oleh Admin/Saudagar. | Tautan File / Catatan |

---

## 4. Desain Struktur Data (Schema)

### 4.1 Kolom `quests.parameters` (JSON)
Menyimpan konfigurasi parameter yang dipilih Admin:
```json
{
  "action_type": "RECRUIT_MEMBERS",
  "target_threshold": 5,
  "baseline_mode": "SINCE_STAKE", 
  "filter": {
    "verified_phone_only": false
  }
}
```

### 4.2 Metadata Progres pada `escrow_transactions`
Agar performa query tetap ringan tanpa harus memindai database berulang-ulang, pada `escrow_transactions` dicatat snapshot awal saat user melakukan staking:
```json
{
  "initial_baseline": 12,
  "current_progress": 3,
  "target_progress": 5,
  "last_evaluated_at": "2026-09-13T06:15:00Z"
}
```
*Catatan:* `initial_baseline: 12` mencatat bahwa saat user mengambil misi ini, user sudah punya 12 referral. Maka untuk target 5 referral baru, user harus mencapai total 17 referral sebelum `expires_at`.

---

## 5. Komponen Layanan: `QuestEvaluatorService`

Layanan inti ini mengisolasi seluruh logika kalkulasi dari Controller:

```php
namespace App\Services;

use App\Models\EscrowTransaction;
use App\Models\User;
use App\Models\ServiceOrder;
use Carbon\Carbon;
use Exception;

class QuestEvaluatorService
{
    /**
     * Evaluasi progres terkini pengguna untuk escrow misi tertentu.
     */
    public static function evaluateProgress(EscrowTransaction $escrow): array
    {
        $quest = $escrow->quest;
        $params = $quest->parameters ?? [];
        $actionType = $params['action_type'] ?? 'MANUAL_PROOF_SUBMISSION';
        $target = intval($params['target_threshold'] ?? 1);
        $userId = $escrow->user_id;
        $stakedAt = $escrow->created_at;

        $currentProgress = 0;

        switch ($actionType) {
            case 'RECRUIT_MEMBERS':
                // Hitung anggota baru yang mendaftar menggunakan referral user setelah staking dimulai
                $currentProgress = User::where('upline_id', $userId)
                    ->where('created_at', '>=', $stakedAt)
                    ->count();
                break;

            case 'REGISTER_MERCHANT_UNIMART':
                $currentProgress = ServiceOrder::where('user_id', $userId)
                    ->where('created_at', '>=', $stakedAt)
                    ->whereHas('ecosystemService', fn($q) => $q->where('slug', 'unimart-pos'))
                    ->whereIn('status', ['ACTIVE', 'COMPLETED'])
                    ->count();
                break;

            case 'DIGITAL_PPOB_TRANSACTION':
                // Query modul transaksi PPOB
                $currentProgress = ServiceOrder::where('user_id', $userId)
                    ->where('created_at', '>=', $stakedAt)
                    ->where('order_type', 'PPOB')
                    ->where('status', 'SUCCESS')
                    ->count();
                break;

            case 'DAILY_CHECKIN_STREAK':
                $currentProgress = \App\Models\UserCheckin::where('user_id', $userId)
                    ->where('created_at', '>=', $stakedAt)
                    ->count();
                break;

            case 'MANUAL_PROOF_SUBMISSION':
                // Menunggu approval admin
                $currentProgress = ($escrow->status === 'APPROVED_BY_ADMIN') ? $target : 0;
                break;
        }

        $isCompleted = ($currentProgress >= $target);

        return [
            'action_type' => $actionType,
            'current_progress' => $currentProgress,
            'target_threshold' => $target,
            'percentage' => min(100, intval(($currentProgress / max(1, $target)) * 100)),
            'is_completed' => $isCompleted,
            'is_expired' => Carbon::now()->gt($escrow->expires_at),
        ];
    }

    /**
     * Klaim / Verifikasi Otomatis Misi
     */
    public static function claimQuestReward(string $escrowId): array
    {
        $escrow = EscrowTransaction::with(['quest', 'user.wallet'])->findOrFail($escrowId);

        if ($escrow->status !== 'LOCKED') {
            throw new Exception("Misi ini sudah diselesaikan sebelumnya ({$escrow->status}).");
        }

        $eval = self::evaluateProgress($escrow);

        if ($eval['is_expired'] && !$eval['is_completed']) {
            // Waktu habis dan belum tercapai -> Resolusi GAGAL
            WalletService::resolveQuestFailed($escrow->id);
            throw new Exception("Batas waktu misi telah berakhir sebelum target terpenuhi. Taruhan Koin Perak dipotong.");
        }

        if (!$eval['is_completed']) {
            throw new Exception("Target misi belum terpenuhi. Capaian Anda saat ini: {$eval['current_progress']}/{$eval['target_threshold']}.");
        }

        // Target terpenuhi tepat waktu -> Resolusi SUKSES
        $result = WalletService::resolveQuestSuccess($escrow->id);

        return [
            'success' => true,
            'evaluation' => $eval,
            'wallet_result' => $result,
        ];
    }
}
```

---

## 6. Integrasi Antarmuka Admin (Admin UX Flow)

Pada modal / form `CreateQuestModal.jsx` & `EditQuestModal.jsx`:

1. **Bagian Konten Misi (Kreatif)**:
   - Judul Misi: *"Perluas Karavan: Ajak 5 Rekan Saudagar"*
   - Deskripsi Misi: *"Sebarkan tautan saudagar Anda dan ajak 5 rekan baru bergabung ke Ekspedisi Gunung Mas."*
   - Kategori & Persona Target: (Semua Saudagar / Pedagang / Siswa)
   - Taruhan Koin Perak: `100` Perak
   - Hadiah Koin Emas: `50` Emas
   - Batas Waktu: `48` Jam

2. **Bagian Parameter Sistem (Teknis Otomatis)**:
   - Dropdown **Target Aksi Sistem**:
     - `[ Pilihan: Rekrut Anggota Baru ]`
   - Input Field Dinamis **Jumlah Sasaran (Target)**:
     - `[ 5 ]` Orang
   - Indikator Visual:
     > *"Sistem akan otomatis menghitung referral baru yang mendaftar atas nama pengguna selama 48 jam sejak misi diambil."*

---

## 7. Rencana Tahapan Eksekusi (Implementation Roadmap)

1. **Tahap 1: Validasi Request & Schema Model**
   - Tambahkan validasi `parameters.action_type` dan `parameters.target_threshold` di `AdminDashboardController::storeQuest` dan `updateQuest`.
2. **Tahap 2: Implementasi `QuestEvaluatorService`**
   - Bangun evaluator logic yang mengecek riwayat real-time user sesuai kategori aksi.
3. **Tahap 3: Endpoint Klaim Misi & Progress Bar Pengguna**
   - Tambahkan route `POST /expedition/claim/{escrowId}` yang memungkinkan user mengecek progres dan mengklaim reward emas secara instan.
   - Di tampilan kartu misi pengguna, tampilkan progress bar real-time: `[=====>    ] 3 / 5 Anggota`.
4. **Tahap 4: Scheduled Cron Resolver**
   - Command Laravel `php artisan quests:resolve-expired` untuk otomatis mendiskualifikasi misi yang kedaluwarsa tanpa perlu campur tangan manual admin.
5. **Tahap 5: Form UI Admin**
   - Perbarui tampilan modal Create/Edit Quest di React/Inertia Admin Panel dengan dropdown preset parameter ramah pengguna.
