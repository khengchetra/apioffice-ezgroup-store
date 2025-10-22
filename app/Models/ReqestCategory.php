<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReqestCategory extends Model
{
    protected $table = 'reqestcategory';

    protected $fillable = [
        'name',
        'remark',
        'is_active',
        'is_show',
        'user_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
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