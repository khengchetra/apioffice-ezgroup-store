<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'number_phone',
        'profile_image',
        'cover_image',
        'date_of_birth',
        'start_work',
        'employee_code',
        'bank_account_number',
        'username',
        'email',
        'password',
        'gender_id',
        'role_id',
        'is_show',
        'branch_id',
        'department_id',
        'position_id',
        'user_id',
        'other',
        'remember_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'start_work' => 'date',
        'is_show' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->remember_token)) {
                $user->remember_token = Str::random(60);
            }
        });

        static::updating(function ($user) {
            if ($user->isDirty('password')) {
                $user->remember_token = Str::random(60);
            }
        });
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}