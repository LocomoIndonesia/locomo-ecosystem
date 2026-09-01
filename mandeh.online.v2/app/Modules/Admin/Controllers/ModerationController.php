<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ModerationQueue;
use App\Models\Interaction;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function index()
    {
        $queue = ModerationQueue::with(['interaction.user', 'interaction.entity'])
            ->whereNull('decision')
            ->latest()
            ->paginate(20);
            
        return view('admin::moderation.index', compact('queue'));
    }

    public function decide(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:approve,reject',
            'reason' => 'nullable|string',
        ]);

        $item = ModerationQueue::findOrFail($id);
        $item->update([
            'decision' => $request->decision,
            'reason' => $request->reason,
            'assigned_to' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if ($request->decision === 'approve') {
            $item->interaction->update(['status' => 'public']);
        } else {
            $item->interaction->update(['status' => 'rejected']);
        }

        return back()->with('success', 'Moderation decision recorded.');
    }
}
