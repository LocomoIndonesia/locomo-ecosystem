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
     * Fallback deterministik cerdas untuk ekstraksi silabus dan wizard (TI-14, TAT-03, TAT-18)
     */
    protected function mockDeterministicResponse(string $purpose, string $prompt): array
    {
        if ($purpose === 'plan_extract') {
            $ambiguities = [];
            // Deteksi teks yang tidak menyebutkan frekuensi asesmen atau durasi
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

        return ['status' => 'ok', 'data' => []];
    }
}
