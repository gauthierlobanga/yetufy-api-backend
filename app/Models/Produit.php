<?php

namespace App\Models;

use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Nnjeim\World\Models\Currency;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;
use Spatie\Tags\HasTags;

class Produit extends Model implements HasMedia, Sitemapable
{
    use HasComments, HasFactory, HasTags;
    use HasUuids, InteractsWithMedia, SoftDeletes;

    protected $table = 'produits';

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
        'brand_id',
        'currency_id',
        'reference',
        'nom',
        'slug',
        'short_description',
        'description_longue',
        'prix_ht',
        'prix_ttc',
        'prix_promotion',
        'quantite_stock',
        'seuil_alerte',
        'sku',
        'ean',
        'poids',
        'hauteur',
        'largeur',
        'profondeur',
        'unite_mesure',
        'attributes',
        'attributs',
        'metadata',
        'statut',
        'is_featured',
        'is_new',
        'is_bestseller',
        'views_count',
        'sold_count',
        'average_rating',
        'reviews_count',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'published_at',
        'scheduled_for',
        'expires_at',
        'is_deal_of_the_day',   // ← ajout

        // 'search_document',
        // 'image_search_metadata',
        // 'search_embedding_synced_at',
    ];

    protected $casts = [
        'prix_ht' => 'decimal:2',
        'prix_ttc' => 'decimal:2',
        'prix_promotion' => 'decimal:2',
        'poids' => 'decimal:2',
        'hauteur' => 'decimal:2',
        'largeur' => 'decimal:2',
        'profondeur' => 'decimal:2',
        'attributs' => 'array',
        'attributes' => 'array',
        'metadata' => 'array',
        'seo_keywords' => 'array',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_bestseller' => 'boolean',
        'views_count' => 'integer',
        'sold_count' => 'integer',
        'average_rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'quantite_stock' => 'integer',
        'published_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'expires_at' => 'datetime',
        'is_deal_of_the_day' => 'boolean',

        // 'image_search_metadata' => 'array',
        // 'search_embedding_synced_at' => 'datetime',
    ];

    protected $attributes = [
        'is_new' => false,
        'is_featured' => false,
        'is_bestseller' => false,
        'is_deal_of_the_day' => false,
    ];

    const STATUS_DRAFT = 'brouillon';

    const STATUS_PUBLISHED = 'publie';

    const STATUS_ARCHIVED = 'archive';

    const STATUS_OUT_OF_STOCK = 'out_of_stock';

    const STATUS_DISCONTINUED = 'discontinued';

    private const CACHE_TTL = 604800; // 1 semaine

    /**
     * Tailles d'images optimisées pour les produits
     */
    private const array IMAGE_SIZES = [
        'thumb' => ['width' => 100, 'height' => 100, 'fit' => Fit::Crop],
        'card' => ['width' => 600, 'height' => 600, 'fit' => Fit::Crop],
        'small' => ['width' => 300, 'height' => 300, 'fit' => Fit::Contain],
        'medium' => ['width' => 800, 'height' => 800, 'fit' => Fit::Contain],
        'large' => ['width' => 1200, 'height' => 1200, 'fit' => Fit::Contain],
        'zoom' => ['width' => 2000, 'height' => 2000, 'fit' => Fit::Max],
    ];

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_PUBLISHED => 'Publié',
            self::STATUS_OUT_OF_STOCK => 'Rupture de stock',
            self::STATUS_DISCONTINUED => 'Abandonné',
        ];
    }

    // ========== RELATIONS ==========

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function variantes(): HasMany
    {
        return $this->hasMany(VarianteProduit::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class,
            'produit_categorie_pivot',
            'produit_id',
            'category_id')
            ->using(ProductCategoryPivot::class)
            ->withPivot('is_primary', 'order')
            ->withTimestamps()
            ->orderByPivot('order');
    }

    public function primaryCategory()
    {
        return $this->categories()->wherePivot('is_primary', true)->first();
    }

    public function entrepots(): BelongsToMany
    {
        return $this->belongsToMany(Entrepot::class, 'produit_entrepot')
            ->withPivot('quantite', 'quantite_reservee', 'updated_at')
            ->withTimestamps();
    }

    public function fournisseurs(): BelongsToMany
    {
        return $this->belongsToMany(Fournisseur::class, 'produit_fournisseur')
            ->withPivot('prix_achat_ht', 'delai_approvisionnement_jours', 'reference_fournisseur', 'is_primary')
            ->withTimestamps();
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_produit')
            ->withPivot('valeur_specifique')
            ->withTimestamps();
    }

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Taxe::class, 'produit_taxe');
    }

    public function avis(): HasMany
    {
        return $this->hasMany(AvisClient::class);
    }

    public function approvedAvis()
    {
        return $this->avis()->where('approuve', true);
    }

    public function mouvementsStock(): HasMany
    {
        return $this->hasMany(MouvementStock::class);
    }

    public function itemPaniers(): HasMany
    {
        return $this->hasMany(ItemPanier::class);
    }

    public function ligneCommandes(): HasMany
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    // ========== MEDIA CONFIGURATION ==========

    public function registerMediaCollections(): void
    {
        // Images principales du produit
        $this->addMediaCollection('image_principale')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->withResponsiveImages();
        // Images principales du produit
        $this->addMediaCollection('images')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->withResponsiveImages();

        // Vidéos du produit
        $this->addMediaCollection('videos')
            ->useDisk('public')
            ->acceptsMimeTypes(['video/mp4', 'video/webm']);

        // Documents (manuels, fiches techniques)
        $this->addMediaCollection('documents')
            ->useDisk('public')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Miniature (carré)
        $this->addMediaConversion('thumb')
            ->width(self::IMAGE_SIZES['thumb']['width'])
            ->height(self::IMAGE_SIZES['thumb']['height'])
            ->fit(self::IMAGE_SIZES['thumb']['fit'])
            ->format('webp')
            ->quality(80)
            ->nonQueued()
            ->performOnCollections('images');

        // Format carte (carré)
        $this->addMediaConversion('card')
            ->width(self::IMAGE_SIZES['card']['width'])
            ->height(self::IMAGE_SIZES['card']['height'])
            ->fit(self::IMAGE_SIZES['card']['fit'])
            ->format('webp')
            ->quality(85)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('image_principale');

        // Petit format
        $this->addMediaConversion('small')
            ->width(self::IMAGE_SIZES['small']['width'])
            ->height(self::IMAGE_SIZES['small']['height'])
            ->fit(self::IMAGE_SIZES['small']['fit'])
            ->format('webp')
            ->quality(85)
            ->withResponsiveImages()
            ->nonQueued()
            ->performOnCollections('images');

        // Format moyen (affichage principal)
        $this->addMediaConversion('medium')
            ->width(self::IMAGE_SIZES['medium']['width'])
            ->height(self::IMAGE_SIZES['medium']['height'])
            ->fit(self::IMAGE_SIZES['medium']['fit'])
            ->format('webp')
            ->quality(90)
            ->withResponsiveImages()
            ->nonQueued()   // ← changez ici
            ->performOnCollections('images');

        // Grand format
        $this->addMediaConversion('large')
            ->width(self::IMAGE_SIZES['large']['width'])
            ->height(self::IMAGE_SIZES['large']['height'])
            ->fit(self::IMAGE_SIZES['large']['fit'])
            ->format('webp')
            ->quality(90)
            ->withResponsiveImages()
            ->queued()
            ->performOnCollections('images');

        // Format zoom (haute résolution)
        $this->addMediaConversion('zoom')
            ->width(self::IMAGE_SIZES['zoom']['width'])
            ->height(self::IMAGE_SIZES['zoom']['height'])
            ->fit(self::IMAGE_SIZES['zoom']['fit'])
            ->format('webp')
            ->quality(95)
            ->queued()
            ->performOnCollections('images');
    }

    // ========== MEDIA ACCESSORS AVEC CACHE ==========
    public function getImageUrl(string $conversion = 'medium'): ?string
    {
        $cacheKey = "product_{$this->id}_image_{$conversion}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($conversion) {
            // Chercher d'abord dans image_principale
            $media = $this->getFirstMedia('image_principale');
            if (! $media) {
                // Sinon dans images
                $media = $this->getFirstMedia('images');
            }

            if (! $media || ! $media->disk) {
                return null;
            }

            return $media->hasGeneratedConversion($conversion)
                ? $media->getUrl($conversion)
                : $media->getUrl();
        });
    }

    public function getAllImages(): array
    {
        $cacheKey = "product_{$this->id}_all_images";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            // Fusionner image_principale et images en une seule liste
            $mainMedia = $this->getMedia('image_principale');
            $galleryMedia = $this->getMedia('images');

            $allMedia = $mainMedia->merge($galleryMedia);

            return $allMedia->map(function ($media, $index) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                    'card' => $media->hasGeneratedConversion('card') ? $media->getUrl('card') : $media->getUrl(),
                    'small' => $media->hasGeneratedConversion('small') ? $media->getUrl('small') : $media->getUrl(),
                    'medium' => $media->hasGeneratedConversion('medium') ? $media->getUrl('medium') : $media->getUrl(),
                    'large' => $media->hasGeneratedConversion('large') ? $media->getUrl('large') : $media->getUrl(),
                    'zoom' => $media->hasGeneratedConversion('zoom') ? $media->getUrl('zoom') : $media->getUrl(),
                    'alt' => $media->getCustomProperty('alt', $this->nom),
                    'is_primary' => $media->collection_name === 'image_principale' || $media->getCustomProperty('is_primary', false),
                    'order' => $media->order_column,
                ];
            })->sortBy('order')->values()->toArray();
        });
    }

    public function getPrimaryImage(): ?array
    {
        $images = $this->getAllImages();

        return collect($images)->firstWhere('is_primary', true) ?? $images[0] ?? null;
    }

    // ========== ACCESSORS ==========

    public function getImagePrincipaleAttribute(): ?string
    {
        return $this->getImageUrl('medium');
    }

    public function getImagePrincipaleThumbAttribute(): ?string
    {
        return $this->getImageUrl('thumb');
    }

    public function getImagePrincipaleCardAttribute(): ?string
    {
        return $this->getImageUrl('card');
    }

    public function getImagesAttribute(): array
    {
        return $this->getAllImages();
    }

    public function getPrixActuelAttribute(): float
    {
        return $this->prix_promotion ?? $this->prix_ttc;
    }

    public function getEstEnPromotionAttribute(): bool
    {
        return ! is_null($this->prix_promotion) && $this->prix_promotion < $this->prix_ttc;
    }

    public function getReductionPourcentageAttribute(): ?float
    {
        if (! $this->est_en_promotion) {
            return null;
        }

        return round((($this->prix_ttc - $this->prix_promotion) / $this->prix_ttc) * 100, 0);
    }

    public function getStockTotalAttribute(): int
    {
        return $this->entrepots()->sum('produit_entrepot.quantite');
    }

    public function getStockDisponibleAttribute(): int
    {
        $stockEntrepots = $this->entrepots()->sum('produit_entrepot.quantite')
                          - $this->entrepots()->sum('produit_entrepot.quantite_reservee');

        return $stockEntrepots > 0 ? $stockEntrepots : (int) $this->quantite_stock;
    }

    public function getStockReserveAttribute(): int
    {
        return $this->entrepots()->sum('produit_entrepot.quantite_reservee');
    }

    public function getEstEnStockAttribute(): bool
    {
        return $this->stock_disponible > 0;
    }

    public function getNoteMoyenneAttribute(): float
    {
        return round($this->approvedAvis()->avg('note') ?? 0, 1);
    }

    public function getNombreAvisAttribute(): int
    {
        return $this->approvedAvis()->count();
    }

    public function getUrlAttribute(): string
    {
        return route('tenant.product.show', $this->slug);
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->attributes['seo_title'] ?? $this->nom;
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->attributes['seo_description'] ?? Str::limit(strip_tags($this->short_description ?? $this->description_longue ?? ''), 160);
    }

    public function buildSearchDocument(): string
    {
        return Str::squish(collect([
            $this->nom,
            $this->reference,
            $this->sku,
            $this->ean,
            $this->short_description,
            $this->description_longue,
            $this->brand?->nom,
            $this->categories->pluck('nom')->implode(' '),
            collect($this->attributes ?? [])->flatten()->implode(' '),
            collect($this->attributs ?? [])->flatten()->implode(' '),
        ])->filter()->implode(' '));
    }

    // ========== SCOPES ==========

    public function scopePublished($query)
    {
        return $query->where('statut', self::STATUS_PUBLISHED)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeInStock($query)
    {
        return $query->where('quantite_stock', '>', 0);
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('prix_promotion')
            ->whereColumn('prix_promotion', '<', 'prix_ttc');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true)
            ->orWhere('created_at', '>=', now()->subDays(30));
    }

    public function scopeBestseller($query)
    {
        return $query->where('is_bestseller', true)
            ->orWhere('sold_count', '>', 0)
            ->orderBy('sold_count', 'asc');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('categories', fn ($q) => $q->where('produit_categories.id', $categoryId));
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('prix_ttc', [$min, $max]);
    }

    public function scopeByCategorySlug($query, $slug)
    {
        return $query->whereHas('categories', fn ($q) => $q->where('produit_categories.slug', $slug));
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nom', 'like', "%{$term}%")
                ->orWhere('description_longue', 'like', "%{$term}%")
                ->orWhere('short_description', 'like', "%{$term}%")
                ->orWhere('reference', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('ean', 'like', "%{$term}%");
        });
    }

    // ========== MÉTHODES MÉTIER ==========

    public function hasSufficientStock(int $quantity): bool
    {
        return $this->stock_disponible >= $quantity;
    }

    public function reserveStock(int $quantity): bool
    {
        if (! $this->hasSufficientStock($quantity)) {
            return false;
        }

        $this->quantite_stock -= $quantity;
        $this->save();

        return true;
    }

    public function releaseStock(int $quantity): void
    {
        $this->quantite_stock += $quantity;

        if ($this->status === self::STATUS_OUT_OF_STOCK && $this->quantite_stock > 0) {
            $this->status = self::STATUS_PUBLISHED;
        }

        $this->save();
    }

    public function decrementerStock(int $quantity): bool
    {
        if (! $this->hasSufficientStock($quantity)) {
            return false;
        }

        $this->quantite_stock -= $quantity;
        $this->sold_count += $quantity;

        if ($this->quantite_stock <= 0 && $this->status === self::STATUS_PUBLISHED) {
            $this->status = self::STATUS_OUT_OF_STOCK;
        }

        $this->save();

        return true;
    }

    public function incrementerStock(int $quantity): void
    {
        $this->quantite_stock += $quantity;

        if ($this->status === self::STATUS_OUT_OF_STOCK && $this->quantite_stock > 0) {
            $this->status = self::STATUS_PUBLISHED;
        }

        $this->save();
    }

    public function incrementerVues(): void
    {
        $this->increment('views_count');
    }

    public function updateRating(): void
    {
        $this->reviews_count = $this->approvedAvis()->count();
        $this->average_rating = $this->approvedAvis()->avg('note') ?? 0;
        $this->save();
    }

    public function getRelatedProducts(int $limit = 6)
    {
        $categoryIds = $this->categories()->pluck('produit_categories.id');

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        return self::published()
            ->where('id', '!=', $this->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('produit_categories.id', $categoryIds))
            ->inStock()
            ->orderBy('sold_count', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getVariations(): array
    {
        $variations = [];
        $variants = $this->variantes;

        foreach ($variants as $variant) {
            $attributes = $variant->attributs ?? [];
            foreach ($attributes as $key => $value) {
                if (! isset($variations[$key])) {
                    $variations[$key] = [];
                }
                if (! in_array($value, $variations[$key])) {
                    $variations[$key][] = $value;
                }
            }
        }

        return $variations;
    }

    public function clearCache(): void
    {
        Cache::forget("product_{$this->id}_image_thumb");
        Cache::forget("product_{$this->id}_image_card");
        Cache::forget("product_{$this->id}_image_small");
        Cache::forget("product_{$this->id}_image_medium");
        Cache::forget("product_{$this->id}_image_large");
        Cache::forget("product_{$this->id}_image_zoom");
        Cache::forget("product_{$this->id}_all_images");
    }

    public function toSitemapTag(): Url|string|array
    {
        return Url::create($this->url)
            ->setLastModificationDate($this->updated_at)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.9);
    }

    public function scopeDealOfTheDay($query)
    {
        return $query->where('is_deal_of_the_day', true)
            ->where('statut', self::STATUS_PUBLISHED)
            ->where('quantite_stock', '>', 0)
            ->whereNotNull('prix_promotion')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    // ========== BOOT ==========

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($produit) {
            if (empty($produit->slug)) {
                $produit->slug = Str::slug($produit->nom);
            }

            if (empty($produit->sku)) {
                $produit->sku = 'PRD-'.strtoupper(Str::random(8));
            }
        });

        static::updating(function ($produit) {
            if ($produit->isDirty('nom') && ! $produit->isDirty('slug')) {
                $produit->slug = Str::slug($produit->nom);
            }
        });

        static::saved(function ($produit) {
            // $searchDocument = $produit->buildSearchDocument();

            // if ($produit->search_document !== $searchDocument) {
            //     $produit->forceFill([
            //         'search_document' => $searchDocument,
            //     ])->saveQuietly();
            // }

            $produit->clearCache();
        });

        static::deleted(function ($produit) {
            $produit->clearCache();
        });
    }
}
