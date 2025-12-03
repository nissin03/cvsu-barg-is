<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPriceDiscount extends Model
{
    protected $fillable = [
        'payment_id',
        'price_id',
        'discount_proof_path',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function price()
    {
        return $this->belongsTo(Price::class);
    }
}
