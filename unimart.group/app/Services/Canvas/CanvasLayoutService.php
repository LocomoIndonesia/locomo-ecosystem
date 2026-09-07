<?php

namespace App\Services\Canvas;

use Illuminate\Support\Facades\File;

/**
 * CanvasLayoutService
 *
 * Service yang membaca file JSON Layout Definition dari
 * resources/canvas-layouts/{role}/{screen}.json
 * dan mengembalikannya sebagai array PHP yang siap dikonsumsi
 * oleh canvas.js di frontend.
 */
class CanvasLayoutService
{
    /**
     * Base path untuk folder canvas-layouts
     */
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = resource_path('canvas-layouts');
    }

    /**
     * Ambil definisi layout untuk screen & role tertentu.
     * Jika layout role spesifik tidak ada, fallback ke shared.
     */
    public function getLayout(string $screen, string $role): array
    {
        // Coba ambil layout role-spesifik: owner/pos.json, staff/orders.json, dst
        $rolePath   = "{$this->basePath}/{$role}/{$screen}.json";
        // Fallback ke shared jika tidak ada
        $sharedPath = "{$this->basePath}/shared/{$screen}.json";

        if (File::exists($rolePath)) {
            return $this->parseLayout($rolePath, $screen, $role);
        }

        if (File::exists($sharedPath)) {
            return $this->parseLayout($sharedPath, $screen, $role);
        }

        // Tidak ditemukan — kembalikan layout kosong dengan pesan error
        return [
            'screen'  => $screen,
            'role'    => $role,
            'title'   => 'Halaman Tidak Ditemukan',
            'error'   => "Layout '{$screen}' untuk role '{$role}' belum tersedia.",
            'widgets' => [],
        ];
    }

    /**
     * Ambil daftar command untuk Help Panel,
     * difilter berdasarkan role user.
     */
    public function getHelp(string $role): array
    {
        $helpPath = "{$this->basePath}/shared/help.json";

        if (!File::exists($helpPath)) {
            return ['screen' => 'help', 'title' => 'Bantuan', 'groups' => []];
        }

        $help = json_decode(File::get($helpPath), true);

        // Filter setiap command berdasarkan role
        if (isset($help['groups'])) {
            foreach ($help['groups'] as &$group) {
                $group['commands'] = array_values(array_filter(
                    $group['commands'] ?? [],
                    fn($cmd) => in_array($role, $cmd['roles'] ?? [$role])
                ));
            }

            // Hapus group yang sudah tidak punya command setelah difilter
            $help['groups'] = array_values(array_filter(
                $help['groups'],
                fn($g) => !empty($g['commands'])
            ));
        }

        return $help;
    }

    /**
     * Ambil daftar semua screen yang tersedia untuk role tertentu.
     */
    public function getAvailableScreens(string $role): array
    {
        $roleDir   = "{$this->basePath}/{$role}";
        $sharedDir = "{$this->basePath}/shared";

        $screens = [];

        if (File::isDirectory($roleDir)) {
            foreach (File::files($roleDir) as $file) {
                $screens[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            }
        }

        if (File::isDirectory($sharedDir)) {
            foreach (File::files($sharedDir) as $file) {
                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                if (!in_array($name, $screens) && $name !== 'help') {
                    $screens[] = $name;
                }
            }
        }

        return $screens;
    }

    /**
     * Parse file JSON layout dan tambahkan metadata runtime.
     */
    protected function parseLayout(string $path, string $screen, string $role): array
    {
        $content = File::get($path);
        $layout  = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'screen'  => $screen,
                'role'    => $role,
                'error'   => 'Layout JSON tidak valid: ' . json_last_error_msg(),
                'widgets' => [],
            ];
        }

        // Tambahkan metadata runtime
        $layout['_meta'] = [
            'screen'      => $screen,
            'role'        => $role,
            'loaded_at'   => now()->toIso8601String(),
            'source_path' => str_replace(base_path() . '/', '', $path),
        ];

        return $layout;
    }
}
