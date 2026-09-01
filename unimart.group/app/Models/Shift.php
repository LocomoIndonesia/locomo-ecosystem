<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Tenant\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'opened_at',
        'closed_at',
        'starting_cash',
        'actual_ending_cash',
        'system_ending_cash',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Relasi ke User (Kasir yang membuka shift).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Petty Cash Expenses yang terjadi dalam shift ini.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Relasi ke Orders yang dicatat selama shift ini.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
