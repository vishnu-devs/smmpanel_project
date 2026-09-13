<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'original_name',
        'is_custom_name',
        'description',
        'price_per_k',
        'min_quantity',
        'max_quantity',
        'status',
        'sort_order',
        'provider_id',
        'provider_service_id',
        'provider_rate',
        'average_time',
    ];

    protected $casts = [
        'price_per_k' => 'decimal:4',
        'provider_rate' => 'decimal:4',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'failover_mappings' => 'array',
    ];

    public function getNameAttribute($value)
    {
        return \App\Services\BrandingSanitizer::clean($value, $this->relationLoaded('provider') && $this->provider ? $this->provider->name : null);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = \App\Services\BrandingSanitizer::clean($value);
    }

    public function getDescriptionAttribute($value)
    {
        return \App\Services\BrandingSanitizer::clean($value, $this->relationLoaded('provider') && $this->provider ? $this->provider->name : null);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = \App\Services\BrandingSanitizer::clean($value);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Determine if this service represents a Custom Comments service.
     */
    public function isCustomComments(): bool
    {
        $name = strtolower($this->original_name ?: $this->name ?: '');
        $catName = strtolower($this->relationLoaded('category') && $this->category ? $this->category->name : '');
        $desc = strtolower($this->description ?: '');

        // Exclude comment likes / random comments / emoji comments if they don't accept custom text
        if (preg_match('/\b(comment likes|comments likes|comment like|comments like|likes on comment|likes on comments)\b/i', $name) && !str_contains($name, 'custom')) {
            return false;
        }
        if (preg_match('/\b(random comment|random comments|emoji comment|emoji comments|positive comments)\b/i', $name) && !str_contains($name, 'custom')) {
            return false;
        }

        // 1. Direct regex match (custom comments / custom_comments)
        if (preg_match('/custom\s*comments?|custome\s*comments?|custom_comments?/i', $name)) {
            return true;
        }

        // 2. Both "custom" and "comment" present anywhere in the service name (e.g. "Instagram Comments | Custom | Indian")
        if (preg_match('/\b(custom|custome)\b/i', $name) && preg_match('/\b(comment|comments)\b/i', $name)) {
            return true;
        }

        // 3. Category has custom comments or both keywords
        if ($catName && (preg_match('/custom\s*comments?|custome\s*comments?/i', $catName) || (preg_match('/\b(custom|custome)\b/i', $catName) && preg_match('/\b(comment|comments)\b/i', $catName)))) {
            if (!preg_match('/\b(random|likes?|emoji)\b/i', $name)) {
                return true;
            }
        }

        // 4. Custom tagged comments e.g. | Custom |, [Custom], (Custom), /Custom/
        if (preg_match('/[\[\|\(\/]\s*(custom|custome)\s*[\]\|\)\/]/i', $name) && (str_contains($name, 'comment') || str_contains($catName, 'comment'))) {
            return true;
        }

        return false;
    }

    public function getIsCustomCommentsAttribute(): bool
    {
        return $this->isCustomComments();
    }
}
