<?php

namespace Database\Seeders;

use App\Models\MstDepartment;
use Illuminate\Database\Seeder;

class MstDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Human Resource',
            'General Affair',
            'Engineering',
            'Civil',
            'Purchasing',
            'Warehouse',
        ];

        foreach ($departments as $name) {
            MstDepartment::query()->updateOrCreate(['name' => $name]);
        }
    }
}
