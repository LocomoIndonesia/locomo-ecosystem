<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard utama Owner.
     */
    public function index()
    {
        $tenant = app('tenant');
        $package = $tenant->settings['package'] ?? 'Bisnis';

        // Data statistik simulasi untuk mempercantik UI dashboard
        $stats = [
            'today_sales' => 'Rp 3.840.000',
            'orders_count' => 42,
            'active_tables' => '12 / 15',
            'team_count' => User::where('tenant_id', $tenant->id)->where('role', '!=', 'owner')->count(),
        ];

        return view('owner.dashboard', compact('tenant', 'package', 'stats'));
    }

    /**
     * Tampilkan daftar staf/karyawan.
     */
    public function team()
    {
        $tenant = app('tenant');
        $package = $tenant->settings['package'] ?? 'Bisnis';
        
        // Ambil semua staff (selain owner) yang terikat ke tenant ini
        $staff = User::where('tenant_id', $tenant->id)
            ->where('role', '!=', 'owner')
            ->get();

        return view('owner.team', compact('tenant', 'package', 'staff'));
    }

    /**
     * Tambah karyawan baru.
     */
    public function addStaff(Request $request)
    {
        $tenant = app('tenant');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in(['cashier', 'chef', 'waiter', 'admin_gudang', 'kurir'])],
            'pin' => ['required', 'string', 'digits:4'],
        ]);

        // Cek apakah PIN sudah digunakan oleh staf lain di tenant yang sama
        $pinExists = User::where('tenant_id', $tenant->id)
            ->where('pin', $request->pin)
            ->exists();

        if ($pinExists) {
            return back()->withErrors(['pin' => 'PIN ini sudah digunakan oleh karyawan lain di toko Anda.'])->withInput();
        }

        // Buat email acak untuk staf karena kolom email bersifat unik di database
        $alias = strtolower(str_replace(' ', '', $request->name));
        $email = $alias . rand(100, 999) . '@unimart.local';

        // Buat user staf
        User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $email,
            'password' => Hash::make($request->pin), // Gunakan PIN sebagai password default
            'role' => $request->role,
            'pin' => $request->pin,
        ]);

        return redirect()->route('owner.team')->with('success', 'Karyawan berhasil ditambahkan!');
    }

    /**
     * Memperbarui pengaturan personalisasi toko / landing page.
     */
    public function updateSettings(Request $request)
    {
        $tenant = app('tenant');
        $settings = $tenant->settings ?? [];

        $request->validate([
            'landing_title' => ['required', 'string', 'max:255'],
            'landing_description' => ['required', 'string', 'max:1000'],
            'instagram_url' => ['nullable', 'string'],
            'whatsapp_url' => ['nullable', 'string'],
        ]);

        $settings['landing_title'] = $request->landing_title;
        $settings['landing_description'] = $request->landing_description;
        $settings['instagram_url'] = $request->instagram_url;
        $settings['whatsapp_url'] = $request->whatsapp_url;

        $tenant->update(['settings' => $settings]);

        return back()->with('success', 'Pengaturan personalisasi landing page berhasil diperbarui!');
    }
}
