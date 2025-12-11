<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\College;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_set',
        'profile_image',
        'utype',
        'role',
        'sex',
        'position_id',
        'phone_number',
        'year_level',
        'course_id',
        'college_id',
        'isDefault',
        'colleges',
    ];

    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return Storage::disk('public')->url($this->profile_image);
        }
        return asset('images/profile.jpg');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_set' => 'boolean',
        ];
    }

    public function getEmailForVerification()
    {
        return strtolower($this->email);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function facilities()
    {
        return $this->hasMany(Facility::class);
    }
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function sentReplies()
    {
        return $this->hasMany(ContactReply::class, 'admin_id');
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function addons()
    {
        return $this->hasMany(Addon::class);
    }
}
