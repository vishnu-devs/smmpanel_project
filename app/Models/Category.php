<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'original_name', 'is_custom_name', 'status', 'sort_order', 'is_pinned'];

    public function getNameAttribute($value)
    {
        return \App\Services\BrandingSanitizer::clean($value);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = \App\Services\BrandingSanitizer::clean($value);
    }

    public function getPlatformAttribute()
    {
        return \App\Services\PlatformHelper::detectPlatform($this->name) ?? 'others';
    }

    public function services()
    {
        return $this->hasMany(Service::class)->orderBy('id', 'asc');
    }
}
