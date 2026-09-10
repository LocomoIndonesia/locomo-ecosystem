<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OnboardingMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user) {
            // ADMIN root platform bypasses onboarding checks
            if ($user->role === 'ADMIN') {
                return $next($request);
            }

            $step = intval($user->onboarding_step);

            // If user has not completed onboarding (step < 6)
            if ($step < 6) {
                // Allow requests to onboarding endpoints or logout
                if ($request->is('onboarding*') || $request->is('logout') || $request->is('api/payment/webhook')) {
                    return $next($request);
                }

                // Redirect dynamically based on the current step
                switch ($step) {
                    case 2:
                        return redirect()->route('onboarding.awakening');
                    case 3:
                        return redirect()->route('onboarding.path');
                    case 4:
                        return redirect()->route('onboarding.brotherhood');
                    case 5:
                        return redirect()->route('onboarding.pledge');
                    default:
                        // Default to Step 2 (Awakening) if user is registered but step is in between
                        return redirect()->route('onboarding.awakening');
                }
            } else {
                // If onboarding is completed and user tries to access onboarding page, redirect to dashboard
                if ($request->is('onboarding*')) {
                    return redirect()->route('dashboard');
                }
            }
        }

        return $next($request);
    }
}
