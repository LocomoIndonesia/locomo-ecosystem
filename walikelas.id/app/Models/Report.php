<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'program_id',
        'enrollment_id',
        'version_no',
        'status',
        'facts_layer',
        'findings_layer',
        'recommendations_layer',
        'attribution_tags',
        'approved_by',
        'approved_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'version_no' => 'integer',
            'facts_layer' => 'array',
            'findings_layer' => 'array',
            'recommendations_layer' => 'array',
            'attribution_tags' => 'array',
            'approved_at' => 'datetime',
            'created_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(ReportSource::class, 'report_id');
    }
}
