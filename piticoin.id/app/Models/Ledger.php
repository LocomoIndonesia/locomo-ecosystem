<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Ledger extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'reference_type',
        'reference_id',
        'description',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
