<?php

namespace App\Modules\Business\Services;

use App\Modules\Business\Models\Business;
use Illuminate\Support\Str;

class BusinessService
{
    /**
     * Create a new business listing.
     */
    public function create(array $data): Business
    {
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return Business::create($data);
    }

    /**
     * Get all verified businesses.
     */
    public function getVerifiedBusinesses()
    {
        return Business::where('verification_status', 'verified')
            ->where('operational_status', 'active')
            ->get();
    }
}
