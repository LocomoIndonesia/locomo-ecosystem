<?php

namespace App\Modules\Business\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends BaseModel
{
    protected $fillable = [
        'business_id',
        'name',
        'description',
        'price',
        'image',
        'category',
        'is_best_seller',
        'is_available',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
