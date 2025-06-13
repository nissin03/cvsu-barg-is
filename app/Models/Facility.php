<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Facility extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'slug',
        'facility_type',
        'description',
        'image',
        'images',
        'created_by',
        'rules_and_regulations',
        'requirements',
        'archived_at',
        'archived',
        'created_by'
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

    public function transaction()
    {
        return $this->hasMany(TransactionReservation::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function individualPrice()
    {
        $price = $this->prices()->where('price_type', 'individual')->first();

        return $price ? $price->value : 0;
    }

    // public function wholePlace()
    // {
    //     $price = $this->prices()->where('price_type', 'whole')->first();

    //     return $price ? $price->value : 0;
    // }

}
