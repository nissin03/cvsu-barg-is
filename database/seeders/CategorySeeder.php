<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;
use File;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Accessories' => [
                'image' => 'lace_6.jpg',
                'Umbrella' => 'semi_golf_umbrella_manual.jpg',
                'Keychain' => 'key_holder.jpg',
                'Lace' => 'lace.jpg',
                'Pin' => 'cvsu_pin.jpg',
            ],
            'Apparel' => [
                'image' => 'IMG_0136.jpg',
                'T-Shirt' => 'IMG_0137.jpg',
                'Cap' => 'IMG_0125.jpg',
                'Bonnet' => 'IMG_0123.jpg',
                'Jacket' => 'IMG_0161.jpg',
                'Polo' => 'polo.jpg',
                'NSTP' => 'NSTP_t-shirt.jpg',
                'PE' => [
                    'image' => 'pe_category.jpg',
                    'pe tshirt' => 'pe_t-shirt.jpg',
                    'pe short' => 'pe_short.jpg',
                ],
                'Cspear' => [
                    'image' => 'cspear_logo.jpg',
                    'cspear tshirt' => 'cspear_t-shirt.jpg',
                    'cspear short' => 'cspear_shorts.jpg',
                    'cspear jogging pants' => 'cspear_jogging-pants.jpg',
                ],
                'Male Uniform' => 'polo.jpg',
                'Female Uniform' => 'blouse.jpg',
            ],
            'Home & Kitchen' => [
                'image' => 'IMG_0113_2.jpg',
                'Mug' => 'IMG_0113.jpg',
                'Utensils' => 'IMG_0147.jpg',
                'Coffee Blend' => 'blend_coffee.jpg',
                'Fans' => 'IMG_0159.jpg',
                'Tumbler' => 'tumbler.jpg',
            ],
            'Stationery' => [
                'image' => 'cat_stationary.jpg',
                'Notebook' => 'IMG_0148.jpg',
                'Ballpens' => 'IMG_0163.jpg',
                'Books' => 'IMG_0171.jpg',
            ],
        ];

        foreach ($categories as $parentName => $children) {
            $parentImage = $this->getImageName($children['image'] ?? null);
            $parentSlug = $this->generateUniqueSlug($parentName);
            
            $parentCategory = Category::create([
                'name' => $parentName,
                'slug' => $parentSlug,
                'image' => $parentImage,
                'parent_id' => null,
            ]);

            unset($children['image']);

            foreach ($children as $childKey => $childValue) {
                if (is_array($childValue)) {
                    $childImage = $this->getImageName($childValue['image'] ?? null);
                    $childSlug = $this->generateUniqueSlug($childKey, $parentSlug);
                    
                    $childCategory = Category::create([
                        'name' => $childKey,
                        'slug' => $childSlug,
                        'image' => $childImage,
                        'parent_id' => $parentCategory->id,
                    ]);

                    foreach ($childValue as $grandchildName => $imageName) {
                        if ($grandchildName !== 'image') {
                            $imageName = $this->getImageName($imageName);
                            $grandchildSlug = $this->generateUniqueSlug($grandchildName, $childSlug);
                            
                            Category::create([
                                'name' => $grandchildName,
                                'slug' => $grandchildSlug,
                                'image' => $imageName,
                                'parent_id' => $childCategory->id,
                            ]);
                        }
                    }
                } else {
                    $imageName = $this->getImageName($childValue);
                    $childSlug = $this->generateUniqueSlug($childKey, $parentSlug);
                    
                    Category::create([
                        'name' => $childKey,
                        'slug' => $childSlug,
                        'image' => $imageName,
                        'parent_id' => $parentCategory->id,
                    ]);
                }
            }
        }
    }

    private function getImageName($imageName)
    {
        if (!$imageName) return null;
        
        $sourcePath = public_path('uploads/categories');
        if (!File::exists($sourcePath . '/' . $imageName)) {
            $this->command->error('Image does not exist: ' . $imageName);
            return null;
        }

        return $imageName;
    }

    private function generateUniqueSlug($name, $parentSlug = null)
    {
        $baseSlug = Str::slug($name);
        $slug = $parentSlug ? "{$parentSlug}-{$baseSlug}" : $baseSlug;
        $count = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $parentSlug ? "{$parentSlug}-{$baseSlug}-{$count}" : "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}