<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityAttribute extends Model
{
    use HasFactory;

    protected $table = 'facility_attributes';
    protected $attributes = [
        'sex_restriction' => null,
    ];
    protected $fillable = [
        'facility_id',
        'room_name',
        'capacity',
        'whole_capacity',
        'sex_restriction'
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    
}
