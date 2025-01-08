<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category; // Ensure the Category model is correctly namespaced

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the category hierarchy
        $categories = [
            'Accessories' => [
                'Umbrella',
                'Keychain' => [
                    'Keychain for Car',
                ],
                'Lace',
            ],
            'Apparel' => [
                'T-Shirt',
                'Polo Shirt',
                'Cap',
                'Bonnet',
                'Jacket',
            ],
            'Home & Kitchen' => [
                'CVSU Mug',
                'Case of Utensils',
                'Coffee Blend',
                'Fans',
            ],
            'Stationery' => [
                'Notebook',
                'Ballpens',
                'Books',
            ],
        ];

        foreach ($categories as $parentName => $children) {
            // Create parent category
            $parentCategory = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'image' => null, // You can set a default image path if available
                'parent_id' => null,
            ]);

            // Iterate through children
            foreach ($children as $childKey => $childValue) {
                if (is_array($childValue)) {
                    // If the child has its own children
                    $childCategory = Category::create([
                        'name' => $childKey,
                        'slug' => Str::slug($childKey),
                        'image' => null,
                        'parent_id' => $parentCategory->id,
                    ]);

                    // Iterate through grandchildren
                    foreach ($childValue as $grandchildName) {
                        Category::create([
                            'name' => $grandchildName,
                            'slug' => Str::slug($grandchildName),
                            'image' => null,
                            'parent_id' => $childCategory->id,
                        ]);
                    }
                } else {
                    // If the child has no further children
                    Category::create([
                        'name' => $childValue,
                        'slug' => Str::slug($childValue),
                        'image' => null,
                        'parent_id' => $parentCategory->id,
                    ]);
                }
            }
        }

        // Optionally, add any standalone categories here
        // Example:
        // Category::create([
        //     'name' => 'Books',
        //     'slug' => Str::slug('Books'),
        //     'image' => null,
        //     'parent_id' => null,
        // ]);
    }
}
