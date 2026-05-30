<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionProduit extends Pivot
{
    use SoftDeletes;

    protected $table = 'promotion_produit';

    protected $primaryKey = 'created_at';

    protected $fillable = [
        'promotion_id',
        'produit_id',
        'valeur_specifique',
        'quantite_minimale',
        'quantite_maximale',
        'est_actif',
    ];

    protected $casts = [
        'valeur_specifique' => 'decimal:2',
        'quantite_minimale' => 'integer',
        'quantite_maximale' => 'integer',
        'est_actif' => 'boolean',
    ];

    protected $attributes = [
        'est_actif' => true,
        'quantite_minimale' => 1,
    ];

    // Relations
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    // Accessors
    public function getValeurEffectiveAttribute(): float
    {
        return $this->valeur_specifique ?? $this->promotion?->valeur ?? 0;
    }

    public function getTypeReductionAttribute(): string
    {
        return $this->promotion?->type ?? 'inconnu';
    }

    public function getLibelleReductionAttribute(): string
    {
        $valeur = $this->valeur_effective;

        return match ($this->type_reduction) {
            Promotion::TYPE_POURCENTAGE => "-{$valeur}%",
            Promotion::TYPE_MONTANT_FIXE => '-'.number_format($valeur, 2).'€',
            Promotion::TYPE_LIVRAISON_OFFERTE => 'Livraison offerte',
            default => 'Réduction',
        };
    }

    public function getEstApplicableAttribute(): bool
    {
        return $this->est_actif && $this->promotion?->est_active;
    }

    // Méthodes métier
    public function aValeurSpecifique(): bool
    {
        return ! is_null($this->valeur_specifique);
    }

    public function calculerReduction(float $prixOriginal, int $quantite = 1): float
    {
        if (! $this->est_applicable) {
            return 0;
        }

        // Vérifier les limites de quantité
        if ($this->quantite_minimale && $quantite < $this->quantite_minimale) {
            return 0;
        }
        if ($this->quantite_maximale && $quantite > $this->quantite_maximale) {
            return 0;
        }

        $valeur = $this->valeur_effective;
        $prixTotal = $prixOriginal * $quantite;

        return match ($this->type_reduction) {
            Promotion::TYPE_POURCENTAGE => $prixTotal * ($valeur / 100),
            Promotion::TYPE_MONTANT_FIXE => min($valeur * $quantite, $prixTotal),
            default => 0,
        };
    }

    public function activer(): void
    {
        $this->est_actif = true;
        $this->save();
    }

    public function desactiver(): void
    {
        $this->est_actif = false;
        $this->save();
    }

    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('est_actif', true);
    }

    public function scopeParProduit($query, $produitId)
    {
        return $query->where('produit_id', $produitId);
    }

    public function scopeParPromotion($query, $promotionId)
    {
        return $query->where('promotion_id', $promotionId);
    }
}
