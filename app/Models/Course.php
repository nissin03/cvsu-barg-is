<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'code',
        'college_id'
    ];

      protected $casts = [
        'deleted_at' => 'datetime',
    ];
    
    public function college()
    {
        return $this->belongsTo(College::class);
    }

      public function users()
    {
        return $this->hasMany(User::class);
    }
}