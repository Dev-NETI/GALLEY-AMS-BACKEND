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
            'units',
            'canteen-utensils',
            'vip-dining-utensils',
            'storage-room-fdc',
            'inventory-stocks',
            'galley-inventory',
            'bottled-water-inventory',
            'cleaning-materials-inventory',
            'stock-receivals',
            'stock-issuances',
            'consumable-scanner',
        ]);

        $scannerDefaultPermissions = json_encode([
            'consumable-scanner'
        ]);

        $users = [
            [
                'name'          => 'Ariel Sotto',
                'email'         => 'ariel.sotto@neti.com.ph',
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
            [
                'name'          => 'Scanner Boy',
                'email'         => 'cosmicsher96@gmail.com',
                'password'      => Hash::make('password'),
                'user_type'     => 'scanner',
                'department_id' => $dept('GOD'),
                'permissions'   => $scannerDefaultPermissions,
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
