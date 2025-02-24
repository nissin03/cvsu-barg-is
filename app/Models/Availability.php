<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;
    protected $fillable = [
        'facility_id',
        'facility_attribute_id',
        'date_from',
        'date_to',
        'remaining_capacity',
    ];
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function facilityAttribute()
    {
        return $this->belongsTo(FacilityAttribute::class);
    }

    public function transactionReservations()
    {
        return $this->hasMany(TransactionReservation::class);
    }

    public function qualificationApprovals()
    {
        return $this->hasMany(QualificationApproval::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
