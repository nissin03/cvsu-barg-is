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
        'addon_id',
        'addon_reservation_id',
        'addon_payment_id',
        'quantity',
        'user_id',
        'status',
    ];

    // public function facility()
    // {
    //     return $this->belongsTo(Facility::class);
    // }
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

<<<<<<< HEAD
    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function reservation()
    {
        return $this->belongsTo(AddonReservation::class);
    }

    public function addonpayment()
    {
        return $this->belongsTo(AddonPayment::class);
=======
    public function facility()
    {
        return $this->hasOneThrough(
            Facility::class,
            Availability::class,
            'id',
            'id',
            'availability_id',
            'facility_id'
        );
>>>>>>> bc52b149c2f1166cd2a1fd2a3e749aa6c5679233
    }
}
