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
        // Define image mappings for each product
        $imageMappings = [
            'Umbrella' => [
                'image' => 'images/barg_images/IMG_0109.jpg',
                'images' => [
                    'images/barg_images/IMG_0109.jpg',
                    'images/barg_images/IMG_0109.jpg',
                ],
            ],
            'Keychain' => [
                'image' => 'images/barg_images/IMG_0121.jpg',
                'images' => [
                    'images/barg_images/IMG_0121.jpg',
                    'images/barg_images/IMG_0121.jpg',
                ],
            ],
            'CVSU Mug' => [
                'image' => 'images/barg_images/IMG_0113.jpg',
                'images' => [
                    'images/barg_images/IMG_0113.jpg',
                    'images/barg_images/IMG_0114.jpg',
                ],
            ],
            'Cap' => [
                'image' => 'images/barg_images/IMG_0125.jpg',
                'images' => [
                    'images/barg_images/IMG_0125.jpg',
                    'images/barg_images/IMG_0125.jpg',
                ],
            ],
            'Bonnet' => [
                'image' => 'images/barg_images/IMG_0123.jpg',
                'images' => [
                    'images/barg_images/IMG_0123.jpg',
                    'images/barg_images/IMG_0123.jpg',
                ],
            ],
            'Keychain for Car' => [
                'image' => 'images/barg_images/IMG_0127.jpg',
                'images' => [
                    'images/barg_images/IMG_0127.jpg',
                    'images/barg_images/IMG_0127.jpg',
                ],
            ],
            'T-Shirt' => [
                'image' => 'images/barg_images/IMG_0136.jpg',
                'images' => [
                    'images/barg_images/IMG_0136.jpg',
                    'images/barg_images/IMG_0137.jpg',
                ],
            ],
            'Polo Shirt' => [
                'image' => 'images/barg_images/IMG_0140.jpg',
                'images' => [
                    'images/barg_images/IMG_0142.jpg',
                    'images/barg_images/IMG_0145.jpg',
                ],
            ],
            'Case of Utensils' => [
                'image' => 'images/barg_images/IMG_0147.jpg',
                'images' => [
                    'images/barg_images/IMG_0147.jpg',
                    'images/barg_images/IMG_0147.jpg',
                ],
            ],
            'Notebook' => [
                'image' => 'images/barg_images/IMG_0148.jpg',
                'images' => [
                    'images/barg_images/IMG_0148.jpg',
                    'images/barg_images/IMG_0148.jpg',
                ],
            ],
            'Coffee Blend' => [
                'image' => 'images/barg_images/IMG_0150.jpg',
                'images' => [
                    'images/barg_images/IMG_0152.jpg',
                    'images/barg_images/IMG_0155.jpg',
                    'images/barg_images/IMG_0156.jpg',
                ],
            ],
            'Fans' => [
                'image' => 'images/barg_images/IMG_0159.jpg',
                'images' => [
                    'images/barg_images/IMG_0159.jpg',
                    'images/barg_images/IMG_0159.jpg',
                ],
            ],
            'Lace' => [
                'image' => 'images/barg_images/IMG_0161.jpg',
                'images' => [
                    'images/barg_images/IMG_0161.jpg',
                    'images/barg_images/IMG_0161.jpg',
                ],
            ],
            'Jacket' => [
                'image' => 'images/barg_images/IMG_0161.jpg',
                'images' => [
                    'images/barg_images/IMG_0161.jpg',
                    'images/barg_images/IMG_0161.jpg',
                ],
            ],
            'Ballpens' => [
                'image' => 'images/barg_images/IMG_0163.jpg',
                'images' => [
                    'images/barg_images/IMG_0163.jpg',
                    'images/barg_images/IMG_0161.jpg',
                ],
            ],
            'Books' => [
                'image' => 'images/barg_images/IMG_0171.jpg',
                'images' => [
                    'images/barg_images/IMG_0175.jpg',
                    'images/barg_images/IMG_0176.jpg',
                    'images/barg_images/IMG_0180.jpg',
                ],
            ], 
            
        ];

        $products = [
            [
                'name' => 'Umbrella',
                'category' => 'Accessories',
                'attributes' => [],
            ],
            [
                'name' => 'Keychain',
                'category' => 'Accessories',
                'attributes' => [],
            ],
            [
                'name' => 'Keychain for Car',
                'category' => 'Accessories > Keychain',
                'attributes' => [],
            ],
            [
                'name' => 'CVSU Mug',
                'category' => 'Home & Kitchen',
                'attributes' => [],
            ],
            [
                'name' => 'Cap',
                'category' => 'Apparel',
                'attributes' => [],
            ],
            [
                'name' => 'Bonnet',
                'category' => 'Apparel',
                'attributes' => [],
            ],
            [
                'name' => 'T-Shirt',
                'category' => 'Apparel',
                'attributes' => [
                    'Sizes' => ['Small', 'Medium', 'Large', 'X-Large'],
                    'Colors' => ['Red', 'Blue', 'Green', 'Black'],
                ],
            ],
            [
                'name' => 'Polo Shirt',
                'category' => 'Apparel',
                'attributes' => [
                    'Sizes' => ['Small', 'Medium', 'Large', 'X-Large'],
                    'Colors' => ['White', 'Black', 'Navy', 'Gray'],
                ],
            ],
            [
                'name' => 'Case of Utensils',
                'category' => 'Home & Kitchen',
                'attributes' => [],
            ],
            [
                'name' => 'Notebook',
                'category' => 'Stationery',
                'attributes' => [],
            ],
            [
                'name' => 'Coffee Blend',
                'category' => 'Home & Kitchen',
                'attributes' => [
                    'Types' => ['Naturally-Sweetened', 'Liberica-Based', 'Antioxidant-Enriched'],
                ],
            ],
            [
                'name' => 'Fans',
                'category' => 'Home & Kitchen',
                'attributes' => [],
            ],
            [
                'name' => 'Lace',
                'category' => 'Accessories',
                'attributes' => [],
            ],
            [
                'name' => 'Jacket',
                'category' => 'Apparel',
                'attributes' => [],
            ],
            [
                'name' => 'Ballpens',
                'category' => 'Stationery',
                'attributes' => [
                    'Colors' => ['Black', 'Blue', 'Red'],
                ],
            ],
            [
                'name' => 'Books',
                'category' => 'Stationery',
                'attributes' => [],
            ],
        ];

        foreach ($products as $productData) {
            // Fetch or create the category based on the category path
            $category = $this->getCategoryByPath($productData['category']);

            if (!$category) {
                $this->command->error("Category not found for product: {$productData['name']}");
                continue;
            }

            // Retrieve image mapping for the product
            $images = $imageMappings[$productData['name']] ?? null;

            if (!$images) {
                $this->command->error("Image mapping not found for product: {$productData['name']}");
                continue;
            }

            // Create the product
            $product = Product::create([
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'short_description' => 'Short description for ' . $productData['name'],
                'description' => 'Detailed description for ' . $productData['name'],
                'price' => rand(1000, 10000) / 100, // Random price between 10.00 and 100.00
                'quantity' => 50,
                'stock_status' => 'instock',
                'reorder_quantity' => 10,
                'outofstock_quantity' => 0,
                'featured' => false,
                'image' => $images['image'], // Assign primary image
                'images' => json_encode($images['images']), // Assign additional images as JSON
                'sex' => 'all',
                'low_stock_notified' => false,
                'archived' => false,
                'archived_at' => null,
                'category_id' => $category->id,
                'product_attribute_id' => null, // Will be set if there's a primary attribute
            ]);

            // Handle product attributes if any
            if (!empty($productData['attributes'])) {
                foreach ($productData['attributes'] as $attributeName => $values) {
                    // Create or fetch the ProductAttribute
                    $attribute = ProductAttribute::firstOrCreate(
                        ['name' => $attributeName],
                        ['name' => $attributeName]
                    );

                    // If this is the first attribute, assign it to the product's product_attribute_id
                    if (is_null($product->product_attribute_id)) {
                        $product->product_attribute_id = $attribute->id;
                        $product->save();
                    }

                    // Create ProductAttributeValues
                    foreach ($values as $value) {
                        ProductAttributeValue::create([
                            'product_attribute_id' => $attribute->id,
                            'product_id' => $product->id,
                            'value' => $value,
                            'price' => rand(1000, 5000) / 100, // Random additional price between 10.00 and 50.00
                            'quantity' => rand(10, 100),
                            'stock_status' => 'instock',
                        ]);
                    }
                }
            }
        }

        $this->command->info('Products seeded successfully with specific images!');
    }

    /**
     * Helper function to get Category by path (e.g., 'Accessories > Keychain')
     */
    private function getCategoryByPath(string $path): ?Category
    {
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
