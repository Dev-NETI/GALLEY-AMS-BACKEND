<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $dept = fn (string $code) => Department::where('code', $code)->first()?->id;

        $employees = [
            // GOD
            ['first_name' => 'Rosa',    'last_name' => 'Villanueva',  'department_id' => $dept('GOD'),  'position' => 'Head Chef',     'phone' => '0921-001-0001', 'status' => 'active'],
            ['first_name' => 'Carlos',  'last_name' => 'Fernandez',   'department_id' => $dept('GOD'),  'position' => 'Sous Chef',     'phone' => '0921-001-0002', 'status' => 'active'],
            ['first_name' => 'Elena',   'last_name' => 'Castillo',    'department_id' => $dept('GOD'),  'position' => 'Kitchen Staff', 'phone' => '0921-001-0003', 'status' => 'active'],
        ];

        foreach ($employees as $employee) {
            if ($employee['department_id']) {
                Employee::firstOrCreate(
                    [
                        'first_name'    => $employee['first_name'],
                        'last_name'     => $employee['last_name'],
                        'department_id' => $employee['department_id'],
                    ],
                    $employee
                );
            }
        }
    }
}
