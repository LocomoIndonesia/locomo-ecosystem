<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'program_id',
        'payload',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public static function log(
        string $actorType,
        ?string $actorId,
        string $action,
        ?string $subjectType = null,
        ?string $subjectId = null,
        ?string $programId = null,
        array $payload = []
    ): self {
        return self::create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'program_id' => $programId,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
