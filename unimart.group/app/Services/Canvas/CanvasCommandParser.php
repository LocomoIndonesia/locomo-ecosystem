<?php

namespace App\Services\Canvas;

/**
 * CanvasCommandParser
 *
 * Service rule-based yang menerima string command dari user
 * dan mengembalikan instruksi JSON action.
 *
 * Dirancang agar mudah di-extend ke NLP/AI di masa depan
 * tanpa mengubah interface-nya.
 *
 * Action Types:
 *   - load_screen  : Muat screen tertentu di canvas shell
 *   - open_sheet   : Buka bottom sheet dengan form/konten tertentu
 *   - open_help    : Buka help panel
 *   - redirect     : Redirect ke URL eksternal (KDS dll)
 *   - error        : Command tidak dikenali
 */
class CanvasCommandParser
{
    /**
     * Map command → action.
     * Key bisa berupa string exact atau regex pattern (prefix ~).
     * Urutan array = prioritas (pertama cocok = dipakai).
     */
    protected array $commandMap = [

        // ── Navigasi Screen ──────────────────────────────────────────
        'home'              => ['action' => 'load_screen', 'screen' => 'home'],
        'beranda'           => ['action' => 'load_screen', 'screen' => 'home'],
        'dashboard'         => ['action' => 'load_screen', 'screen' => 'dashboard'],

        'pos'               => ['action' => 'load_screen', 'screen' => 'pos'],
        'kasir'             => ['action' => 'load_screen', 'screen' => 'pos'],
        'buka kasir'        => ['action' => 'load_screen', 'screen' => 'pos'],

        'orders'            => ['action' => 'load_screen', 'screen' => 'orders'],
        'pesanan'           => ['action' => 'load_screen', 'screen' => 'orders'],
        'lihat pesanan'     => ['action' => 'load_screen', 'screen' => 'orders'],
        'order masuk'       => ['action' => 'load_screen', 'screen' => 'orders'],

        'menu'              => ['action' => 'load_screen', 'screen' => 'menu'],
        'kelola menu'       => ['action' => 'load_screen', 'screen' => 'menu'],
        'tambah menu'       => ['action' => 'load_screen', 'screen' => 'menu', 'auto_open_sheet' => 'menu_add'],
        'produk'            => ['action' => 'load_screen', 'screen' => 'menu'],

        'finance'           => ['action' => 'load_screen', 'screen' => 'finance'],
        'laporan'           => ['action' => 'load_screen', 'screen' => 'finance'],
        'omset'             => ['action' => 'load_screen', 'screen' => 'finance'],
        'cek omset'         => ['action' => 'load_screen', 'screen' => 'finance'],
        'laba rugi'         => ['action' => 'load_screen', 'screen' => 'finance'],

        'team'              => ['action' => 'load_screen', 'screen' => 'team'],
        'tim'               => ['action' => 'load_screen', 'screen' => 'team'],
        'staf'              => ['action' => 'load_screen', 'screen' => 'team'],
        'kelola tim'        => ['action' => 'load_screen', 'screen' => 'team'],
        'karyawan'          => ['action' => 'load_screen', 'screen' => 'team'],

        'inventory'         => ['action' => 'load_screen', 'screen' => 'inventory'],
        'stok'              => ['action' => 'load_screen', 'screen' => 'inventory'],
        'cek stok'          => ['action' => 'load_screen', 'screen' => 'inventory'],
        'gudang'            => ['action' => 'load_screen', 'screen' => 'inventory'],
        'stok habis'        => ['action' => 'load_screen', 'screen' => 'inventory', 'params' => ['filter' => 'low_stock']],

        'suppliers'         => ['action' => 'load_screen', 'screen' => 'suppliers'],
        'supplier'          => ['action' => 'load_screen', 'screen' => 'suppliers'],
        'data supplier'     => ['action' => 'load_screen', 'screen' => 'suppliers'],

        'purchases'         => ['action' => 'load_screen', 'screen' => 'purchases'],
        'kulakan'           => ['action' => 'load_screen', 'screen' => 'purchases'],
        'beli stok'         => ['action' => 'load_screen', 'screen' => 'purchases'],
        'pengadaan'         => ['action' => 'load_screen', 'screen' => 'purchases'],

        'payables'          => ['action' => 'load_screen', 'screen' => 'payables'],
        'utang'             => ['action' => 'load_screen', 'screen' => 'payables'],
        'buku utang'        => ['action' => 'load_screen', 'screen' => 'payables'],

        'receivables'       => ['action' => 'load_screen', 'screen' => 'receivables'],
        'piutang'           => ['action' => 'load_screen', 'screen' => 'receivables'],
        'buku piutang'      => ['action' => 'load_screen', 'screen' => 'receivables'],
        'kasbon'            => ['action' => 'load_screen', 'screen' => 'receivables'],

        'tables'            => ['action' => 'load_screen', 'screen' => 'tables'],
        'meja'              => ['action' => 'load_screen', 'screen' => 'tables'],
        'atur meja'         => ['action' => 'load_screen', 'screen' => 'tables'],
        'qr meja'           => ['action' => 'load_screen', 'screen' => 'tables'],

        'settings'          => ['action' => 'load_screen', 'screen' => 'settings'],
        'pengaturan'        => ['action' => 'load_screen', 'screen' => 'settings'],
        'setting toko'      => ['action' => 'load_screen', 'screen' => 'settings'],

        'notifications'     => ['action' => 'load_screen', 'screen' => 'notifications'],
        'notifikasi'        => ['action' => 'load_screen', 'screen' => 'notifications'],
        'notif'             => ['action' => 'load_screen', 'screen' => 'notifications'],

        // KDS → redirect ke halaman terpisah
        'kds'               => ['action' => 'redirect', 'url' => '/canvas/kds'],
        'dapur'             => ['action' => 'redirect', 'url' => '/canvas/kds'],
        'layar dapur'       => ['action' => 'redirect', 'url' => '/canvas/kds'],

        // ── Sheet Actions ────────────────────────────────────────────
        'shift open'        => ['action' => 'open_sheet', 'sheet' => 'shift_open'],
        'buka shift'        => ['action' => 'open_sheet', 'sheet' => 'shift_open'],

        'shift close'       => ['action' => 'open_sheet', 'sheet' => 'shift_close'],
        'tutup shift'       => ['action' => 'open_sheet', 'sheet' => 'shift_close'],

        'expense'           => ['action' => 'open_sheet', 'sheet' => 'expense_form'],
        'pengeluaran'       => ['action' => 'open_sheet', 'sheet' => 'expense_form'],
        'catat pengeluaran' => ['action' => 'open_sheet', 'sheet' => 'expense_form'],
        'kas kecil'         => ['action' => 'open_sheet', 'sheet' => 'expense_form'],

        // ── Help Panel ────────────────────────────────────────────────
        'help'              => ['action' => 'open_help'],
        '/help'             => ['action' => 'open_help'],
        'bantuan'           => ['action' => 'open_help'],
        'perintah'          => ['action' => 'open_help'],
        'daftar perintah'   => ['action' => 'open_help'],
    ];

