<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;


    protected $table = 'facilities';
    protected $fillable = [
        'name', 'description', 'type', 'image', 'images', 'slug', 
        'featured', 'status', 'created_by', 'rules_and_regulations', 
        'requirements', 'archived_at'
    ];


    public function facilityAttributes()
    {
        return $this->hasMany(FacilityAttribute::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    public function individualPrice()
    {
        $price = $this->prices()->where('price_type', 'individual')->first();

        return $price ? $price->value : 0;
    }


}

