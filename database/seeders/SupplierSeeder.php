<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $godId = Department::where('name', 'GOD')->value('id');

        $suppliers = [
            'Ekol Trading',
            'JCL Fruit and Vegetables Wholesaling',
            'JIgs and Jen Consumer Goods Trading',
            'TND Marketing',
            'SJ Gas',
            'Gerabelle Foods',
            'GenerationHope Inc.',
            'Oxychem',
            'V.M Liwanag Industries Corp',
        ];

        foreach ($suppliers as $name) {
            Supplier::firstOrCreate(
                ['name' => $name],
                ['department_id' => 1],
            );
        }
    }
}
