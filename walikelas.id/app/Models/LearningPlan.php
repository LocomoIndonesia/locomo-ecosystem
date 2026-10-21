<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPlan extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'program_id',
        'created_at',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(LearningProgram::class, 'program_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PlanVersion::class, 'plan_id');
    }

    public function latestVersion()
    {
        return $this->hasOne(PlanVersion::class, 'plan_id')->latestOfMany('version_no');
    }
}
