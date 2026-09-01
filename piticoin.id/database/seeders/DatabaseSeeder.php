<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Quest;
use App\Models\LiquidityPoolSystem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Initialize Liquidity Pool
        LiquidityPoolSystem::updateOrCreate(
            ['id' => 1],
            [
                'total_fiat_in_bank' => 500000,
                'total_gold_in_circulation' => 0,
            ]
        );
        $this->command->info('Liquidity Pool system initialized.');

        // 2. Add Quests (Expedition Maps)
        $quests = [
            [
                'title' => 'Ekspedisi Bukit Emas',
                'description' => 'Menelusuri perbukitan terjal untuk menemukan bongkahan emas murni. Cocok untuk Saudagar pemula.',
                'stake_amount_silver' => 10000,
                'reward_amount_gold' => 2000,
                'time_limit_hours' => 24,
                'is_active' => true,
            ],
            [
                'title' => 'Riam Rimba Harta',
                'description' => 'Mengarungi riam sungai liar di tengah rimba belantara demi harta karun tersembunyi.',
                'stake_amount_silver' => 50000,
                'reward_amount_gold' => 12000,
                'time_limit_hours' => 48,
                'is_active' => true,
            ],
            [
                'title' => 'Lembah Naga Perak',
                'description' => 'Ekspedisi tingkat tinggi ke dalam lembah berkabut tempat penyimpanan koin perak kuno.',
                'stake_amount_silver' => 100000,
                'reward_amount_gold' => 25000,
                'time_limit_hours' => 72,
                'is_active' => true,
            ],
        ];

        foreach ($quests as $q) {
            Quest::updateOrCreate(['title' => $q['title']], $q);
        }
        $this->command->info('Active Quests seeded.');

        // 3. Create Sample Users (Referral Chain)
        $password = Hash::make('password');

        // Datuk Grand (Level 2)
        $datuk = User::updateOrCreate(
            ['whatsapp' => '08123456780'],
            [
                'name' => 'Datuk Grand',
                'role' => 'GUEST',
                'password' => $password,
            ]
        );
        Wallet::updateOrCreate(
            ['user_id' => $datuk->id],
            ['silver_balance' => 0, 'gold_balance' => 0]
        );

        // Sutan Upline (Level 1)
        $sutan = User::updateOrCreate(
            ['whatsapp' => '08123456781'],
            [
                'name' => 'Sutan Upline',
                'role' => 'GUEST',
                'upline_id' => $datuk->id,
                'password' => $password,
            ]
        );
        Wallet::updateOrCreate(
            ['user_id' => $sutan->id],
            ['silver_balance' => 0, 'gold_balance' => 0]
        );

        // Buyung Merchant (Main Merchant)
        $buyung = User::updateOrCreate(
            ['whatsapp' => '08123456782'],
            [
                'name' => 'Buyung Merchant',
                'role' => 'MERCHANT',
                'license_type' => 'SAUDAGAR',
                'license_status' => 'ACTIVE',
                'upline_id' => $sutan->id,
                'grand_upline_id' => $datuk->id,
                'password' => $password,
            ]
        );
        Wallet::updateOrCreate(
            ['user_id' => $buyung->id],
            [
                'silver_balance' => 100000, // Starts with 100,000 Silver coins
                'gold_balance' => 0,
            ]
        );

        // Siti Saudagar (Uji Coba Saudagar Baru)
        $siti = User::updateOrCreate(
            ['whatsapp' => '08123456783'],
            [
                'name' => 'Siti Saudagar',
                'role' => 'MERCHANT',
                'license_type' => 'SAUDAGAR',
                'license_status' => 'ACTIVE',
                'upline_id' => $sutan->id,
                'grand_upline_id' => $datuk->id,
                'password' => $password,
            ]
        );
        Wallet::updateOrCreate(
            ['user_id' => $siti->id],
            [
                'silver_balance' => 10000,
                'gold_balance' => 0,
            ]
        );

        // Randi Petualang (Uji Coba Petualang Baru)
        $randi = User::updateOrCreate(
            ['whatsapp' => '08123456784'],
            [
                'name' => 'Randi Petualang',
                'role' => 'MERCHANT',
                'license_type' => 'PETUALANG',
                'license_status' => 'ACTIVE',
                'upline_id' => $sutan->id,
                'grand_upline_id' => $datuk->id,
                'password' => $password,
            ]
        );
        Wallet::updateOrCreate(
            ['user_id' => $randi->id],
            [
                'silver_balance' => 10000,
                'gold_balance' => 0,
            ]
        );

        $this->command->info('Sample referral chain users and wallets seeded.');
    }
}
