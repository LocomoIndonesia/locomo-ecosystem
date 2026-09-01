<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Vanguard\Models\VanguardParticipant;
use App\Modules\Vanguard\Models\VanguardSurvey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin Manager
        User::updateOrCreate([
            'email' => 'admin@mandeh.online',
        ], [
            'name' => 'Admin Manager HPI',
            'phone_number' => '081111111111',
            'password' => Hash::make('password123'),
            'role' => 'admin_manager',
            'is_verified' => true,
        ]);

        // 2. Create Verified User (to test business register)
        User::updateOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'Budi Pelaku Wisata',
            'phone_number' => '081222222222',
            'password' => Hash::make('password123'),
            'role' => 'wisatawan',
            'is_verified' => true,
        ]);

        // 3. Create Unverified User (for verification queue)
        User::updateOrCreate([
            'email' => 'calon@example.com',
        ], [
            'name' => 'Andi Pendaftar Baru',
            'phone_number' => '081233333333',
            'password' => Hash::make('password123'),
            'role' => 'wisatawan',
            'is_verified' => false,
        ]);

        // 4. Create Graduated Vanguard (for inauguration testing)
        $vanguard1 = User::updateOrCreate([
            'email' => 'kader1@example.com',
        ], [
            'name' => 'Joni Vanguard Lulus',
            'phone_number' => '081244444444',
            'password' => Hash::make('password123'),
            'role' => 'wisatawan',
            'is_verified' => true,
        ]);

        VanguardParticipant::updateOrCreate([
            'user_id' => $vanguard1->id,
        ], [
            'tracking_id' => 'VGD-20260617-1111',
            'name' => $vanguard1->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'reputation_points' => 90,
            'is_vanguard_graduated' => true,
            'is_inaugurated' => false,
        ]);

        // 5. Create Active/Scheduled surveys with structured choices
        VanguardSurvey::updateOrCreate([
            'title' => 'Pemilihan Ketua Umum DPC HPI Pessel 2026',
        ], [
            'description' => 'Gunakan hak suara Anda untuk memilih pemimpin pramuwisata Nagari.',
            'target_roles' => ['vanguard'],
            'options' => [
                [
                    'title' => 'Budi Pandeka (Nagari Carocok)',
                    'description' => 'Pemandu senior berpengalaman 12 tahun di pariwisata bahari.',
                    'profile_url' => 'https://hpi.or.id/profile/budi-pandeka',
                    'link_text' => 'Lihat Portfolio'
                ],
                [
                    'title' => 'Randi Sikumbang (Nagari Cubadak)',
                    'description' => 'Pegiat ekowisata dan instruktur selam tersertifikasi nasional.',
                    'profile_url' => 'https://hpi.or.id/profile/randi-sikumbang',
                    'link_text' => 'Lihat Lisensi'
                ],
                [
                    'title' => 'Aulia Harahap (Nagari Koto Tarusan)',
                    'description' => 'Pengembang digital hub pemandu dan pelopor WiFi wisata.',
                    'profile_url' => 'https://hpi.or.id/profile/aulia-harahap',
                    'link_text' => 'Lihat Bio & Visi'
                ]
            ],
            'start_time' => Carbon::now()->subMinutes(10), // Active
            'end_time' => Carbon::now()->addDays(5),
        ]);

        VanguardSurvey::updateOrCreate([
            'title' => 'Evaluasi Penyelenggaraan LDK Mandeh Offline',
        ], [
            'description' => 'Jajak pendapat tentang kenyamanan akomodasi dan kualitas materi.',
            'target_roles' => ['all'],
            'options' => [
                [
                    'title' => 'Sangat Memuaskan',
                    'description' => 'Fasilitas akomodasi sangat nyaman dan konsumsi berkualitas.',
                ],
                [
                    'title' => 'Cukup Memuaskan',
                    'description' => 'Fasilitas standar, namun masih ada ruang peningkatan pelayanan.',
                ],
                [
                    'title' => 'Kurang Memuaskan',
                    'description' => 'Akomodasi kurang memadai atau logistik sering terlambat.',
                ]
            ],
            'start_time' => Carbon::now()->addDays(1), // Scheduled (Not started)
            'end_time' => Carbon::now()->addDays(4),
        ]);

        // 6. Seed default missions with date/time
        \App\Modules\Vanguard\Models\VanguardMission::updateOrCreate([
            'title' => 'Simulasi Pelayanan Tamu di Kawasan Mandeh',
        ], [
            'description' => 'Latih kemampuan pemanduan Anda dengan mensimulasikan pelayanan tamu secara nyata sesuai standar HPI.',
            'armada_type' => 'darat_laut',
            'points_reward' => 30,
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->addDays(5),
        ]);

        \App\Modules\Vanguard\Models\VanguardMission::updateOrCreate([
            'title' => 'Kampanye Kebangsaan & Kode Etik HPI Pessel',
        ], [
            'description' => 'Ikuti diskusi materi tentang wawasan kebangsaan dan kode etik profesi pramuwisata.',
            'armada_type' => 'both',
            'points_reward' => 20,
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->addDays(5),
        ]);

        \App\Modules\Vanguard\Models\VanguardMission::updateOrCreate([
            'title' => 'Kampanye Media Sosial Wisata Nagari',
        ], [
            'description' => 'Buat infografis atau tulisan promosi wisata nagari terintegrasi di media sosial untuk menarik traffic.',
            'armada_type' => 'udara',
            'points_reward' => 35,
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->addDays(5),
        ]);

        \App\Modules\Vanguard\Models\VanguardMission::updateOrCreate([
            'title' => 'Simulasi Penyelamatan Wisata Air (Water Rescue)',
        ], [
            'description' => 'Pelatihan teknik pertolongan pertama pada korban tenggelam di perairan laut Mandeh (TIC).',
            'armada_type' => 'darat_laut',
            'points_reward' => 50,
            'start_time' => Carbon::now()->addDays(2),
            'end_time' => Carbon::now()->addDays(4),
        ]);

        // 7. Create Verified Pelaku Wisata Mock User
        $pelaku = User::updateOrCreate([
            'email' => 'pelaku@example.com',
        ], [
            'name' => 'Roni Pelaku Wisata',
            'phone_number' => '081255555555',
            'password' => Hash::make('password123'),
            'role' => 'business_owner',
            'is_verified' => true,
        ]);

        VanguardParticipant::updateOrCreate([
            'user_id' => $pelaku->id,
        ], [
            'tracking_id' => 'VGD-20260624-9999',
            'name' => $pelaku->name,
            'origin' => 'lokal',
            'armada' => 'darat_laut',
            'profile_type' => 'pelaku_wisata',
            'is_hpi_candidate' => false,
            'profession' => 'Restoran Owner',
            'reputation_points' => 60,
            'initial_rating' => 4,
            'nagari' => 'Nagari Carocok',
            'kecamatan' => 'Koto XI Tarusan',
            'kabupaten' => 'Pesisir Selatan',
            'provinsi' => 'Sumatera Barat',
            'full_address' => 'Jl. Dermaga Carocok Mandeh No. 12',
            'gender' => 'L',
            'date_of_birth' => '1988-08-08',
        ]);
    }
}
