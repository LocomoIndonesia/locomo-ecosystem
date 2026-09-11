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
                'description' => 'Aplikasi kasir cloud cerdas, manajemen stok, QRIS dinamis, dan laporan laba rugi instan untuk UMKM.',
                'price_gold' => 80,
                'price_fiat' => 100000,
                'service_url' => 'https://unimart.id',
                'webhook_url' => 'https://unimart.id/api/webhook/license',
                'status' => 'ACTIVE',
                'is_active' => true,
                'wizard_schema' => [
                    'steps' => [
                        [
                            'id' => 'shop_profile',
                            'title' => 'Profil & Identitas Toko',
                            'description' => 'Masukkan nama dan jenis unit usaha yang akan didaftarkan',
                            'fields' => [
                                [
                                    'name' => 'shop_name',
                                    'label' => 'Nama Toko / Usaha',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'Contoh: Kios Berkah Jaya',
                                ],
                                [
                                    'name' => 'business_category',
                                    'label' => 'Kategori Usaha',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => [
                                        'Retail & Toko Kelontong',
                                        'Warung Kopi & FnB',
                                        'Jasa & Servis Handphone',
                                        'Fashion & Pakaian',
                                        'Pertanian & Peternakan',
                                    ],
                                ],
                                [
                                    'name' => 'shop_description',
                                    'label' => 'Keterangan Singkat',
                                    'type' => 'textarea',
                                    'required' => false,
                                    'placeholder' => 'Menjual aneka sembako, makanan ringan, dan minuman dingin...',
                                ],
                            ],
                        ],
                        [
                            'id' => 'operational_payout',
                            'title' => 'Operasional & Rekening Kasir',
                            'description' => 'Alamat lokasi toko dan rekening tujuan pencairan pendapatan QRIS',
                            'fields' => [
                                [
                                    'name' => 'city',
                                    'label' => 'Kota / Kabupaten',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'Contoh: Kota Palangka Raya',
                                ],
                                [
                                    'name' => 'shop_address',
                                    'label' => 'Alamat Lengkap Toko',
                                    'type' => 'textarea',
                                    'required' => true,
                                    'placeholder' => 'Jl. Diponegoro No. 45, RT 02 / RW 05...',
                                ],
                                [
                                    'name' => 'bank_name',
                                    'label' => 'Bank / E-Wallet Pencairan',
                                    'type' => 'select',
                                    'required' => true,
                                    'options' => [
                                        'Bank Central Asia (BCA)',
                                        'Bank Mandiri',
                                        'Bank Rakyat Indonesia (BRI)',
                                        'Bank Negara Indonesia (BNI)',
                                        'DANA E-Wallet',
                                        'GoPay Mitra',
                                    ],
                                ],
                                [
                                    'name' => 'account_number',
                                    'label' => 'Nomor Rekening / HP E-Wallet',
                                    'type' => 'text',
                                    'required' => true,
                                    'placeholder' => 'Contoh: 8820192841',
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
    }
}
