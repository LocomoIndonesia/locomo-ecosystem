<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Quest;
use App\Models\EscrowTransaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQuestCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_admin_can_create_quest()
    {
        $admin = User::create([
            'name' => 'Root Admin',
            'username' => 'admin_test_' . uniqid(),
            'whatsapp' => '08000000001',
            'role' => 'ADMIN',
            'license_status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($admin)->postJson('/admin/quests', [
            'title' => 'Misi Penjelajah Hutan',
            'description' => 'Jelajahi dan petakan nagari sekitar.',
            'quest_category' => 'DEFAULT',
            'time_window' => 'ALL',
            'target_persona' => 'ALL',
            'stake_amount_silver' => 1000,
            'reward_amount_gold' => 50,
            'time_limit_hours' => 24,
            'min_level' => 1,
            'is_active' => true,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'quest' => [
                'title' => 'Misi Penjelajah Hutan',
                'stake_amount_silver' => 1000,
                'reward_amount_gold' => 50,
            ],
        ]);

        $this->assertDatabaseHas('quests', [
            'title' => 'Misi Penjelajah Hutan',
            'quest_category' => 'DEFAULT',
        ]);
    }

    public function test_admin_can_update_quest()
    {
        $admin = User::create([
            'name' => 'Root Admin',
            'username' => 'admin_test_' . uniqid(),
            'whatsapp' => '08000000002',
            'role' => 'ADMIN',
            'license_status' => 'ACTIVE',
        ]);

        $quest = Quest::create([
            'title' => 'Misi Awal',
            'description' => 'Deskripsi awal.',
            'quest_category' => 'DEFAULT',
            'time_window' => 'ALL',
            'target_persona' => 'ALL',
            'stake_amount_silver' => 500,
            'reward_amount_gold' => 20,
            'time_limit_hours' => 12,
            'min_level' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->putJson("/admin/quests/{$quest->id}", [
            'title' => 'Misi Diperbarui',
            'description' => 'Deskripsi sudah diubah.',
            'quest_category' => 'TIME_WINDOW',
            'time_window' => 'MORNING',
            'target_persona' => 'UMKM',
            'stake_amount_silver' => 800,
            'reward_amount_gold' => 35,
            'time_limit_hours' => 6,
            'min_level' => 2,
            'is_active' => false,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'quest' => [
                'title' => 'Misi Diperbarui',
                'is_active' => false,
            ],
        ]);

        $this->assertDatabaseHas('quests', [
            'id' => $quest->id,
            'title' => 'Misi Diperbarui',
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_delete_quest_when_user_is_actively_working_on_it()
    {
        $admin = User::create([
            'name' => 'Root Admin',
            'username' => 'admin_test_' . uniqid(),
            'whatsapp' => '08000000003',
            'role' => 'ADMIN',
            'license_status' => 'ACTIVE',
        ]);

        $member = User::create([
            'name' => 'Member Aktif',
            'username' => 'member_active_' . uniqid(),
            'whatsapp' => '08123456781',
            'role' => 'MERCHANT',
            'license_status' => 'ACTIVE',
        ]);

        $quest = Quest::create([
            'title' => 'Misi Sedang Berjalan',
            'description' => 'Misi ini sedang dikerjakan user.',
            'quest_category' => 'DEFAULT',
            'time_window' => 'ALL',
            'target_persona' => 'ALL',
            'stake_amount_silver' => 1000,
            'reward_amount_gold' => 50,
            'time_limit_hours' => 24,
            'min_level' => 1,
            'is_active' => true,
        ]);

        // Create an active locked escrow
        EscrowTransaction::create([
            'user_id' => $member->id,
            'quest_id' => $quest->id,
            'staked_silver' => 1000,
            'status' => 'LOCKED',
            'expires_at' => Carbon::now()->addHours(10),
        ]);

        // Try to delete -> should be rejected with 422
        $response = $this->actingAs($admin)->deleteJson("/admin/quests/{$quest->id}");

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        $this->assertDatabaseHas('quests', [
            'id' => $quest->id,
        ]);
    }

    public function test_admin_can_delete_quest_when_no_active_participants()
    {
        $admin = User::create([
            'name' => 'Root Admin',
            'username' => 'admin_test_' . uniqid(),
            'whatsapp' => '08000000004',
            'role' => 'ADMIN',
            'license_status' => 'ACTIVE',
        ]);

        $quest = Quest::create([
            'title' => 'Misi Kosong',
            'description' => 'Misi tanpa peserta aktif.',
            'quest_category' => 'DEFAULT',
            'time_window' => 'ALL',
            'target_persona' => 'ALL',
            'stake_amount_silver' => 500,
            'reward_amount_gold' => 25,
            'time_limit_hours' => 12,
            'min_level' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->deleteJson("/admin/quests/{$quest->id}");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('quests', [
            'id' => $quest->id,
        ]);
    }
}
