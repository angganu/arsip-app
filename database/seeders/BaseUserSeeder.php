<?php

namespace Database\Seeders;

use App\Models\BaseRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BaseUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managerRole = BaseRole::query()->where('name', 'manager')->firstOrFail();
        $adminRole = BaseRole::query()->where('name', 'administrator')->firstOrFail();

        $manager = User::query()->updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Sample Manager',
                'password' => Hash::make('password123'),
            ]
        );

        $administrator = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Sample Administrator',
                'password' => Hash::make('password123'),
            ]
        );

        $manager->roles()->syncWithoutDetaching([$managerRole->id]);
        $administrator->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
