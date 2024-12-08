<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    protected $table = 'prices';

    protected $fillable = ['facility_id', 'name', 'price_type', 'value', 'is_based_on_days'];
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    // public function facilityAttribute()
    // {
    //     return $this->belongsTo(FacilityAttribute::class);
    // }

    // Price belongs to Facility
}
