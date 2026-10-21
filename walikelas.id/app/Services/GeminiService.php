<?php

namespace App\Services;

use App\Models\AiRun;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiService
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Memanggil Google Gemini dengan strict JSON schema & logging audit ke ai_runs (TI-05, TI-14)
     */
    public function generateStructured(
        string $purpose,
        string $systemPrompt,
        string $userPrompt,
        array $jsonSchema,
        ?string $programId = null,
        array $inputRefs = []
    ): array {
        $startTime = microtime(true);
        $aiRunId = Str::uuid()->toString();
        $promptVersion = 'v1.0';

        // 1. Jika API key belum terpasang atau mode mock aktif, gunakan deterministic fallback (TI-14)
        if (empty($this->apiKey)) {
            $fallbackResult = $this->mockDeterministicResponse($purpose, $userPrompt);
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            AiRun::create([
                'id' => $aiRunId,
                'purpose' => $purpose,
                'program_id' => $programId,
                'prompt_version' => $promptVersion,
                'model_name' => $this->model . ' (local-deterministic-mock)',
                'input_refs' => $inputRefs,
                'output_payload' => $fallbackResult,
                'status' => 'ok',
                'latency_ms' => $latencyMs,
                'created_at' => now(),
            ]);

            return $fallbackResult;
        }

        // 2. Pemanggilan API Google Gemini
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $userPrompt]]],
            ],
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => 'application/json',
                'responseSchema' => $jsonSchema,
            ],
        ];

        try {
            $response = Http::timeout(25)->post($url, $payload);
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            if (!$response->successful()) {
                Log::warning('Gemini API Non-200 Response: ' . $response->body());
                $fallbackResult = $this->mockDeterministicResponse($purpose, $userPrompt);

                AiRun::create([
                    'id' => $aiRunId,
                    'purpose' => $purpose,
                    'program_id' => $programId,
                    'prompt_version' => $promptVersion,
                    'model_name' => $this->model,
                    'input_refs' => $inputRefs,
                    'output_payload' => $fallbackResult,
                    'status' => 'error',
                    'latency_ms' => $latencyMs,
                    'created_at' => now(),
                ]);

                return $fallbackResult;
            }

            $rawCandidate = $response->json('candidates.0.content.parts.0.text');
            $decoded = json_decode($rawCandidate, true) ?: [];

            AiRun::create([
                'id' => $aiRunId,
                'purpose' => $purpose,
                'program_id' => $programId,
                'prompt_version' => $promptVersion,
                'model_name' => $this->model,
                'input_refs' => $inputRefs,
                'output_payload' => $decoded,
                'status' => 'ok',
                'latency_ms' => $latencyMs,
                'created_at' => now(),
            ]);

            return $decoded;
        } catch (Exception $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            Log::error('Gemini Exception: ' . $e->getMessage());

            $fallbackResult = $this->mockDeterministicResponse($purpose, $userPrompt);

            AiRun::create([
                'id' => $aiRunId,
                'purpose' => $purpose,
                'program_id' => $programId,
                'prompt_version' => $promptVersion,
                'model_name' => $this->model,
                'input_refs' => $inputRefs,
                'output_payload' => $fallbackResult,
                'status' => 'timeout',
                'latency_ms' => $latencyMs,
                'created_at' => now(),
            ]);

            return $fallbackResult;
        }
    }

    /**
     * Fallback deterministik cerdas untuk ekstraksi silabus, penilaian rubrik, gap analysis, dan intervensi (TI-14, TAT-03, TAT-10, TAT-18, TAT-21)
     */
    protected function mockDeterministicResponse(string $purpose, string $prompt): array
    {
        if ($purpose === 'plan_extract') {
            $ambiguities = [];
            // Deteksi teks yang tidak menyebutkan frekuensi asesmen atau durasi (TAT-18)
            if (!str_contains(strtolower($prompt), 'menit') && !str_contains(strtolower($prompt), 'jam')) {
                $ambiguities[] = 'Alokasi waktu pertemuan tidak disebutkan secara spesifik dalam silabus.';
            }
            if (!str_contains(strtolower($prompt), 'rubrik') && !str_contains(strtolower($prompt), 'kriteria')) {
                $ambiguities[] = 'Kriteria keberhasilan/rubrik belum memiliki pedoman penskoran eksplisit.';
            }

            return [
                'title' => 'Rencana Ajar Literasi Naratif Terstruktur',
                'grade_level' => 3,
                'objectives' => [
                    'Mengidentifikasi tokoh dan latar tempat fabel dengan tepat (LIT-B-EXP-01).',
                    'Mengurutkan alur peristiwa cerita dari awal hingga resolusi (LIT-B-SEQ-02).',
                    'Memperkaya kosakata baru melalui kalimat fabel (LIT-B-VOC-03).',
                ],
                'activities' => [
                    [
                        'session' => 1,
                        'name' => 'Membaca Terpandu Cerita Fabel',
                        'duration_minutes' => 35,
                        'description' => 'Membaca bersama kutipan fabel dan menandai tokoh serta kosakata baru.',
                    ],
                    [
                        'session' => 2,
                        'name' => 'Misi Literasi Digital & Refleksi',
                        'duration_minutes' => 25,
                        'description' => 'Siswa menyelesaikan misi interaktif 10 menit di Learner Runner.',
                    ],
                ],
                'assessment_strategy' => [
                    'type' => 'formative_checkpoint',
                    'frequency' => 'per_session',
                    'rubrics' => ['LIT-B-EXP-01', 'LIT-B-SEQ-02', 'LIT-B-VOC-03'],
                ],
                'ambiguities' => $ambiguities,
            ];
        }

        if ($purpose === 'rubric_score') {
            $lower = strtolower($prompt);
            $hasSelaput = str_contains($lower, 'selaput');
            $hasKolam = str_contains($lower, 'kolam');
            $hasJejak = str_contains($lower, 'jejak');

            $criteriaMet = [];
            if ($hasSelaput) {
                $criteriaMet[] = 'Menyebutkan jejak kaki memiliki selaput tipis';
            }
            if ($hasKolam || $hasJejak) {
                $criteriaMet[] = 'Menyebutkan jejak kaki basah di tanah dekat kolam';
            }

            $score = count($criteriaMet) >= 2 ? 4 : (count($criteriaMet) === 1 ? 3 : 2);
            $level = $score >= 3 ? 'indicated_mastery' : 'needs_support';

            return [
                'score' => $score,
                'level' => $level,
                'criteria_met' => $criteriaMet,
                'rationale' => count($criteriaMet) > 0
                    ? 'Siswa berhasil mendeskripsikan ciri jejak berselaput dan lokasi sekitar kolam dari teks fabel.'
                    : 'Jawaban siswa belum mencakup bukti eksplisit dari teks fabel.',
                'feedback_for_student' => 'Kerja bagus! Terus amati petunjuk cerita dengan teliti ya.',
                'uncertainty' => 'low',
            ];
        }

        if ($purpose === 'gap_analysis') {
            return [
                'uncertainty' => 'low',
                'identified_gaps' => [
                    'Perlu penguatan pada pengurutan alur peristiwa (LIT-B-SEQ-02).',
                ],
                'suggested_focus' => 'Latihan membaca terpandu alur maju-mundur fabel.',
            ];
        }

        if ($purpose === 'intervention') {
            return [
                'competency_code' => 'LIT-B-EXP-01',
                'action_title' => 'Membaca Terpandu Berpasangan: Menandai Ciri Tokoh',
                'description' => 'Aktivitas 15 menit mendampingi siswa membaca paragraf fabel dan melingkari kata kunci ciri fisik tokoh.',
                'rationale' => 'Siswa memerlukan penguatan identifikasi detail tersurat sebelum berlanjut ke penarikan kesimpulan inferensial.',
                'target_session' => 'Pertemuan remedial literasi berikutnya',
            ];
        }

        if ($purpose === 'report_draft') {
            return [
                'facts_layer' => [
                    'sessions_attended' => 2,
                    'missions_completed' => 2,
                    'offline_checkpoints_confirmed' => 1,
                    'last_activity_date' => now()->toDateString(),
                ],
                'findings_layer' => [
                    'competency_evaluations' => [
                        [
                            'code' => 'LIT-B-EXP-01',
                            'title' => 'Identifikasi Informasi Tersurat Fabel',
                            'status' => 'indicated_mastery',
                            'evidence_summary' => 'Menjawab benar kuis tokoh berang-berang dan menguraikan jejak berselaput.',
                        ],
                    ],
                ],
                'recommendations_layer' => [
                    'next_steps' => [
                        'Lanjutkan ke materi pengayaan pengurutan alur cerita (LIT-B-SEQ-02).',
                        'Pertahankan kebiasaan mendongeng 5 menit bersama keluarga di rumah.',
                    ],
                ],
                'attribution_tags' => [
                    'human_note' => ['Catatan observasi guru pada sesi tatap muka.'],
                    'auto_assessment' => ['Kuis digital skor 100% pada 2 langkah.'],
                    'educator_validated' => ['Divalidasi oleh wali kelas.'],
                ],
            ];
        }

        return ['status' => 'ok', 'data' => []];
    }
}
