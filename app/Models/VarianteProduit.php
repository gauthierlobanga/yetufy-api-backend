<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class VarianteProduit extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'variante_produits';

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
        'tenant_id',
        'produit_id',
        'nom',
        'valeur',
        'supplement_prix',
        'stock',
        'sku_variante',
        'attributs',
        'order',
    ];

    protected $casts = [
        'supplement_prix' => 'decimal:2',
        'stock' => 'integer',
        'attributs' => 'array',
        'order' => 'integer',
    ];

    protected $attributes = [
        'supplement_prix' => 0,
        'stock' => 0,
        'order' => 0,
    ];

    // Durée de cache
    private const CACHE_TTL = 3600; // 1 heure

    // ========== RELATIONS ==========

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    public function itemPaniers(): HasMany
    {
        return $this->hasMany(ItemPanier::class);
    }

    public function ligneCommandes(): HasMany
    {
        return $this->hasMany(LigneCommande::class);
    }

    // ========== ACCESSORS ==========

    public function getPrixActuelAttribute(): float
    {
        $cacheKey = "variante_{$this->id}_prix_actuel";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return $this->produit->prix_actuel + $this->supplement_prix;
        });
    }

    public function getNomCompletAttribute(): string
    {
        return $this->produit->nom.' - '.$this->nom.': '.$this->valeur;
    }

    public function getFormattedPrixAttribute(): string
    {
        return number_format($this->prix_actuel, 2).' €';
    }

    public function getEstEnStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    public function getStockDisponibleAttribute(): int
    {
        // Réserver une logique de réservation si nécessaire
        $reserved = $this->itemPaniers()->sum('quantite');

        return max(0, $this->stock - $reserved);
    }

    public function getTotalVendusAttribute(): int
    {
        return $this->ligneCommandes()->sum('quantite');
    }

    // ========== SCOPES ==========

    public function scopeEnStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeRupture($query)
    {
        return $query->where('stock', '<=', 0);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeByProduit($query, $produitId)
    {
        return $query->where('produit_id', $produitId);
    }

    // ========== MÉTHODES MÉTIER ==========

    public function hasSufficientStock(int $quantity): bool
    {
        return $this->stock >= $quantity;
    }

    public function decrementerStock(int $quantity): bool
    {
        if (! $this->hasSufficientStock($quantity)) {
            return false;
        }

        $this->stock -= $quantity;
        $this->save();

        return true;
    }

    public function incrementerStock(int $quantity): void
    {
        $this->stock += $quantity;
        $this->save();
    }

    public function clearCache(): void
    {
        Cache::forget("variante_{$this->id}_prix_actuel");
    }

    // ========== BOOT ==========

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($variante) {
            $variante->clearCache();
        });

        static::deleted(function ($variante) {
            $variante->clearCache();
        });
    }
}
