<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;


    protected $fillable = ['name', 'price_type',  'value', 'facility_id','facility_attribute_id',  'is_based_on_days', 'is_there_a_quantity'];
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
    
    public function facilityAttributes()
    {
        return $this->belongsToMany(FacilityAttribute::class);
    }
   

}
