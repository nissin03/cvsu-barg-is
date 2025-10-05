<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddonReservation extends Model
{
    use HasFactory;
    protected $fillable = 
    [
        'addon_id',
        'availability_id',
        'remaining_quantity',
        'remaining_capacity',
        'nights'
    ];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function payments()
    {
        return $this->hasOne(AddonPayment::class);
    }

    public function availability()
    {
        return $this->belongsTo(Availability::class, 'availability_id');
    }

    public function transactionReservations()
    {
        return $this->hasMany(TransactionReservation::class);
    }
}
