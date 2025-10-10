<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    
    protected $fillable = [
        'role_name',
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

    public function compilePermissions()
    {
        return $this->hasMany(CompilePermission::class);
    }

    // Define permissions through compilePermissions pivot
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'compile_permission', 'role_id', 'permission_id')
            ->withPivot('sub_permission_id', 'check_permission_id');
    }
}
