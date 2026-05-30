<?php

// app/Models/AbandonPanier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbandonPanier extends Model
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

    protected $table = 'abandon_paniers';

    protected $fillable = [
        'panier_id',
        'raison',
        'etape_abandon',
        'nombre_relances',
        'derniere_relance',
        'recupere',
        'date_recuperation',
        'analytics_data',
    ];

    protected $casts = [
        'analytics_data' => 'array',
        'nombre_relances' => 'integer',
        'recupere' => 'boolean',
        'derniere_relance' => 'datetime',
        'date_recuperation' => 'datetime',
    ];

    // Constantes pour les étapes d'abandon
    const ETAPE_PANIER = 'panier';

    const ETAPE_IDENTIFICATION = 'identification';

    const ETAPE_LIVRAISON = 'livraison';

    const ETAPE_PAIEMENT = 'paiement';

    const ETAPE_CONFIRMATION = 'confirmation';

    const RAISON_PRIX = 'prix_trop_eleve';

    const RAISON_FRAIS_LIVRAISON = 'frais_livraison';

    const RAISON_COMPTE = 'creation_compte';

    const RAISON_TECHNIQUE = 'probleme_technique';

    const RAISON_COMPARAISON = 'comparaison_prix';

    const RAISON_AUTRE = 'autre';

    public static function getEtapes(): array
    {
        return [
            self::ETAPE_PANIER => 'Panier',
            self::ETAPE_IDENTIFICATION => 'Identification',
            self::ETAPE_LIVRAISON => 'Livraison',
            self::ETAPE_PAIEMENT => 'Paiement',
            self::ETAPE_CONFIRMATION => 'Confirmation',
        ];
    }

    public static function getRaisons(): array
    {
        return [
            self::RAISON_PRIX => 'Prix trop élevé',
            self::RAISON_FRAIS_LIVRAISON => 'Frais de livraison trop élevés',
            self::RAISON_COMPTE => 'Création de compte obligatoire',
            self::RAISON_TECHNIQUE => 'Problème technique',
            self::RAISON_COMPARAISON => 'Comparaison des prix',
            self::RAISON_AUTRE => 'Autre raison',
        ];
    }

    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
    }

    public function relances(): HasMany
    {
        return $this->hasMany(RelancePanier::class);
    }

    // Accessors
    public function getEtapeLabelAttribute(): string
    {
        return self::getEtapes()[$this->etape_abandon] ?? $this->etape_abandon;
    }

    public function getRaisonLabelAttribute(): string
    {
        return self::getRaisons()[$this->raison] ?? $this->raison;
    }

    public function getTempsAbandonAttribute(): ?string
    {
        if (! $this->created_at) {
            return null;
        }

        return $this->created_at->diffForHumans();
    }

    public function getTauxConversionRelancesAttribute(): float
    {
        if ($this->relances->isEmpty()) {
            return 0;
        }

        $converties = $this->relances->where('a_conduit_achat', true)->count();

        return round(($converties / $this->relances->count()) * 100, 2);
    }

    public function getDerniereRelanceAttribute(): ?RelancePanier
    {
        return $this->relances()->latest('envoye_at')->first();
    }

    public function getValeurPanierAttribute(): float
    {
        return $this->panier?->total_general ?? 0;
    }

    // Scopes
    public function scopeNonRecuperes($query)
    {
        return $query->where('recupere', false);
    }

    public function scopeRecuperes($query)
    {
        return $query->where('recupere', true);
    }

    public function scopeParEtape($query, $etape)
    {
        return $query->where('etape_abandon', $etape);
    }

    public function scopeParRaison($query, $raison)
    {
        return $query->where('raison', $raison);
    }

    public function scopeARelancer($query, $delaiHeures = 24)
    {
        return $query->where('recupere', false)
            ->where(function ($q) use ($delaiHeures) {
                $q->whereNull('derniere_relance')
                    ->orWhere('derniere_relance', '<', now()->subHours($delaiHeures));
            });
    }

    public function scopeRecents($query, $jours = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($jours));
    }

    // Méthodes métier
    public function enregistrerRelance(string $canal, array $contenu = []): RelancePanier
    {
        $relance = $this->relances()->create([
            'canal' => $canal,
            'contenu' => $contenu,
            'statut' => RelancePanier::STATUT_ENVOYE,
            'envoye_at' => now(),
        ]);

        $this->nombre_relances++;
        $this->derniere_relance = now();
        $this->save();

        return $relance;
    }

    public function marquerRecupere(?Commande $commande = null): void
    {
        $this->recupere = true;
        $this->date_recuperation = now();

        if ($commande) {
            $metadata = $this->analytics_data ?? [];
            $metadata['commande_id'] = $commande->id;
            $this->analytics_data = $metadata;
        }

        $this->save();
    }

    public function getAnalyticsData(string $key, $default = null)
    {
        return data_get($this->analytics_data, $key, $default);
    }

    public function getValeurPotentielleAttribute(): float
    {
        return $this->valeur_panier;
    }

    public function getScorePrioriteAttribute(): int
    {
        $score = 0;

        // Plus le panier a de valeur, plus la priorité est haute
        if ($this->valeur_panier > 200) {
            $score += 3;
        } elseif ($this->valeur_panier > 100) {
            $score += 2;
        } elseif ($this->valeur_panier > 50) {
            $score += 1;
        }

        // Plus on est avancé dans le tunnel, plus la priorité est haute
        $poidsEtape = [
            self::ETAPE_PAIEMENT => 4,
            self::ETAPE_LIVRAISON => 3,
            self::ETAPE_IDENTIFICATION => 2,
            self::ETAPE_PANIER => 1,
        ];
        $score += $poidsEtape[$this->etape_abandon] ?? 0;

        // Moins il y a eu de relances, plus c'est prioritaire
        $score += max(0, 3 - $this->nombre_relances);

        return min(10, $score);
    }
}
