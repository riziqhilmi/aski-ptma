<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->exists()) {
            throw new \RuntimeException('DatabaseSeeder demo hanya boleh dijalankan pada database kosong. Gunakan migrate:fresh --seed setelah memastikan target database benar.');
        }

        User::create([
            'name' => 'Administrator ASKI-PTMA',
            'email' => 'Admin@aski-ptma.test',
            'password' => Hash::make('Password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->call(DemoDataSeeder::class);
    }
}
