<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProcessingLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'provider_id',
        'action',
        'request_payload',
        'response_payload',
        'error_message',
        'response_time_ms',
        'retry_count',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
