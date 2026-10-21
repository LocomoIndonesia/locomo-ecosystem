<?php

namespace Database\Seeders;

use App\Models\Competency;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MissionSeeder extends Seeder
{
    public function run(): void
    {
        $curator = User::where('email', 'curator@walikelas.id')->firstOrFail();

        $compExp = Competency::where('code', 'LIT-B-EXP-01')->firstOrFail();
        $rubricExp = $compExp->rubrics()->firstOrFail();

        $compVoc = Competency::where('code', 'LIT-B-VOC-03')->firstOrFail();
        $rubricVoc = $compVoc->rubrics()->firstOrFail();

        // 1. Misi Digital 1: "Misteri Jejak Kaki di Kebun Paman"
        Mission::updateOrCreate(
            ['title' => 'Misteri Jejak Kaki di Kebun Paman', 'version' => 1],
            [
                'id' => Str::uuid()->toString(),
                'program_id' => null, // Pustaka umum kurasi
                'competency_id' => $compExp->id,
                'rubric_id' => $rubricExp->id,
                'kind' => 'digital',
                'estimated_minutes' => 10,
                'status' => 'ready',
                'created_by' => $curator->id,
                'steps_payload' => [
                    [
                        'step_key' => 'step_1_tokoh_jejak',
                        'component_type' => 'SingleChoiceCard',
                        'props' => [
                            'passage' => 'Pagi hari setelah hujan reda, Paman melihat deretan jejak kaki basah di tanah dekat kolam ikan. Jejak kaki itu memiliki selaput tipis di antara jari-jarinya. Tak jauh dari sana, seekor Berang-berang kecil tampak sedang mengeringkan bulunya di atas batu hangat sambil tersenyum malu.',
                            'question' => 'Siapakah tamu yang meninggalkan jejak kaki di dekat kolam Paman?',
                            'options' => [
                                ['id' => 'opt_a', 'text' => 'Kucing belang tetangga', 'is_correct' => false],
                                ['id' => 'opt_b', 'text' => 'Berang-berang kecil', 'is_correct' => true],
                                ['id' => 'opt_c', 'text' => 'Burung pipit kebun', 'is_correct' => false],
                            ],
                            'hints' => [
                                'Perhatikan kalimat kedua di paragraf pertama.',
                                'Siapa hewan yang memiliki telapak kaki berselaput di dekat kolam?',
                            ],
                            'explanation' => 'Teks secara tersurat menyebutkan Berang-berang kecil yang memiliki selaput tipis di antara jari-jarinya di dekat kolam.',
                        ],
                    ],
                    [
                        'step_key' => 'step_2_uraian_jejak',
                        'component_type' => 'ShortPromptReader',
                        'props' => [
                            'passage' => 'Paman tersenyum melihat jejak tersebut. Jejak itu tidak merusak tanaman cabai paman, melainkan hanya melintas menuju tepi kolam berbatu.',
                            'prompt' => 'Jelaskan tanda jejak kaki yang ditinggalkan oleh tamu paman tersebut!',
                            'rubric_criteria' => [
                                'Menyebutkan jejak kaki memiliki selaput tipis',
                                'Menyebutkan jejak kaki basah di tanah dekat kolam',
                            ],
                            'min_words' => 5,
                        ],
                    ],
                ],
            ]
        );

        // 2. Misi Digital 2: "Kata Ajaib Burung Hantu"
        Mission::updateOrCreate(
            ['title' => 'Kata Ajaib Burung Hantu', 'version' => 1],
            [
                'id' => Str::uuid()->toString(),
                'program_id' => null,
                'competency_id' => $compVoc->id,
                'rubric_id' => $rubricVoc->id,
                'kind' => 'digital',
                'estimated_minutes' => 10,
                'status' => 'ready',
                'created_by' => $curator->id,
                'steps_payload' => [
                    [
                        'step_key' => 'step_1_jodoh_kata',
                        'component_type' => 'ContextWordMatcher',
                        'props' => [
                            'prompt' => 'Jodohkan kata bercetak tebal dari fabel Burung Hantu dengan artinya yang paling tepat!',
                            'pairs' => [
                                [
                                    'word' => 'gigih',
                                    'meaning' => 'tetap teguh dan tidak mudah putus asa dalam berusaha',
                                    'example_sentence' => 'Burung Hantu gigih mencari makan meski angin malam bertiup kencang.',
                                ],
                                [
                                    'word' => 'gulita',
                                    'meaning' => 'gelap pekat tanpa ada penerangan cahaya',
                                    'example_sentence' => 'Hutan belantara menjadi gulita saat bulan tertutup awan mendung.',
                                ],
                                [
                                    'word' => 'berteduh',
                                    'meaning' => 'berlindung di bawah tempat aman agar tidak kehujanan atau kepanasan',
                                    'example_sentence' => 'Tupai kecil bergegas berteduh di lubang pohon beringin tua.',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // 3. Misi Rumah: "Mendongeng di Meja Makan"
        Mission::updateOrCreate(
            ['title' => 'Mendongeng di Meja Makan', 'version' => 1],
            [
                'id' => Str::uuid()->toString(),
                'program_id' => null,
                'competency_id' => $compExp->id,
                'rubric_id' => $rubricExp->id,
                'kind' => 'home',
                'estimated_minutes' => 5,
                'status' => 'ready',
                'created_by' => $curator->id,
                'steps_payload' => [
                    [
                        'step_key' => 'home_step_storytelling',
                        'component_type' => 'ShortPromptReader',
                        'props' => [
                            'passage' => 'Ceritakan kembali petualangan Kancil atau Tupai kepada Ayah/Ibu/Keluarga selama 5 menit saat santap malam atau sebelum tidur.',
                            'prompt' => 'Siapa anggota keluarga yang mendengarkan ceritamu malam ini?',
                            'rubric_criteria' => ['Anak menceritakan tokoh utama dan tindakan baiknya'],
                        ],
                    ],
                ],
                'offline_activity_guide' => [
                    'title' => 'Panduan Mendongeng Santai Bersama Anak di Rumah',
                    'instructions' => 'Dengarkan ananda menceritakan kembali fabel yang dipelajari di kelas. Berikan apresiasi atas keberanian ananda bercerita, lalu pilih konfirmasi di bawah ini.',
                    'guardian_response_options' => [
                        [
                            'code' => 'done',
                            'label' => 'Selesai',
                            'description' => 'Anak berhasil menceritakan alur cerita dengan antusias.',
                        ],
                        [
                            'code' => 'not_yet',
                            'label' => 'Belum Selesai',
                            'description' => 'Belum sempat malam ini, jadwalkan kembali besok.',
                        ],
                        [
                            'code' => 'need_help',
                            'label' => 'Butuh Bantuan Guru',
                            'description' => 'Anak kesulitan mengingat tokoh cerita, mohon arahan guru.',
                        ],
                    ],
                ],
            ]
        );
    }
}
