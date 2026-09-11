<?php

return [
    'secret' => env('LOCOMO_ECOSYSTEM_SECRET', 'locomo_ecosystem_secret_key_2026'),

    'services' => [
        'unimart' => [
            'name' => 'Unimart POS Juragan Pro',
            'code' => 'unimart',
            'base_domain' => env('UNIMART_BASE_DOMAIN', env('APP_ENV') === 'production' ? 'unimart.group' : 'unimart.group.localhost'),
            'api_url' => env('UNIMART_INTERNAL_API_URL', env('APP_ENV') === 'production' ? 'https://unimart.group/api/v1/external/register-store' : 'http://nginx/api/v1/external/register-store'),
            'web_url' => env('UNIMART_WEB_URL', env('APP_ENV') === 'production' ? 'https://unimart.group' : 'http://unimart.group.localhost'),
            'packages' => [
                'basic' => [
                    'id' => 'basic',
                    'name' => 'Basic Starter',
                    'price_fiat' => 50000,
                    'price_gold' => 40,
                    'features' => ['Profil digital & QR Menu online otonom', 'Integrasi QRIS resmi', 'Laporan penjualan harian'],
                ],
                'bisnis' => [
                    'id' => 'bisnis',
                    'name' => 'Bisnis Pro',
                    'price_fiat' => 100000,
                    'price_gold' => 80,
                    'recommended' => true,
                    'features' => ['Semua fitur Basic', 'Self-Order QR Meja + KDS Dapur', 'Notifikasi WhatsApp instan', 'Manajemen Utang Piutang & Stok'],
                ],
                'enterprise' => [
                    'id' => 'enterprise',
                    'name' => 'Enterprise VIP',
                    'price_fiat' => 200000,
                    'price_gold' => 160,
                    'features' => ['Semua fitur Bisnis', 'Full POS Kasir Multi-Karyawan', 'AI Gemini Chatbot WhatsApp 24 Jam', 'Integrasi WiFi iZone'],
                ],
            ],
        ],

        'walikelas' => [
            'name' => 'WaliKelas.id LMS & Bimbel',
            'code' => 'walikelas',
            'web_url' => env('WALIKELAS_WEB_URL', env('APP_ENV') === 'production' ? 'https://walikelas.id' : 'http://walikelas.id.localhost'),
        ],

        'tapak-pendekar' => [
            'name' => 'Tapak Pendekar Nusantara',
            'code' => 'tapak-pendekar',
            'web_url' => env('TAPAK_PENDEKAR_WEB_URL', env('APP_ENV') === 'production' ? 'https://tapak.pandeka.id' : 'http://tapak.pandeka.localhost'),
        ],
    ],
];
