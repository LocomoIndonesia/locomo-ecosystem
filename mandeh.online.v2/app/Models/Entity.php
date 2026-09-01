<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    use HasUuids;

    protected $table = 'entities';

    protected $fillable = [
        'owner_id',
        'upline_id',
        'name',
        'slug',
        'type',
        'description',
        'price_range',
        'lat',
        'lng',
        'qr_token',
        'qr_code_path',
        'subscription_package',
        'payment_proof_path',
        'is_active',
        'payment_verified_at',
    ];

    protected $appends = ['google_maps_url'];

    /**
     * Scope a query to only include active businesses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the owner that owns the business.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the upline user who registered the business.
     */
    public function upline(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_id');
    }

    /**
     * Get the Google Maps URL for the business.
     */
    public function getGoogleMapsUrlAttribute(): string
    {
        return "https://www.google.com/maps/dir/?api=1&destination={$this->lat},{$this->lng}";
    }

    /**
     * Get the interactions for the entity.
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class, 'entity_id');
    }

    /**
     * Get the business meta for the entity.
     */
    public function businessMeta()
    {
        return $this->hasOne(BusinessMeta::class, 'entity_id');
    }

    /**
     * Get the associations for the entity.
     */
    public function associations(): HasMany
    {
        return $this->hasMany(EntityAssociation::class, 'entity_id');
    }
}
