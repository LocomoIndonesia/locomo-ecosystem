<?php

namespace App\Modules\Business\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'category',
        'description',
        'geolocation',
        'contact',
        'verification_status',
        'operational_status',
    ];

    protected $casts = [
        'geolocation' => 'array',
        'contact' => 'array',
    ];

    /**
     * Get the user that owns the business.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the reviews for the business.
     */
    public function reviews()
    {
        return $this->hasMany(\App\Modules\Review\Models\Review::class);
    }

    /**
     * Get the products for the business.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the menu items for the business.
     */
    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Get the average rating attribute.
     */
    public function getAverageRatingAttribute(): float
    {
        return (float) $this->reviews()->avg('rating') ?: 0;
    }
}
