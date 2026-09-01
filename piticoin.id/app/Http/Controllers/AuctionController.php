<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Services\ExperienceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Exception;

class AuctionController extends Controller
{
    /**
     * Display the P2P Auction page.
     */
    public function index()
    {
        // Fetch users to act as simulated sellers/buyers
        $users = User::where('id', '!=', Auth::id())->limit(5)->get();

        // Standard mock auction items
        $items = [
            [
                'id' => '1',
                'name' => 'Phoenix Egg (Telur Phoenix)',
                'seller_name' => 'Saudagar Sutan',
                'seller_id' => $users[0]->id ?? null,
                'current_bid' => 4500,
                'buyout_price' => 6000,
                'icon' => '🥚',
                'description' => 'Telur Phoenix mistis dari faksi Nisnas. Memberikan bonus drop rate telur langka +3% jika berhasil menetas.'
            ],
            [
                'id' => '2',
                'name' => 'Iron Suit Engine Upgrade (MAU Part)',
                'seller_name' => 'Mekanik Buyung',
                'seller_id' => $users[1]->id ?? null,
                'current_bid' => 12000,
                'buyout_price' => 15000,
                'icon' => '⚙️',
                'description' => 'Suku cadang mesin Iron Suit generasi ke-5 dari faksi Manusia. Mengurangi konsumsi bahan bakar koin perak saat aktif.'
            ]
        ];

        return Inertia::render('Games/P2PAuction', [
            'user' => Auth::user(),
            'wallet' => Auth::user()->wallet,
            'auction_items' => $items
        ]);
    }

    /**
     * Buyout an item instantly.
     * Deducts silver from buyer, applies 10% burn tax, gives remaining 90% to seller.
     */
    public function buyout(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string',
            'price' => 'required|integer|min:1',
            'seller_id' => 'required|uuid|exists:users,id',
        ]);

        $buyer = Auth::user();
        $price = intval($request->input('price'));
        $sellerId = $request->input('seller_id');
        $itemName = $request->input('item_name');

        try {
            DB::transaction(function () use ($buyer, $price, $sellerId, $itemName) {
                // 1. Fetch buyer's wallet (lock for update)
                $buyerWallet = Wallet::where('user_id', $buyer->id)->lockForUpdate()->first();
                if (!$buyerWallet || $buyerWallet->silver_balance < $price) {
                    throw new Exception("Saldo perak tidak cukup. Dibutuhkan: {$price} Perak.");
                }

                // 2. Fetch seller's wallet (lock for update)
                $sellerWallet = Wallet::where('user_id', $sellerId)->lockForUpdate()->first();
                if (!$sellerWallet) {
                    throw new Exception("Wallet penjual tidak ditemukan.");
                }

                // 3. Deduct price from buyer
                $buyerWallet->decrement('silver_balance', $price);

                // 4. Calculate 10% system tax (burned)
                $tax = intval($price * 10 / 100);
                $sellerGain = $price - $tax;

                // 5. Transfer remaining 90% to seller
                $sellerWallet->increment('silver_balance', $sellerGain);

                // 6. Log experience point updates for trade activity
                ExperienceService::addExperience(
                    $buyer->id,
                    15,
                    'TRADE',
                    "Membeli item lelang: {$itemName} seharga {$price} Perak (Pajak Bakar: {$tax} Perak)"
                );

                ExperienceService::addExperience(
                    $sellerId,
                    25,
                    'TRADE',
                    "Menjual item lelang: {$itemName} seharga {$price} Perak (Diterima: {$sellerGain} Perak)"
                );
            });

            return response()->json([
                'success' => true,
                'message' => "Pembelian lelang sukses! Anda berhasil membeli {$itemName}."
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
