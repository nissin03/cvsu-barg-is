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

        // Loop through top-level categories.
        foreach ($categories as $parentName => $children) {
            $parentCategory = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'image' => null, // Adjust if you have a default image.
                'parent_id' => null,
            ]);

            // Loop through children.
            foreach ($children as $childKey => $childValue) {
                if (is_array($childValue)) {
                    // For subcategories: $childKey is the subcategory name.
                    $childCategory = Category::create([
                        'name' => $childKey,
                        'slug' => Str::slug($childKey),
                        'image' => null,
                        'parent_id' => $parentCategory->id,
                    ]);

                    // Create grandchildren.
                    foreach ($childValue as $grandchildName) {
                        Category::create([
                            'name' => $grandchildName,
                            'slug' => Str::slug($grandchildName),
                            'image' => null,
                            'parent_id' => $childCategory->id,
                        ]);
                    }
                } else {
                    // For simple children.
                    Category::create([
                        'name' => $childValue,
                        'slug' => Str::slug($childValue),
                        'image' => null,
                        'parent_id' => $parentCategory->id,
                    ]);
                }
            }
        }
    }
}
