<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerTier extends Model
{
    protected $fillable = [
        'name',
        'min_spending',
        'max_spending',
        'discount_percentage',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'min_spending' => 'decimal:4',
        'max_spending' => 'decimal:4',
        'discount_percentage' => 'decimal:2',
        'sort_order' => 'integer',
    ];
}
