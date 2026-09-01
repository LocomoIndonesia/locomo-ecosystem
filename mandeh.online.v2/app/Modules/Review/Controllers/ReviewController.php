<?php

namespace App\Modules\Review\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Review\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Submit a review for a business.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'business_id' => 'required|uuid|exists:businesses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5',
        ]);

        $review = $this->reviewService->submit([
            'business_id' => $request->business_id,
            'user_id' => auth()->id() ?? $request->user_id, // Fallback for testing
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Review submitted successfully',
            'data' => $review,
        ]);
    }
}
