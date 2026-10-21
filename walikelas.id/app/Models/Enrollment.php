<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'program_id',
        'learner_id',
        'status',
        'enrolled_at',
        'left_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LearningProgram::class, 'program_id');
    }

    public function learner(): BelongsTo
    {
        return $this->belongsTo(Learner::class, 'learner_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'enrollment_id');
    }

    public function masteryStates(): HasMany
    {
        return $this->hasMany(MasteryState::class, 'enrollment_id');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(EducatorObservation::class, 'enrollment_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'enrollment_id');
    }
}
