<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'neutel@admin.local'],
            [
                'name' => 'Neutel',
                'password' => Hash::make('12345678'),
                'estado' => true,
            ]
        );

        $adminRole = Role::findOrCreate('Administrador', 'web');
        $user->syncRoles([$adminRole]);
    }
}
