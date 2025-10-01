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

        User::create([
            'name' => 'Arash Moradi',
            'email' => 'arash.moradi89@gmail.com',
            'password' => bcrypt('LaraApp@8731'),
        ]);

        // Create sample websites for testing
        Website::create([
            'url' => 'https://www.google.com',
            'name' => 'Google',
            'is_active' => true,
        ]);

        Website::create([
            'url' => 'https://www.github.com',
            'name' => 'GitHub',
            'is_active' => true,
        ]);

        Website::create([
            'url' => 'https://laravel.com',
            'name' => 'Laravel',
            'is_active' => true,
        ]);

        Website::create([
            'url' => 'https://example-inactive-site.com',
            'name' => 'Inactive Test Site',
            'is_active' => false,
        ]);
    }
}
