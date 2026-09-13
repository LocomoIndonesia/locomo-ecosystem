<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    /**
     * Display Terms and Conditions page.
     */
    public function terms(): Response
    {
        return Inertia::render('Legal/Terms', [
            'appName' => 'PitiCoin.id',
            'lastUpdated' => '13 September 2026',
        ]);
    }

    /**
     * Display Privacy Policy page (compliant with UU PDP No. 27/2022).
     */
    public function privacy(): Response
    {
        return Inertia::render('Legal/Privacy', [
            'appName' => 'PitiCoin.id',
            'lastUpdated' => '13 September 2026',
        ]);
    }
}
