<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionPanier extends Pivot
{
    use SoftDeletes;

    protected $table = 'promotion_panier';

    protected $fillable = [
        'promotion_id',
        'panier_id',
        'montant_applique',
        'applied_at',
        'code_saisi',
        'est_manuelle',
        'details',
    ];

    protected $casts = [
        'montant_applique' => 'decimal:2',
        'applied_at' => 'datetime',
        'est_manuelle' => 'boolean',
        'details' => 'array',
    ];

    protected $attributes = [
        'est_manuelle' => false,
    ];

    // Relations
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
    }

    // Accessors
    public function getCodePromoAttribute(): ?string
    {
        return $this->promotion?->code;
    }

    public function getTypePromotionAttribute(): ?string
    {
        return $this->promotion?->type;
    }

    public function getLibellePromotionAttribute(): string
    {
        return $this->promotion?->libelle_reduction ?? 'Promotion';
    }

    public function getEstPourcentageAttribute(): bool
    {
        return $this->promotion?->type === Promotion::TYPE_POURCENTAGE;
    }

    public function getEstManuelleLabelAttribute(): string
    {
        return $this->est_manuelle ? 'Manuelle' : 'Automatique';
    }

    public function getTauxReductionAttribute(): ?float
    {
        if (! $this->panier || ! $this->est_pourcentage) {
            return null;
        }

        return round(($this->montant_applique / max(1, $this->panier->sous_total)) * 100, 2);
    }

    public function getDetailsJsonAttribute(): string
    {
        return json_encode($this->details, JSON_PRETTY_PRINT);
    }

    // Méthodes métier
    public function annuler(): bool
    {
        // Restaurer le panier
        if ($this->panier) {
            $this->panier->total_remises -= $this->montant_applique;
            $this->panier->total_general = $this->panier->sous_total +
                                           $this->panier->total_taxes +
                                           $this->panier->total_livraison -
                                           $this->panier->total_remises;
            $this->panier->save();
        }

        return $this->delete();
    }

    public function reapply(): void
    {
        if ($this->panier && $this->promotion) {
            $nouveauMontant = $this->promotion->calculerReduction($this->panier->sous_total);
            $this->montant_applique = $nouveauMontant;
            $this->applied_at = now();
            $this->save();

            // Mettre à jour le panier
            $this->panier->total_remises += $nouveauMontant;
            $this->panier->total_general = $this->panier->sous_total +
                                           $this->panier->total_taxes +
                                           $this->panier->total_livraison -
                                           $this->panier->total_remises;
            $this->panier->save();
        }
    }

    public function ajouterDetail(string $key, $value): void
    {
        $details = $this->details ?? [];
        $details[$key] = $value;
        $this->details = $details;
        $this->save();
    }

    // Scopes
    public function scopeManuelles($query)
    {
        return $query->where('est_manuelle', true);
    }

    public function scopeAutomatiques($query)
    {
        return $query->where('est_manuelle', false);
    }

    public function scopeParPanier($query, $panierId)
    {
        return $query->where('panier_id', $panierId);
    }

    public function scopeParPromotion($query, $promotionId)
    {
        return $query->where('promotion_id', $promotionId);
    }

    public function scopeAvecMontant($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('montant_applique', '>=', $min);
        }
        if ($max) {
            $query->where('montant_applique', '<=', $max);
        }

        return $query;
    }

    public function scopeRecents($query, $jours = 30)
    {
        return $query->where('applied_at', '>=', now()->subDays($jours));
    }
}
