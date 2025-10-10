<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';
    
    protected $fillable = [
        'permission_name',
        'remark',
        'is_show',
    ];

    protected $casts = [
        'is_show' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Add this relationship to resolve the "subPermissions" error
    public function subPermissions()
    {
        return $this->hasMany(SubPermission::class, 'permission_id');
    }

    // Optional: If needed for the controller's eager loading (compilePermissions.permission)
    public function compilePermissions()
    {
        return $this->hasMany(CompilePermission::class, 'permission_id');
    }
}