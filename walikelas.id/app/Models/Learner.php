<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Learner extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'display_name',
        'grade_level',
        'birth_year',
        'avatar_id',
        'is_synthetic',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'birth_year' => 'integer',
            'is_synthetic' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'learner_id');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(LearningProgram::class, 'enrollments', 'learner_id', 'program_id')
            ->withPivot(['id', 'status', 'enrolled_at', 'left_at'])
            ->withTimestamps(false);
    }

    public function guardianRelations(): HasMany
    {
        return $this->hasMany(GuardianRelation::class, 'learner_id');
    }
}
