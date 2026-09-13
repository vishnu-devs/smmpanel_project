<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $whatsapp
 * @property string $password
 * @property float $balance
 * @property string $role
 * @property string|null $api_key
 * @property string $status
 * @property string|null $google_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'whatsapp',
        'password',
        'balance',
        'role',
        'api_key',
        'status',
        'google_id',
        'balance_reminder_unsubscribed',
        'last_balance_reminder_sent_at',
        'referred_by',
        'referral_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:4',
            'balance_reminder_unsubscribed' => 'boolean',
            'last_balance_reminder_sent_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralCommissions()
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_id');
    }

    public function customDiscounts()
    {
        return $this->hasMany(CustomerDiscount::class);
    }

    public function getEffectiveDiscountDetails(): array
    {
        return \App\Services\DiscountService::getUserEffectiveDiscountDetails($this);
    }

    public function getEffectiveDiscountPercentage(): float
    {
        return \App\Services\DiscountService::getUserDiscountPercentage($this);
    }

    public function getTierNameAttribute(): string
    {
        $discountDetails = $this->getEffectiveDiscountDetails();
        return strtoupper($discountDetails['tier_name'] ?? 'BRONZE');
    }
}
