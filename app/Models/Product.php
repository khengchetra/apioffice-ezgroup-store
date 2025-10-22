<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'image',
        'name_kh',
        'name_en',
        'name_cn',
        'category_id',
        'user_id',
        'remark',
        'is_active',
        'is_show',
    ];

    // Concatenate names for display
    protected $appends = ['display_name'];

    public function getDisplayNameAttribute()
    {
        return implode(' / ', array_filter([
            $this->name_kh,
            $this->name_en,
            $this->name_cn,
        ]));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_show', true);
    }
}