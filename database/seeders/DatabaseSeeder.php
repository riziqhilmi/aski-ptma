<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = env('ADMIN_SEED_PASSWORD');

        if (blank($password)) {
            throw new \RuntimeException('ADMIN_SEED_PASSWORD harus diatur di file .env sebelum menjalankan seeder Admin.');
        }

        User::updateOrCreate(
            ['email' => 'admin@aski-ptma.test'],
            [
                'name' => 'Administrator ASKI-PTMA',
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        );
    }
}
