<?php

// app/Models/ProgrammeFidelite.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgrammeFidelite extends Model
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

    protected $table = 'programme_fidelites';

    protected $fillable = [
        'nom',
        'type',
        'regles',
        'recompenses',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'regles' => 'array',
        'recompenses' => 'array',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    // Constantes
    const TYPE_POINTS = 'points';

    const TYPE_PALIERS = 'paliers';

    const TYPE_CASHBACK = 'cashback';

    public static function getTypes(): array
    {
        return [
            self::TYPE_POINTS => 'Points de fidélité',
            self::TYPE_PALIERS => 'Paliers',
            self::TYPE_CASHBACK => 'Cashback',
        ];
    }

    // Relations
    public function comptes(): HasMany
    {
        return $this->hasMany(CompteFidelite::class);
    }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public function getEstActifAttribute(): bool
    {
        $now = now();

        return (! $this->date_debut || $this->date_debut <= $now) &&
               (! $this->date_fin || $this->date_fin >= $now);
    }

    public function getNbParticipantsAttribute(): int
    {
        return $this->comptes()->count();
    }

    public function getPointsDistribuesAttribute(): int
    {
        return $this->comptes()->sum('points_cumules');
    }

    public function getPointsUtilisesAttribute(): int
    {
        return $this->comptes()->sum('points_cumules') - $this->comptes()->sum('points');
    }

    // Scopes
    public function scopeActifs($query)
    {
        $now = now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('date_debut')
                ->orWhere('date_debut', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('date_fin')
                ->orWhere('date_fin', '>=', $now);
        });
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Méthodes métier
    public function getRegleGain(): array
    {
        return $this->regles['gain'] ?? ['type' => 'montant', 'valeur' => 1, 'points' => 1];
    }

    public function getRegleSeuils(): array
    {
        return $this->regles['seuils'] ?? [];
    }

    public function getRegleExpiration(): array
    {
        return $this->regles['expiration'] ?? ['duree' => 365, 'unite' => 'jours'];
    }

    public function calculerPointsGagnes(float $montant): int
    {
        $regle = $this->getRegleGain();

        return match ($regle['type'] ?? 'montant') {
            'montant' => floor($montant / ($regle['valeur'] ?? 1)) * ($regle['points'] ?? 1),
            'quantite' => ($montant > 0 ? 1 : 0) * ($regle['points'] ?? 1),
            'pourcentage' => floor($montant * ($regle['pourcentage'] ?? 0) / 100),
            default => 0,
        };
    }

    public function getRecompensePourPoints(int $points): ?array
    {
        foreach ($this->recompenses ?? [] as $recompense) {
            if ($recompense['points_requis'] <= $points) {
                return $recompense;
            }
        }

        return null;
    }

    public function getTauxCashback(): float
    {
        if ($this->type !== self::TYPE_CASHBACK) {
            return 0;
        }

        return $this->regles['taux_cashback'] ?? 0;
    }
}
