<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define image mappings for each product (ensure these files exist in public/uploads/products).
        $imageMappings = [
            'Umbrella' => [
                'image'  => 'IMG_0109.jpg',
                'images' => ['IMG_0109.jpg', 'IMG_0109.jpg'],
            ],
            'Keychain' => [
                'image'  => 'IMG_0121.jpg',
                'images' => ['IMG_0121.jpg', 'IMG_0121.jpg'],
            ],
            'Keychain for Car' => [
                'image'  => 'IMG_0127.jpg',
                'images' => ['IMG_0127.jpg', 'IMG_0127.jpg'],
            ],
            'CVSU Mug' => [
                'image'  => 'IMG_0113.jpg',
                'images' => ['IMG_0113.jpg', 'IMG_0114.jpg', 'mug.jpg'],
            ],
            'Cap' => [
                'image'  => 'IMG_0125.jpg',
                'images' => ['IMG_0125.jpg', 'IMG_0125.jpg'],
            ],
            'Bonnet' => [
                'image'  => 'IMG_0123.jpg',
                'images' => ['IMG_0123.jpg', 'IMG_0123.jpg'],
            ],
            'T-Shirt' => [
                'image'  => 'IMG_0136.jpg',
                'images' => ['IMG_0136.jpg', 'IMG_0137.jpg'],
            ],
            'Polo Shirt' => [
                'image'  => 'IMG_0140.jpg',
                'images' => ['IMG_0142.jpg', 'IMG_0145.jpg'],
            ],
            'Case of Utensils' => [
                'image'  => 'IMG_0147.jpg',
                'images' => ['IMG_0147.jpg', 'IMG_0147.jpg'],
            ],
            'Notebook' => [
                'image'  => 'IMG_0148.jpg',
                'images' => ['IMG_0148.jpg', 'IMG_0148.jpg'],
            ],
            'Coffee Blend' => [
                'image'  => 'blend_coffee.jpg',
                'images' => ['IMG_0150.jpg', 'IMG_0152.jpg', 'IMG_0155.jpg', 'IMG_0156.jpg', 'original_blend.jpg', 'liberica_based.jpg', 'anti_oxidant.jpg', 'naturally_sweetened.jpg'],
            ],
            'CvSU Fans' => [
                'image'  => 'IMG_0159.jpg',
                'images' => ['IMG_0159.jpg', 'IMG_0159.jpg'],
            ],
            'Lace' => [
                'image'  => 'lace.jpg',
                'images' => ['lace_2.jpg', 'lace_3.jpg', 'lace_4    .jpg', 'lace_5.jpg'],
            ],
            'Jacket' => [
                'image'  => 'IMG_0161.jpg',
                'images' => ['IMG_0161.jpg', 'IMG_0161.jpg', 'jacket.jpg'],
            ],
            'Ballpens' => [
                'image'  => 'IMG_0163.jpg',
                'images' => ['IMG_0163.jpg', 'IMG_0164.jpg'],
            ],
            'CvSU Ballpen' => [
                'image'  => 'IMG_0163.jpg',
                'images' => ['IMG_0163.jpg', 'IMG_0164.jpg'],
            ],
            'Books' => [
                'image'  => 'IMG_0171.jpg',
                'images' => ['IMG_0175.jpg', 'IMG_0176.jpg', 'IMG_0180.jpg', 'ballpen.jpg'],
            ],
            'Blouse' => [
                'image'  => 'blouse.jpg',
                'images' => ['blouse.jpg'],
            ],
            'Slacks' => [
                'image'  => 'slacks.jpg',
                'images' => ['slacks.jpg'],
            ],
            'pe tshirt' => [
                'image'  => 'pe_t-shirt.jpg',
                'images' => ['pe_t-shirt.jpg'],
            ],
            'junior pe shirt' => [
                'image'  => 'junior_pe_shirt.jpg',
                'images' => ['junior_pe_shirt.jpg'],
            ],
            'senior pe shirt' => [
                'image'  => 'senior_pe_shirt.jpg',
                'images' => ['senior_pe_shirt.jpg'],
            ],
            'pe short' => [
                'image'  => 'pe_short.jpg',
                'images' => ['pe_short.jpg'],
            ],
            'junior short' => [
                'image'  => 'junior_short.jpg',
                'images' => ['junior_short.jpg'],
            ],
            'senior jogging pants' => [
                'image'  => 'senior_jogging_pants.jpg',
                'images' => ['senior_jogging_pants.jpg'],
            ],
            'polo' => [
                'image'  => 'polo.jpg',
                'images' => ['polo.jpg'],
            ],
            'NSTP t-shirt' => [
                'image'  => 'NSTP_t-shirt.jpg',
                'images' => ['NSTP_t-shirt.jpg'],
            ],
            'cspear tshirt' => [
                'image'  => 'cspear_t-shirt.jpg',
                'images' => ['cspear_t-shirt.jpg'],
            ],
            'cspear short' => [
                'image'  => 'cspear_shorts.jpg',
                'images' => ['cspear_shorts.jpg'],
            ],
            'cspear jogging pants' => [
                'image'  => 'cspear_jogging-pants.jpg',
                'images' => ['cspear_jogging-pants.jpg'],
            ],
            'semi-golf umbrella manual' => [
                'image'  => 'semi_golf_umbrella_manual.jpg',
                'images' => ['semi_golf_umbrella_manual.jpg'],
            ],
            'semi-golf umbrella automatic' => [
                'image'  => 'semi_golf_umbrella_automatic.jpg',
                'images' => ['semi_golf_umbrella_automatic.jpg'],
            ],
            'cvsu pin' => [
                'image'  => 'cvsu_pin.jpg',
                'images' => ['cvsu_pin.jpg'],
            ],
            'tumbler' => [
                'image'  => 'tumbler.jpg',
                'images' => ['tumbler.jpg'],
            ],
        ];

        $products = [
            [
                'name'       => 'Umbrella',
                'category'   => 'Accessories > Umbrella',
                'price'      => 220,
                'attributes' => [],
            ],
            [
                'name'       => 'Keychain',
                'category'   => 'Accessories > Keychain',
                'price'      => 100,
                'attributes' => [],
            ],
            [
                'name'       => 'Keychain for Car',
                'category'   => 'Accessories > Keychain',
                'price'      => 100,
                'attributes' => [],
            ],
            [
                'name'       => 'CVSU Mug',
                'category'   => 'Home & Kitchen > Mug',
                'price'      => 70,
                'attributes' => [
                    'CvSU Mug' => [
                        'CvSU Mug'        => 70,
                        'CvSU Mug w/ Box' => 80,
                    ],
                ],
            ],
            [
                'name'       => 'Cap',
                'category'   => 'Apparel > Cap',
                'attributes' => [],
            ],
            [
                'name'       => 'Bonnet',
                'category'   => 'Apparel > Bonnet',
                'attributes' => [],
            ],
            [
                'name'       => 'T-Shirt',
                'category'   => 'Apparel > T-Shirt',
                'price'      => 220,
                'attributes' => [],
            ],
            [
                'name'       => 'Polo Shirt',
                'category'   => 'Apparel > Polo',
                'price'      => 240,
                'attributes' => [],
            ],
            [
                'name'       => 'Case of Utensils',
                'category'   => 'Home & Kitchen > Utensils',
                'attributes' => [],
            ],
            [
                'name'       => 'Notebook',
                'category'   => 'Stationery > Notebook',
                'price'      => 125,
                'attributes' => [],
            ],
            [
                'name'       => 'Coffee Blend',
                'category'   => 'Home & Kitchen > Coffee Blend',
                'price'      => 250,
                'attributes' => [
                    'Types' => [
                        'Naturally-Sweetened'  => 280,
                        'Liberica-Based'       => 260,
                        'Antioxidant-Enriched'  => 290,
                        'Original Blend'       => 250,
                    ],
                ],
            ],
            [
                'name'       => 'CvSU Fans',
                'category'   => 'Home & Kitchen > Fans',
                'price'      => 50,
                'attributes' => [],
            ],
            [
                'name'       => 'Lace',
                'category'   => 'Accessories > Lace',
                'price'      => 70,
                'attributes' => [],
            ],
            [
                'name'       => 'Jacket',
                'category'   => 'Apparel > Jacket',
                'price'      => 660,
                'attributes' => [],
            ],
            [
                'name'       => 'CvSU Ballpen',
                'category'   => 'Stationery > Ballpens',
                'price'      => 40,
                'attributes' => [],
            ],
            [
                'name'       => 'Ballpens',
                'category'   => 'Stationery > Ballpens',
                'price'      => 40,
                'attributes' => [
                    'Colors' => [
                        'Black' => 40,
                        'Green' => 40,
                    ],
                    'Types' => [
                        'Parker' => 50,
                    ],
                ],
            ],
            [
                'name'       => 'Books',
                'category'   => 'Stationery > Books',
                'attributes' => [],
            ],
            // New products with variant prices:
            [
                'name'       => 'Blouse',
                'category'   => 'Apparel > Female Uniform',
                'price'      => 325,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 325,
                        's'   => 325,
                        'm'   => 325,
                        'l'   => 325,
                        'xl'  => 340,
                        '2xl' => 355,
                        '3xl' => 365,
                        '4xl' => 380,
                        '5xl' => 390,
                        '6xl' => 405,
                    ],
                ],
            ],
            [
                'name'       => 'Slacks',
                'category'   => 'Apparel > Female Uniform',
                'price'      => 375,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 375,
                        's'   => 390,
                        'm'   => 410,
                        'l'   => 430,
                        'xl'  => 450,
                        '2xl' => 470,
                        '3xl' => 490,
                        '4xl' => 510,
                        '5xl' => 530,
                        '6xl' => 550,
                    ],
                ],
            ],
            [
                'name'       => 'pe tshirt',
                'category'   => 'Apparel > PE',
                'price'      => 315,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 315,
                        's'   => 315,
                        'm'   => 315,
                        'l'   => 315,
                        'xl'  => 325,
                        '2xl' => 340,
                        '3xl' => 355,
                        '4xl' => 365,
                        '5xl' => 380,
                        '6xl' => 390,
                        '7xl' => 395,
                    ],
                ],
            ],
            [
                'name'       => 'junior pe shirt',
                'category'   => 'Apparel > PE',
                'price'      => 485,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 485,
                        's'   => 495,
                        'm'   => 510,
                        'l'   => 520,
                        'xl'  => 535,
                        '2xl' => 550,
                        '3xl' => 560,
                    ],
                ],
            ],
            [
                'name'       => 'senior pe shirt',
                'category'   => 'Apparel > PE',
                'price'      => 495,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 495,
                        's'   => 495,
                        'm'   => 510,
                        'l'   => 520,
                        'xl'  => 535,
                        '2xl' => 550,
                        '3xl' => 560,
                    ],
                ],
            ],
            [
                'name'       => 'pe short',
                'category'   => 'Apparel > PE',
                'price'      => 315,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 315,
                        's'   => 315,
                        'm'   => 315,
                        'l'   => 315,
                        'xl'  => 325,
                        '2xl' => 340,
                        '3xl' => 355,
                        '4xl' => 365,
                        '5xl' => 380,
                        '6xl' => 390,
                    ],
                ],
            ],
            [
                'name'       => 'junior short',
                'category'   => 'Apparel > PE',
                'price'      => 340,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 340,
                        's'   => 355,
                        'm'   => 365,
                        'l'   => 380,
                        'xl'  => 390,
                        '2xl' => 405,
                        '3xl' => 420,
                    ],
                ],
            ],
            [
                'name'       => 'senior jogging pants',
                'category'   => 'Apparel > PE',
                'price'      => 510,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 510,
                        's'   => 520,
                        'm'   => 535,
                        'l'   => 550,
                        'xl'  => 560,
                        '2xl' => 575,
                        '3xl' => 585,
                    ],
                ],
            ],
            [
                'name'       => 'polo',
                'category'   => 'Apparel > Male Uniform',
                'price'      => 365,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 365,
                        's'   => 365,
                        'm'   => 365,
                        'l'   => 365,
                        'xl'  => 385,
                        '2xl' => 405,
                        '3xl' => 425,
                        '4xl' => 445,
                        '5xl' => 465,
                        '6xl' => 485,
                        '7xl' => 505,
                    ],
                ],
            ],
            [
                'name'       => 'NSTP t-shirt',
                'category'   => 'Apparel > NSTP',
                'price'      => 315,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 315,
                        's'   => 315,
                        'm'   => 315,
                        'l'   => 315,
                        'xl'  => 325,
                        '2xl' => 340,
                        '3xl' => 355,
                        '4xl' => 365,
                        '5xl' => 380,
                        '6xl' => 390,
                    ],
                ],
            ],
            [
                'name'       => 'cspear tshirt',
                'category'   => 'Apparel > Cspear',
                'price'      => 260,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 260,
                        's'   => 260,
                        'm'   => 260,
                        'l'   => 260,
                        'xl'  => 275,
                        '2xl' => 285,
                        '3xl' => 300,
                        '4xl' => 310,
                    ],
                ],
            ],
            [
                'name'       => 'cspear short',
                'category'   => 'Apparel > Cspear',
                'price'      => 310,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 310,
                        's'   => 310,
                        'm'   => 325,
                        'l'   => 325,
                        'xl'  => 335,
                        '2xl' => 350,
                        '3xl' => 360,
                        '4xl' => 375,
                    ],
                ],
            ],
            [
                'name'       => 'cspear jogging pants',
                'category'   => 'Apparel > Cspear',
                'price'      => 400,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 400,
                        's'   => 410,
                        'm'   => 425,
                        'l'   => 435,
                        'xl'  => 450,
                        '2xl' => 460,
                        '3xl' => 475,
                        '4xl' => 485,
                    ],
                ],
            ],
            [
                'name'       => 'semi-golf umbrella manual',
                'category'   => 'Accessories > Umbrella',
                'price'      => 280,
                'attributes' => [],
            ],
            [
                'name'       => 'semi-golf umbrella automatic',
                'category'   => 'Accessories > Umbrella',
                'price'      => 340,
                'attributes' => [],
            ],
            [
                'name'       => 'cvsu pin',
                'category'   => 'Accessories > Pin',
                'price'      => 100,
                'attributes' => [],
            ],
            [
                'name'       => 'tumbler',
                'category'   => 'Home & Kitchen > Tumbler',
                'price'      => 220,
                'attributes' => [],
            ],
        ];

        // Loop through each product definition.
        foreach ($products as $productData) {
            // Retrieve the category using the provided path.
            $category = $this->getCategoryByPath($productData['category']);
            if (!$category) {
                $this->command->error("Category not found for product: {$productData['name']}");
                continue;
            }

            // Retrieve the image mapping.
            $images = $imageMappings[$productData['name']] ?? null;
            if (!$images) {
                $this->command->error("Image mapping not found for product: {$productData['name']}");
                continue;
            }

            // Use the provided price if available; otherwise, generate a random price.
            $basePrice = isset($productData['price']) ? $productData['price'] : (rand(1000, 10000) / 100);

            // Create the product.
            $product = Product::create([
                'name'                  => $productData['name'],
                'slug'                  => Str::slug($productData['name']),
                'short_description'     => 'Short description for ' . $productData['name'],
                'description'           => 'Detailed description for ' . $productData['name'],
                'price'                 => $basePrice,
                'quantity'              => 50,
                'stock_status'          => 'instock',
                'reorder_quantity'      => 10,
                'outofstock_quantity'   => 0,
                'featured'              => true,
                'image'                 => $images['image'],
                'images'                => implode(',', $images['images']),
                // 'sex'                   => 'all',
                'low_stock_notified'    => false,
                'archived'              => false,
                'archived_at'           => null,
                'category_id'           => $category->id,
                'product_attribute_id'  => null,
            ]);

            // Process attributes if provided.
            if (!empty($productData['attributes'])) {
                foreach ($productData['attributes'] as $attributeName => $values) {
                    // Create or retrieve the product attribute.
                    $attribute = ProductAttribute::firstOrCreate(
                        ['name' => $attributeName],
                        ['name' => $attributeName]
                    );

                    // For the first attribute, assign its ID to the product.
                    if (is_null($product->product_attribute_id)) {
                        $product->product_attribute_id = $attribute->id;
                        $product->save();
                    }

                    // Determine if the values array is associative.
                    $isAssociative = array_keys($values) !== range(0, count($values) - 1);

                    // Create attribute values.
                    foreach ($values as $variantKey => $variantValue) {
                        $price = $isAssociative ? $variantValue : (rand(1000, 5000) / 100);
                        ProductAttributeValue::create([
                            'product_attribute_id' => $attribute->id,
                            'product_id'           => $product->id,
                            'value'                => $variantKey,
                            'description'          => 'Description for ' . $variantKey . ' of ' . $productData['name'],
                            'price'                => $price,
                            'quantity'             => rand(10, 100),
                            'stock_status'         => 'instock',
                        ]);
                    }
                }
                // Since the product has attributes, remove the general (base) price.
                $product->price = 0;
                $product->save();
            }
        }

        // $this->command->info('Products seeded successfully with variant prices replacing the general price!');
    }

    /**
     * Helper function to get Category by path (e.g., "Accessories > Keychain").
     */
    private function getCategoryByPath(string $path): ?Category
    {
        // Split the path by ">" and trim each part.
        $names = array_map('trim', explode('>', $path));
        $parent = null;
        foreach ($names as $name) {
            $query = Category::where('name', $name);
            if ($parent) {
                $query->where('parent_id', $parent->id);
            } else {
                $query->whereNull('parent_id');
            }
            $parent = $query->first();
            if (!$parent) {
                return null;
            }
        }
        return $parent;
    }
}
