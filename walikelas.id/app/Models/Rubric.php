<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubric extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'competency_id',
        'version',
        'levels',
        'origin',
        'review_status',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'levels' => 'array',
            'version' => 'integer',
        ];
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class, 'competency_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class, 'rubric_id');
    }
}
