<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddonPayment extends Model
{
    use HasFactory;
    protected $table = 'addon_payments';
    protected $fillable = 
    [
        'addon_id',
        'addon_reservation_id',
        'total',
        'status',
        'downpayment_amount,'
    ];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function reservation()
    {
        return $this->belongsTo(AddonReservation::class);
    }

     public function addonTransaction()
    {
        return $this->hasMany(addonTransaction::class);
    }
}
