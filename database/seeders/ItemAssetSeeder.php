<?php

namespace Database\Seeders;

use App\Models\AssetAssignment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemAsset;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemAssetSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $dept  = fn (string $code) => Department::where('code', $code)->first()?->id;
        $item  = fn (string $name) => Item::where('name', $name)->first()?->id;
        $emp   = fn (string $eid) => Employee::where('employee_id', $eid)->first();

        $assets = [
            // ── Kitchen Equipment (GOD) ───────────────────────────────────
            ['item_code' => 'GOD-FRG-001', 'item' => 'Toshiba Inverter Refrigerator', 'serial' => 'TSB-GRA-SN00701', 'dept' => 'GOD', 'purchase_date' => '2024-01-20', 'purchase_price' => 28000.00, 'status' => 'available', 'assignee' => null],
            ['item_code' => 'GOD-FRG-002', 'item' => 'Toshiba Inverter Refrigerator', 'serial' => 'TSB-GRA-SN00702', 'dept' => 'GOD', 'purchase_date' => '2024-01-20', 'purchase_price' => 28000.00, 'status' => 'available', 'assignee' => null],
            ['item_code' => 'GOD-GCR-001', 'item' => 'Modena Commercial Gas Range',   'serial' => 'MOD-CS6640-SN00801', 'dept' => 'GOD', 'purchase_date' => '2024-01-20', 'purchase_price' => 27000.00, 'status' => 'available', 'assignee' => null],
            ['item_code' => 'GOD-CKW-001', 'item' => 'Meyer Accolade Cookware Set',   'serial' => null,                 'dept' => 'GOD', 'purchase_date' => '2024-01-20', 'purchase_price' => 9500.00,  'status' => 'available', 'assignee' => null],
        ];

        foreach ($assets as $assetData) {
            $itemId = $item($assetData['item']);
            $deptId = $dept($assetData['dept']);

            if (! $itemId || ! $deptId) {
                continue;
            }

            $asset = ItemAsset::firstOrCreate(
                ['item_code' => $assetData['item_code']],
                [
                    'item_id'         => $itemId,
                    'serial_number'   => $assetData['serial'],
                    'purchase_date'   => $assetData['purchase_date'],
                    'purchase_price'  => $assetData['purchase_price'],
                    'warranty_expiry' => date('Y-m-d', strtotime($assetData['purchase_date'] . ' +1 year')),
                    'department_id'   => $deptId,
                    'status'          => $assetData['status'],
                ]
            );

            // Create active assignment if assignee is set and asset doesn't have one yet
            if ($assetData['assignee'] && $asset->wasRecentlyCreated) {
                $employee = $emp($assetData['assignee']);
                if ($employee) {
                    AssetAssignment::create([
                        'asset_id'            => $asset->id,
                        'assignable_type'     => Employee::class,
                        'assignable_id'       => $employee->id,
                        'assigned_by'         => $admin->id,
                        'assigned_at'         => now()->subDays(rand(30, 180)),
                        'condition_on_assign' => 'good',
                        'purpose'             => 'Official work use',
                        'status'              => 'active',
                    ]);
                }
            }
        }
    }
}
