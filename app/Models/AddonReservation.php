<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddonReservation extends Model
{
    use HasFactory;

    protected $table = 'addons_reservations';

    protected $fillable =
    [
        'addon_id',
        'remaining_quantity',
        'remaining_capacity',
        'nights',
        'days',
        'date_from',
        'date_to',
        'quantity',
    ];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function payments()
    {
        return $this->hasMany(AddonPayment::class);
    }

    public function addonTransaction()
    {
        return $this->hasMany(AddonTransaction::class);
    }
}
