<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\StockReceival;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventoryStockSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $dept  = fn(string $code) => Department::where('code', $code)->first()?->id;
        $item  = fn(string $name) => Item::where('name', $name)->first()?->id;
        $supp  = fn(string $name) => Supplier::where('name', 'like', "%{$name}%")->first()?->id;

        $stocks = [
            // ── GOD: Food ─────────────────────────────────────────────────
            ['item' => 'Chicken Breast',  'dept' => 'GOD', 'qty' => 50, 'supplier' => 'FreshMart', 'unit_cost' => 180.00],
            ['item' => 'Liempo',          'dept' => 'GOD', 'qty' => 40, 'supplier' => 'FreshMart', 'unit_cost' => 220.00],
            ['item' => 'Fish Fillet',     'dept' => 'GOD', 'qty' => 30, 'supplier' => 'FreshMart', 'unit_cost' => 150.00],
            ['item' => 'Long Grain Rice', 'dept' => 'GOD', 'qty' => 10, 'supplier' => 'FreshMart', 'unit_cost' => 2800.00],
            ['item' => 'Cooking Oil',     'dept' => 'GOD', 'qty' => 20, 'supplier' => 'FreshMart', 'unit_cost' => 95.00],
            ['item' => 'Dishwashing Liquid', 'dept' => 'GOD', 'qty' => 12, 'supplier' => 'FreshMart', 'unit_cost' => 65.00],
        ];

        foreach ($stocks as $s) {
            $itemId = $item($s['item']);
            $deptId = $dept($s['dept']);

            if (! $itemId || ! $deptId) {
                continue;
            }

            // Upsert inventory stock
            InventoryStock::updateOrCreate(
                ['item_id' => $itemId, 'department_id' => $deptId],
                ['quantity' => $s['qty']]
            );

            // Record a matching stock receival for audit trail
            StockReceival::create([
                'item_id'       => $itemId,
                'department_id' => $deptId,
                'quantity'      => $s['qty'],
                'unit_cost'     => $s['unit_cost'],
                'supplier_id'   => $supp($s['supplier']),
                'received_by'   => $admin->id,
                'received_at'   => now()->subDays(rand(7, 60)),
                'notes'         => 'Initial stock load',
            ]);
        }
    }
}
