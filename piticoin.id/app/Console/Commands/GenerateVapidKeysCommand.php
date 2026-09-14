<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpush:vapid {--force : Overwrite existing keys in .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID public and private keys for WebPush notifications and optionally update .env';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keys = VAPID::createVapidKeys();

        $publicKey = $keys['publicKey'];
        $privateKey = $keys['privateKey'];
        $subject = config('webpush.vapid.subject', 'mailto:admin@piticoin.id');

        $this->info('VAPID Keys generated successfully:');
        $this->line("VAPID_PUBLIC_KEY={$publicKey}");
        $this->line("VAPID_PRIVATE_KEY={$privateKey}");
        $this->line("VAPID_SUBJECT={$subject}");

        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            if (!str_contains($envContent, 'VAPID_PUBLIC_KEY') || $this->option('force')) {
                if (str_contains($envContent, 'VAPID_PUBLIC_KEY')) {
                    $envContent = preg_replace('/VAPID_PUBLIC_KEY=.*/', "VAPID_PUBLIC_KEY={$publicKey}", $envContent);
                    $envContent = preg_replace('/VAPID_PRIVATE_KEY=.*/', "VAPID_PRIVATE_KEY={$privateKey}", $envContent);
                } else {
                    $envContent .= "\n# WebPush VAPID Keys\nVAPID_PUBLIC_KEY={$publicKey}\nVAPID_PRIVATE_KEY={$privateKey}\nVAPID_SUBJECT={$subject}\n";
                }

                file_put_contents($envPath, $envContent);
                $this->info('.env updated successfully with VAPID credentials.');
            } else {
                $this->warn('VAPID keys already exist in .env. Use --force to overwrite.');
            }
        }

        return Command::SUCCESS;
    }
}
