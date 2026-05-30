<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statistique extends Model
{
    use HasUuids, SoftDeletes;

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

    protected $table = 'statistiques';

    protected $fillable = [
        'type',
        'donnees',
        'date_reference',
    ];

    protected function casts(): array
    {
        return [
            'donnees' => 'array',
            'date_reference' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Constantes
    public const string TYPE_VENTES_JOUR = 'ventes_jour';

    public const string TYPE_VENTES_MOIS = 'ventes_mois';

    public const string TYPE_VENTES_AN = 'ventes_an';

    public const string TYPE_TOP_PRODUITS = 'top_produits';

    public const string TYPE_TOP_CLIENTS = 'top_clients';

    public const string TYPE_PANIER_MOYEN = 'panier_moyen';

    public const string TYPE_TAUX_CONVERSION = 'taux_conversion';

    public const string TYPE_ABANDONS = 'abandons';

    public static function getTypes(): array
    {
        return [
            self::TYPE_VENTES_JOUR,
            self::TYPE_VENTES_MOIS,
            self::TYPE_VENTES_AN,
            self::TYPE_TOP_PRODUITS,
            self::TYPE_TOP_CLIENTS,
            self::TYPE_PANIER_MOYEN,
            self::TYPE_TAUX_CONVERSION,
            self::TYPE_ABANDONS,
        ];
    }

    /**
     * Relation avec le tenant
     *
     * @return BelongsTo<Tenant, Statistique>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Accessors
    public function getLibelleTypeAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_VENTES_JOUR => 'Ventes du jour',
            self::TYPE_VENTES_MOIS => 'Ventes du mois',
            self::TYPE_VENTES_AN => 'Ventes de l\'année',
            self::TYPE_TOP_PRODUITS => 'Top produits',
            self::TYPE_TOP_CLIENTS => 'Top clients',
            self::TYPE_PANIER_MOYEN => 'Panier moyen',
            self::TYPE_TAUX_CONVERSION => 'Taux de conversion',
            self::TYPE_ABANDONS => 'Paniers abandonnés',
            default => $this->type,
        };
    }

    public function getValeurAttribute()
    {
        return $this->donnees['valeur'] ?? null;
    }

    public function getEvolutionAttribute(): ?float
    {
        return $this->donnees['evolution'] ?? null;
    }

    // Méthodes utilitaires
    public static function enregistrer(string $type, array $donnees, $dateReference = null): self
    {
        return static::updateOrCreate(
            [
                'type' => $type,
                'date_reference' => $dateReference ?? now()->toDateString(),
            ],
            [
                'donnees' => $donnees,
            ]
        );
    }

    public static function getVentesJour($date = null): ?self
    {
        $date = $date ?? now()->toDateString();

        return static::where('type', self::TYPE_VENTES_JOUR)
            ->where('date_reference', $date)
            ->first();
    }

    public static function getTopProduits(int $limit = 10, $dateDebut = null, $dateFin = null): array
    {
        $query = static::where('type', self::TYPE_TOP_PRODUITS);

        if ($dateDebut && $dateFin) {
            $query->whereBetween('date_reference', [$dateDebut, $dateFin]);
        }

        return $query->latest('date_reference')
            ->first()?->donnees['produits'] ?? [];
    }

    // Scopes
    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeParDate($query, $date)
    {
        return $query->where('date_reference', $date);
    }

    public function scopeEntreDates($query, $debut, $fin)
    {
        return $query->whereBetween('date_reference', [$debut, $fin]);
    }

    public function scopeRecents($query, $limit = 30)
    {
        return $query->orderBy('date_reference', 'desc')->limit($limit);
    }
}
