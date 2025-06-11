<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'availability_id',
        'user_id',
        'status',
        'total_price',
        'updated_by'
    ];


    public function availability()
    {
        return $this->belongsTo(Availability::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class);
    }

    public function transactionReservations()
    {
        return $this->hasMany(TransactionReservation::class);
    }
}
