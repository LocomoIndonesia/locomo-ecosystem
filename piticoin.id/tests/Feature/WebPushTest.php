<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebPushSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WebPushTest extends TestCase
{
    use DatabaseTransactions;

    protected function createTestUser(): User
    {
        return User::create([
            'name' => 'WebPush Tester',
            'username' => 'webpush_' . uniqid(),
            'whatsapp' => '08' . rand(100000000, 999999999),
            'role' => 'GUEST',
            'license_status' => 'ACTIVE',
            'onboarding_step' => 6,
        ]);
    }

    public function test_can_get_vapid_public_key(): void
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->getJson('/api/webpush/vapid-key');

        $response->assertStatus(200);
        $response->assertJsonStructure(['publicKey']);
        $this->assertNotEmpty($response->json('publicKey'));
    }

    public function test_can_subscribe_to_webpush(): void
    {
        $user = $this->createTestUser();

        $payload = [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-device-token-' . uniqid(),
            'keys' => [
                'p256dh' => 'BCJ6vN2GjL8jGfT...dummyKey...',
                'auth' => 'dummyAuthSecret123',
            ],
            'contentEncoding' => 'aes128gcm',
        ];

        $response = $this->actingAs($user)->postJson('/api/webpush/subscribe', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('web_push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => $payload['endpoint'],
            'public_key' => $payload['keys']['p256dh'],
            'auth_token' => $payload['keys']['auth'],
        ]);
    }

    public function test_can_unsubscribe_from_webpush(): void
    {
        $user = $this->createTestUser();

        $sub = WebPushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-device-token-' . uniqid(),
            'public_key' => 'BCJ6vN2GjL8jGfT...dummyKey...',
            'auth_token' => 'dummyAuthSecret123',
            'content_encoding' => 'aes128gcm',
        ]);

        $response = $this->actingAs($user)->postJson('/api/webpush/unsubscribe', [
            'endpoint' => $sub->endpoint,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('web_push_subscriptions', [
            'id' => $sub->id,
        ]);
    }

    public function test_send_test_notification_returns_400_when_no_active_subscription(): void
    {
        $user = $this->createTestUser();

        $response = $this->actingAs($user)->postJson('/api/webpush/test');

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
