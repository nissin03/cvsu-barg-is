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

    public function addon()
    {
        return $this->belongsTo(Addon::class, 'addon_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'addon_payment_id');
    }

    public function addonReservation()
    {
        return $this->belongsTo(AddonReservation::class, 'addon_reservation_id');
    }

    public function transactionReservation()
    {
        return $this->belongsTo(TransactionReservation::class, 'transaction_reservation_id');
    }
    public function addonPayment()
    {
        return $this->belongsTo(AddonPayment::class, 'addon_payment_id');
    }
}
