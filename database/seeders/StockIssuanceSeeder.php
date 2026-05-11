<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\StockIssuance;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockIssuanceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $dept  = fn (string $code) => Department::where('code', $code)->first();
        $item  = fn (string $name) => Item::where('name', $name)->first();
        $emp   = fn (string $id) => Employee::where('employee_id', $id)->first();

        $issuances = [
            // ── GOD: Food to department ────────────────────────────────────
            [
                'item'           => 'Chicken Breast',
                'dept_code'      => 'GOD',
                'quantity'       => 10,
                'issued_to_type' => 'department',
                'issued_to_id'   => null,
                'dept_target'    => 'GOD',
                'purpose'        => 'Weekly kitchen supply',
                'reference_no'   => 'ISS-GOD-001',
                'issued_at'      => now()->subDays(7),
            ],
            [
                'item'           => 'Long Grain Rice',
                'dept_code'      => 'GOD',
                'quantity'       => 2,
                'issued_to_type' => 'department',
                'issued_to_id'   => null,
                'dept_target'    => 'GOD',
                'purpose'        => 'Bi-weekly rice supply',
                'reference_no'   => 'ISS-GOD-002',
                'issued_at'      => now()->subDays(14),
            ],
            [
                'item'           => 'Cooking Oil',
                'dept_code'      => 'GOD',
                'quantity'       => 5,
                'issued_to_type' => 'department',
                'issued_to_id'   => null,
                'dept_target'    => 'GOD',
                'purpose'        => 'Monthly cooking supply',
                'reference_no'   => 'ISS-GOD-003',
                'issued_at'      => now()->subDays(10),
            ],
        ];

        foreach ($issuances as $s) {
            $itemModel = $item($s['item']);
            $deptModel = $dept($s['dept_code']);

            if (! $itemModel || ! $deptModel) {
                continue;
            }

            // Determine issuable (morphable)
            if ($s['issued_to_type'] === 'employee' && ! empty($s['issued_to_id'])) {
                $issuable = $emp($s['issued_to_id']);
            } else {
                $targetCode = $s['dept_target'] ?? $s['dept_code'];
                $issuable   = $dept($targetCode);
            }

            if (! $issuable) {
                continue;
            }

            // Check that stock exists before issuing
            $stock = InventoryStock::where('item_id', $itemModel->id)
                ->where('department_id', $deptModel->id)
                ->first();

            if (! $stock || $stock->quantity < $s['quantity']) {
                continue; // Skip if insufficient stock
            }

            // Create issuance
            StockIssuance::create([
                'item_id'            => $itemModel->id,
                'from_department_id' => $deptModel->id,
                'quantity'           => $s['quantity'],
                'issuable_type'      => get_class($issuable),
                'issuable_id'        => $issuable->id,
                'purpose'            => $s['purpose'],
                'issued_by'          => $admin->id,
                'issued_at'          => $s['issued_at'],
            ]);

            // Decrement stock
            $stock->decrement('quantity', $s['quantity']);
        }
    }
}
