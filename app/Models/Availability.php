<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;
    protected $fillable = [
        'facility_id', 'facility_attribute_id', 'price_id', 'remaining_capacity', 
        'qualification', 'date_from', 'date_to', 'total_price',  'user_id',
        'status', 'transaction_id',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function facilityAttribute()
    {
        return $this->belongsTo(FacilityAttribute::class);
    }
    public function price()
    {
        return $this->belongsTo(Price::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
