<?php

namespace Database\Seeders;

use App\Models\UtensilItem;
use Illuminate\Database\Seeder;

class UtensilItemSeeder extends Seeder
{
    private static array $items = [
        'canteen_utensils' => [
            'Dinner Spoon',
            'Dinner Fork',
            'Dinner Plate',
            'Soup Bowl',
            'Soup Cup',
            'Coffee Mug',
            'Saucer',
            'Bread Plate',
            'Glass Water -Small',
            'Salad Plate',
            'Serving Tray',
        ],

        'vip_dining' => [
            'Soup Tureen with Design',
            'Soup Tureen-White',
            'Coffee Cups',
            'Saucer',
            'Dinner Plate',
            'Tea Pot',
            'Sauce Boat-White',
            'Demitasse Cup',
            'Demitasse Saucer',
            'Soy Dish',
            'Bread Plate',
            'Oval Plate-Big',
            'Oval Plate-Deep',
            'Oval Plate-Small',
            'Soup Plate',
            'Salad Plate',
            'Milk Dispenser',
            'Ramiken-Oval',
            'Bread Plate-Wave Shape',
            'Saucer with design',
            'Show Plate',
            'Espresso Cup -',
            'Espresso cup saucer-Small',
            'Espresso cup saucer-Medium',
            'Tea cup (Floral )',
            'Tea pot-Colorfull',
        ],

        'storage_room_fdc' => [
            'Dinner Plate',
            'Salad Plates',
            'Salad Plates w/ diff design',
            'Bread and Butter Plates',
            'Square Plates',
            'Wave Plates',
            'Oval Plates - small',
            'Oval Plates - medium',
            'Oval Plates - Large',
            'Oval Plates with design',
            'Cereal Bowl',
            'Soup Bowl-Big',
            'Soup Bowl-Medium',
            'Soup Bowl',
            'Soup Bowl with ear',
            'Pasta Bowl',
            'Coffee Cups',
            'Saucer',
            'Tea Spoon',
            'Dessert Spoon',
            'Soup Spoon',
            'Spoon',
            'Serving Spoon',
            'Dinner Knife',
            'Butter Knife',
            'Serving Fork',
            'Dessert Fork - small',
            'Dinner Fork',
            'Ramiken - small',
            'Ramiken - medium',
            'Serving Tray-Rectangular',
            'Oval Tray',
            'Bar Tray',
            'Oval Tray Stand',
            'High Ball - 8oz',
            'High Ball - 12oz',
            'Water Goblet',
            'Red Wine Glass',
            'White Wine Glass',
            'Beer Mugs',
            'Chafing Rolled Dish',
            'Regular Chafing Dish',
            'Rice Bucket',
            'Plastic Juice Jug',
            'Soup Warmer',
            'Coffee Maker (New)',
            'Coffee Maker w/o Kettle',
            'Peculator - Old',
            'Peculator - New',
            'Juice Jar',
            'Ice Cream Cup',
            'Tong - small',
            'Tong - big',
            'Food Cover',
            'Canteen Tray',
            'Blue Crates',
            'Plate Crates',
            'Crab Crackers',
        ],
    ];

    public function run(): void
    {
        foreach (self::$items as $category => $names) {
            foreach ($names as $order => $name) {
                UtensilItem::firstOrCreate(
                    ['category' => $category, 'name' => $name],
                    ['sort_order' => $order + 1]
                );
            }
        }
    }
}
