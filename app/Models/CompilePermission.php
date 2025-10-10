<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompilePermission extends Model
{
    protected $table = 'compile_permission';
    
    protected $fillable = [
        'role_id',
        'permission_id',
        'sub_permission_id',
        'check_permission_id'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function subPermission()
    {
        return $this->belongsTo(SubPermission::class);
    }

    public function checkPermission()
    {
        return $this->belongsTo(CheckPermission::class);
    }
}