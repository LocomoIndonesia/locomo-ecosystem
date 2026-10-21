<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consent extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'guardian_relation_id',
        'program_id',
        'purpose_scope',
        'policy_version',
        'granted_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
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
