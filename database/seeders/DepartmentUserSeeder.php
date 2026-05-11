<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DepartmentUserSeeder extends Seeder
{
    public function run(): void
    {
        $dept = fn(string $code) => Department::where('code', $code)->first()?->id;

        $defaultPermissions = json_encode([
            'categories',
            'suppliers',
            'items',
            'item-assets',
            'asset-assignments',
            'inventory-stocks',
            'stock-receivals',
            'stock-issuances',
        ]);

        $users = [
            [
                'name'          => 'GOD Administrator',
                'email'         => 'god@neti.com.ph',
                'password'      => Hash::make('password'),
                'user_type'     => 'employee',
                'department_id' => $dept('GOD'),
                'permissions'   => $defaultPermissions,
            ],
            [
                'name'          => 'Sherwin',
                'email'         => 'sherwin.roxas@neti.com.ph',
                'password'      => Hash::make('password'),
                'user_type'     => 'employee',
                'department_id' => $dept('GOD'),
                'permissions'   => $defaultPermissions,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
