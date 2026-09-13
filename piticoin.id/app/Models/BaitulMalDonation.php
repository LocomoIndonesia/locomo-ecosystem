<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaitulMalDonation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'amount_gold',
        'masked_donor_name',
        'masked_donor_phone',
        'prayer_message',
        'is_anonymous',
        'aamiin_count',
    ];

    protected $casts = [
        'amount_gold' => 'integer',
        'is_anonymous' => 'boolean',
        'aamiin_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
