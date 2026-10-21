<?php

namespace Database\Seeders;

use App\Models\DynamicComponentCatalog;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            [
                'component_type' => 'SingleChoiceCard',
                'description' => 'Pertanyaan pilihan ganda berbasis teks bacaan dengan petunjuk bertingkat.',
                'schema_definition' => [
                    'type' => 'object',
                    'required' => ['passage', 'question', 'options'],
                    'properties' => [
                        'passage' => ['type' => 'string'],
                        'question' => ['type' => 'string'],
                        'options' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => ['id', 'text', 'is_correct'],
                                'properties' => [
                                    'id' => ['type' => 'string'],
                                    'text' => ['type' => 'string'],
                                    'is_correct' => ['type' => 'boolean'],
                                ],
                            ],
                        ],
                        'hints' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                        'explanation' => ['type' => 'string'],
                    ],
                ],
            ],
            [
                'component_type' => 'SequenceOrderingCard',
                'description' => 'Mengurutkan potongan peristiwa/kalimat cerita naratif secara kronologis.',
                'schema_definition' => [
                    'type' => 'object',
                    'required' => ['prompt', 'items'],
                    'properties' => [
                        'prompt' => ['type' => 'string'],
                        'items' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => ['id', 'text', 'correct_order'],
                                'properties' => [
                                    'id' => ['type' => 'string'],
                                    'text' => ['type' => 'string'],
                                    'correct_order' => ['type' => 'integer'],
                                ],
                            ],
                        ],
                        'hints' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            [
                'component_type' => 'ContextWordMatcher',
                'description' => 'Menjodohkan kosakata baru atau kata sukar dengan makna kontekstualnya.',
                'schema_definition' => [
                    'type' => 'object',
                    'required' => ['prompt', 'pairs'],
                    'properties' => [
                        'prompt' => ['type' => 'string'],
                        'pairs' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => ['word', 'meaning', 'example_sentence'],
                                'properties' => [
                                    'word' => ['type' => 'string'],
                                    'meaning' => ['type' => 'string'],
                                    'example_sentence' => ['type' => 'string'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'component_type' => 'ShortPromptReader',
                'description' => 'Membaca kutipan fabel pendek dan memberikan tanggapan esai singkat / penalaran.',
                'schema_definition' => [
                    'type' => 'object',
                    'required' => ['passage', 'prompt', 'rubric_criteria'],
                    'properties' => [
                        'passage' => ['type' => 'string'],
                        'prompt' => ['type' => 'string'],
                        'rubric_criteria' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                        'min_words' => ['type' => 'integer'],
                    ],
                ],
            ],
        ];

        foreach ($components as $component) {
            DynamicComponentCatalog::updateOrCreate(
                ['component_type' => $component['component_type']],
                [
                    'schema_definition' => $component['schema_definition'],
                    'description' => $component['description'],
                    'is_active' => true,
                    'created_at' => now(),
                ]
            );
        }
    }
}
