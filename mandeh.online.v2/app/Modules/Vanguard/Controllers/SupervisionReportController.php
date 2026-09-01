<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vanguard\Models\TicRevitalizationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisionReportController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Seed initial supervision findings if database is empty
        if (TicRevitalizationLog::count() === 0) {
            TicRevitalizationLog::create([
                'title' => 'Kerusakan Plafon Toilet Lantai 1 Gedung TIC',
                'reporter_name' => 'Armada Darat (Randi)',
                'description' => 'Ditemukan plafon toilet bocor dan lapuk di lantai 1 Gedung TIC Mandeh. Perlu perbaikan segera sebelum Muscab dimulai.',
                'status' => 'pending',
            ]);
            TicRevitalizationLog::create([
                'title' => 'Tumpukan Sampah Plastik di Area Pantai Carocok Anau',
                'reporter_name' => 'Armada Darat (Budi)',
                'description' => 'Tumpukan sampah plastik sisa wisatawan menumpuk di sisi timur pantai, mengganggu kenyamanan dan kelestarian ekosistem.',
                'status' => 'in_progress',
            ]);
            TicRevitalizationLog::create([
                'title' => 'Pemasangan Access Point WiFi Hub di Pos Tarusan',
                'reporter_name' => 'Armada Udara (Hengki)',
                'description' => 'Selesai memasang router internet siber untuk mendukung pelaporan online pelaku wisata.',
                'status' => 'completed',
            ]);
        }

        $logs = TicRevitalizationLog::orderBy('created_at', 'desc')->get();

        return view('vanguard::supervision', ['logs' => $logs]);
    }

    public function storeLog(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'reporter_name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        TicRevitalizationLog::create([
            'title' => $request->title,
            'reporter_name' => $request->reporter_name,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Laporan pengawasan berhasil dikirim ke Admin Manager.');
    }
}
