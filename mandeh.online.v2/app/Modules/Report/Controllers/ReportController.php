<?php

namespace App\Modules\Report\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Report\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function create()
    {
        return view('report::create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'location' => 'required',
            'urgency' => 'required|in:low,medium,high,critical',
            'description' => 'required|min:10',
        ]);

        Report::create([
            'user_id' => auth()->id(),
            'location' => $request->location,
            'urgency' => $request->urgency,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Laporan Anda telah berhasil dikirim.');
    }
}
