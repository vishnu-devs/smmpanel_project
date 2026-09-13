<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPlatform extends Model
{
    protected $fillable = [
        'key',
        'name',
        'icon',
        'color',
        'keywords',
        'is_enabled',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to return enabled platforms ordered by sort_order
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true)->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Get keywords array
     */
    public function getKeywordsArrayAttribute(): array
    {
        if (empty($this->keywords)) {
            return [strtolower($this->key), strtolower($this->name)];
        }
        return array_values(array_filter(array_map('trim', explode(',', strtolower($this->keywords)))));
    }
}
