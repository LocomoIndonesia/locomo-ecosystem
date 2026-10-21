<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competency extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'code',
        'domain',
        'title',
        'description',
        'grade_band',
        'prerequisite_id',
        'version',
        'review_status',
        'source_ref',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
        ];
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Competency::class, 'prerequisite_id');
    }

    public function rubrics(): HasMany
    {
        return $this->hasMany(Rubric::class, 'competency_id');
    }

    public function activeRubric()
    {
        return $this->hasOne(Rubric::class, 'competency_id')->latestOfMany('version');
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class, 'competency_id');
    }
}
