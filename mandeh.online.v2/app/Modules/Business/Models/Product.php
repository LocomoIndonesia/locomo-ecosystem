<?php

namespace App\Modules\Business\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends BaseModel
{
    protected $fillable = [
        'business_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
