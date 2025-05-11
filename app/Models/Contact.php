<?php

namespace App\Models;

use App\Models\ContactReplies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;
    public function replies()
    {
        return $this->hasMany(ContactReplies::class);
    }
}
