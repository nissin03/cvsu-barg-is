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
            'Fans' => [
                'image'  => 'IMG_0159.jpg',
                'images' => ['IMG_0159.jpg', 'IMG_0159.jpg'],
            ],
            'Lace' => [
                'image'  => 'lace.jpg',
                'images' => ['lace_2.jpg', 'lace_3.jpg', 'lace_4.jpg', 'lace_5.jpg'],
            ],
            'Jacket' => [
                'image'  => 'IMG_0161.jpg',
                'images' => ['IMG_0161.jpg', 'IMG_0161.jpg', 'jacket.jpg'],
            ],
            'Ballpens' => [
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
            'pe short' => [
                'image'  => 'pe_short.jpg',
                'images' => ['pe_short.jpg'],
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

        // Define product data.
        // Note: For products with variants, the category string includes a subcategory.
        // For example, products that should be grouped under PE have a category "Apparel > PE".
        // (Your CategorySeeder must have created both "Apparel" and its children "PE" and "Cspear", etc.)
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
                'name'       => 'Fans',
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
                'name'       => 'Ballpens',
                'category'   => 'Stationery > Ballpens',
                'price'      => 40,
                'attributes' => [],
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
                'price'      => 275,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 275,
                        's'   => 275,
                        'm'   => 275,
                        'l'   => 275,
                        'xl'  => 290,
                        '2xl' => 300,
                        '3xl' => 315,
                        '4xl' => 330,
                        '5xl' => 340,
                        '6xl' => 350,
                    ],
                ],
            ],
            [
                'name'       => 'Slacks',
                'category'   => 'Apparel > Female Uniform',
                'price'      => 320,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 320,
                        's'   => 340,
                        'm'   => 360,
                        'l'   => 375,
                        'xl'  => 400,
                        '2xl' => 415,
                        '3xl' => 435,
                        '4xl' => 450,
                        '5xl' => 470,
                        '6xl' => 490,
                    ],
                ],
            ],
            [
                'name'       => 'pe tshirt',
                'category'   => 'Apparel > PE',
                'price'      => 270,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 250,
                        's'   => 250,
                        'm'   => 250,
                        'l'   => 250,
                        'xl'  => 270,
                        '2xl' => 275,
                        '3xl' => 290,
                        '4xl' => 300,
                        '5xl' => 315,
                        '6xl' => 325,
                    ],
                ],
            ],
            [
                'name'       => 'pe short',
                'category'   => 'Apparel > PE',
                'price'      => 270,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 250,
                        's'   => 250,
                        'm'   => 250,
                        'l'   => 250,
                        'xl'  => 270,
                        '2xl' => 275,
                        '3xl' => 290,
                        '4xl' => 300,
                        '5xl' => 315,
                        '6xl' => 325,
                    ],
                ],
            ],
            [
                'name'       => 'polo',
                'category'   => 'Apparel > Male Uniform',
                'price'      => 315,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 315,
                        's'   => 315,
                        'm'   => 315,
                        'l'   => 315,
                        'xl'  => 335,
                        '2xl' => 350,
                        '3xl' => 370,
                        '4xl' => 390,
                        '5xl' => 410,
                        '6xl' => 425,
                    ],
                ],
            ],
            [
                'name'       => 'NSTP t-shirt',
                'category'   => 'Apparel > NSTP',
                'price'      => 250,
                'attributes' => [
                    'Sizes' => [
                        'xs'  => 250,
                        's'   => 250,
                        'm'   => 250,
                        'l'   => 250,
                        'xl'  => 270,
                        '2xl' => 275,
                        '3xl' => 290,
                        '4xl' => 300,
                        '5xl' => 315,
                        '6xl' => 325,
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
                'featured'              => false,
                'image'                 => $images['image'],
                'images'                => implode(',', $images['images']),
                'sex'                   => 'all',
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
