<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualificationApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'availability_id',
        'qualification',
        'user_id',
        'status',
    ];

    public function availability()
    {
        return $this->belongsTo(Availability::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
