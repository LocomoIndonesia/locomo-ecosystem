<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LearningProgram extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'mode',
        'organization_id',
        'title',
        'goal_summary',
        'grade_level',
        'period_start',
        'period_end',
        'planned_session_count',
        'status',
        'active_plan_version_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'planned_session_count' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function educators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'program_educators', 'program_id', 'user_id')
            ->withPivot(['id', 'role', 'started_at', 'ended_at']);
    }

    public function activeEducators(): BelongsToMany
    {
        return $this->educators()->wherePivotNull('ended_at');
    }

    public function learningPlan(): HasOne
    {
        return $this->hasOne(LearningPlan::class, 'program_id');
    }

    public function activePlanVersion(): BelongsTo
    {
        return $this->belongsTo(PlanVersion::class, 'active_plan_version_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'program_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'program_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'program_id');
    }

    public function outboxMessages(): HasMany
    {
        return $this->hasMany(OutboxMessage::class, 'program_id');
    }
}
