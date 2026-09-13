<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'category',
        'title',
        'details',
        'screenshot_path',
        'status',
        'admin_notes',
    ];

    /**
     * Relationship: User who submitted feedback
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable category name
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'issue' => '🐛 Report an Issue / Bug',
            'feature_request' => '💡 New Feature Suggestion',
            'ui_feedback' => '🎨 UI & Usability Feedback',
            'payment_issue' => '💳 Payment / Deposit Issue',
            default => '📌 General Feedback',
        };
    }
}
