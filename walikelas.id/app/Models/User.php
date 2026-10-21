<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone_e164',
        'password_hash',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function educatorProfile(): HasOne
    {
        return $this->hasOne(EducatorProfile::class, 'user_id');
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'created_by');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(LearningProgram::class, 'program_educators', 'user_id', 'program_id')
            ->withPivot(['id', 'role', 'started_at', 'ended_at']);
    }

    public function activePrograms(): BelongsToMany
    {
        return $this->programs()->wherePivotNull('ended_at');
    }

    public function guardianRelations(): HasMany
    {
        return $this->hasMany(GuardianRelation::class, 'guardian_user_id');
    }

    public function guardianInvitations(): HasMany
    {
        return $this->hasMany(GuardianInvitation::class, 'created_by');
    }
}
