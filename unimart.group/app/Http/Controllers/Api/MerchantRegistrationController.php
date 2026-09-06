<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MerchantRegistrationController extends Controller
{
    public function registerLead(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255',
            'owner_phone' => 'required|string|max:20',
            'business_type' => 'required|string',
            'agent_id' => 'required'
        ]);

        // 1. Create Tenant
        $tenant = Tenant::create([
            'name' => $request->business_name,
            'domain' => Str::slug($request->business_name),
            'business_type' => $request->business_type,
            'owner_name' => $request->owner_name,
            'owner_email' => $request->owner_email,
            'owner_phone' => $request->owner_phone,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(3),
            'settings' => [
                'verified' => false,
                'agent_id' => $request->agent_id,
                'business_type' => $request->business_type,
                'owner_phone' => $request->owner_phone,
                'owner_name' => $request->owner_name,
            ]
        ]);

        // 2. Create Owner User
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->owner_name,
            'email' => $request->owner_email,
            'role' => 'owner',
            'password' => bcrypt(Str::random(12)),
        ]);

        // 3. Dispatch Email & WhatsApp (Simulated via log)
        $confirmUrl = url('/api/v1/unimart/confirm-lead?email=' . urlencode($request->owner_email));
        Log::info("Simulated WhatsApp & Email sent to owner {$request->owner_name} ({$request->owner_email}). Content: Please confirm your registration by clicking link {$confirmUrl}");

        return response()->json([
            'message' => 'Lead registered successfully in Unimart. Confirmation pending.',
            'tenant_id' => $tenant->id,
            'user_id' => $user->id
        ], 201);
    }

    public function confirmLead(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return response()->json(['message' => 'Email parameter required.'], 400);
        }

        $user = User::where('email', $email)->where('role', 'owner')->first();
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Owner user not found.'], 404);
        }

        $tenant = Tenant::find($user->tenant_id);
        if (!$tenant) {
            return response()->json(['message' => 'Tenant not found.'], 404);
        }

        $settings = $tenant->settings;
        if ($settings['verified'] ?? false) {
            return response()->json(['message' => 'Tenant is already verified.'], 200);
        }

        // Set verified to true
        $settings['verified'] = true;
        $tenant->settings = $settings;
        $tenant->save();

        // Trigger PitiCoin Webhook: lead.confirmed
        $webhookUrl = config('services.piticoin.webhook_url') ?: env('PITICOIN_WEBHOOK_URL', app()->environment('local') ? 'http://piticoin.id.localhost/api/v1/webhooks/unimart-event' : 'https://piticoin.id/api/v1/webhooks/unimart-event');
        $secret = env('WEBHOOK_SECRET', 'piticoin_local_secret_key');

        $payload = [
            'event' => 'lead.confirmed',
            'owner_email' => $user->email,
            'business_name' => $tenant->name,
        ];

        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, $secret);

        $response = Http::withHeaders([
            'X-Unimart-Signature' => $signature,
            'Content-Type' => 'application/json'
        ])->post($webhookUrl, $payload);

        Log::info("Webhook lead.confirmed triggered to PitiCoin.", [
            'url' => $webhookUrl,
            'response_status' => $response->status(),
            'response_body' => $response->body()
        ]);

        return response()->json([
            'message' => 'Lead confirmed. PitiCoin webhook triggered.',
            'webhook_response' => $response->json()
        ], 200);
    }

    public function verifyPayment(Request $request)
    {
        $email = $request->input('email');
        $subscriptionPrice = $request->input('subscription_price', 100000); // Default 100.000 Perak

        $user = User::where('email', $email)->where('role', 'owner')->first();
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Owner user not found.'], 404);
        }

        $tenant = Tenant::find($user->tenant_id);

        // Trigger PitiCoin Webhook: payment.verified
        $webhookUrl = config('services.piticoin.webhook_url') ?: env('PITICOIN_WEBHOOK_URL', app()->environment('local') ? 'http://piticoin.id.localhost/api/v1/webhooks/unimart-event' : 'https://piticoin.id/api/v1/webhooks/unimart-event');
        $secret = env('WEBHOOK_SECRET', 'piticoin_local_secret_key');

        $payload = [
            'event' => 'payment.verified',
            'owner_email' => $user->email,
            'business_name' => $tenant->name,
            'subscription_price' => $subscriptionPrice
        ];

        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, $secret);

        $response = Http::withHeaders([
            'X-Unimart-Signature' => $signature,
            'Content-Type' => 'application/json'
        ])->post($webhookUrl, $payload);

        Log::info("Webhook payment.verified triggered to PitiCoin.", [
            'url' => $webhookUrl,
            'response_status' => $response->status(),
            'response_body' => $response->body()
        ]);

        return response()->json([
            'message' => 'Payment verified. PitiCoin webhook triggered.',
            'webhook_response' => $response->json()
        ], 200);
    }
}
