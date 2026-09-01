<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name',
        'whatsapp',
        'role',
        'upline_id',
        'grand_upline_id',
        'password',
        'faction',
        'role_class',
        'license_type',
        'license_status',
        'onboarding_step',
        'level',
        'experience_points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function upline()
    {
        return $this->belongsTo(User::class, 'upline_id');
    }

    public function grandUpline()
    {
        return $this->belongsTo(User::class, 'grand_upline_id');
    }

    public function downlines()
    {
        return $this->hasMany(User::class, 'upline_id');
    }

    public function grandDownlines()
    {
        return $this->hasMany(User::class, 'grand_upline_id');
    }

    public function escrowTransactions()
    {
        return $this->hasMany(EscrowTransaction::class, 'user_id');
    }

    public function agentMissions()
    {
        return $this->hasMany(AgentMission::class, 'user_id');
    }
}
