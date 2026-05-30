<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Promotion extends Model implements HasMedia
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

    protected $guarded = ['id'];

    protected $casts = [
        'valeur' => 'decimal:2',
        'minimum_panier' => 'decimal:2',
        'cumulable' => 'boolean',
        'est_active' => 'boolean',
        'produits_cibles' => 'array',
        'metadata' => 'array',
        'coupons' => 'array',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    const TYPE_POURCENTAGE = 'pourcentage';

    const TYPE_MONTANT_FIXE = 'montant_fixe';

    const TYPE_LIVRAISON_OFFERTE = 'livraison_offerte';

    /**
     * Relations avec les pivots personnalisés
     */
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'promotion_produit')
            ->using(PromotionProduit::class)
            ->withPivot('valeur_specifique', 'quantite_minimale', 'quantite_maximale', 'est_actif')
            ->withTimestamps()
            ->withPivotValue('est_actif', true);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'promotion_client')
            ->using(PromotionClient::class)
            ->withPivot('utilisations', 'utilisations_max', 'premiere_utilisation', 'derniere_utilisation', 'est_actif', 'notes')
            ->withTimestamps();
    }

    public function paniers()
    {
        return $this->belongsToMany(Panier::class, 'promotion_panier')
            ->using(PromotionPanier::class)
            ->withPivot('montant_applique', 'applied_at', 'code_saisi', 'est_manuelle', 'details')
            ->withTimestamps();
    }

    // Méthodes utilitaires
    public function getValeurPourProduit(Produit $produit): ?float
    {
        $pivot = $this->produits()
            ->where('produit_id', $produit->id)
            ->first();

        return $pivot?->pivot->valeur_specifique ?? $this->valeur;
    }

    public function getQuantiteMinimalePourProduit(Produit $produit): int
    {
        $pivot = $this->produits()
            ->where('produit_id', $produit->id)
            ->first();

        return $pivot?->pivot->quantite_minimale ?? 1;
    }

    public function getUtilisationsParClient(Client $client): ?PromotionClient
    {
        return $this->clients()
            ->where('client_id', $client->id)
            ->first()
            ?->pivot;
    }

    public function estApplicablePourClient(Client $client): bool
    {
        $relation = $this->clients()
            ->where('client_id', $client->id)
            ->first();

        return $relation && $relation->pivot->peut_utiliser;
    }

    /**
     * Accessors
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('banner', 'thumb') ?: null;
    }

    public function getIsCurrentlyActiveAttribute(): bool
    {
        $now = now();

        return $this->est_active &&
            (! $this->date_debut || $this->date_debut <= $now) &&
            (! $this->date_fin || $this->date_fin >= $now);
    }
    // public function getEstActiveAttribute(): bool
    // {
    //     $now = now();

    //     return (! $this->date_debut || $this->date_debut <= $now) &&
    //         (! $this->date_fin || $this->date_fin >= $now);
    // }

    public function getLibelleReductionAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_POURCENTAGE => "-{$this->valeur}%",
            self::TYPE_MONTANT_FIXE => '-'.number_format($this->valeur, 2).'€',
            self::TYPE_LIVRAISON_OFFERTE => 'Livraison offerte',
            default => $this->valeur,
        };
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('date_debut')->orWhere('date_debut', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('date_fin')->orWhere('date_fin', '>=', $now);
        });
    }

    public function calculerReduction(float $sousTotal): float
    {
        return match ($this->type) {
            self::TYPE_POURCENTAGE => $sousTotal * ($this->valeur / 100),
            self::TYPE_MONTANT_FIXE => min($this->valeur, $sousTotal),
            self::TYPE_LIVRAISON_OFFERTE => 0,
            default => 0,
        };
    }

    // Configuration des médias pour l'image bannière
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(600)
            ->height(400)
            ->fit(Fit::Crop)
            ->format('webp')
            ->quality(85);
    }

    public function getCouponsAttribute($value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return collect($decoded ?? [])
            ->map(fn ($c) => [
                'code' => $c['code'] ?? '',
                'discount' => (float) ($c['discount'] ?? 0),
                'min_amount' => (float) ($c['min_amount'] ?? 0),
            ])
            ->filter(fn ($c) => $c['code'])
            ->values()
            ->toArray();
    }

    public static function activePromotion(): ?self
    {
        return self::CurrentlyActive()
            ->latest('date_debut')
            ->first();
    }

    public function scopeCurrentlyActive($query)
    {
        $now = now();

        return $query->where('est_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('date_debut')->orWhere('date_debut', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('date_fin')->orWhere('date_fin', '>=', $now);
            });
    }
}
