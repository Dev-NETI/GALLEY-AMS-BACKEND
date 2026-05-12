<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $u = fn(string $abbr) => Unit::where('abbreviation', $abbr)->value('id');

        $kg       = $u('kg');
        $pcs      = $u('pcs');
        $pack     = $u('pack');
        $sack     = $u('sack');
        $tray     = $u('tray');
        $btl      = $u('btl');
        $roll     = $u('roll');
        $can      = $u('can');
        $tub      = $u('tub');
        $tin      = $u('tin');
        $sachet   = $u('sachet');
        $packet   = $u('packet');
        $canister = $u('canister');
        $cs       = $u('case');
        $cby      = $u('cby');
        $bundle   = $u('bundle');

        $deptId = Department::where('code', 'GOD')->value('id');
        $getCat = fn(string $name) => Category::where('name', $name)
            ->where('department_id', $deptId)
            ->value('id');

        // Pre-resolve category IDs
        $BEEF     = $getCat('BEEF');
        $PORK     = $getCat('PORK');
        $CHICKEN  = $getCat('CHICKEN');
        $SEAFOOD  = $getCat('SEAFOOD');
        $PROC     = $getCat('PROCESSED MEATS');
        $VEG      = $getCat('VEGETABLES');
        $FRUITS   = $getCat('FRESH FRUITS');
        $RICE     = $getCat('RICE');
        $EGGS     = $getCat('EGGS');
        $COND     = $getCat('CONDIMENTS');
        $SEAS     = $getCat('SEASONING');
        $NOODLES  = $getCat('NOODLES & PASTA');
        $OIL      = $getCat('OIL');
        $SUGAR    = $getCat('SUGAR / OTHERS');
        $CANNED   = $getCat('CANNED GOODS');
        $SUPPLIES = $getCat('SUPPLIES');
        $OTHERS   = $getCat('OTHERS');
        $CLEANING = $getCat('CLEANING MATERIALS');
        $WATER    = $getCat('BOTTLED WATER');

        $c = fn(string $name, string $desc, $catId, $unitId) => [
            'name'            => $name,
            'description'     => $desc,
            'category_id'     => $catId,
            'unit_id'         => $unitId,
            'item_type'       => 'consumable',
            'brand'           => null,
            'model'           => null,
            'specifications'  => null,
            'min_stock_level' => 20,
            'department_id'   => $deptId,
        ];

        $items = [

            // ── BEEF ─────────────────────────────────────────────────────────
            $c('Beef - Sliced',    'Thinly sliced beef cuts for stir-fry and main dishes',          $BEEF, $kg),
            $c('Beef Brisket',     'Beef brisket cut for stews and braises',                        $BEEF, $kg),
            $c('Bulalo / Kansi',   'Beef shank with bone marrow for bulalo soup',                   $BEEF, $kg),
            $c('Ground Beef',      'Minced beef for burgers, pasta, and meat dishes',               $BEEF, $kg),
            $c('Oxtripe',          'Beef tripe for kare-kare and callos',                           $BEEF, $kg),
            $c('Top Round Beef',   'Lean top round beef cut for roasting and steak',                $BEEF, $kg),

            // ── PORK ─────────────────────────────────────────────────────────
            $c('Bopis',                        'Pork lungs and heart for spicy bopis dish',         $PORK, $kg),
            $c('Ground Pork',                  'Minced pork for fillings and meat dishes',          $PORK, $kg),
            $c('Liempo',                       'Pork belly cut for grilling and braising',          $PORK, $kg),
            $c('Liempo Slice',                 'Sliced pork belly for quick cooking',               $PORK, $kg),
            $c('Pork BBQ',                     'Pork cuts on skewer for barbecue',                  $PORK, $pcs),
            $c('Pork Chop',                    'Pork chop cut for frying and grilling',             $PORK, $kg),
            $c('Pork Liver',                   'Pork liver for adobo and sautéed dishes',           $PORK, $kg),
            $c('Pork Gizzard (Balunbalunan)',   'Pork gizzard for soups and stir-fry',              $PORK, $kg),
            $c('Pork Maskara',                 'Pork face cut for sinigang and local dishes',       $PORK, $kg),
            $c('Pork Pata',                    'Pork leg for crispy pata and pata tim',             $PORK, $kg),
            $c('Pork Pigue',                   'Pork shoulder cut for roasting and lechon',         $PORK, $kg),
            $c('Spareribs',                    'Pork spareribs for BBQ and sinigang',               $PORK, $kg),

            // ── CHICKEN ──────────────────────────────────────────────────────
            $c('Chicken Breast',   'Boneless chicken breast for grilling and frying',               $CHICKEN, $kg),
            $c('Chicken Gizzard',  'Chicken gizzard for soups and sautéed dishes',                  $CHICKEN, $kg),
            $c('Chicken Liver',    'Chicken liver for adobo and sautéed dishes',                    $CHICKEN, $kg),
            $c('Chicken Thigh',    'Chicken thigh cut for roasting and braising',                   $CHICKEN, $kg),
            $c('Chicken Whole',    'Whole chicken for roasting and lechon manok',                   $CHICKEN, $kg),
            $c('Chicken Wings',    'Chicken wings for frying and grilling',                         $CHICKEN, $kg),

            // ── SEAFOOD ──────────────────────────────────────────────────────
            $c('Alumahan',             'Round scad fish for frying and sinigang',                   $SEAFOOD, $kg),
            $c('Bagoong Alamang',      'Shrimp paste condiment and cooking ingredient',             $SEAFOOD, $kg),
            $c('Bisugo',               'Threadfin bream fish for frying and soups',                 $SEAFOOD, $kg),
            $c('Daing na Bangus',      'Dried marinated milkfish for frying',                       $SEAFOOD, $pack),
            $c('Daing na Galunggong',  'Dried round scad for frying',                               $SEAFOOD, $pack),
            $c('Dalagang Bukid',       'Fusilier fish for frying and soups',                        $SEAFOOD, $kg),
            $c('Dry Dilis',            'Dried anchovies for snacks and side dishes',                $SEAFOOD, $kg),
            $c('Fish Fillet',          'Boneless fish fillet for frying and baking',                $SEAFOOD, $kg),
            $c('Galunggong',           'Round scad fish for frying and soups',                      $SEAFOOD, $kg),
            $c('Hasa-hasa',            'Short-bodied mackerel for frying and sinigang',             $SEAFOOD, $kg),
            $c('Matangbaka',           'Big-eyed scad fish for frying',                             $SEAFOOD, $kg),
            $c('Maya-maya',            'Red snapper fish for kinilaw and frying',                   $SEAFOOD, $kg),
            $c('Bangus Boneless',      'Deboned milkfish for grilling and frying',                  $SEAFOOD, $kg),
            $c('Mussels',              'Fresh mussels for soups and pasta',                         $SEAFOOD, $kg),
            $c('Okoy',                 'Fresh small shrimp for okoy fritter',                       $SEAFOOD, $kg),
            $c('Pink Salmon Head',     'Pink salmon head for sinigang and soups',                   $SEAFOOD, $kg),
            $c('Salmon Local',         'Local salmon for grilling and sinigang',                    $SEAFOOD, $kg),
            $c('Shrimp / Swahe',       'Fresh shrimp for sautéed dishes and soups',                 $SEAFOOD, $kg),
            $c('Squid',                'Fresh squid for adobo and grilling',                        $SEAFOOD, $kg),
            $c('Tambakol',             'Yellowfin tuna for grilling and kinilaw',                   $SEAFOOD, $kg),
            $c('Tilapia Whole',        'Whole tilapia for frying and sinigang',                     $SEAFOOD, $kg),
            $c('Tulingan',             'Bullet tuna for sinigang and frying',                       $SEAFOOD, $kg),
            $c('Tuyo Salinas',         'Salted dried fish for breakfast and side dishes',           $SEAFOOD, $kg),
            $c('Tanigue',              'Spanish mackerel for kinilaw and grilling',                 $SEAFOOD, $kg),
            $c('Tawilis',              'Freshwater sardinella for frying',                          $SEAFOOD, $kg),

            // ── PROCESSED MEATS ──────────────────────────────────────────────
            $c('Bacon',                    'Cured pork belly strips for breakfast',                 $PROC, $pack),
            $c('Beef Burger Patties',      'Pre-formed beef burger patties for grilling',           $PROC, $pack),
            $c('Beef Steak',               'Packaged marinated beef steak cuts',                    $PROC, $pack),
            $c('Cheesedog',                'Cheese-filled hotdog sausage',                          $PROC, $pack),
            $c('Chicken Franks',           'Chicken sausage franks for frying and grilling',        $PROC, $pack),
            $c('Chicken Nuggets',          'Breaded chicken nuggets for frying',                    $PROC, $pack),
            $c('Chicken Tocino',           'Sweet-cured chicken tocino for breakfast',              $PROC, $pack),
            $c('Corned Beef',              'Packaged corned beef for quick meals',                  $PROC, $pack),
            $c('Daing na Bangus (Packaged)', 'Packaged dried marinated milkfish',                    $PROC, $pack),
            $c('Embotido',                 'Filipino-style rolled meatloaf',                        $PROC, $pack),
            $c('Garlic Longganisa',        'Garlic-flavored pork sausage',                          $PROC, $pack),
            $c('Garlic Sausage',           'Garlic-seasoned processed sausage',                     $PROC, $pack),
            $c('Ham',                      'Cured ham for sandwiches and dishes',                   $PROC, $pack),
            $c('Hotdog',                   'Pork and chicken hotdog sausage',                       $PROC, $pack),
            $c('Hungarian Sausage',        'Smoked Hungarian-style sausage',                        $PROC, $pack),
            $c('Longganisa',               'Sweet pork sausage for breakfast',                      $PROC, $pack),
            $c('Meatballs',                'Pre-formed pork or beef meatballs',                     $PROC, $pack),
            $c('Meatloaf',                 'Processed pork meatloaf',                               $PROC, $pack),
            $c('Pork Tapa',                'Cured marinated pork slices for breakfast',             $PROC, $pack),
            $c('Pork Tocino',              'Sweet-cured pork tocino for breakfast',                 $PROC, $pack),
            $c('Siomai',                   'Frozen pork and shrimp dumplings',                      $PROC, $pack),
            $c('Sweet Ham',                'Sweet-glazed processed ham',                            $PROC, $pack),
            $c('Beef Tapa',                'Cured marinated beef slices for breakfast',             $PROC, $pack),
            $c('Tinapa',                   'Smoked fish for side dishes and breakfast',             $PROC, $kg),
            $c('Vienna Sausage',           'Packaged Vienna sausage links',                         $PROC, $pack),
            $c('Sisig Sausage',            'Sisig-flavored processed sausage',                      $PROC, $pack),

            // ── VEGETABLES ───────────────────────────────────────────────────
            $c('Alugbati',                   'Malabar spinach for soups and sautéed dishes',       $VEG, $kg),
            $c('Ampalaya',                   'Bitter gourd for pinakbet and stir-fry',              $VEG, $kg),
            $c('Baguio Beans',               'Green beans from Baguio for salads and stir-fry',    $VEG, $kg),
            $c('Baguio Lettuce',             'Fresh Baguio lettuce for salads',                     $VEG, $kg),
            $c('Banana Blossom',             'Banana flower for kare-kare and soups',               $VEG, $kg),
            $c('Bell Pepper (Red & Green)',  'Mixed bell peppers for stir-fry and garnish',         $VEG, $kg),
            $c('Brocolli',                   'Fresh broccoli for stir-fry and salads',              $VEG, $kg),
            $c('Cabbage',                    'Fresh cabbage for soups and stir-fry',                $VEG, $kg),
            $c('Calamansi',                  'Philippine lime for seasoning and drinks',             $VEG, $kg),
            $c('Carrots',                    'Fresh carrots for soups, salads, and stir-fry',       $VEG, $kg),
            $c('Cauliflower',                'Fresh cauliflower for stir-fry and soups',            $VEG, $kg),
            $c('Chili Finger Chili - Green', 'Green finger chili for spicy dishes',                 $VEG, $kg),
            $c('Chili Labuyo - Red',         'Small red labuyo chili for spicy dishes',             $VEG, $kg),
            $c('Cucumber',                   'Fresh cucumber for salads and garnish',               $VEG, $kg),
            $c('Dahon ng Sili',              'Chili leaves for soups and local dishes',             $VEG, $kg),
            $c('Eggplant',                   'Fresh eggplant for tortang talong and ensalada',      $VEG, $kg),
            $c('Gabi Root',                  'Taro root for sinigang and laing',                    $VEG, $kg),
            $c('Fresh Garlic',               'Fresh garlic bulbs for cooking and seasoning',        $VEG, $kg),
            $c('Fresh Gata',                 'Fresh coconut milk for ginataan dishes',              $VEG, $kg),
            $c('Ginger',                     'Fresh ginger root for cooking and seasoning',         $VEG, $kg),
            $c('Green Peas',                 'Fresh or frozen green peas for soups and rice',       $VEG, $kg),
            $c('Kamote',                     'Sweet potato for soups and desserts',                 $VEG, $kg),
            $c('Kangkong',                   'Water spinach for adobo and sautéed dishes',          $VEG, $kg),
            $c('Labanos',                    'White radish for sinigang and soups',                 $VEG, $kg),
            $c('Labong',                     'Fresh bamboo shoots for sinigang and local dishes',   $VEG, $kg),
            $c('Laing',                      'Dried taro leaves for laing dish',                    $VEG, $kg),
            $c('Langka Gayat',               'Sliced jackfruit for kare-kare and local dishes',     $VEG, $kg),
            $c('Lettuce',                    'Fresh romaine or iceberg lettuce for salads',         $VEG, $kg),
            $c('Lumpia Wrapper',             'Thin pastry wrapper for lumpiang shanghai',           $VEG, $pcs),
            $c('Malunggay',                  'Moringa leaves for soups and salads',                 $VEG, $kg),
            $c('Mixed Vegies',               'Assorted mixed vegetables for soups and stir-fry',   $VEG, $kg),
            $c('Monggo',                     'Mung beans for monggo guisado',                       $VEG, $kg),
            $c('Okra',                       'Fresh okra for sinigang and pinakbet',                $VEG, $kg),
            $c('Onion Leaves',               'Spring onion leaves for garnish and soups',           $VEG, $kg),
            $c('Onion White',                'White onion for cooking and seasoning',               $VEG, $kg),
            $c('Onion Red',                  'Red onion for kinilaw and raw dishes',                $VEG, $kg),
            $c('Onion Leaks',                'Leeks for soups and stir-fry',                        $VEG, $kg),
            $c('Papaya - Green',             'Unripe green papaya for tinola and achara',           $VEG, $kg),
            $c('Patola',                     'Sponge gourd for soups and miso dishes',              $VEG, $kg),
            $c('Pechay Chinese',             'Chinese cabbage for soups and stir-fry',              $VEG, $kg),
            $c('Pechay Tagalog',             'Local pechay for soups and sautéed dishes',           $VEG, $kg),
            $c('Potato',                     'Fresh potato for soups, frying, and stews',           $VEG, $kg),
            $c('Kalabasa',                   'Squash for soups, ginataan, and pinakbet',            $VEG, $kg),
            $c('Puso ng Saging Gayat',       'Sliced banana heart for kare-kare and soups',         $VEG, $kg),
            $c('Saluyot',                    'Jute leaves for saluyot soup and local dishes',       $VEG, $kg),
            $c('Saba',                       'Saba banana for cooking and desserts',                $VEG, $pcs),
            $c('Sayote',                     'Chayote for soups and sautéed dishes',               $VEG, $kg),
            $c('Sigarilyas',                 'Winged beans for stir-fry and salads',                $VEG, $kg),
            $c('Singkamas',                  'Turnip for salads and kinilaw garnish',               $VEG, $kg),
            $c('Sitaw',                      'String beans for adobo and sautéed dishes',           $VEG, $kg),
            $c('Sitsaro',                    'Snow peas for stir-fry and soups',                    $VEG, $kg),
            $c('Star Anise',                 'Whole star anise spice for stews and soups',          $VEG, $kg),
            $c('Lemon Grass',                'Lemon grass stalks for soups and marinades',          $VEG, $kg),
            $c('Togue',                      'Mung bean sprouts for soups and stir-fry',            $VEG, $kg),
            $c('Tofu',                       'Fresh tofu block for sautéed dishes and soups',       $VEG, $pcs),
            $c('Tomato',                     'Fresh tomatoes for sauces, salads, and soups',        $VEG, $kg),
            $c('Upo',                        'Bottle gourd for soups and sautéed dishes',           $VEG, $kg),
            $c('Yellow Corn',                'Fresh yellow corn for soups and grilling',            $VEG, $kg),
            $c('Red Beans',                  'Dried red beans for soups and desserts',              $VEG, $kg),
            $c('Young Corn',                 'Baby corn for stir-fry and soups',                    $VEG, $kg),

            // ── FRESH FRUITS ─────────────────────────────────────────────────
            $c('Apple',                    'Fresh apple for desserts and fruit salad',              $FRUITS, $pcs),
            $c('Avocado',                  'Fresh avocado for salads and shakes',                   $FRUITS, $pcs),
            $c('Banana - Lakatan Maniba',  'Unripe lakatan banana for cooking',                     $FRUITS, $kg),
            $c('Banana - Lakatan Hinog',   'Ripe lakatan banana for desserts and snacks',           $FRUITS, $kg),
            $c('Honeydew',                 'Honeydew melon for fruit salad and desserts',           $FRUITS, $pcs),
            $c('Mango',                    'Fresh mango for desserts, shakes, and salads',          $FRUITS, $pcs),
            $c('Melon',                    'Fresh melon for fruit salad and desserts',              $FRUITS, $pcs),
            $c('Orange',                   'Fresh orange for juice and desserts',                   $FRUITS, $pcs),
            $c('Pakwan',                   'Watermelon for desserts and refreshments',              $FRUITS, $pcs),
            $c('Pineapple',                'Fresh pineapple for dishes, desserts, and drinks',      $FRUITS, $pcs),
            $c('Ponkan',                   'Ponkan mandarin orange for snacks and desserts',        $FRUITS, $pcs),

            // ── RICE ─────────────────────────────────────────────────────────
            $c('Long Grain Rice',   'Long grain white rice for daily meals',                        $RICE, $sack),
            $c('Japanese Rice',     'Short-grain Japanese rice for special dishes',                 $RICE, $kg),
            $c('Malagkit',          'Glutinous rice for kakanin and desserts',                      $RICE, $kg),

            // ── EGGS ─────────────────────────────────────────────────────────
            $c('White Eggs',   'White chicken eggs for cooking and baking',                         $EGGS, $tray),
            $c('Salted Eggs',  'Cured salted eggs for salads and side dishes',                      $EGGS, $tray),

            // ── CONDIMENTS ───────────────────────────────────────────────────
            $c('Catsup',                   'Tomato ketchup for dipping and cooking',               $COND, $btl),
            $c('Hot Sauce',                'Spicy hot sauce for dipping and seasoning',             $COND, $btl),
            $c('Kikkoman Soy Sauce',       'Japanese soy sauce for marinades and dipping',          $COND, $btl),
            $c('Knorr Seasoning',          'Liquid seasoning for cooking and marinating',           $COND, $btl),
            $c('Mang Tomas Lechon Sauce',  'Sweet lechon liver sauce for dipping',                 $COND, $btl),
            $c('Oyster Sauce',             'Oyster-based sauce for stir-fry and marinades',         $COND, $btl),
            $c('Fish Sauce',               'Fermented fish sauce for seasoning Filipino dishes',    $COND, $btl),
            $c('Soy Sauce',                'All-purpose soy sauce for cooking and marinating',      $COND, $btl),
            $c('Tabasco',                  'Tabasco pepper sauce for seasoning and dipping',        $COND, $btl),
            $c('Vinegar',                  'White cane vinegar for cooking and dipping',            $COND, $btl),
            $c('Honey',                    'Natural honey for glazing and sweetening',              $COND, $btl),

            // ── SEASONING ────────────────────────────────────────────────────
            $c('Rock Salt',        'Coarse rock salt for cooking and preserving',                   $SEAS, $sack),
            $c('Pepper',           'Ground black pepper for seasoning',                             $SEAS, $pack),
            $c('Aromat',           'Nestlé Aromat all-purpose seasoning powder',                    $SEAS, $pack),
            $c('Pork Broth',       'Pork broth base in tub for soups and stews',                   $SEAS, $tub),
            $c('Chicken Broth',    'Chicken broth base in tub for soups and stews',                $SEAS, $tub),
            $c('Beef Broth',       'Beef broth base in tub for soups and stews',                   $SEAS, $tub),
            $c('Chicken Powder',   'Chicken flavor powder seasoning',                               $SEAS, $tub),
            $c('Shrimp Powder',    'Shrimp flavor powder seasoning',                               $SEAS, $pack),
            $c('Sinigang Mix',     'Tamarind-based sinigang soup mix sachet',                       $SEAS, $sachet),

            // ── NOODLES & PASTA ──────────────────────────────────────────────
            $c('Bihon',             'Rice vermicelli noodles for pancit bihon',                     $NOODLES, $kg),
            $c('Canton',            'Egg noodles for pancit canton',                                $NOODLES, $kg),
            $c('Macaroni Pasta',    'Dried elbow macaroni for sopas and pasta',                     $NOODLES, $kg),
            $c('Miki (Fresh)',      'Fresh thick egg noodles for pancit miki',                      $NOODLES, $kg),
            $c('Misua',             'Thin wheat noodles for misua soup',                            $NOODLES, $kg),
            $c('Palabok Noodles',   'Thick rice noodles for pancit palabok',                       $NOODLES, $kg),
            $c('Sotanghon',         'Glass noodles for pancit sotanghon',                           $NOODLES, $kg),
            $c('Spaghetti Pasta',   'Dried spaghetti pasta for pasta dishes',                       $NOODLES, $kg),

            // ── OIL ──────────────────────────────────────────────────────────
            $c('Cooking Oil',   'Refined cooking oil in tin for frying and sautéing',               $OIL, $tin),
            $c('Sesame Oil',    'Toasted sesame oil for flavoring Asian dishes',                    $OIL, $btl),

            // ── SUGAR / OTHERS ───────────────────────────────────────────────
            $c('Cornstarch',        'Cornstarch for thickening sauces and gravies',                 $SUGAR, $sack),
            $c('Sugar, Brown',      'Brown sugar for cooking and baking',                           $SUGAR, $sack),
            $c('Sugar, White',      'Refined white sugar for cooking, baking, and drinks',          $SUGAR, $sack),
            $c('All Purpose Flour', 'All-purpose wheat flour for baking and coating',               $SUGAR, $sack),

            // ── CANNED GOODS ─────────────────────────────────────────────────
            $c('Button Mushroom',        'Canned sliced button mushrooms for soups and pasta',     $CANNED, $can),
            $c('Cream of Corn',          'Creamed corn for soups and side dishes',                 $CANNED, $kg),
            $c('Garbanzos',              'Canned chickpeas for cocido and salads',                 $CANNED, $can),
            $c('Coconut Milk in Can',    'Canned coconut milk for ginataan and curries',           $CANNED, $can),
            $c('Luncheon Meat',          'Canned luncheon meat for quick meals',                   $CANNED, $can),
            $c('Pork & Beans',           'Canned pork and beans for quick meals',                  $CANNED, $can),
            $c('Sardines in Tomato Sauce', 'Canned sardines in tomato sauce',                       $CANNED, $can),
            $c('Whole Kernel Corn',      'Canned whole kernel corn for soups and salads',          $CANNED, $can),

            // ── SUPPLIES ─────────────────────────────────────────────────────
            $c('Aluminum Foil',  'Heavy-duty aluminum foil for wrapping and baking',               $SUPPLIES, $roll),
            $c('Cling Wrap',     'Plastic cling wrap for covering and storing food',               $SUPPLIES, $roll),
            $c('Handgloves',     'Disposable food-safe gloves for food handling',                  $SUPPLIES, $pack),

            // ── OTHERS ───────────────────────────────────────────────────────
            $c('Cream of Pumpkin',       'Pumpkin cream soup base packet',                         $OTHERS, $packet),
            $c('Cream of Mushroom',      'Cream of mushroom soup base',                            $OTHERS, $kg),
            $c('Curry Powder',           'Blended curry spice powder for curries',                 $OTHERS, $packet),
            $c('Demiglace Sauce',        'Rich demi-glace sauce base for gravies',                 $OTHERS, $kg),
            $c('Bread Crumbs',           'Seasoned breadcrumbs for coating and toppings',          $OTHERS, $pack),
            $c('Atsuete',                'Annatto seeds or powder for coloring dishes',            $OTHERS, $pack),
            $c('Mushroom Shiitake',      'Dried shiitake mushrooms for soups and stir-fry',        $OTHERS, $pack),
            $c('Peanut Butter',          'Peanut butter in tub for kare-kare and spreads',         $OTHERS, $tub),
            $c('Pineapple Tidbits',      'Canned pineapple tidbits for sweet and sour dishes',     $OTHERS, $can),
            $c('Spanish Paprika',        'Smoked Spanish paprika powder for seasoning',            $OTHERS, $pack),
            $c('Tomato Paste',           'Concentrated tomato paste packet for sauces',            $OTHERS, $packet),
            $c('Tomato Sauce',           'Bottled tomato sauce for pasta and stews',               $OTHERS, $pcs),
            $c('Pineapple Chunks',       'Canned pineapple chunks for desserts and dishes',        $OTHERS, $can),
            $c('Pickle (Whole)',         'Whole pickled cucumbers in bottle',                      $OTHERS, $btl),
            $c('Pickle (Relish)',        'Sweet pickle relish in bottle',                          $OTHERS, $btl),
            $c('Salted Black Beans',     'Canned salted black beans for sauces and stir-fry',     $OTHERS, $can),
            $c('Onion Powder',           'Dehydrated onion powder for seasoning',                  $OTHERS, $pack),
            $c('Liver Spread',           'Canned liver spread for sandwiches and dishes',          $OTHERS, $can),
            $c('BBQ Marinade',           'Bottled BBQ marinade sauce for grilling',                $OTHERS, $btl),
            $c('Basil Leaves',           'Dried basil leaves in canister for Italian dishes',      $OTHERS, $canister),
            $c('Mayonnaise',             'Mayonnaise in tub for salads and sandwiches',            $OTHERS, $tub),
            $c('Crab and Corn',          'Crab and corn soup base packet',                         $OTHERS, $packet),
            $c('Cheese Sauce Mix',       'Cheese sauce powder mix packet',                         $OTHERS, $packet),
            $c('Garlic Powder',          'Dehydrated garlic powder for seasoning',                 $OTHERS, $kg),
            $c('Fried Garlic Granules',  'Crispy fried garlic granules for toppings',              $OTHERS, $kg),
            $c('Whole Red Parmiento',    'Canned whole red pimiento peppers',                      $OTHERS, $can),
            $c('Charcoal',               'Charcoal in sack for grilling and BBQ',                  $OTHERS, $sack),
            $c('Century Tuna Chunks',    'Canned tuna chunks in oil or water',                     $OTHERS, $can),
            $c('Fried Garlic',           'Crispy fried garlic packet for toppings',                $OTHERS, $packet),
            $c('Hoisin Sauce',           'Sweet Chinese hoisin sauce in case',                     $OTHERS, $cs),
            $c('Sweet Chili Sauce',      'Sweet chili dipping sauce in case',                      $OTHERS, $cs),
            $c('Lard',                   'Rendered pork lard in tub for cooking',                  $OTHERS, $tub),
            $c('Margarine',              'Margarine in tub for cooking and baking',                $OTHERS, $tub),
            $c('Rosemary',               'Dried rosemary herb in canister',                        $OTHERS, $canister),
            $c('Cayenne Powder',         'Cayenne chili powder in tub for seasoning',              $OTHERS, $tub),
            $c('Bamboo Shoot',           'Canned bamboo shoots for soups and stir-fry',            $OTHERS, $can),
            $c('Straw Mushroom',         'Canned straw mushrooms for soups and stir-fry',          $OTHERS, $can),
            $c('Whole Mushroom',         'Canned whole mushrooms in case',                         $OTHERS, $cs),

            // ── CLEANING MATERIALS ───────────────────────────────────────────
            $c('All Purpose Cleaner',          'Multi-surface all-purpose cleaner in carboy',       $CLEANING, $cby),
            $c('Chlorine Bleach & Sanitizer',  'Chlorine-based bleach and sanitizer in carboy',    $CLEANING, $cby),
            $c('Compact Tissue (16packs/box)',  'Compact tissue paper, 16 packs per box',           $CLEANING, $pack),
            $c('Dishwashing Liquid',           'Dishwashing liquid concentrate in carboy',          $CLEANING, $cby),
            $c('Fire Gel',                     'Fire-starting gel in tub for cooking ignition',    $CLEANING, $tub),
            $c('Floor Squeegee',               'Floor water squeegee for wet floor cleaning',      $CLEANING, $pcs),
            $c('Floor Squeegee Handle',        'Replacement handle for floor squeegee',            $CLEANING, $pcs),
            $c('Garbage Bag (Black) Med.',     'Black medium garbage bag in bundle',               $CLEANING, $bundle),
            $c('Garbage Bag (Black) XXL',      'Black extra-large garbage bag in bundle',          $CLEANING, $bundle),
            $c('Hand Soap',                    'Liquid hand soap in carboy for hygiene',           $CLEANING, $cby),
            $c('Mop Bucket Squeezer',          'Mop bucket with squeezer mechanism',               $CLEANING, $pcs),
            $c('Mop Head',                     'Replacement cotton mop head',                      $CLEANING, $pcs),
            $c('Mop Handle',                   'Mop stick handle for floor cleaning',              $CLEANING, $pcs),
            $c('Pop-up Tissue (60pcs/box)',     'Pop-up facial tissue, 60 pcs per box',            $CLEANING, $pcs),
            $c('Pot Holder',                   'Heat-resistant pot holder for kitchen use',        $CLEANING, $pcs),
            $c('Push Brush',                   'Heavy-duty push brush for floor scrubbing',        $CLEANING, $pcs),
            $c('Round Rugs White',             'White round floor rug for kitchen areas',          $CLEANING, $pcs),
            $c('Rubber Gloves',                'Chemical-resistant rubber gloves for cleaning',    $CLEANING, $pcs),
            $c('Scotch Brite 3M',              '3M Scotch-Brite scrubbing pad',                    $CLEANING, $pcs),
            $c('Scour Pad w/Foam',             'Dual-sided scouring pad with foam for dishwashing', $CLEANING, $pcs),
            $c('Soft Broom',                   'Soft-bristle broom for indoor sweeping',           $CLEANING, $pcs),
            $c('Spray Bottle',                 'Empty spray bottle for cleaning solutions',         $CLEANING, $pcs),
            $c('Squeegee w/Foam',              'Window squeegee with foam applicator',             $CLEANING, $pcs),
            $c('Stick Broom',                  'Stick broom for sweeping floors',                  $CLEANING, $pcs),

            // ── BOTTLED WATER ────────────────────────────────────────────────
            $c('Bottled Water', 'Purified drinking water in bottle',                                $WATER, $btl),
        ];

        foreach ($items as $row) {
            Item::firstOrCreate(
                ['name' => $row['name'], 'department_id' => $row['department_id']],
                $row
            );
        }
    }
}
