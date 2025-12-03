<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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


    public function getQualificationUrlAttribute()
    {
        if ($this->qualification) {
            return asset('storage/' . $this->qualification);
        }
        return null;
    }

    public function hasQualificationFile()
    {
        return !empty($this->qualification) && Storage::disk('public')->exists($this->qualification);
    }
}
