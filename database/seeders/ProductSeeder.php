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
        $products = config('product_data');
        $imageMappings = config('product_images');

        foreach ($products as $data) {
            $category = $this->getCategoryByPath($data['category']);
            if (!$category) {
                $this->command->error("Category not found: {$data['category']}");
                continue;
            }

            $images = $imageMappings[$data['name']] ?? null;
            if (!$images) {
                $this->command->error("Images not found for product: {$data['name']}");
                continue;
            }

            $product = Product::create([
                'name'                  => $data['name'],
                'slug'                  => Str::slug($data['name']),
                'short_description'     => "Short description for {$data['name']}",
                'description'           => "Detailed description for {$data['name']}",
                'price'                 => $data['price'] ?? rand(1000, 10000) / 100,
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

            if (!empty($data['attributes'])) {
                foreach ($data['attributes'] as $attributeName => $values) {
                    $attribute = ProductAttribute::firstOrCreate(['name' => $attributeName]);

                    if (is_null($product->product_attribute_id)) {
                        $product->product_attribute_id = $attribute->id;
                        $product->save();
                    }

                    $isAssoc = array_keys($values) !== range(0, count($values) - 1);
                    foreach ($values as $key => $value) {
                        $price = $isAssoc ? $value : rand(1000, 5000) / 100;
                        $val = $isAssoc ? $key : $value;

                        ProductAttributeValue::create([
                            'product_attribute_id' => $attribute->id,
                            'product_id'           => $product->id,
                            'value'                => $val,
                            'price'                => $price,
                            'quantity'             => rand(10, 100),
                            'stock_status'         => 'instock',
                        ]);
                    }
                }

                $product->price = 0;
                $product->save();
            }
        }
    }

    /**
     * Helper function to get Category by path (e.g., "Accessories > Keychain").
     */
    private function getCategoryByPath(string $path): ?Category
    {
        $names = array_map('trim', explode('>', $path));
        $parent = null;
        foreach ($names as $name) {
            $query = Category::where('name', $name);
            $query = $parent ? $query->where('parent_id', $parent->id) : $query->whereNull('parent_id');
            $parent = $query->first();
            if (!$parent) {
                return null;
            }
        }
        return $parent;
    }
}
