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
        User::factory()->create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'access_level' => 'admin',
        ]);

        \App\Models\Website::create([
            'name' => 'Google',
            'url' => 'https://google.com',
            'company_name' => 'Google LLC',
            'check_ssl' => true,
        ]);

        \App\Models\Website::create([
            'name' => 'GitHub',
            'url' => 'https://github.com',
            'company_name' => 'GitHub, Inc.',
            'check_ssl' => true,
        ]);
    }
}
