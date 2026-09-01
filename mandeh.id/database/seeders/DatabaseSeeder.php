<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Seed a test user
        User::factory()->create([
            'name' => 'Budi Pandeka',
            'email' => 'budi@mandeh.id',
            'password' => bcrypt('password'),
        ]);

        // Seed SSO clients
        \DB::table('sso_clients')->insert([
            [
                'client_id' => 'locomo-web-client',
                'client_secret' => hash('sha256', 'locomo-secret'), // Client secret hashed
                'name' => 'Locomo Learning Platform',
                'redirect_uris' => 'http://locomo-web.localhost/callback',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 'mandeh-online-client',
                'client_secret' => hash('sha256', 'mandeh-online-secret'),
                'name' => 'Mandeh Online Tourism & Community Portal',
                'redirect_uris' => 'http://mandeh.online.localhost/callback',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
