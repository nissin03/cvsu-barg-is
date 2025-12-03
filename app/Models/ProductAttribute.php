<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name'];

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values', 'product_attribute_id', 'product_id', 'value');
    }
}
