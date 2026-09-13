<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivedPayment extends Model
{
    protected $fillable = [
        'utr',
        'amount',
        'status',
        'user_id',
        'sender_name',
        'raw_data',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
