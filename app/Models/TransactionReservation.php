<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'availability_id',
        'facility_attribute_id',
        'payment_id',
        'price_id',
       
        'quantity',
        'user_id',
        'status',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
    public function availability()
    {
        return $this->belongsTo(Availability::class);
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

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function addonTransaction()
    {
        return $this->hasMany(addonTransaction::class);
    }
}
