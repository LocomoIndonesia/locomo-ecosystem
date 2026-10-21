<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Response extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'attempt_id',
        'step_key',
        'answer',
        'hints_used',
        'auto_result',
        'safety_flag',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'answer' => 'array',
            'hints_used' => 'integer',
            'safety_flag' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class, 'attempt_id');
    }
}
