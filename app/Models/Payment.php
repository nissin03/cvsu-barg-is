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
        'updated_by',
        'discount_id',
        'gross_total',
        'discount_percent',
        'discount_amount',
        'discount_applies_to',
        'discount_proof_path',
    ];


    public function availability()
    {
        return $this->belongsTo(Availability::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function updatedBy()
    // {
    //     return $this->belongsTo(User::class, 'updated_by');
    // }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by')->where('utype', 'ADM');
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class);
    }

    public function transactionReservations()
    {
        return $this->hasMany(TransactionReservation::class);
    }
    public function addonTransactions()
    {
        return $this->hasManyThrough(
            AddonTransaction::class,           // Final model
            TransactionReservation::class,     // Intermediate model
            'payment_id',                      // Foreign key on transaction_reservations table
            'transaction_reservation_id',      // Foreign key on addon_transactions table
            'id',                              // Local key on payments table
            'id'                               // Local key on transaction_reservations table
        );
    }

    public function groupedAvailabilities()
    {
        if (!$this->availability) {
            return $this->newCollection();
        }

        return Availability::where('facility_id', $this->availability->facility_id)
            ->where('facility_attribute_id', $this->availability->facility_attribute_id)
            ->where('date_from', '>=', $this->availability->date_from)
            ->where('date_to', '<=', $this->availability->date_to)
            ->orderBy('date_from');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
}
