<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin & guru: firstOrCreate agar password yang sudah diganti di
        // produksi tidak ke-reset tiap migrate --seed diulang.
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'nis' => 'ADM001',
                'name' => 'Administrator',
                'class' => null,
                'role' => User::ROLE_ADMIN,
                'email' => null,
                'password' => Hash::make('password'),
                'email_verified_at' => null,
            ]
        );

        User::firstOrCreate(
            ['username' => 'guru'],
            [
                'nis' => 'GRU001',
                'name' => 'Guru',
                'class' => null,
                'role' => User::ROLE_GURU,
                'email' => null,
                'password' => Hash::make('password'),
                'email_verified_at' => null,
            ]
        );

        $students = [
            ['nis' => '18034', 'name' => 'Budi Santoso', 'class' => 'XII IPA 1'],
            ['nis' => '18035', 'name' => 'Siti Aisyah', 'class' => 'XII IPA 1'],
            ['nis' => '18036', 'name' => 'Andi Pratama', 'class' => 'XI IPS 2'],
            ['nis' => '18037', 'name' => 'Dewi Lestari', 'class' => 'X TKJ 1'],
        ];

        foreach ($students as $student) {
            User::updateOrCreate(
                ['nis' => $student['nis']],
                [
                    'name' => $student['name'],
                    'class' => $student['class'],
                    'role' => User::ROLE_SISWA,
                    'email' => null,
                    'password' => null,
                ]
            );
        }
    }
}
