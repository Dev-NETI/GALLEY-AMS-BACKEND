<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece',       'abbreviation' => 'pcs'],
            ['name' => 'Kilogram',    'abbreviation' => 'kg'],
            ['name' => 'Gram',        'abbreviation' => 'g'],
            ['name' => 'Liter',       'abbreviation' => 'L'],
            ['name' => 'Milliliter',  'abbreviation' => 'mL'],
            ['name' => 'Set',         'abbreviation' => 'set'],
            ['name' => 'Pair',        'abbreviation' => 'pair'],
            ['name' => 'Box',         'abbreviation' => 'box'],
            ['name' => 'Pack',        'abbreviation' => 'pack'],
            ['name' => 'Ream',        'abbreviation' => 'ream'],
            ['name' => 'Roll',        'abbreviation' => 'roll'],
            ['name' => 'Dozen',       'abbreviation' => 'doz'],
            ['name' => 'Meter',       'abbreviation' => 'm'],
            ['name' => 'Unit',        'abbreviation' => 'unit'],
            ['name' => 'Bottle',      'abbreviation' => 'btl'],
            ['name' => 'Can',         'abbreviation' => 'can'],
            ['name' => 'Sack',        'abbreviation' => 'sack'],
            ['name' => 'Tray',        'abbreviation' => 'tray'],
            ['name' => 'Tub',         'abbreviation' => 'tub'],
            ['name' => 'Sachet',      'abbreviation' => 'sachet'],
            ['name' => 'Tin',         'abbreviation' => 'tin'],
            ['name' => 'Packet',      'abbreviation' => 'packet'],
            ['name' => 'Canister',    'abbreviation' => 'canister'],
            ['name' => 'Case',        'abbreviation' => 'case'],
            ['name' => 'Carboy',      'abbreviation' => 'cby'],
            ['name' => 'Bundle',      'abbreviation' => 'bundle'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit['name']], $unit);
        }
    }
}
