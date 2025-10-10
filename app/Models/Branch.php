<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'branch_name',
        'is_show',
        'remark',
        'user_id'
    ];

    protected $casts = [
        'is_show' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}