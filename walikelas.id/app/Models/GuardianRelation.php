<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuardianRelation extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'learner_id',
        'guardian_user_id',
        'relation_type',
        'status',
        'verified_via',
        'educator_confirmed_by',
        'verified_at',
        'revoked_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'revoked_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function learner(): BelongsTo
    {
        return $this->belongsTo(Learner::class, 'learner_id');
    }

    public function guardianUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_user_id');
    }

    public function educatorConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'educator_confirmed_by');
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class, 'guardian_relation_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(GuardianInvitation::class, 'guardian_relation_id');
    }

    public function confirmations(): HasMany
    {
        return $this->hasMany(GuardianConfirmation::class, 'guardian_relation_id');
    }

    public function outboxMessages(): HasMany
    {
        return $this->hasMany(OutboxMessage::class, 'guardian_relation_id');
    }
}
