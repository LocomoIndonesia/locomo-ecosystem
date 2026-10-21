<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiRun extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'purpose',
        'program_id',
        'prompt_version',
        'model_name',
        'input_refs',
        'output_payload',
        'status',
        'latency_ms',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'input_refs' => 'array',
            'output_payload' => 'array',
            'latency_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LearningProgram::class, 'program_id');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(AiSuggestion::class, 'ai_run_id');
    }
}
