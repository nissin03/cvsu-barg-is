<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityAttribute extends Model
{
    use HasFactory;

    protected $table = 'facility_attributes';
    protected $fillable = [
        'facility_id',
        'name',
        'capacity',
        'sex_restriction'
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    // FacilityAttribute has many Prices
    public function prices()
    {
        return $this->hasMany(Price::class);
    }
    
}
