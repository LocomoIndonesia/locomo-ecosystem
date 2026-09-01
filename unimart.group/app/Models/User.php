<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Tenant\BelongsToTenant;

#[Fillable(['name', 'email', 'phone', 'password', 'tenant_id', 'pin', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, BelongsToTenant;

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

    /**
     * Mengecek apakah user memiliki role tertentu.
     */
    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    public function isChef(): bool
    {
        return $this->hasRole('chef');
    }

    public function isWaiter(): bool
    {
        return $this->hasRole('waiter');
    }

    public function isAdminGudang(): bool
    {
        return $this->hasRole('admin_gudang');
    }

    public function isKurir(): bool
    {
        return $this->hasRole('kurir');
    }
}
