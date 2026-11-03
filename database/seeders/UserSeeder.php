<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Buat akun pustakawan untuk autentikasi Week 6.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'pustakawan@del.ac.id'],
            [
                'name'     => 'Pustakawan SIPUS',
                'email'    => 'pustakawan@del.ac.id',
                'password' => Hash::make('password123'),
            ]
        );

        $this->command->info('✅ UserSeeder: Akun pustakawan@del.ac.id berhasil dibuat.');
    }
}
