<?php

namespace App\Services;

class ModuleManager
{
    /**
     * Daftar seluruh modul industri yang didukung platform Unimart.
     */
    protected array $supportedModules = [
        'caffe' => [
            'name' => 'Caffe & F&B',
            'code' => 'caffe',
            'icon' => '☕',
            'description' => 'Modul khusus kafe, resto, dan kuliner dengan meja, KDS dapur, varian menu, dan QR self-order.',
            'features' => ['tables', 'kds', 'qr_menu', 'pos_dine_in', 'chef_pin'],
            'default_layout' => 'caffe::layouts.master',
        ],
        'grosiran' => [
            'name' => 'Grosir & Distributor',
            'code' => 'grosiran',
            'icon' => '📦',
            'description' => 'Modul grosiran & kulakan dengan tier harga bertingkat, satuan karton/dus, dan surat jalan pengiriman.',
            'features' => ['price_tiers', 'wholesale_pos', 'delivery_fleets', 'bulk_discounts'],
            'default_layout' => 'grosir::layouts.master',
        ],
        'minimarket' => [
            'name' => 'Minimarket & Retail',
            'code' => 'minimarket',
            'icon' => '🛒',
            'description' => 'Modul retail modern dengan barcode scanner cepat, kasir shift cepat, dan peringatan stok minimum.',
            'features' => ['barcode_scanner', 'fast_pos', 'min_stock_alert'],
            'default_layout' => 'minimarket::layouts.master',
        ],
        'apotek' => [
            'name' => 'Apotek & Farmasi',
            'code' => 'apotek',
            'icon' => '💊',
            'description' => 'Modul apotek dengan resep dokter, nomor batch obat, dan pelacakan tanggal kadaluarsa.',
            'features' => ['prescriptions', 'batch_expiry', 'bpom_code'],
            'default_layout' => 'apotek::layouts.master',
        ],
        'butik' => [
            'name' => 'Butik & Fashion',
            'code' => 'butik',
            'icon' => '👗',
            'description' => 'Modul fashion dengan matriks ukuran (S/M/L/XL), warna, dan bungkus kado.',
            'features' => ['sizes', 'colors', 'gift_wrap'],
            'default_layout' => 'butik::layouts.master',
        ],
    ];

    /**
     * Dapatkan daftar seluruh modul.
     */
    public function getAllModules(): array
    {
        return $this->supportedModules;
    }

    /**
     * Cek apakah sebuah modul valid.
     */
    public function isValidModule(string $code): bool
    {
        return isset($this->supportedModules[$code]);
    }

    /**
     * Dapatkan metadata modul tertentu.
     */
    public function getModule(string $code): ?array
    {
        return $this->supportedModules[$code] ?? null;
    }

    /**
     * Dapatkan view yang sesuai dengan modul bisnis tenant.
     */
    public function resolveView(string $businessType, string $viewName, string $fallbackView): string
    {
        $moduleView = strtolower($businessType) . '::' . $viewName;
        
        if (view()->exists($moduleView)) {
            return $moduleView;
        }

        return $fallbackView;
    }
}
