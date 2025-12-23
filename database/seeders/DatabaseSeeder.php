<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Website;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create regular user for testing
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        // Create sample websites for testing (assigned to admin user)
        Website::create([
            'url' => 'https://www.google.com',
            'name' => 'Google',
            'is_active' => true,
            'user_id' => $adminUser->id,
        ]);

        Website::create([
            'url' => 'https://www.github.com',
            'name' => 'GitHub',
            'is_active' => true,
            'user_id' => $adminUser->id,
        ]);

        Website::create([
            'url' => 'https://laravel.com',
            'name' => 'Laravel',
            'is_active' => true,
            'user_id' => $adminUser->id,
        ]);

        Website::create([
            'url' => 'https://example-inactive-site.com',
            'name' => 'Inactive Test Site',
            'is_active' => false,
            'user_id' => $adminUser->id,
        ]);
    }
}
