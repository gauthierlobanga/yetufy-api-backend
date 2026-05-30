<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Tags\Tag as SpatieTag;

class Tag extends SpatieTag
{
    use HasUuids;

    /**
     * Indique que les clés primaires sont de type string (UUID)
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indique que les clés primaires ne sont pas auto-incrémentées
     *
     * @var bool
     */
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'order_column',
        'color',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_column' => 'integer',
        // 'name' => 'array',
        // 'slug' => 'array',
    ];

    protected $attributes = [
        'is_active' => true,
        'order_column' => 0,
        // 'name' => [],
        // 'slug' => [],
    ];

    // Scope pour les tags actifs
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Accesseur pour le nom traduit
    public function getTranslatedNameAttribute(): string
    {
        return $this->getTranslation('name', app()->getLocale());
    }

    // Accesseur pour le slug traduit
    public function getTranslatedSlugAttribute(): string
    {
        return $this->getTranslation('slug', app()->getLocale());
    }
}
