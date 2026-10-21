<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanVersion extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'plan_id',
        'version_no',
        'status',
        'source_type',
        'content',
        'ambiguities',
        'approved_by',
        'approved_at',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'version_no' => 'integer',
            'content' => 'array',
            'ambiguities' => 'array',
            'approved_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(LearningPlan::class, 'plan_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(PlanSource::class, 'plan_version_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'plan_version_id');
    }
}
