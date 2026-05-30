<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReglePanier extends Model
{
    use HasFactory, SoftDeletes;
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

    protected $table = 'regle_paniers';

    protected $fillable = [
        'panier_id',
        'type',
        'code',
        'conditions',
        'valeur',
        'appliquee',
        'resultat',
        'applied_at',
    ];

    protected $casts = [
        'conditions' => 'array',
        'resultat' => 'array',
        'valeur' => 'decimal:2',
        'appliquee' => 'boolean',
        'applied_at' => 'datetime',
    ];

    // Constantes
    const TYPE_REMISE_POURCENTAGE = 'remise_pourcentage';

    const TYPE_REMISE_MONTANT = 'remise_montant';

    const TYPE_LIVRAISON_OFFERTE = 'livraison_offerte';

    const TYPE_PRODUIT_OFFERT = 'produit_offert';

    const TYPE_CODE_PROMO = 'code_promo';

    public static function getTypes(): array
    {
        return [
            self::TYPE_REMISE_POURCENTAGE => 'Remise en pourcentage',
            self::TYPE_REMISE_MONTANT => 'Remise en montant',
            self::TYPE_LIVRAISON_OFFERTE => 'Livraison offerte',
            self::TYPE_PRODUIT_OFFERT => 'Produit offert',
            self::TYPE_CODE_PROMO => 'Code promo',
        ];
    }

    // Relations
    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
    }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public function getLibelleAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_REMISE_POURCENTAGE => "Remise de {$this->valeur}%",
            self::TYPE_REMISE_MONTANT => 'Remise de '.number_format($this->valeur, 2).'€',
            self::TYPE_LIVRAISON_OFFERTE => 'Livraison offerte',
            self::TYPE_PRODUIT_OFFERT => 'Produit offert',
            self::TYPE_CODE_PROMO => "Code promo: {$this->code}",
            default => $this->code ?? 'Règle appliquée',
        };
    }

    public function getMontantReductionAttribute(): float
    {
        return $this->resultat['montant'] ?? 0;
    }

    public function getDescriptionReductionAttribute(): ?string
    {
        return $this->resultat['description'] ?? null;
    }

    public function getEstValideAttribute(): bool
    {
        // Vérifier si la règle est toujours valide
        if ($this->appliquee) {
            return true;
        }

        $conditions = $this->conditions ?? [];

        // Vérifier la date de validité
        if (isset($conditions['date_debut']) && now()->lt($conditions['date_debut'])) {
            return false;
        }
        if (isset($conditions['date_fin']) && now()->gt($conditions['date_fin'])) {
            return false;
        }

        return true;
    }

    // Scopes
    public function scopeAppliquees($query)
    {
        return $query->where('appliquee', true);
    }

    public function scopeNonAppliquees($query)
    {
        return $query->where('appliquee', false);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeParCode($query, $code)
    {
        return $query->where('code', $code);
    }

    // Méthodes métier
    public function appliquer(): void
    {
        $this->appliquee = true;
        $this->applied_at = now();
        $this->save();

        // Recalculer les totaux du panier
        $this->panier?->recalculerTotaux();
    }

    public function annuler(): void
    {
        $this->appliquee = false;
        $this->applied_at = null;
        $this->save();

        // Recalculer les totaux du panier
        $this->panier?->recalculerTotaux();
    }

    public function calculerReduction(Panier $panier): float
    {
        return match ($this->type) {
            self::TYPE_REMISE_POURCENTAGE => $panier->sous_total * ($this->valeur / 100),
            self::TYPE_REMISE_MONTANT => min($this->valeur, $panier->sous_total),
            self::TYPE_LIVRAISON_OFFERTE => $panier->total_livraison,
            default => 0,
        };
    }
}
