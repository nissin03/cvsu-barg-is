<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddonTransaction extends Model
{
    protected $table = 'addon_transactions';
    protected $fillable = [
        'transaction_reservation_id',
        'addon_id',
        'addon_reservation_id',
        'addon_payment_id',
        'status',
    ];

    public function transaction()
    {
        return $this->belongsTo(TransactionReservation::class);
    }
    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function addon_reservation()
    {
        return $this->belongsTo(AddonReservation::class);
    }

    public function addon_payment()
    {
        return $this->belongsTo(AddonPayment::class);
    }



}
