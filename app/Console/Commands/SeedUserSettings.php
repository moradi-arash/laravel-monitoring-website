<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSetting;
use App\Models\Website;
use Illuminate\Console\Command;

class SeedUserSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:seed {--user-id=1 : The user ID to seed settings for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed user settings from .env file (TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID) and assign existing websites to a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user-id');

        // Find the user by ID
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return Command::FAILURE;
        }

        $this->info("Found user: {$user->name} ({$user->email})");

        // Check if user already has settings
        if ($user->settings) {
            if (!$this->confirm("User already has settings. Do you want to overwrite them?")) {
                $this->info("Operation cancelled.");
                return Command::SUCCESS;
            }
        }

        // Assign existing websites to this user first
        $websitesCount = Website::whereNull('user_id')->count();
        
        if ($websitesCount > 0) {
            $assignedCount = Website::whereNull('user_id')->update(['user_id' => $userId]);
            $this->info("✓ Assigned {$assignedCount} existing websites to user.");
        } else {
            $this->info("No unassigned websites found.");
        }

        // Read Telegram credentials from config
        $telegramBotToken = config('services.telegram.bot_token');
        $telegramChatId = config('services.telegram.chat_id');

        if (!$telegramBotToken || !$telegramChatId) {
            $this->warn("TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID not found in .env file.");
            $this->info("You can set these values in your .env file and run this command again.");
            $this->info("\nMigration completed successfully!");
            $this->info("User {$user->name} now has:");
            $this->info("- {$user->websites()->count()} websites assigned");
            return Command::SUCCESS;
        }

        // Create or update user settings
        $settings = UserSetting::updateOrCreate(
            ['user_id' => $userId],
            [
                'telegram_bot_token' => $telegramBotToken,
                'telegram_chat_id' => $telegramChatId,
            ]
        );

        $this->info("✓ User settings created/updated successfully.");

        $this->info("\nMigration completed successfully!");
        $this->info("User {$user->name} now has:");
        $this->info("- Telegram settings configured");
        $this->info("- {$user->websites()->count()} websites assigned");

        return Command::SUCCESS;
    }
}
