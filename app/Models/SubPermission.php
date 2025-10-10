<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubPermission extends Model
{
    protected $table = 'sub_permission';
    
    protected $fillable = [
        'sub_permission_name',
        'remark',
        'is_show',
        'permission_id'
    ];

    protected $casts = [
        'is_show' => 'boolean',
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function checkPermissions()
    {
        return $this->hasMany(CheckPermission::class);
    }
}