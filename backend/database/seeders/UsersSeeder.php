<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Tpm\Shared\Role;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Demo Operator', 'email' => 'operator@example.com', 'role' => Role::Operator],
            ['name' => 'Demo Technician', 'email' => 'technician@example.com', 'role' => Role::Technician],
            ['name' => 'Demo Manager', 'email' => 'manager@example.com', 'role' => Role::Manager],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role']->value,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
