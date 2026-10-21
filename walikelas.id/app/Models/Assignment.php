<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'program_id',
        'enrollment_id',
        'mission_id',
        'plan_version_id',
        'status',
        'requires_guardian_confirmation',
        'requires_educator_review',
        'due_at',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'requires_guardian_confirmation' => 'boolean',
            'requires_educator_review' => 'boolean',
            'due_at' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LearningProgram::class, 'program_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    public function planVersion(): BelongsTo
    {
        return $this->belongsTo(PlanVersion::class, 'plan_version_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(AssignmentTransition::class, 'assignment_id');
    }

    public function launchTokens(): HasMany
    {
        return $this->hasMany(LaunchToken::class, 'assignment_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class, 'assignment_id');
    }

    public function guardianConfirmations(): HasMany
    {
        return $this->hasMany(GuardianConfirmation::class, 'assignment_id');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(EducatorObservation::class, 'assignment_id');
    }
}
