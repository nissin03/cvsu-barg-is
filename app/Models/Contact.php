<?php

namespace App\Models;

use App\Models\ContactReplies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Contact extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'message'];
    public function replies()
    {
        return $this->hasMany(ContactReply::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
