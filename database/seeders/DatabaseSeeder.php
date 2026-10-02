<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use App\Models\OrganizationMember;
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

        SiteContent::updateOrCreate(
            ['key' => SiteContent::KEY_PROFILE],
            [
                'title' => 'Profil ASKI-PTMA',
                'body' => 'ASKI-PTMA adalah wadah kolaborasi sentra kekayaan intelektual Perguruan Tinggi Muhammadiyah dan Aisyiyah untuk memperkuat pengelolaan, literasi, dan hilirisasi HKI.',
            ],
        );

        SiteContent::updateOrCreate(
            ['key' => SiteContent::KEY_VISION_MISSION],
            [
                'title' => 'Visi dan Misi',
                'body' => "Visi:\nMenjadi jejaring sentra KI PTMA yang unggul, profesional, dan berdampak.\n\nMisi:\n1. Menguatkan tata kelola sentra KI di lingkungan PTMA.\n2. Mendorong peningkatan pencatatan dan pelindungan HKI.\n3. Memperluas kolaborasi, pendampingan, dan literasi kekayaan intelektual.",
            ],
        );

        SiteContent::updateOrCreate(
            ['key' => SiteContent::KEY_CONTACT],
            [
                'title' => 'Kontak ASKI-PTMA',
                'body' => 'Hubungi pengelola ASKI-PTMA untuk informasi keanggotaan, dokumen, dan layanan kekayaan intelektual.',
                'metadata' => [
                    'email' => 'info@aski-ptma.test',
                    'phone' => '',
                    'address' => '',
                    'instagram' => '',
                    'website' => '',
                ],
            ],
        );

        OrganizationMember::updateOrCreate(
            ['name' => 'Ketua ASKI-PTMA', 'position' => 'Ketua'],
            [
                'description' => 'Koordinator penguatan jejaring dan tata kelola ASKI-PTMA.',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );
    }
}
