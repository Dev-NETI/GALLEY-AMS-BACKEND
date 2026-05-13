<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\StockReceival;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GalleyInventoryStockSeeder extends Seeder
{
    /**
     * Initial stock quantities for the GOD (Galley) department — 2026-05-12.
     * Format: 'Item Name (exact match from ItemSeeder)' => quantity
     * Items with qty 0 are omitted — they display as 0 automatically.
     */
    private const DATE = '2026-05-12';

    private static array $stocks = [
        // ── BEEF ─────────────────────────────────────────────────────────────
        'Beef - Sliced'             => 10,
        'Ground Beef'               => 10,
        'Top Round Beef'            => 30,

        // ── CANNED GOODS ─────────────────────────────────────────────────────
        'Button Mushroom'           => 8,
        'Cream of Corn'             => 2,
        'Garbanzos'                 => 30,
        'Coconut Milk in Can'       => 120,
        'Sardines in Tomato Sauce'  => 56,

        // ── CHICKEN ──────────────────────────────────────────────────────────
        'Chicken Thigh'             => 150,
        'Chicken Whole'             => 20,
        'Chicken Wings'             => 20,

        // ── CONDIMENTS ───────────────────────────────────────────────────────
        'Catsup'                    => 74,
        'Hot Sauce'                 => 19,
        'Mang Tomas Lechon Sauce'   => 9,
        'Fish Sauce'                => 18,
        'Soy Sauce'                 => 2,
        'Vinegar'                   => 13,

        // ── EGGS ─────────────────────────────────────────────────────────────
        'White Eggs'                => 93,

        // ── FRESH FRUITS ─────────────────────────────────────────────────────
        'Banana - Lakatan Maniba'   => 35,
        'Pakwan'                    => 10,
        'Pineapple'                 => 18,

        // ── NOODLES & PASTA ──────────────────────────────────────────────────
        'Misua'                     => 5,

        // ── OIL ──────────────────────────────────────────────────────────────
        'Cooking Oil'               => 3,
        'Sesame Oil'                => 1,

        // ── OTHERS ───────────────────────────────────────────────────────────
        'Cream of Pumpkin'          => 11,
        'Cream of Mushroom'         => 2,
        'Demiglace Sauce'           => 4,
        'Bread Crumbs'              => 9,
        'Peanut Butter'             => 5,
        'Tomato Paste'              => 1,
        'Tomato Sauce'              => 14,
        'Pineapple Chunks'          => 4,
        'BBQ Marinade'              => 2,
        'Crab and Corn'             => 17,
        'Hoisin Sauce'              => 15,
        'Sweet Chili Sauce'         => 12,
        'Margarine'                 => 2,

        // ── PORK ─────────────────────────────────────────────────────────────
        'Ground Pork'               => 20,
        'Liempo Slice'              => 20,
        'Pork BBQ'                  => 200,
        'Pork Chop'                 => 20,
        'Pork Liver'                => 5,
        'Pork Maskara'              => 30,
        'Pork Pata'                 => 20,
        'Pork Pigue'                => 20,
        'Spareribs'                 => 20,

        // ── PROCESSED MEATS ──────────────────────────────────────────────────
        'Bacon'                     => 10,
        'Corned Beef'               => 10,
        'Ham'                       => 20,
        'Hotdog'                    => 20,

        // ── RICE ─────────────────────────────────────────────────────────────
        'Japanese Rice'             => 50,

        // ── SEAFOOD ──────────────────────────────────────────────────────────
        'Alumahan'                  => 10,
        'Matangbaka'                => 10,
        'Bangus Boneless'           => 20,
        'Tambakol'                  => 20,
        'Tilapia Whole'             => 20,
        'Tulingan'                  => 20,

        // ── SEASONING ────────────────────────────────────────────────────────
        'Sinigang Mix'              => 35,

        // ── SUGAR / OTHERS ───────────────────────────────────────────────────
        'Sugar, Brown'              => 1,
        'Sugar, White'              => 3,
        'All Purpose Flour'         => 1,

        // ── SUPPLIES ─────────────────────────────────────────────────────────
        'Aluminum Foil'             => 4,
        'Cling Wrap'                => 11,
        'Handgloves'                => 40,

        // ── VEGETABLES ───────────────────────────────────────────────────────
        'Ampalaya'                  => 10,
        'Baguio Beans'              => 9,
        'Bell Pepper (Red & Green)' => 8,
        'Cabbage'                   => 25,
        'Calamansi'                 => 6,
        'Carrots'                   => 16,
        'Chili Finger Chili - Green' => 4,
        'Chili Labuyo - Red'        => 1,
        'Eggplant'                  => 10,
        'Gabi Root'                 => 9,
        'Fresh Garlic'              => 3,
        'Fresh Gata'                => 20,
        'Green Peas'                => 14,
        'Kamote'                    => 16,
        'Kangkong'                  => 7,
        'Labanos'                   => 4,
        'Langka Gayat'              => 15,
        'Lumpia Wrapper'            => 400,
        'Monggo'                    => 15,
        'Okra'                      => 14,
        'Onion White'               => 12,
        'Onion Leaks'               => 6,
        'Pechay Chinese'            => 18,
        'Potato'                    => 7,
        'Kalabasa'                  => 18,
        'Puso ng Saging Gayat'      => 10,
        'Saba'                      => 50,
        'Sayote'                    => 33,
        'Sigarilyas'                => 10,
        'Singkamas'                 => 10,
        'Star Anise'                => 2,
        'Lemon Grass'               => 2,
        'Tomato'                    => 9,
        'Upo'                       => 20,
        'Red Beans'                 => 10,
    ];

    public function run(): void
    {
        $dept = Department::where('code', 'GOD')->first();

        if (! $dept) {
            $this->command->error('GOD department not found — run DepartmentSeeder first.');
            return;
        }

        $admin = User::where('email', 'admin@inventory.com')->first()
            ?? User::where('user_type', 'system_administrator')->first();

        if (! $admin) {
            $this->command->error('Admin user not found — run UserSeeder first.');
            return;
        }

        // Clear previously seeded records so re-running is safe
        $godItemIds = Item::where('department_id', $dept->id)
            ->where('item_type', 'consumable')
            ->pluck('id');

        StockReceival::where('department_id', $dept->id)
            ->where('notes', 'Initial stock — May 2026')
            ->delete();

        InventoryStock::where('department_id', $dept->id)
            ->whereIn('item_id', $godItemIds)
            ->delete();

        // Name → Item map scoped to GOD department consumables
        $itemMap = Item::where('department_id', $dept->id)
            ->where('item_type', 'consumable')
            ->get()
            ->keyBy(fn($i) => strtolower(trim($i->name)));

        $seeded  = 0;
        $skipped = 0;

        foreach (self::$stocks as $name => $qty) {
            $item = $itemMap[strtolower(trim($name))] ?? null;

            if (! $item) {
                $this->command->warn("Item not found: \"{$name}\"");
                $skipped++;
                continue;
            }

            DB::transaction(function () use ($item, $dept, $qty, $admin) {
                InventoryStock::create([
                    'item_id'       => $item->id,
                    'department_id' => $dept->id,
                    'quantity'      => $qty,
                ]);

                StockReceival::create([
                    'item_id'             => $item->id,
                    'department_id'       => $dept->id,
                    'quantity'            => $qty,
                    'supplier_id'         => null,
                    'delivery_receipt_no' => null,
                    'received_by'         => $admin->id,
                    'received_at'         => self::DATE,
                    'notes'               => 'Initial stock — May 2026',
                ]);
            });

            $seeded++;
        }

        $this->command->info("GOD department: {$seeded} items seeded, {$skipped} skipped.");
    }
}
