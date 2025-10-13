<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'image',
        'name_kh',
        'name_en',
        'name_cn',
        'category_id',
        'qty',
        'user_id',
        'remark',
        'is_show'
    ];

    protected $casts = [
        'is_show' => 'boolean',
        'qty' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}