    /**
     * Parse command string dan kembalikan instruksi action.
     */
    public function parse(string $input, string $role = 'owner'): array
    {
        $normalized = $this->normalize($input);

        // ── Cek param tambahan sebelum lookup ──
        // "laporan hari ini" → finance + period=today
        if (str_contains($normalized, 'hari ini')) {
            $base = trim(str_replace('hari ini', '', $normalized));
            $financeKeys = ['laporan', 'omset', 'finance', 'cek omset', 'laba rugi'];
            if (in_array($base, $financeKeys) || empty($base)) {
                return $this->result('load_screen', [
                    'screen' => 'finance',
                    'params' => ['period' => 'today'],
                ]);
            }
        }

        if (str_contains($normalized, 'minggu ini')) {
            $base = trim(str_replace('minggu ini', '', $normalized));
            $financeKeys = ['laporan', 'omset', 'finance', 'cek omset', 'laba rugi'];
            if (in_array($base, $financeKeys) || empty($base)) {
                return $this->result('load_screen', [
                    'screen' => 'finance',
                    'params' => ['period' => 'this_week'],
                ]);
            }
        }

        if (str_contains($normalized, 'bulan ini')) {
            $base = trim(str_replace('bulan ini', '', $normalized));
            $financeKeys = ['laporan', 'omset', 'finance', 'cek omset', 'laba rugi'];
            if (in_array($base, $financeKeys) || empty($base)) {
                return $this->result('load_screen', [
                    'screen' => 'finance',
                    'params' => ['period' => 'this_month'],
                ]);
            }
        }

        // ── Lookup command map ──
        if (array_key_exists($normalized, $this->commandMap)) {
            $def = $this->commandMap[$normalized];
            return $this->result($def['action'], $def);
        }

        // ── Partial match (command yang mengandung keyword) ──
        foreach ($this->commandMap as $keyword => $def) {
            if (str_contains($normalized, $keyword)) {
                return $this->result($def['action'], $def);
            }
        }

        // ── Command tidak dikenali ──
        return $this->result('error', [
            'message' => "Perintah \"{$input}\" tidak dikenali. Ketik /help untuk melihat daftar perintah.",
            'suggestion' => $this->suggest($normalized),
        ]);
    }

    /**
     * Normalize input: lowercase, trim, hapus karakter khusus berlebih.
     */
    protected function normalize(string $input): string
    {
        $input = mb_strtolower(trim($input));
        $input = preg_replace('/\s+/', ' ', $input);         // multiple spaces → 1 space
        $input = preg_replace('/[^\w\s\/]/', '', $input);    // hapus karakter non-word kecuali /
        return $input;
    }

    /**
     * Buat suggestion berdasarkan kedekatan string (levenshtein).
     */
    protected function suggest(string $input): ?string
    {
        $best      = null;
        $bestScore = PHP_INT_MAX;

        foreach (array_keys($this->commandMap) as $keyword) {
            $distance = levenshtein($input, $keyword);
            if ($distance < $bestScore && $distance <= 4) {
                $bestScore = $distance;
                $best = $keyword;
            }
        }

        return $best;
    }

    /**
     * Build response array standar.
     */
    protected function result(string $action, array $data = []): array
    {
        return array_merge([
            'ok'     => $action !== 'error',
            'action' => $action,
        ], $data);
    }
}
