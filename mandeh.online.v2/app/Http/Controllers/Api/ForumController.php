<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumQuestion;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'search']);
    }

    /**
     * Post a new question.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|min:10',
            'category' => 'nullable|string',
        ]);

        $question = ForumQuestion::create([
            'user_id' => $request->user()->id,
            'question' => $request->question,
            'category' => $request->category,
        ]);

        // Standard SQL LIKE search for matching context
        $similarQuestions = ForumQuestion::where('question', 'LIKE', '%' . $request->question . '%')
            ->where('id', '!=', $question->id)
            ->limit(3)
            ->get();

        return response()->json([
            'message' => 'Question posted successfully.',
            'question' => $question,
            'similar_context' => $similarQuestions,
        ], 201);
    }

    /**
     * Search the forum using standard SQL LIKE.
     */
    public function search(Request $request)
    {
        $query = $request->query('q');
        if (!$query) {
            return response()->json(['message' => 'Query is required.'], 400);
        }

        $results = ForumQuestion::where('question', 'LIKE', '%' . $query . '%')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }
}
