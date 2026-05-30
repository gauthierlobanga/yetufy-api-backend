<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
use Spatie\Tags\HasTags;

class ProductCategory extends Model implements HasMedia, Sitemapable
{
    use HasFactory, HasTags, InteractsWithMedia, SoftDeletes;
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

    protected $table = 'produit_categories';

    protected $fillable = [
        'parente_id',
        'nom',
        'slug',
        'description',
        'short_description',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'order',
        'color',
        'est_active',
        'is_featured',
        'show_in_menu',
        'metadata',
    ];

    protected $casts = [
        'est_active' => 'boolean',
        'is_featured' => 'boolean',
        'show_in_menu' => 'boolean',
        'order' => 'integer',
        'metadata' => 'array',
        'seo_keywords' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'est_active' => true,
        'is_featured' => false,
        'show_in_menu' => true,
        'order' => 0,
    ];

    /**
     * Tailles d'images optimisées pour les catégories
     */
    private const array IMAGE_SIZES = [
        'icon' => ['width' => 50, 'height' => 50, 'fit' => Fit::Crop],
        'thumb' => ['width' => 150, 'height' => 150, 'fit' => Fit::Crop],
        'card' => ['width' => 400, 'height' => 300, 'fit' => Fit::Crop],
        'banner' => ['width' => 1200, 'height' => 400, 'fit' => Fit::Crop],
        'medium' => ['width' => 800, 'height' => 600, 'fit' => Fit::Contain],
        'large' => ['width' => 1920, 'height' => 1080, 'fit' => Fit::Max],
    ];

    // Durée de cache (1 semaine)
    private const CACHE_TTL = 604800;

