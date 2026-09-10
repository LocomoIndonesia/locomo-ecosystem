<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    /**
     * Get messages for current authenticated Saudagar.
     */
    public function getMyConsultation(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Mark admin messages as read when saudagar opens it
        Consultation::where('user_id', $user->id)
            ->where('sender_role', 'ADMIN')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Consultation::where('user_id', $user->id)
            ->with('sender:id,name,username,role')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    /**
     * Saudagar replies or sends a consultation question to Admin.
     */
    public function replyToAdmin(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
            'subject' => 'nullable|string|max:255',
        ]);

        $msg = Consultation::create([
            'user_id' => $user->id,
            'sender_id' => $user->id,
            'sender_role' => 'SAUDAGAR',
            'subject' => $request->input('subject', 'Tanya Admin / Konsultasi'),
            'message' => $request->input('message'),
            'is_read' => false,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg->load('sender:id,name,username,role'),
            ]);
        }

        return back()->with('success', 'Pesan Anda telah diteruskan ke Administrator Platform.');
    }
}
