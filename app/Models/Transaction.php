<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'status',
        'amount_paid',
        'change',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
