<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'ledger_id',
        'wallet_id',
        'currency',
        'type',
        'amount',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function wallet(): BelongsTo
    {
        // wallet_id corresponds to users(id) essentially, or specific wallet table depending on architecture.
        // Based on our migration, it links to users(id). Null implies system vault.
        return $this->belongsTo(User::class, 'wallet_id');
    }
}
