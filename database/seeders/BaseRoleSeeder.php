<?php

namespace Database\Seeders;

use App\Models\BaseRole;
use Illuminate\Database\Seeder;

class BaseRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BaseRole::query()->updateOrCreate(['name' => 'manager']);
        BaseRole::query()->updateOrCreate(['name' => 'administrator']);
    }
}
