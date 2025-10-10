<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckPermission extends Model
{
    protected $table = 'check_permission';
    
    protected $fillable = [
        'check_permission_name',
        'remark',
        'is_show',
        'sub_permission_id',
        'permission_id'
    ];

    protected $casts = [
        'is_show' => 'boolean',
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function subPermission()
    {
        return $this->belongsTo(SubPermission::class);
    }
}