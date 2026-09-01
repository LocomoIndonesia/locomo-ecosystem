<?php

namespace App\Modules\Business\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\User;
use App\Models\EntityAssociation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = Entity::with('interactions')->paginate(12);
        return view('business::index', compact('businesses'));
    }

    public function show($id)
    {
        $business = Entity::with('interactions.user')->findOrFail($id);
        return view('business::show', compact('business'));
    }

    public function showBySlug($slug)
    {
        $business = Entity::with(['interactions.user', 'businessMeta'])->where('slug', $slug)->firstOrFail();
        return view('business::show', compact('business'));
    }

    public function menu($slug)
    {
        $business = Entity::where('slug', $slug)->firstOrFail();
        return view('business::menu', compact('business'));
    }

    public function products($slug)
    {
        $business = Entity::where('slug', $slug)->firstOrFail();
        return view('business::products', compact('business'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->is_verified) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda masih dalam proses verifikasi oleh Admin.');
        }

        $registerForSelf = $request->register_for_self === '1';

        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:restoran,hotel,transportasi,tour_guide,produk_lokal,layanan_publik,destinasi',
            'description' => 'nullable|string',
            'price_range' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'register_for_self' => 'nullable|in:0,1',
            'subscription_package' => 'required|in:free,basic,bisnis,vip',
            'payment_proof' => 'required_if:subscription_package,basic,bisnis,vip|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ];

        if (!$registerForSelf) {
            $rules['owner_name'] = 'required|string|max:255';
            $rules['owner_email'] = 'required|email|max:255|unique:users,email';
            $rules['owner_phone'] = 'required|string|max:20|unique:users,phone_number';
            $rules['owner_password'] = 'required|string|min:6';
            $rules['role_in_business'] = 'required|in:owner,driver,manager,staff';
        }

        $request->validate($rules);

        if ($registerForSelf) {
            $ownerId = auth()->id();
            $roleInBusiness = 'owner';
        } else {
            // 1. Create the new owner/driver user (downline)
            $newOwner = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'phone_number' => $request->owner_phone,
                'password' => bcrypt($request->owner_password),
                'role' => $request->role_in_business === 'driver' ? 'wisatawan' : 'business_owner',
                'upline_id' => auth()->id(),
            ]);
            $ownerId = $newOwner->id;
            $roleInBusiness = $request->role_in_business;
        }

        // 2. Generate QR Token and Code
        $qrToken = Str::random(40);
        $qrCodeFilename = $qrToken . '.png';
        $qrCodeDir = storage_path('app/public/qrcodes');

        if (!file_exists($qrCodeDir)) {
            mkdir($qrCodeDir, 0755, true);
        }

        // Generate dummy QR Code using PHP GD
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
            // Fallback if GD is not installed
            file_put_contents($qrCodeDir . '/' . $qrCodeFilename, 'QR_TOKEN:' . $qrToken);
        }

        // 3. Process Subscription & Payment Proof
        $paymentProofPath = null;
        $isActive = true;

        if ($request->subscription_package !== 'free') {
            $isActive = false; // Menunggu verifikasi admin
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
            }
        }

        // 4. Create the business entity
        $entity = Entity::create([
            'owner_id' => $ownerId,
            'upline_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'type' => $request->type,
            'description' => $request->description,
            'price_range' => $request->price_range,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'qr_token' => $qrToken,
            'qr_code_path' => 'qrcodes/' . $qrCodeFilename,
            'subscription_package' => $request->subscription_package,
            'payment_proof_path' => $paymentProofPath,
            'is_active' => $isActive,
        ]);

        // 5. Create association
        EntityAssociation::create([
            'entity_id' => $entity->id,
            'user_id' => $ownerId,
            'role_in_business' => $roleInBusiness,
        ]);

        $message = $isActive 
            ? 'Bisnis berhasil didaftarkan dan langsung aktif!' 
            : 'Bisnis berhasil didaftarkan! Silakan tunggu verifikasi bukti pembayaran oleh Admin untuk mengaktifkan fitur.';

        if ($request->headers->has('referer')) {
            return redirect()->back()->with('success', $message);
        }

        return redirect()->route('business.upline')->with('success', $message);
    }

    public function uplineBusiness()
    {
        if (!auth()->user()->is_verified) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda masih dalam proses verifikasi oleh Admin.');
        }

        $userId = auth()->id();
        $businesses = Entity::where('upline_id', $userId)
            ->orWhere('owner_id', $userId)
            ->with(['owner', 'associations.user'])
            ->get();

        return view('business::upline_business', compact('businesses'));
    }

    public function printQr($id)
    {
        $business = Entity::findOrFail($id);

        // Ensure user is authorized to print this (either owner or upline)
        if ($business->owner_id !== auth()->id() && $business->upline_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mencetak QR Code bisnis ini.');
        }

        return view('business::print_qr', compact('business'));
    }
}
