<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'auth_provider',
        'auth_provider_id',
        'role',
        'is_verified',
        'trust_score',
        'device_fingerprint',
        'upline_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function upline()
    {
        return $this->belongsTo(User::class, 'upline_id');
    }

    public function downlines()
    {
        return $this->hasMany(User::class, 'upline_id');
    }

    public function referredEntities()
    {
        return $this->hasMany(Entity::class, 'upline_id');
    }

    public function entities()
    {
        return $this->hasMany(Entity::class, 'owner_id');
    }

    public function vanguardParticipant()
    {
        return $this->hasOne(\App\Modules\Vanguard\Models\VanguardParticipant::class, 'user_id');
    }

    public function logTrackings()
    {
        return $this->hasMany(UserLogTracking::class, 'user_id');
    }
}
