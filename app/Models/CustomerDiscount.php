<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDiscount extends Model
{
    protected $fillable = [
        'user_id',
        'discount_percentage',
        'status',
        'starts_at',
        'ends_at',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
