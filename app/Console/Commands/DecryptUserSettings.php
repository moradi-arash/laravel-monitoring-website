<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Console\Command;

class DecryptUserSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:decrypt {user_id? : The user ID to decrypt settings for} {--json : Output pure JSON without Laravel formatting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decrypt and display user Telegram settings (for debugging or standalone script integration)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $isJson = $this->option('json');

        if ($userId) {
            // Decrypt settings for specific user
            $user = User::with('settings')->find($userId);
            
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return Command::FAILURE;
            }

            if (!$user->settings) {
                $this->warn("User {$user->name} (ID: {$user->id}) has no settings configured.");
                return Command::SUCCESS;
            }

            $data = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'telegram_bot_token' => $user->settings->telegram_bot_token,
                'telegram_chat_id' => $user->settings->telegram_chat_id,
            ];

            if ($isJson) {
                $this->line(json_encode($data));
            } else {
                $this->info("User: {$user->name} (ID: {$user->id})");
                $this->line("Telegram Bot Token: " . ($data['telegram_bot_token'] ?: 'Not configured'));
                $this->line("Telegram Chat ID: " . ($data['telegram_chat_id'] ?: 'Not configured'));
            }

        } else {
            // Decrypt settings for all users
            $users = User::with('settings')->whereHas('settings')->get();
            
            if ($users->isEmpty()) {
                $this->warn("No users with settings found.");
                return Command::SUCCESS;
            }

            $data = [];
            foreach ($users as $user) {
                $data[] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'telegram_bot_token' => $user->settings->telegram_bot_token,
                    'telegram_chat_id' => $user->settings->telegram_chat_id,
                ];
            }

            if ($isJson) {
                $this->line(json_encode($data));
            } else {
                $this->info("Found {$users->count()} users with settings:");
                foreach ($data as $userData) {
                    $this->line("User: {$userData['user_name']} (ID: {$userData['user_id']})");
                    $this->line("  Telegram Bot Token: " . ($userData['telegram_bot_token'] ?: 'Not configured'));
                    $this->line("  Telegram Chat ID: " . ($userData['telegram_chat_id'] ?: 'Not configured'));
                    $this->line("");
                }
            }
        }

        return Command::SUCCESS;
    }
}



