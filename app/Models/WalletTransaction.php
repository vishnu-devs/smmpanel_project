<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'admin_id',
        'amount',
        'previous_balance',
        'new_balance',
        'action',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'previous_balance' => 'decimal:4',
        'new_balance' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
