<?php

namespace Database\Seeders;

use App\Models\EcosystemService;
use Illuminate\Database\Seeder;

class EcosystemServiceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Unimart POS Juragan Pro
        EcosystemService::updateOrCreate(
            ['code' => 'unimart'],
            [
                'name' => 'Unimart POS Juragan Pro',
                'icon' => 'storefront',
                'category' => 'BISNIS',
                'description' => 'Aplikasi kasir cloud cerdas, self-order QR meja, manajemen stok, dan toko online otonom (/s/{nama_toko}).',
                'price_gold' => 80,
                'price_fiat' => 100000,
                'service_url' => config('ecosystem.services.unimart.web_url', 'http://unimart.group.localhost'),
                'webhook_url' => config('ecosystem.services.unimart.api_url', 'http://nginx/api/v1/external/register-store'),
                'status' => 'ACTIVE',
                'is_active' => true,
                'wizard_schema' => [
                    'package_tiers' => [
                        [
                            'code' => 'basic',
                            'name' => 'Basic Starter',
                            'price_gold' => 40,
                            'price_fiat' => 50000,
                            'desc' => 'Profil digital & QR Menu online otonom, integrasi QRIS resmi, laporan harian',
                        ],
                        [
                            'code' => 'bisnis',
                            'name' => 'Bisnis Pro',
                            'price_gold' => 80,
                            'price_fiat' => 100000,
                            'recommended' => true,
                            'desc' => 'Self-Order QR Meja + KDS Dapur, Notifikasi WhatsApp, Buku Utang Piutang & Kasir Stok',
                        ],
                        [
                            'code' => 'enterprise',
                            'name' => 'Enterprise VIP',
                            'price_gold' => 160,
                            'price_fiat' => 200000,
                            'desc' => 'Full POS Kasir Multi-Karyawan, AI Gemini Chatbot 24 Jam, Integrasi WiFi iZone',
                        ],
                    ],
                    'steps' => [
                        [
                            'id' => 'package_selection',
                            'title' => 'Pilih Paket Layanan',
                            'description' => 'Tentukan skala paket Unimart yang sesuai dengan kebutuhan operasional toko',
                            'fields' => [
                                [
                                    'name' => 'package_tier',
                                    'label' => 'Paket Langganan Unimart',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => [
                                        'Bisnis Pro (80 Emas / Rp 100.000) - Rekomendasi',
                                        'Basic Starter (40 Emas / Rp 50.000)',
                                        'Enterprise VIP (160 Emas / Rp 200.000)',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => 'shop_profile',
                            'title' => 'Identitas & Alamat Toko Online',
                            'description' => 'Tentukan nama toko, format URL otonom (/s/{nama_toko}), dan kategori industri',
                            'fields' => [
                                [
                                    'name' => 'shop_name',
                                    'label' => 'Nama Toko / Resto / Bisnis',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'Contoh: Kopi Kita Nusantara',
                                ],
                                [
                                    'name' => 'domain',
                                    'label' => 'Slug / URL Toko (Otonom)',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'kopikita (Akan menjadi: /s/kopikita)',
                                ],
                                [
                                    'name' => 'business_category',
                                    'label' => 'Kategori Industri',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => [
                                        'Kedai Kopi / Coffee Shop',
                                        'Kafe F&B / Restaurant',
                                        'Toko Grosir / Kelontong',
                                        'Toko Roti / Bakery',
                                        'Warung Makan / Kedai',
                                        'Lainnya',
                                    ],
                                ],
                                [
                                    'name' => 'shop_address',
                                    'label' => 'Alamat Operasional Bisnis',
                                    'type' => 'textarea',
                                    'required' => true,
                                    'placeholder' => 'Jl. Khatib Sulaiman No. 12, Kota Padang...',
                                ],
                                [
                                    'name' => 'shop_description',
                                    'label' => 'Keterangan Singkat Toko',
                                    'type' => 'textarea',
                                    'required' => false,
                                    'placeholder' => 'Menyediakan aneka minuman kopi nusantara dan camilan hangat...',
                                ],
                                [
                                    'name' => 'owner_email',
                                    'label' => 'Email Pemilik (Opsional)',
                                    'type' => 'text',
                                    'required' => false,
                                    'placeholder' => 'owner@kopikita.com (Bila kosong, dibuat otomatis)',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // 2. WaliKelas.id LMS & Tutor
        EcosystemService::updateOrCreate(
            ['code' => 'walikelas'],
            [
                'name' => 'WaliKelas.id LMS & Bimbel',
                'icon' => 'school',
                'category' => 'EDUKASI',
                'description' => 'Platform edukasi digital, booking pengajar privat, bank soal tryout, dan rapor perkembangan belajar siswa.',
                'price_gold' => 60,
                'price_fiat' => 75000,
                'service_url' => 'https://walikelas.id',
                'webhook_url' => 'https://walikelas.id/api/webhook/license',
                'status' => 'ACTIVE',
                'is_active' => true,
                'wizard_schema' => [
                    'steps' => [
                        [
                            'id' => 'student_profile',
                            'title' => 'Profil Peserta Didik',
                            'description' => 'Data siswa yang akan menggunakan akses pembelajaran digital',
                            'fields' => [
                                [
                                    'name' => 'student_name',
                                    'label' => 'Nama Lengkap Siswa',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'Contoh: Ahmad Zaki Pratama',
                                ],
                                [
                                    'name' => 'education_level',
                                    'label' => 'Jenjang Pendidikan',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => [
                                        'SD / MI Sederajat',
                                        'SMP / MTs Sederajat',
                                        'SMA / SMK / MA Sederajat',
                                        'Persiapan UTBK / Kuliah',
                                        'Umum / Pelatihan Kerja',
                                    ],
                                ],
                                [
                                    'name' => 'school_name',
                                    'label' => 'Asal Sekolah / Lembaga',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'Contoh: SMP Negeri 1 Palangka Raya',
                                ],
                            ],
                        ],
                        [
                            'id' => 'learning_program',
                            'title' => 'Program & Minat Belajar',
                            'description' => 'Fokus materi dan pendampingan les yang diinginkan',
                            'fields' => [
                                [
                                    'name' => 'program_type',
                                    'label' => 'Paket Bimbingan',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => [
                                        'Paket Reguler (Akses Bank Soal & Video)',
                                        'Paket Intensif (Tutor Online 4x Seminggu)',
                                        'Paket Privat 1-on-1 Eksklusif',
                                    ],
                                ],
                                [
                                    'name' => 'focus_subject',
                                    'label' => 'Mata Pelajaran Utama',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'Contoh: Matematika & Bahasa Inggris',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // 3. Tapak Pendekar
        EcosystemService::updateOrCreate(
            ['code' => 'tapak-pendekar'],
            [
                'name' => 'Tapak Pendekar Nusantara',
                'icon' => 'sports_martial_arts',
                'category' => 'KOMUNITAS',
                'description' => 'Perkumpulan beladiri tradisional dan kebugaran fisik, kurikulum jurus warisan leluhur, serta lencana kejuaraan.',
                'price_gold' => 40,
                'price_fiat' => 50000,
                'service_url' => 'https://tapakpendekar.id',
                'webhook_url' => 'https://tapakpendekar.id/api/webhook/license',
                'status' => 'ACTIVE',
                'is_active' => true,
                'wizard_schema' => [
                    'steps' => [
                        [
                            'id' => 'martial_profile',
                            'title' => 'Data Calon Pendekar',
                            'description' => 'Informasi pendaftaran murid padepokan beladiri',
                            'fields' => [
                                [
                                    'name' => 'full_name',
                                    'label' => 'Nama Pendekar',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'Contoh: Bayu Samudra',
                                ],
                                [
                                    'name' => 'chapter_region',
                                    'label' => 'Cabang Padepokan / Daerah',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'Contoh: Padepokan Gunung Mas',
                                ],
                                [
                                    'name' => 'belt_rank',
                                    'label' => 'Tingkatan Sabuk / Pengalaman',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => [
                                        'Pemula (Sabuk Putih)',
                                        'Dasar (Sabuk Kuning)',
                                        'Madya (Sabuk Hijau)',
                                        'Lanjutan (Sabuk Biru/Coklat)',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // 4. Internal Product: Paket Umroh Barokah & Maqbul 2026
        EcosystemService::updateOrCreate(
            ['code' => 'umroh-barokah'],
            [
                'name' => 'Paket Umroh Barokah & Maqbul 2026',
                'icon' => 'card_travel',
                'category' => 'UMROH',
                'service_type' => 'INTERNAL',
                'description' => 'Program ibadah Umroh 12 hari plus fasilitas hotel bintang 5 Mekkah & Madinah, bimbingan mutawwif bersertifikat, visa resmi Kemenag, dan bonus perlengkapan ibadah eksekutif. Mendukung pembayaran bertahap (cicilan) dan bagi hasil profit sharing multi-level real-time.',
                'price_gold' => 20000,
                'price_fiat' => 25000000,
                'allows_installments' => true,
                'min_down_payment_gold' => 4000,
                'min_down_payment_fiat' => 5000000,
                'commission_rules' => [
                    'depth' => 3,
                    'levels' => [
                        ['level' => 1, 'percentage' => 10], // L1 sponsor langsung: 10% (2.000 Emas / Rp 2.500.000)
                        ['level' => 2, 'percentage' => 5],  // L2 upline: 5% (1.000 Emas / Rp 1.250.000)
                        ['level' => 3, 'percentage' => 2.5], // L3 grand upline: 2.5% (500 Emas / Rp 625.000)
                    ],
                ],
                'status' => 'ACTIVE',
                'is_active' => true,
                'wizard_schema' => [
                    'steps' => [
                        [
                            'id' => 'pilgrim_profile',
                            'title' => 'Data Calon Jamaah Umroh',
                            'description' => 'Lengkapi identitas jamaah sesuai dokumen paspor resmi',
                            'fields' => [
                                [
                                    'name' => 'passport_name',
                                    'label' => 'Nama Sesuai Paspor',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'Contoh: MUHAMMAD RIDWAN BIN ABDULLAH',
                                ],
                                [
                                    'name' => 'passport_number',
                                    'label' => 'Nomor Paspor RI (Opsional jika belum jadi)',
                                    'type' => 'text',
                                    'required' => false,
                                    'placeholder' => 'Contoh: X1234567',
                                ],
                                [
                                    'name' => 'gender',
                                    'label' => 'Jenis Kelamin Jamaah',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => ['Laki-Laki (Ikhwan)', 'Perempuan (Akhwat)'],
                                ],
                                [
                                    'name' => 'uniform_size',
                                    'label' => 'Ukuran Seragam / Batik / Gamis',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
                                ],
                            ],
                        ],
                        [
                            'id' => 'room_selection',
                            'title' => 'Pilihan Kamar & Jadwal Keberangkatan',
                            'description' => 'Tentukan tipe kamar hotel di Mekkah/Madinah dan periode keberangkatan',
                            'fields' => [
                                [
                                    'name' => 'room_type',
                                    'label' => 'Tipe Kamar Hotel Bintang 5',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => [
                                        'Quad (Sekamar Berempat)',
                                        'Triple (Sekamar Bertiga)',
                                        'Double (Sekamar Berdua)',
                                    ],
                                ],
                                [
                                    'name' => 'departure_season',
                                    'label' => 'Periode Jadwal Keberangkatan',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => [
                                        'Syawal 1447 H / April 2026',
                                        'Muharram 1448 H / Juli 2026',
                                        'Rabiul Awal 1448 H / September 2026',
                                        'Rajab 1448 H / Desember 2026 (Akhir Tahun)',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
