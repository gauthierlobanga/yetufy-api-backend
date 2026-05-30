<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class Brand extends Model implements HasMedia, Sitemapable
{
    use HasFactory, InteractsWithMedia, SoftDeletes;
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

    protected $table = 'brands';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'email',
        'phone',
        'logo',
        'cover_image',
        'color',
        'is_active',
        'is_featured',
        'sort_order',
        'seo',
        'social_links',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'seo' => 'array',
        'social_links' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 0,
    ];

    /**
     * Tailles d'images optimisées pour les marques
     */
    private const array IMAGE_SIZES = [
        'icon' => ['width' => 50, 'height' => 50, 'fit' => Fit::Crop],
        'thumb' => ['width' => 150, 'height' => 150, 'fit' => Fit::Crop],
        'card' => ['width' => 300, 'height' => 200, 'fit' => Fit::Crop],
        'medium' => ['width' => 600, 'height' => 400, 'fit' => Fit::Contain],
        'large' => ['width' => 1200, 'height' => 800, 'fit' => Fit::Contain],
        'cover' => ['width' => 1920, 'height' => 400, 'fit' => Fit::Crop],
    ];

    private const CACHE_TTL = 604800; // 1 semaine

    // ========== RELATIONS ==========

    /**
     * Relation avec les produits de la marque
     */
    public function products(): HasMany
    {
        return $this->hasMany(Produit::class);
    }

    /**
     * Relation avec les produits actifs de la marque
     */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('statut', Produit::STATUS_PUBLISHED);
    }

    /**
     * Relation avec les produits en promotion de la marque
     */
    public function promotedProducts(): HasMany
    {
        return $this->products()->whereNotNull('prix_promotion');
    }

    // ========== MEDIA CONFIGURATION ==========

    /**
     * Configuration des collections de médias
     */
    public function registerMediaCollections(): void
    {
        // Logo de la marque
        $this->addMediaCollection('logo')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
            ->withResponsiveImages();

        // Image de couverture
        $this->addMediaCollection('cover')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();

        // Galerie d'images de la marque
        $this->addMediaCollection('gallery')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();
    }

    /**
     * Configuration des conversions d'images
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Icône (petit format carré)
        $this->addMediaConversion('icon')
            ->width(self::IMAGE_SIZES['icon']['width'])
            ->height(self::IMAGE_SIZES['icon']['height'])
            ->fit(self::IMAGE_SIZES['icon']['fit'])
            ->format('webp')
            ->quality(80)
            ->nonQueued()
            ->performOnCollections('logo');

        // Miniature
        $this->addMediaConversion('thumb')
            ->width(self::IMAGE_SIZES['thumb']['width'])
            ->height(self::IMAGE_SIZES['thumb']['height'])
            ->fit(self::IMAGE_SIZES['thumb']['fit'])
            ->format('webp')
            ->quality(80)
            ->nonQueued()
            ->performOnCollections('logo', 'gallery');

        // Format carte
        $this->addMediaConversion('card')
            ->width(self::IMAGE_SIZES['card']['width'])
            ->height(self::IMAGE_SIZES['card']['height'])
            ->fit(self::IMAGE_SIZES['card']['fit'])
            ->format('webp')
            ->quality(85)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('logo', 'gallery');

        // Format moyen
        $this->addMediaConversion('medium')
            ->width(self::IMAGE_SIZES['medium']['width'])
            ->height(self::IMAGE_SIZES['medium']['height'])
            ->fit(self::IMAGE_SIZES['medium']['fit'])
            ->format('webp')
            ->quality(90)
            ->withResponsiveImages()
            ->queued()
            ->performOnCollections('logo', 'gallery', 'cover');

        // Grand format
        $this->addMediaConversion('large')
            ->width(self::IMAGE_SIZES['large']['width'])
            ->height(self::IMAGE_SIZES['large']['height'])
            ->fit(self::IMAGE_SIZES['large']['fit'])
            ->format('webp')
            ->quality(90)
            ->withResponsiveImages()
            ->queued()
            ->performOnCollections('logo', 'gallery');

        // Image de couverture
        $this->addMediaConversion('cover')
            ->width(self::IMAGE_SIZES['cover']['width'])
            ->height(self::IMAGE_SIZES['cover']['height'])
            ->fit(self::IMAGE_SIZES['cover']['fit'])
            ->format('webp')
            ->quality(85)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('cover');
    }

    // ========== MEDIA ACCESSORS AVEC CACHE ==========

    /**
     * Récupère l'URL du logo avec mise en cache
     */
    public function getLogoUrl(string $conversion = 'medium'): ?string
    {
        $cacheKey = "brand_{$this->id}_logo_{$conversion}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($conversion) {
            $media = $this->getFirstMedia('logo');
            if (! $media || ! $media->disk) {
                return null;
            }

            return $media->hasGeneratedConversion($conversion)
                ? $media->getUrl($conversion)
                : $media->getUrl();
        });
    }

    /**
     * Récupère l'URL de l'image de couverture
     */
    public function getCoverUrl(string $conversion = 'cover'): ?string
    {
        $cacheKey = "brand_{$this->id}_cover_{$conversion}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($conversion) {
            $media = $this->getFirstMedia('cover');
            if (! $media || ! $media->disk) {
                return null;
            }

            return $media->hasGeneratedConversion($conversion)
                ? $media->getUrl($conversion)
                : $media->getUrl();
        });
    }

    /**
     * Récupère la galerie d'images
     */
    public function getGalleryImages(): array
    {
        $cacheKey = "brand_{$this->id}_gallery";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return $this->getMedia('gallery')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                    'card' => $media->hasGeneratedConversion('card') ? $media->getUrl('card') : $media->getUrl(),
                    'medium' => $media->hasGeneratedConversion('medium') ? $media->getUrl('medium') : $media->getUrl(),
                    'alt' => $media->getCustomProperty('alt', $this->name),
                ];
            })->toArray();
        });
    }

    // ========== ACCESSORS ==========

    /**
     * Accesseur pour l'URL du logo (conversion medium par défaut)
     */
    public function getLogoAttribute(): ?string
    {
        return $this->getLogoUrl('medium');
    }

    /**
     * Accesseur pour la miniature du logo
     */
    public function getLogoThumbAttribute(): ?string
    {
        return $this->getLogoUrl('thumb');
    }

    /**
     * Accesseur pour l'URL de couverture
     */
    public function getCoverAttribute(): ?string
    {
        return $this->getCoverUrl('cover');
    }

    /**
     * Accesseur pour l'URL de la marque
     */
    public function getUrlAttribute(): string
    {
        return route('tenant.brands.show', $this->slug);
    }

    /**
     * Accesseur pour le nombre de produits
     */
    public function getProductsCountAttribute(): int
    {
        return $this->activeProducts()->count();
    }

    /**
     * Accesseur pour le titre SEO
     */
    public function getSeoTitleAttribute(): string
    {
        return $this->seo['title'] ?? $this->name;
    }

    /**
     * Accesseur pour la description SEO
     */
    public function getSeoDescriptionAttribute(): string
    {
        return $this->seo['description'] ?? Str::limit($this->description ?? $this->name, 160);
    }

    // ========== SCOPES ==========

    /**
     * Scope pour les marques actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les marques en vedette
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope pour l'ordre d'affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope pour la recherche
     */
    public function scopeSearch($query, string $term)
    {
        return $query->whereFullText(['name', 'description'], $term)
            ->orWhere('name', 'like', "%{$term}%");
    }

    /**
     * Scope pour les marques avec produits
     */
    public function scopeWithProducts($query)
    {
        return $query->has('products');
    }

    // ========== MÉTHODES MÉTIER ==========

    /**
     * Vérifie si la marque a des produits actifs
     */
    public function hasActiveProducts(): bool
    {
        return $this->activeProducts()->exists();
    }

    /**
     * Récupère les produits les plus vendus de la marque
     */
    public function getBestsellers(int $limit = 10)
    {
        return $this->products()
            ->where('statut', Produit::STATUS_PUBLISHED)
            ->orderBy('sold_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Récupère les produits en promotion de la marque
     */
    public function getPromotedProducts(int $limit = 10)
    {
        return $this->products()
            ->whereNotNull('prix_promotion')
            ->where('statut', Produit::STATUS_PUBLISHED)
            ->limit($limit)
            ->get();
    }

    /**
     * Récupère les produits par tranche de prix
     */
    public function getProductsByPriceRange(float $min, float $max)
    {
        return $this->products()
            ->whereBetween('prix_ttc', [$min, $max])
            ->where('statut', Produit::STATUS_PUBLISHED)
            ->get();
    }

    /**
     * Incrémente le compteur de vues de la marque
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Vide le cache de la marque
     */
    public function clearCache(): void
    {
        Cache::forget("brand_{$this->id}_logo_icon");
        Cache::forget("brand_{$this->id}_logo_thumb");
        Cache::forget("brand_{$this->id}_logo_card");
        Cache::forget("brand_{$this->id}_logo_medium");
        Cache::forget("brand_{$this->id}_logo_large");
        Cache::forget("brand_{$this->id}_cover_cover");
        Cache::forget("brand_{$this->id}_gallery");
    }

    /**
     * Génère le sitemap pour la marque
     */
    public function toSitemapTag(): Url|string|array
    {
        return Url::create($this->url)
            ->setLastModificationDate($this->updated_at)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.7);
    }

    // ========== BOOT ==========

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });

        static::updating(function ($brand) {
            if ($brand->isDirty('name') && ! $brand->isDirty('slug')) {
                $brand->slug = Str::slug($brand->name);
            }
        });

        static::saved(function ($brand) {
            $brand->clearCache();
        });

        static::deleted(function ($brand) {
            $brand->clearCache();
        });
    }
}
