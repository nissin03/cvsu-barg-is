<?php

namespace App\Models;

use App\Models\User;
use App\Models\Facility;
use App\Models\FacilityAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = 
    [
        'user_id',
        'facility_id',
        'facility_attribute_id',
        'name',
        'price_type',
        'description',
         'base_price',
        'is_refundable',
        'is_available',
        'capacity',
        'is_based_on_quantity',
        'show'
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);    
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

        public function facilityAttributes()
      {
        return $this->belongsTo(FacilityAttribute::class);
    }
}




