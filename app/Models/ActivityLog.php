<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to write an activity log entry instantly.
     */
    public static function log($action, $payload = null)
    {
        try {
            return self::create([
                'user_id' => auth()->check() ? auth()->id() : null,
                'action' => $action,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent(),
                'payload' => $payload ? (is_array($payload) ? $payload : ['info' => $payload]) : null,
            ]);
        } catch (\Exception $e) {
            // Silently catch seeder or CLI logs failure to prevent execution interruption
            \Illuminate\Support\Facades\Log::warning("ActivityLog log write failure: " . $e->getMessage());
            return null;
        }
    }
}
