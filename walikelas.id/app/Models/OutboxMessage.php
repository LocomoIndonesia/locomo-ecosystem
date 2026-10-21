<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboxMessage extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'guardian_relation_id',
        'program_id',
        'channel',
        'template_key',
        'status',
        'policy_check',
        'scheduled_for',
        'sent_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'policy_check' => 'array',
            'scheduled_for' => 'datetime',
            'sent_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function guardianRelation(): BelongsTo
    {
        return $this->belongsTo(GuardianRelation::class, 'guardian_relation_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LearningProgram::class, 'program_id');
    }
}
