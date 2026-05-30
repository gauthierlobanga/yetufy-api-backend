<?php

// app/Models/Coupon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
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

    protected $fillable = [
        'code',
        'nom',
        'description',
        'type',
        'valeur',
        'minimum_panier',
        'maximum_discount',
        'utilisation_max',
        'utilisation_par_utilisateur',
        'total_utilise',
        'est_actif',
        'date_debut',
        'date_fin',
        'produits_applicables',
        'categories_applicables',
        'produits_exclus',
        'utilisateurs_applicables',
        'premiere_commande',
        'cumulable',
        'free_shipping',
        'metadata',
    ];

    protected $casts = [
        'produits_applicables' => 'array',
        'categories_applicables' => 'array',
        'produits_exclus' => 'array',
        'utilisateurs_applicables' => 'array',
        'metadata' => 'array',
        'valeur' => 'decimal:2',
        'minimum_panier' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'est_actif' => 'boolean',
        'premiere_commande' => 'boolean',
        'cumulable' => 'boolean',
        'free_shipping' => 'boolean',
    ];

    const TYPE_POURCENTAGE = 'pourcentage';

    const TYPE_MONTANT_FIXE = 'montant_fixe';

    const TYPE_LIVRAISON_OFFERTE = 'livraison_offerte';

    public function getEstValideAttribute(): bool
    {
        if (! $this->est_actif) {
            return false;
        }
        if ($this->date_debut && $this->date_debut->isFuture()) {
            return false;
        }
        if ($this->date_fin && $this->date_fin->isPast()) {
            return false;
        }
        if ($this->utilisation_max && $this->total_utilise >= $this->utilisation_max) {
            return false;
        }

        return true;
    }

    public function calculerReduction(float $sousTotal): float
    {
        if (! $this->est_valide) {
            return 0;
        }
        if ($this->minimum_panier && $sousTotal < $this->minimum_panier) {
            return 0;
        }

        $discount = match ($this->type) {
            self::TYPE_POURCENTAGE => $sousTotal * ($this->valeur / 100),
            self::TYPE_MONTANT_FIXE => min($this->valeur, $sousTotal),
            self::TYPE_LIVRAISON_OFFERTE => 0,
            default => 0,
        };

        if ($this->maximum_discount && $discount > $this->maximum_discount) {
            $discount = $this->maximum_discount;
        }

        return $discount;
    }

    public function incrementUtilisation(): void
    {
        $this->increment('total_utilise');
    }

    public function scopeActif($query)
    {
        $now = now();

        return $query->where('est_actif', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('date_debut')->orWhere('date_debut', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('date_fin')->orWhere('date_fin', '>=', $now);
            });
    }
}
