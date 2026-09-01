<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class DestinationController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 1. Authorize: Only Pengurus HPI (role = 'layanan_publik') can create destinations
        if ($user->role !== 'layanan_publik' && $user->role !== 'admin' && $user->role !== 'admin_manager') {
            abort(403, 'Akses ditolak. Hanya Pengurus DPC HPI yang diperbolehkan untuk menambahkan destinasi wisata.');
        }

        if (!$user->is_verified) {
            return redirect()->back()->with('error', 'Akun Anda harus terverifikasi oleh Admin.');
        }

        // 2. Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price_range' => 'nullable|string|max:100',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        // 3. Generate QR Code & Token
        $qrToken = Str::random(40);
        $qrCodeFilename = $qrToken . '.png';
        $qrCodeDir = storage_path('app/public/qrcodes');

        if (!file_exists($qrCodeDir)) {
            mkdir($qrCodeDir, 0755, true);
        }

        if (function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor(150, 150);
            $background_color = imagecolorallocate($im, 255, 255, 255);
            imagefill($im, 0, 0, $background_color);
            $text_color = imagecolorallocate($im, 0, 0, 0);
            imagestring($im, 3, 10, 60, "Mandeh QR", $text_color);
            imagestring($im, 2, 10, 80, substr($qrToken, 0, 16), $text_color);
            imagepng($im, $qrCodeDir . '/' . $qrCodeFilename);
            imagedestroy($im);
        } else {
            file_put_contents($qrCodeDir . '/' . $qrCodeFilename, 'QR_TOKEN:' . $qrToken);
        }

        // 4. Save Destination Entity
        Entity::create([
            'owner_id' => $user->id,
            'upline_id' => null,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'type' => 'destinasi',
            'description' => $request->description,
            'price_range' => $request->price_range ?? 'Free',
            'lat' => $request->lat,
            'lng' => $request->lng,
            'qr_token' => $qrToken,
            'qr_code_path' => 'qrcodes/' . $qrCodeFilename,
        ]);

        return redirect()->back()->with('success', 'Destinasi pariwisata baru berhasil ditambahkan oleh Pengurus HPI!');
    }
}
