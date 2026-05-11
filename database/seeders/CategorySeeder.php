<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $dept = fn (string $code) => Department::where('code', $code)->first()?->id;

        $categories = [
            // GOD — Kitchen & Food
            ['name' => 'BEEF',             'description' => 'Beef cuts and beef products',                  'dept' => 'GOD'],
            ['name' => 'PORK',             'description' => 'Pork cuts and pork products',                  'dept' => 'GOD'],
            ['name' => 'CHICKEN',          'description' => 'Chicken and poultry products',                 'dept' => 'GOD'],
            ['name' => 'SEAFOOD',          'description' => 'Fish, shrimp, squid, and other seafood',       'dept' => 'GOD'],
            ['name' => 'PROCESSED MEATS',  'description' => 'Hotdog, ham, tocino, longganisa, and similar', 'dept' => 'GOD'],
            ['name' => 'VEGETABLES',       'description' => 'Fresh vegetables and leafy greens',            'dept' => 'GOD'],
            ['name' => 'FRESH FRUITS',     'description' => 'Fresh fruits',                                 'dept' => 'GOD'],
            ['name' => 'RICE',             'description' => 'Rice varieties and rice products',             'dept' => 'GOD'],
            ['name' => 'EGGS',             'description' => 'Chicken eggs and other egg products',          'dept' => 'GOD'],
            ['name' => 'CONDIMENTS',       'description' => 'Sauces, dressings, and condiments',           'dept' => 'GOD'],
            ['name' => 'SEASONING',        'description' => 'Spices, seasonings, and flavor enhancers',    'dept' => 'GOD'],
            ['name' => 'NOODLES & PASTA',  'description' => 'Noodles, pasta, and similar dry goods',       'dept' => 'GOD'],
            ['name' => 'OIL',              'description' => 'Cooking oil and related fats',                'dept' => 'GOD'],
            ['name' => 'SUGAR / OTHERS',   'description' => 'Sugar, flour, and other dry staples',         'dept' => 'GOD'],
            ['name' => 'CANNED GOODS',     'description' => 'Canned and preserved food products',          'dept' => 'GOD'],
            ['name' => 'SUPPLIES',         'description' => 'Kitchen and galley supplies',                 'dept' => 'GOD'],
            ['name' => 'OTHERS',           'description' => 'Miscellaneous items',                         'dept' => 'GOD'],
            ['name' => 'CLEANING MATERIALS', 'description' => 'Detergents, disinfectants, and cleaning tools', 'dept' => 'GOD'],
            ['name' => 'BOTTLED WATER',    'description' => 'Bottled and purified drinking water',         'dept' => 'GOD'],
        ];

        foreach ($categories as $cat) {
            $deptId = $dept($cat['dept']);
            Category::firstOrCreate(
                ['name' => $cat['name'], 'department_id' => $deptId],
                [
                    'description'   => $cat['description'],
                    'department_id' => $deptId,
                ]
            );
        }
    }
}
