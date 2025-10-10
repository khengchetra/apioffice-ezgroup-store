<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    protected $table = 'gender';
    
    protected $fillable = [
        'gender_name',
        'is_show',
        'remark'
    ];

    protected $casts = [
        'is_show' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}