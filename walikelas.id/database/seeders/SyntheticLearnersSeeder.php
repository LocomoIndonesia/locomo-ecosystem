<?php

namespace Database\Seeders;

use App\Models\Consent;
use App\Models\EducatorProfile;
use App\Models\Enrollment;
use App\Models\GuardianRelation;
use App\Models\Learner;
use App\Models\LearningProgram;
use App\Models\Organization;
use App\Models\ProgramEducator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SyntheticLearnersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lead Educator (Sekolah)
        $educatorBudi = User::firstOrCreate(
            ['email' => 'guru.budi@walikelas.id'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Budi Santoso, S.Pd.',
                'phone_e164' => '+6281234567801',
                'password_hash' => bcrypt('WaliKelas2026!'),
                'is_active' => true,
            ]
        );

        EducatorProfile::updateOrCreate(
            ['user_id' => $educatorBudi->id],
            [
                'display_name' => 'Pak Guru Budi',
                'bio' => 'Wali Kelas 3 SD berpengalaman 8 tahun penguatan literasi awal.',
                'accepted_ai_terms_version' => 'v1.0',
                'created_at' => now(),
            ]
        );

        // 2. Private Tutor (Privat Mandiri)
        $tutorSiti = User::firstOrCreate(
            ['email' => 'tutor.siti@walikelas.id'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Siti Rahma, M.Pd.',
                'phone_e164' => '+6281234567802',
                'password_hash' => bcrypt('WaliKelas2026!'),
                'is_active' => true,
            ]
        );

        EducatorProfile::updateOrCreate(
            ['user_id' => $tutorSiti->id],
            [
                'display_name' => 'Kak Siti',
                'bio' => 'Tutor membaca privat spesialisasi remedial anak berkebutuhan pendampingan.',
                'accepted_ai_terms_version' => 'v1.0',
                'created_at' => now(),
            ]
        );

        // 3. Organisasi Sekolah
        $schoolOrg = Organization::updateOrCreate(
            ['npsn' => '20104523'],
            [
                'id' => Str::uuid()->toString(),
                'kind' => 'school',
                'name' => 'SD Negeri Cemara 01',
                'created_by' => $educatorBudi->id,
                'created_at' => now(),
            ]
        );

        // 4. Program Mode Sekolah (TI-01 & TI-02)
        $schoolProgram = LearningProgram::updateOrCreate(
            ['title' => 'Kelas 3A - Literasi Membaca Fabel'],
            [
                'id' => Str::uuid()->toString(),
                'mode' => 'school',
                'organization_id' => $schoolOrg->id,
                'goal_summary' => 'Meningkatkan kelancaran membaca pemahaman fabel dan pengayaan kosakata.',
                'grade_level' => 3,
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->addMonths(3)->endOfMonth()->toDateString(),
                'planned_session_count' => 12,
                'status' => 'active',
                'created_by' => $educatorBudi->id,
            ]
        );

        ProgramEducator::firstOrCreate(
            [
                'program_id' => $schoolProgram->id,
                'user_id' => $educatorBudi->id,
            ],
            [
                'id' => Str::uuid()->toString(),
                'role' => 'lead',
                'started_at' => now(),
            ]
        );

        // 5. Program Mode Privat Tanpa Sekolah (TI-01)
        $privateProgram = LearningProgram::updateOrCreate(
            ['title' => 'Bimbingan Membaca Privat Ceria'],
            [
                'id' => Str::uuid()->toString(),
                'mode' => 'private',
                'organization_id' => null, // Bebas institusi
                'goal_summary' => 'Pendampingan privat membaca intensif 1-on-1 dan kelompok kecil.',
                'grade_level' => 3,
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->addMonths(2)->endOfMonth()->toDateString(),
                'planned_session_count' => 8,
                'status' => 'active',
                'created_by' => $tutorSiti->id,
            ]
        );

        ProgramEducator::firstOrCreate(
            [
                'program_id' => $privateProgram->id,
                'user_id' => $tutorSiti->id,
            ],
            [
                'id' => Str::uuid()->toString(),
                'role' => 'lead',
                'started_at' => now(),
            ]
        );

        // 6. 10 Siswa Sintetis Minimalis (Hanya Nama Panggilan & Avatar Ramah Anak)
        $syntheticStudents = [
            ['name' => 'Rian', 'avatar' => 'avatar_kancil_01', 'guardian_name' => 'Ibu Ratna (Wali Rian)', 'phone' => '+628990001001'],
            ['name' => 'Maya', 'avatar' => 'avatar_tupai_02', 'guardian_name' => 'Ayah Surya (Wali Maya)', 'phone' => '+628990001002'],
            ['name' => 'Bima', 'avatar' => 'avatar_berang_03', 'guardian_name' => 'Ibu Dewi (Wali Bima)', 'phone' => '+628990001003'],
            ['name' => 'Alya', 'avatar' => 'avatar_pipit_04', 'guardian_name' => 'Ibu Linda (Wali Alya)', 'phone' => '+628990001004'],
            ['name' => 'Dafi', 'avatar' => 'avatar_kura_05', 'guardian_name' => 'Ayah Rudi (Wali Dafi)', 'phone' => '+628990001005'],
            ['name' => 'Sari', 'avatar' => 'avatar_kancil_01', 'guardian_name' => 'Ibu Wati (Wali Sari)', 'phone' => '+628990001006'],
            ['name' => 'Arka', 'avatar' => 'avatar_tupai_02', 'guardian_name' => 'Ayah Hendra (Wali Arka)', 'phone' => '+628990001007'],
            ['name' => 'Nabila', 'avatar' => 'avatar_pipit_04', 'guardian_name' => 'Ibu Nina (Wali Nabila)', 'phone' => '+628990001008'],
            ['name' => 'Kiki', 'avatar' => 'avatar_berang_03', 'guardian_name' => 'Ayah Joko (Wali Kiki)', 'phone' => '+628990001009'],
            ['name' => 'Tio', 'avatar' => 'avatar_kura_05', 'guardian_name' => 'Ibu Rani (Wali Tio)', 'phone' => '+628990001010'],
        ];

        foreach ($syntheticStudents as $index => $item) {
            $learner = Learner::updateOrCreate(
                ['display_name' => $item['name'], 'is_synthetic' => true],
                [
                    'id' => Str::uuid()->toString(),
                    'grade_level' => 3,
                    'birth_year' => 2017,
                    'avatar_id' => $item['avatar'],
                    'created_by' => $educatorBudi->id,
                    'created_at' => now(),
                ]
            );

            // Daftarkan ke Program Sekolah
            Enrollment::firstOrCreate(
                [
                    'program_id' => $schoolProgram->id,
                    'learner_id' => $learner->id,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]
            );

            // 5 Siswa pertama juga didaftarkan ke Program Privat
            if ($index < 5) {
                Enrollment::firstOrCreate(
                    [
                        'program_id' => $privateProgram->id,
                        'learner_id' => $learner->id,
                    ],
                    [
                        'id' => Str::uuid()->toString(),
                        'status' => 'active',
                        'enrolled_at' => now(),
                    ]
                );
            }

            // User Wali
            $guardianUser = User::firstOrCreate(
                ['phone_e164' => $item['phone']],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => $item['guardian_name'],
                    'is_active' => true,
                ]
            );

            // Relasi Wali Terverifikasi (TI-03)
            $relation = GuardianRelation::firstOrCreate(
                [
                    'learner_id' => $learner->id,
                    'guardian_user_id' => $guardianUser->id,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'relation_type' => 'parent',
                    'status' => 'verified',
                    'verified_via' => 'two_sided_educator_confirmation',
                    'educator_confirmed_by' => $educatorBudi->id,
                    'verified_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Consent Terverifikasi untuk Program Sekolah
            Consent::firstOrCreate(
                [
                    'guardian_relation_id' => $relation->id,
                    'program_id' => $schoolProgram->id,
                    'purpose_scope' => 'PROGRAM_PARTICIPATION',
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'policy_version' => 'v1.0',
                    'granted_at' => now(),
                ]
            );

            // Consent untuk Program Privat (jika ikut)
            if ($index < 5) {
                Consent::firstOrCreate(
                    [
                        'guardian_relation_id' => $relation->id,
                        'program_id' => $privateProgram->id,
                        'purpose_scope' => 'PROGRAM_PARTICIPATION',
                    ],
                    [
                        'id' => Str::uuid()->toString(),
                        'policy_version' => 'v1.0',
                        'granted_at' => now(),
                    ]
                );
            }
        }
    }
}
