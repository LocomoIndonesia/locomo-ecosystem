<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Tampilkan halaman layout meja dan generator QR Code.
     */
    public function index()
    {
        $tenant = app('tenant');
        $package = $tenant->settings['package'] ?? 'Bisnis';
        
        // Ambil jumlah meja dari settings tenant (default: 15)
        $tablesCount = $tenant->settings['tables_count'] ?? 15;

        // Ambil data meja aktif (misal meja 1 s/d N)
        $tables = [];
        for ($i = 1; $i <= $tablesCount; $i++) {
            $tables[] = [
                'number' => $i,
                'qr_url' => url('/caffe/' . $tenant->domain . '?table=' . $i)
            ];
        }

        return view('owner.tables', compact('tenant', 'package', 'tables'));
    }

    /**
     * Perbarui jumlah meja kafe.
     */
    public function updateTablesCount(Request $request)
    {
        $tenant = app('tenant');

        $request->validate([
            'tables_count' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $settings = $tenant->settings;
        $settings['tables_count'] = intval($request->tables_count);
        
        $tenant->update([
            'settings' => $settings
        ]);

        return redirect()->route('owner.tables')->with('success', 'Jumlah meja berhasil diperbarui!');
    }
}
