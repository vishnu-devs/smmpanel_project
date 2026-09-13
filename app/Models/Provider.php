<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Provider extends Model
{
    protected $fillable = [
        'name', 'api_url', 'api_key', 'status', 'balance',
        'priority', 'timeout', 'retry_count', 'currency', 'auto_sync',
        'response_time_ms', 'last_sync_success', 'last_sync_failed', 'failed_requests_count', 'last_sync_error',
    ];

    protected $casts = [
        'balance' => 'decimal:4',
        'auto_sync' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'api_key',
    ];

    /**
     * Automatically decrypt SMM Provider API keys.
     */
    public function getApiKeyAttribute($value)
    {
        if (empty($value)) {
            return '';
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // Fallback for unencrypted legacy seeder values
            return $value;
        }
    }

    /**
     * Automatically encrypt SMM Provider API keys.
     */
    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = Crypt::encryptString($value);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
