<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'image', 'parent_id'];
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id');
    }
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function archivedChildren()
    {
        return $this->hasMany(Category::class, 'parent_id')->onlyTrashed();
    }
    protected static function booted()
    {
        static::deleting(function ($category) {
            if ($category->children()->count() > 0) {
                $category->children()->each(function ($child) {
                    $child->delete();
                });
            }
        });

        static::restoring(function ($category) {
            $category->children()->withTrashed()->get()->each->restore();
        });
    }
}
