<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralCommission extends Model
{
    protected $fillable = [
        'referral_id',
        'referrer_id',
        'referred_id',
        'deposit_reference',
        'deposit_amount',
        'commission_rate',
        'commission_amount',
        'status',
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:4',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:4',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }
}