    // ========== RELATIONS ==========

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parente_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parente_id')->orderBy('order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'produit_categorie_pivot', 'category_id', 'produit_id')
            ->using(ProductCategoryPivot::class)
            ->withPivot(['is_primary', 'order', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    public function featuredProducts()
    {
        return $this->products()->where('is_featured', true);
    }

    // ========== MEDIA CONFIGURATION ==========

    public function registerMediaCollections(): void
    {
        // Image principale de la catégorie
        $this->addMediaCollection('image')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
            ->withResponsiveImages();

        // Bannière de la catégorie
        $this->addMediaCollection('banner')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();

        // Icône de la catégorie
        $this->addMediaCollection('icon')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/svg+xml', 'image/png', 'image/webp']);

        // Galerie d'images de la catégorie
        $this->addMediaCollection('gallery')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->withResponsiveImages();
    }

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
            ->performOnCollections('icon', 'image');

        // Miniature
        $this->addMediaConversion('thumb')
            ->width(self::IMAGE_SIZES['thumb']['width'])
            ->height(self::IMAGE_SIZES['thumb']['height'])
            ->fit(self::IMAGE_SIZES['thumb']['fit'])
            ->format('webp')
            ->quality(80)
            ->nonQueued()
            ->performOnCollections('image', 'gallery');

        // Format carte
        $this->addMediaConversion('card')
            ->width(self::IMAGE_SIZES['card']['width'])
            ->height(self::IMAGE_SIZES['card']['height'])
            ->fit(self::IMAGE_SIZES['card']['fit'])
            ->format('webp')
            ->quality(85)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('image');

        // Bannière
        $this->addMediaConversion('banner')
            ->width(self::IMAGE_SIZES['banner']['width'])
            ->height(self::IMAGE_SIZES['banner']['height'])
            ->fit(self::IMAGE_SIZES['banner']['fit'])
            ->format('webp')
            ->quality(85)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('banner');

        // Format moyen
        $this->addMediaConversion('medium')
            ->width(self::IMAGE_SIZES['medium']['width'])
            ->height(self::IMAGE_SIZES['medium']['height'])
            ->fit(self::IMAGE_SIZES['medium']['fit'])
            ->format('webp')
            ->quality(90)
            ->withResponsiveImages()
            ->nonQueued()   // ← changez ici
            ->performOnCollections('image', 'gallery', 'banner');

        // Grand format
        $this->addMediaConversion('large')
            ->width(self::IMAGE_SIZES['large']['width'])
            ->height(self::IMAGE_SIZES['large']['height'])
            ->fit(self::IMAGE_SIZES['large']['fit'])
            ->format('webp')
            ->quality(90)
            ->withResponsiveImages()
            ->queued()
            ->performOnCollections('image', 'banner');
    }

    // ========== MEDIA ACCESSORS AVEC CACHE ==========

    public function getImageUrl(string $conversion = 'card'): ?string
    {
        $cacheKey = "category_{$this->id}_image_{$conversion}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($conversion) {
            $media = $this->getFirstMedia('image');
            if (! $media || ! $media->disk) {
                return null;
            }

            return $media->hasGeneratedConversion($conversion)
                ? $media->getUrl($conversion)
                : $media->getUrl();
        });
    }

    public function getBannerUrl(string $conversion = 'banner'): ?string
    {
        $cacheKey = "category_{$this->id}_banner_{$conversion}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($conversion) {
            $media = $this->getFirstMedia('banner');
            if (! $media || ! $media->disk) {
                return null;
            }

            return $media->hasGeneratedConversion($conversion)
                ? $media->getUrl($conversion)
                : $media->getUrl();
        });
    }

    public function getIconUrl(): ?string
    {
        $cacheKey = "category_{$this->id}_icon";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $media = $this->getFirstMedia('icon');
            if (! $media || ! $media->disk) {
                return null;
            }

            return $media->hasGeneratedConversion('icon')
                ? $media->getUrl('icon')
                : $media->getUrl();
        });
    }

    public function getGalleryImages(): array
    {
        $cacheKey = "category_{$this->id}_gallery";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return $this->getMedia('gallery')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                    'medium' => $media->hasGeneratedConversion('medium') ? $media->getUrl('medium') : $media->getUrl(),
                    'alt' => $media->getCustomProperty('alt', $this->nom),
                ];
            })->toArray();
        });
    }

    // ========== ACCESSORS ==========

    public function getImageAttribute(): ?string
    {
        return $this->getImageUrl('card');
    }

    public function getImageThumbAttribute(): ?string
    {
        return $this->getImageUrl('thumb');
    }

    public function getBannerAttribute(): ?string
    {
        return $this->getBannerUrl('banner');
    }

    public function getIconAttribute(): ?string
    {
        return $this->getIconUrl();
    }

    public function getUrlAttribute(): string
    {
        return route('tenant.product.category.show', $this->slug);
    }

    public function getFullPathAttribute(): string
    {
        $path = collect();
        $current = $this;

        while ($current) {
            $path->prepend($current->nom);
            $current = $current->parent;
        }

        return $path->implode(' > ');
    }

    public function getProductsCountAttribute(): int
    {
        return $this->products()->where('statut', Produit::STATUS_PUBLISHED)->count();

    }

    public function getLevelAttribute(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    public function getSeoTitleAttribute($value): string
    {
        return $value ?? $this->nom;
    }

    public function getSeoDescriptionAttribute($value): string
    {
        return $value ?? $this->short_description;
    }

    public function getShortDescriptionAttribute()
    {
        $description = is_array($this->description)
            ? json_encode($this->description)
            : $this->description;

        return Str::limit(strip_tags($description), 120);
    }

    public function getDescriptionTextAttribute()
    {
        $description = is_array($this->description)
            ? json_encode($this->description)
            : $this->description;

        return Str::limit(strip_tags($description), 120);
    }

    // ========== SCOPES ==========

    public function scopeActive($query)
    {
        return $query->where('produit_categories.est_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('produit_categories.is_featured', true);
    }

    public function scopeInMenu($query)
    {
        return $query->where('produit_categories.show_in_menu', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('produit_categories.parente_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeWithProductsCount($query)
    {
        return $query->withCount('products');
    }

    // ========== MÉTHODES MÉTIER ==========

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function hasParent(): bool
    {
        return ! is_null($this->parente_id);
    }

    public function getAllChildrenIds(): array
    {
        $ids = [$this->id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }

        return $ids;
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $current = $this;

        while ($current) {
            array_unshift($breadcrumb, [
                'id' => $current->id,
                'name' => $current->nom,
                'slug' => $current->slug,
                'url' => $current->url,
            ]);
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    public function clearCache(): void
    {
        Cache::forget("category_{$this->id}_image_card");
        Cache::forget("category_{$this->id}_image_thumb");
        Cache::forget("category_{$this->id}_banner_banner");
        Cache::forget("category_{$this->id}_icon");
        Cache::forget("category_{$this->id}_gallery");

        foreach (array_keys(self::IMAGE_SIZES) as $conversion) {
            Cache::forget("category_{$this->id}_image_{$conversion}");
            Cache::forget("category_{$this->id}_banner_{$conversion}");
        }
    }

    public function toSitemapTag(): Url|string|array
    {
        return Url::create($this->url)
            ->setLastModificationDate($this->updated_at)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.8);
    }

    // ========== BOOT ==========

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->nom);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('nom') && ! $category->isDirty('slug')) {
                $category->slug = Str::slug($category->nom);
            }
        });

        static::saved(function ($category) {
            $category->clearCache();
        });

        static::deleted(function ($category) {
            $category->clearCache();
        });
    }
}
