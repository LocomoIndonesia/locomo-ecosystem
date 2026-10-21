<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducatorDecision extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'suggestion_id',
        'subject_type',
        'subject_id',
        'decision',
        'reason',
        'edited_payload',
        'decided_by',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'edited_payload' => 'array',
            'decided_at' => 'datetime',
        ];
    }

    public function suggestion(): BelongsTo
    {
        return $this->belongsTo(AiSuggestion::class, 'suggestion_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'decision_id');
    }
}
