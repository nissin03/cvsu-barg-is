<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'facility_id',
        'quantity',
        'total_price',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
