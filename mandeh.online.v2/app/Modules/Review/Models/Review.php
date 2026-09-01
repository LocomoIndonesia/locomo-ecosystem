<?php

namespace App\Modules\Review\Models;

use App\Models\BaseModel;
use App\Models\User;
use App\Modules\Business\Models\Business;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'user_id',
        'rating',
        'comment',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'rating' => 'integer',
    ];

    /**
     * Get the business that was reviewed.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user who wrote the review.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
