<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name'        => 'Galley Operations Department',
                'code'        => 'GOD',
                'description' => 'Manages food preparation, kitchen equipment, and food supply inventories.',
            ],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
