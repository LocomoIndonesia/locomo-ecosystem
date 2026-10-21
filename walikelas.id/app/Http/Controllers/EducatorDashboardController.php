<?php

namespace App\Http\Controllers;

use App\Models\LearningProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EducatorDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        if (!$user) {
            // Auto login ke lead educator Pak Budi untuk lingkungan dev/demonstrasi
            $user = User::where('email', 'guru.budi@walikelas.id')->first()
                ?? User::first();
            if ($user) {
                Auth::login($user);
            }
        }

        $programs = $user ? $user->activePrograms()
            ->with(['organization', 'activePlanVersion'])
            ->withCount('enrollments')
            ->orderBy('created_at', 'desc')
            ->get() : collect();

        // Ambil metrik ringkas
        $totalSchoolPrograms = $programs->where('mode', 'school')->count();
        $totalPrivatePrograms = $programs->where('mode', 'private')->count();
        $totalLearners = $programs->sum('enrollments_count');

        return Inertia::render('Dashboard', [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile' => $user->educatorProfile,
                ] : null,
            ],
            'programs' => $programs,
            'metrics' => [
                'school_count' => $totalSchoolPrograms,
                'private_count' => $totalPrivatePrograms,
                'total_learners' => $totalLearners,
            ],
        ]);
    }
}
