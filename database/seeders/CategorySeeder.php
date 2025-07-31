<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the category hierarchy.
        // Under Apparel, we add two subcategories: "PE" and "Cspear".
        $categories = [
            'Accessories' => [
                'Umbrella',
                'Keychain' => [
                    'Keychain for Car',
                ],
                'Lace',
                'Pin',
            ],
            'Apparel' => [
                'T-Shirt',
                'Cap',
                'Bonnet',
                'Jacket',
                'Polo',
                'NSTP',
                'PE' => [
                    'pe tshirt',
                    'pe short',
                ],
                'Cspear' => [
                    'Cspear tshirt',
                    'Cspear short',
                    'Cspear jogging pants',
                ],
                'Male Uniform',
                'Female Uniform',
            ],
            'Home & Kitchen' => [
                'Mug',
                'Utensils',
                'Coffee Blend',
                'Fans',
                'Tumbler',
            ],
            'Stationery' => [
                'Notebook',
                'Ballpens',
                'Books',
            ],
        ];

        // Map categories to their respective images
        $categoryImages = [
            'Accessories' => '/IMG_0121.jpg',
            'Apparel' => '/IMG_0123.jpg',
            'Home & Kitchen' => '/coffee.jpg',
            'Stationery' => '/IMG_0125.jpg',
            'T-Shirt' => '/IMG_0127.jpg',
            'Cap' => '/IMG_0130.jpg',
            'Bonnet' => '/IMG_0132.jpg',
            'Jacket' => '/IMG_0134.jpg',
            'Polo' => '/IMG_0136.jpg',
            'NSTP' => '/IMG_0137.jpg',
            'PE' => '/IMG_0140.jpg',
            'Cspear' => '/IMG_0142.jpg',
            'Male Uniform' => '/IMG_0145.jpg',
            'Female Uniform' => '/IMG_0147.jpg',
            'Mug' => '/coffee2.jpg',
            'Utensils' => '/IMG_0148.jpg',
            'Coffee Blend' => '/coffee3.jpg',
            'Fans' => '/IMG_0150.jpg',
            'Tumbler' => '/IMG_0152.jpg',
            'Notebook' => '/IMG_0155.jpg',
            'Ballpens' => '/IMG_0156.jpg',
            'Books' => '/IMG_0159.jpg',
        ];

        // Loop through top-level categories.
        foreach ($categories as $parentName => $children) {
            $parentCategory = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'image' => $categoryImages[$parentName] ?? '/IMG_0161.jpg', // Fallback image if not found
                'parent_id' => null,
            ]);

            // Loop through children.
            foreach ($children as $childKey => $childValue) {
                if (is_array($childValue)) {
                    // For subcategories: $childKey is the subcategory name.
                    $childCategory = Category::create([
                        'name' => $childKey,
                        'slug' => Str::slug($childKey),
                        'image' => $categoryImages[$childKey] ?? '/IMG_0161.jpg',
                        'parent_id' => $parentCategory->id,
                    ]);

                    // Create grandchildren.
                    foreach ($childValue as $grandchildName) {
                        Category::create([
                            'name' => $grandchildName,
                            'slug' => Str::slug($grandchildName),
                            'image' => $categoryImages[$grandchildName] ?? '/IMG_0161.jpg',
                            'parent_id' => $childCategory->id,
                        ]);
                    }
                } else {
                    // For simple children.
                    Category::create([
                        'name' => $childValue,
                        'slug' => Str::slug($childValue),
                        'image' => $categoryImages[$childValue] ?? '/IMG_0161.jpg',
                        'parent_id' => $parentCategory->id,
                    ]);
                }
            }
        }
    }
}
