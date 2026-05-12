<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@tesis.local'],
            [
                'client_id'      => null,
                'name'           => 'Super Admin',
                'password'       => Hash::make('admin123'),
                'is_super_admin' => true,
                'active'         => true,
            ]
        )->syncRoles(['Super Admin']);
    }
}
