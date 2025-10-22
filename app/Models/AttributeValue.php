<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeValue extends Model
{
    protected $fillable = ['attribute_id', 'value', 'hex_code', 'is_show'];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}