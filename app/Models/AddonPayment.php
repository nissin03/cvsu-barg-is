<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddonPayment extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'addon_id',
        'addon_reservation_id',
        'total',
        'status',
    ];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function reservation()
    {
        return $this->belongsTo(AddonReservation::class);
    }

    public function transactionReservations()
    {
        return $this->hasMany(TransactionReservation::class);
    }
}
