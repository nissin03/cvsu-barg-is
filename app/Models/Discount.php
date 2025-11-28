<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'percent',
        'applies_to',
        'requires_proof',
        'active',
    ];

    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'discount_facility', 'discount_id', 'facility_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function prices()
    {
        return $this->belongsToMany(Price::class, 'discount_prices');
    }
}
