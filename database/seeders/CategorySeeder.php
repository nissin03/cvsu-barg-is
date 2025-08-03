<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;
use File;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categories = [
            'Accessories' => [
                'image' => 'lace_6.jpg',  // Parent category image
                'Umbrella' => 'semi_golf_umbrella_manual.jpg',
                'Keychain' => 'key_holder.jpg',
                'Lace' => 'lace.jpg',
                'Pin' => 'cvsu_pin.jpg',
            ],
            'Apparel' => [
                'image' => 'IMG_0136.jpg', // Parent category image
                'T-Shirt' => 'IMG_0137.jpg',
                'Cap' => 'IMG_0125.jpg',
                'Bonnet' => 'IMG_0123.jpg',
                'Jacket' => 'IMG_0161.jpg',
                'Polo' => 'polo.jpg',
                'NSTP' => 'NSTP_t-shirt.jpg',
                'PE' => [
                    'image' => 'pe_category.jpg', // Added image for PE parent category
                    'pe tshirt' => 'pe_t-shirt.jpg',
                    'pe short' => 'pe_short.jpg',
                ],
                'Cspear' => [
                    'image' => 'cspear_logo.jpg', // Added image for Cspear parent category
                    'cspear tshirt' => 'cspear_t-shirt.jpg',
                    'cspear short' => 'cspear_shorts.jpg',
                    'cspear jogging pants' => 'cspear_jogging-pants.jpg',
                ],
                'Male Uniform' => 'polo.jpg',
                'Female Uniform' => 'blouse.jpg',
            ],
            'Home & Kitchen' => [
                'image' => 'IMG_0113_2.jpg', // Parent category image
                'Mug' => 'IMG_0113.jpg',
                'Utensils' => 'IMG_0147.jpg',
                'Coffee Blend' => 'blend_coffee.jpg',
                'Fans' => 'IMG_0159.jpg',
                'Tumbler' => 'tumbler.jpg',
            ],
            'Stationery' => [
                'image' => 'cat_stationary.jpg', // Parent category image
                'Notebook' => 'IMG_0148.jpg',
                'Ballpens' => 'IMG_0163.jpg',
                'Books' => 'IMG_0171.jpg',
            ],
        ];

        // Loop through top-level categories.
        foreach ($categories as $parentName => $children) {
            // Check if the parent category has an image.
            $parentImage = isset($children['image']) ? $this->getImageName($children['image']) : null;

            // Create the parent category
            $parentCategory = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'image' => $parentImage, // Store the parent category image if it exists
                'parent_id' => null,
            ]);

            // Remove the 'image' key from children to loop correctly
            unset($children['image']);

            // Loop through children.
            foreach ($children as $childKey => $childValue) {
                if (is_array($childValue)) {
                    // For subcategories
                    $childImage = isset($childValue['image']) ? $this->getImageName($childValue['image']) : null;
                    $childCategory = Category::create([
                        'name' => $childKey,
                        'slug' => Str::slug($childKey),
                        'image' => $childImage, // Use the subcategory image if it exists
                        'parent_id' => $parentCategory->id,
                    ]);

                    // Create grandchildren (subcategories with images)
                    foreach ($childValue as $grandchildName => $imageName) {
                        if ($grandchildName !== 'image') {
                            $imageName = $this->getImageName($imageName); // Just the image name
                            Category::create([
                                'name' => $grandchildName,
                                'slug' => Str::slug($grandchildName),
                                'image' => $imageName, // Store only the image name
                                'parent_id' => $childCategory->id,
                            ]);
                        }
                    }
                } else {
                    // For simple children (with image paths)
                    $imageName = $this->getImageName($childValue); // Just the image name
                    Category::create([
                        'name' => $childKey,
                        'slug' => Str::slug($childKey),
                        'image' => $imageName, // Store only the image name
                        'parent_id' => $parentCategory->id,
                    ]);
                }
            }
        }
    }

    /**
     * Helper function to get only the image name
     */
    private function getImageName($imageName)
    {
        // Image path relative to the 'public' folder
        $imagePath = 'uploads/categories/' . $imageName;

        // Check if the image exists in the directory
        $sourcePath = public_path('uploads/categories');
        if (!File::exists($sourcePath . '/' . $imageName)) {
            $this->command->error('Image does not exist: ' . $imagePath);
            return null;
        }

        // Return the image name only
        return $imageName; // Just return the image name (e.g., semi_golf_umbrella_manual.jpg)
    }

}
