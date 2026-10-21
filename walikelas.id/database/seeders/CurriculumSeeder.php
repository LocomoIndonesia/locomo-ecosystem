<?php

namespace Database\Seeders;

use App\Models\Competency;
use App\Models\Rubric;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada satu user kurator sistem untuk atribut created_by
        $curator = User::firstOrCreate(
            ['email' => 'curator@walikelas.id'],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Kurator Kurikulum Nasional',
                'password_hash' => bcrypt('WaliKelasCurator2026!'),
                'is_active' => true,
            ]
        );

        // 1. LIT-B-EXP-01: Informasi Tersurat
        $compExp = Competency::updateOrCreate(
            ['code' => 'LIT-B-EXP-01', 'version' => 1],
            [
                'id' => Str::uuid()->toString(),
                'domain' => 'literacy',
                'title' => 'Informasi Tersurat Teks Fabel',
                'description' => 'Menemukan informasi eksplisit (tokoh, latar tempat, waktu kejadian) dalam teks narasi fabel pendek.',
                'grade_band' => 'FASE_B_GR3',
                'review_status' => 'reviewed',
                'source_ref' => 'CP Bahasa Indonesia Kurikulum Merdeka Fase B (Elemen Membaca dan Memirsa)',
            ]
        );

        Rubric::updateOrCreate(
            ['competency_id' => $compExp->id, 'version' => 1],
            [
                'id' => Str::uuid()->toString(),
                'levels' => [
                    'NEEDS_SUPPORT' => 'Siswa belum dapat mengidentifikasi tokoh utama atau latar tempat secara tepat meskipun dibantu petunjuk.',
                    'DEVELOPING' => 'Siswa dapat menemukan tokoh dan latar tempat dengan bantuan 1-2 petunjuk konteks.',
                    'INDICATED_MASTERY' => 'Siswa mandiri mengidentifikasi tokoh, latar tempat, dan peristiwa eksplisit secara tepat dan konsisten.',
                ],
                'origin' => 'curated',
                'review_status' => 'reviewed',
                'created_by' => $curator->id,
            ]
        );

        // 2. LIT-B-SEQ-02: Kronologi Peristiwa (Prerequisite: LIT-B-EXP-01)
        $compSeq = Competency::updateOrCreate(
            ['code' => 'LIT-B-SEQ-02', 'version' => 1],
            [
                'id' => Str::uuid()->toString(),
                'domain' => 'literacy',
                'title' => 'Kronologi Peristiwa Cerita Naratif',
                'description' => 'Mengurutkan alur kejadian awal, tengah (konflik), dan akhir (penyelesaian) dalam teks naratif fabel.',
                'grade_band' => 'FASE_B_GR3',
                'prerequisite_id' => $compExp->id,
                'review_status' => 'reviewed',
                'source_ref' => 'CP Bahasa Indonesia Kurikulum Merdeka Fase B (Elemen Membaca dan Memirsa)',
            ]
        );

        Rubric::updateOrCreate(
            ['competency_id' => $compSeq->id, 'version' => 1],
            [
                'id' => Str::uuid()->toString(),
                'levels' => [
                    'NEEDS_SUPPORT' => 'Urutan peristiwa acak atau terbalik antara awal dan akhir cerita.',
                    'DEVELOPING' => 'Dapat mengurutkan awal dan akhir cerita dengan benar, namun bagian pertengahan masih tertukar.',
                    'INDICATED_MASTERY' => 'Mengurutkan seluruh tahapan peristiwa cerita dari awal, pertengahan, hingga resolusi akhir secara runtut.',
                ],
                'origin' => 'curated',
                'review_status' => 'reviewed',
                'created_by' => $curator->id,
            ]
        );

        // 3. LIT-B-VOC-03: Kosakata Kontekstual
        $compVoc = Competency::updateOrCreate(
            ['code' => 'LIT-B-VOC-03', 'version' => 1],
            [
                'id' => Str::uuid()->toString(),
                'domain' => 'literacy',
                'title' => 'Kosakata Kontekstual Fabel',
                'description' => 'Memahami makna kosakata baru dan sifat tokoh melalui konteks kalimat cerita fabel.',
                'grade_band' => 'FASE_B_GR3',
                'review_status' => 'reviewed',
                'source_ref' => 'CP Bahasa Indonesia Kurikulum Merdeka Fase B (Elemen Membaca dan Memirsa)',
            ]
        );

        Rubric::updateOrCreate(
            ['competency_id' => $compVoc->id, 'version' => 1],
            [
                'id' => Str::uuid()->toString(),
                'levels' => [
                    'NEEDS_SUPPORT' => 'Belum memahami arti kata sukar dalam kalimat fabel dan memilih arti yang berlawanan.',
                    'DEVELOPING' => 'Dapat menjodohkan sebagian kata sukar dengan bantuan kalimat konteks contoh.',
                    'INDICATED_MASTERY' => 'Memahami seluruh kosakata baru dalam cerita fabel dan dapat mengaitkan maknanya dengan tindakan tokoh.',
                ],
                'origin' => 'curated',
                'review_status' => 'reviewed',
                'created_by' => $curator->id,
            ]
        );
    }
}
