<?php

namespace App\Modules\Review\Services;

use App\Modules\Review\Models\Review;

class ReviewService
{
    /**
     * Submit a new review.
     */
    public function submit(array $data): Review
    {
        return Review::updateOrCreate(
            ['business_id' => $data['business_id'], 'user_id' => $data['user_id']],
            ['rating' => $data['rating'], 'comment' => $data['comment']]
        );
    }

    /**
     * Get average rating for a business.
     */
    public function getAverageRating(string $businessId): float
    {
        return (float) Review::where('business_id', $businessId)->avg('rating') ?: 0;
    }
}
