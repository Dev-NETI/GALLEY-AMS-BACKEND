<?php

namespace Database\Seeders;

use App\Models\UtensilInventoryRecord;
use App\Models\UtensilItem;
use Illuminate\Database\Seeder;

class UtensilInventoryRecordSeeder extends Seeder
{
    /**
     * Initial inventory data — May 2026.
     * Format: 'Item Name' => [beginning, add, breakages]
     */
    private const YEAR  = 2026;
    private const MONTH = 5;

    private static array $canteen = [
        'Dinner Spoon'       => [432, 0, 0],
        'Dinner Fork'        => [623, 0, 0],
        'Dinner Plate'       => [325, 0, 0],
        'Soup Bowl'          => [2,   0, 0],
        'Soup Cup'           => [58,  0, 0],
        'Coffee Mug'         => [33,  0, 0],
        'Saucer'             => [202, 0, 0],
        'Bread Plate'        => [280, 0, 0],
        'Glass Water -Small' => [133, 0, 0],
        'Salad Plate'        => [280, 0, 0],
        'Serving Tray'       => [162, 0, 0],
    ];

    private static array $vip_dining = [
        'Soup Tureen with Design'    => [8,  0, 0],
        'Soup Tureen-White'          => [22, 0, 0],
        'Coffee Cups'                => [24, 0, 0],
        'Saucer'                     => [38, 0, 0],
        'Dinner Plate'               => [57, 0, 0],
        'Tea Pot'                    => [8,  0, 0],
        'Sauce Boat-White'           => [8,  0, 0],
        'Demitasse Cup'              => [12, 0, 0],
        'Demitasse Saucer'           => [11, 0, 0],
        'Soy Dish'                   => [41, 0, 0],
        'Bread Plate'                => [49, 0, 0],
        'Oval Plate-Big'             => [16, 0, 0],
        'Oval Plate-Deep'            => [8,  0, 0],
        'Oval Plate-Small'           => [8,  0, 0],
        'Soup Plate'                 => [29, 0, 0],
        'Salad Plate'                => [12, 0, 0],
        'Milk Dispenser'             => [8,  0, 0],
        'Ramiken-Oval'               => [41, 0, 0],
        'Bread Plate-Wave Shape'     => [30, 0, 0],
        'Saucer with design'         => [7,  0, 0],
        'Show Plate'                 => [2,  0, 0],
        'Espresso Cup -'             => [6,  0, 0],
        'Espresso cup saucer-Small'  => [6,  0, 0],
        'Espresso cup saucer-Medium' => [6,  0, 0],
        'Tea cup (Floral )'          => [3,  0, 0],
        'Tea pot-Colorfull'          => [2,  0, 0],
    ];

    private static array $storage_room_fdc = [
        'Dinner Plate'               => [98,  0, 9],
        'Salad Plates'               => [769, 0, 2],
        'Salad Plates w/ diff design'=> [19,  0, 0],
        'Bread and Butter Plates'    => [57,  0, 0],
        'Square Plates'              => [3,   0, 0],
        'Wave Plates'                => [28,  0, 0],
        'Oval Plates - small'        => [40,  0, 0],
        'Oval Plates - medium'       => [34,  0, 0],
        'Oval Plates - Large'        => [1,   0, 0],
        'Oval Plates with design'    => [3,   0, 0],
        'Cereal Bowl'                => [21,  0, 0],
        'Soup Bowl-Big'              => [5,   0, 0],
        'Soup Bowl-Medium'           => [4,   0, 0],
        'Soup Bowl'                  => [22,  0, 0],
        'Soup Bowl with ear'         => [350, 0, 0],
        'Pasta Bowl'                 => [0,   0, 0],
        'Coffee Cups'                => [347, 0, 0],
        'Saucer'                     => [238, 0, 0],
        'Tea Spoon'                  => [126, 0, 0],
        'Dessert Spoon'              => [108, 0, 0],
        'Soup Spoon'                 => [381, 0, 0],
        'Spoon'                      => [102, 0, 0],
        'Serving Spoon'              => [19,  0, 0],
        'Dinner Knife'               => [406, 0, 0],
        'Butter Knife'               => [0,   0, 0],
        'Serving Fork'               => [52,  0, 0],
        'Dessert Fork - small'       => [408, 0, 0],
        'Dinner Fork'                => [489, 0, 0],
        'Ramiken - small'            => [247, 0, 1],
        'Ramiken - medium'           => [164, 0, 3],
        'Serving Tray-Rectangular'   => [66,  0, 0],
        'Oval Tray'                  => [6,   0, 0],
        'Bar Tray'                   => [8,   0, 0],
        'Oval Tray Stand'            => [7,   0, 0],
        'High Ball - 8oz'            => [162, 0, 0],
        'High Ball - 12oz'           => [194, 0, 0],
        'Water Goblet'               => [489, 0, 2],
        'Red Wine Glass'             => [83,  0, 0],
        'White Wine Glass'           => [72,  0, 0],
        'Beer Mugs'                  => [66,  0, 0],
        'Chafing Rolled Dish'        => [8,   0, 0],
        'Regular Chafing Dish'       => [18,  0, 0],
        'Rice Bucket'                => [5,   0, 0],
        'Plastic Juice Jug'          => [2,   0, 0],
        'Soup Warmer'                => [3,   0, 0],
        'Coffee Maker (New)'         => [6,   0, 0],
        'Coffee Maker w/o Kettle'    => [4,   0, 0],
        'Peculator - Old'            => [6,   0, 0],
        'Peculator - New'            => [3,   0, 0],
        'Juice Jar'                  => [3,   0, 0],
        'Ice Cream Cup'              => [119, 0, 0],
        'Tong - small'               => [0,   0, 0],
        'Tong - big'                 => [0,   0, 0],
        'Food Cover'                 => [46,  0, 0],
        'Canteen Tray'               => [0,   0, 0],
        'Blue Crates'                => [10,  0, 0],
        'Plate Crates'               => [10,  0, 0],
        'Crab Crackers'              => [1,   0, 0],
    ];

    public function run(): void
    {
        $this->seedCategory('canteen_utensils', self::$canteen, 'Canteen Utensils');
        $this->seedCategory('vip_dining', self::$vip_dining, 'VIP Dining');
        $this->seedCategory('storage_room_fdc', self::$storage_room_fdc, 'Storage Room FDC');
    }

    private function seedCategory(string $category, array $data, string $label): void
    {
        $itemMap = UtensilItem::where('category', $category)
            ->get()
            ->keyBy(fn ($i) => strtolower(trim($i->name)));

        foreach ($data as $name => [$beginning, $add, $breakages]) {
            $item = $itemMap[strtolower($name)] ?? null;

            if (! $item) {
                $this->command->warn("{$label} — item not found: \"{$name}\"");
                continue;
            }

            UtensilInventoryRecord::firstOrCreate(
                [
                    'utensil_item_id' => $item->id,
                    'year'            => self::YEAR,
                    'month'           => self::MONTH,
                ],
                [
                    'beginning' => $beginning,
                    'add_qty'   => $add,
                    'breakages' => $breakages,
                ]
            );
        }

        $this->command->info("{$label} inventory records seeded for May 2026.");
    }
}
