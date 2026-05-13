<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Run order matters — later seeders depend on earlier ones.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,             // 1. Users (needed as FK for assignments & receivals)
            DepartmentSeeder::class,       // 2. Departments
            CategorySeeder::class,         // 3. Categories (parents seeded before children)
            UnitSeeder::class,             // 4. Units of measurement
            SupplierSeeder::class,         // 5. Suppliers
            ItemSeeder::class,             // 6. Item definitions (fixed_asset & consumable)
            EmployeeSeeder::class,         // 7. Employees (requires departments)
            ItemAssetSeeder::class,        // 8. Individual fixed-asset units + initial assignments
            InventoryStockSeeder::class,   // 9. Consumable stocks (no receival/issuance records)
            DepartmentUserSeeder::class,   // 10. One user account per department
            UtensilItemSeeder::class,             // 11. Utensil items for all 3 categories
            UtensilInventoryRecordSeeder::class,  // 12. Initial inventory records (Canteen/VIP/Storage — May 2026)
            GalleyInventoryStockSeeder::class,    // 13. Initial stock for GOD consumables — May 2026
        ]);
    }
}
