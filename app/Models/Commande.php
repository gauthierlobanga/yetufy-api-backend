<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commande extends Model
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
        'client_id',
        'panier_id',
        'adresse_facturation_id',
        'adresse_livraison_id',
        'numero_commande',
        'statut',
        'sous_total',
        'taxe',
        'frais_livraison',
        'total',
        'mode_paiement',
        'notes',
        'date_commande',
        'date_paiement',
        'date_expedition',
        'date_livraison',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sous_total' => 'decimal:2',
        'taxe' => 'decimal:2',
        'frais_livraison' => 'decimal:2',
        'total' => 'decimal:2',
        'date_commande' => 'datetime',
        'date_paiement' => 'datetime',
        'date_expedition' => 'datetime',
        'date_livraison' => 'datetime',
    ];

    const STATUT_EN_ATTENTE = 'en_attente';

    const STATUT_EN_COURS = 'en_cours';

    const STATUT_TERMINE = 'termine';

    const STATUT_ANNULE = 'annule';

    const STATUT_REJETE = 'rejete';

    public static function getStatuts(): array
    {
        return [
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_TERMINE => 'Terminée',
            self::STATUT_ANNULE => 'Annulée',
            self::STATUT_REJETE => 'Rejetée',
        ];
    }

    /**
     * Relations
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
    }

    public function adresseFacturation(): BelongsTo
    {
        return $this->belongsTo(Adresse::class, 'adresse_facturation_id');
    }

    public function adresseLivraison(): BelongsTo
    {
        return $this->belongsTo(Adresse::class, 'adresse_livraison_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    /**
     * Accessors
     */
    public function getLibelleStatutAttribute(): string
    {
        return self::getStatuts()[$this->statut] ?? $this->statut;
    }

    public function getTotalPayeAttribute(): float
    {
        return $this->paiements()->where('statut', 'valide')->sum('montant');
    }

    public function getMontantRestantAttribute(): float
    {
        return max(0, $this->total - $this->total_paye);
    }

    /**
     * Méthodes métier
     */
    public function marquerPayee(): void
    {
        $this->statut = self::STATUT_EN_COURS;
        $this->date_paiement = now();
        $this->save();
    }

    public function marquerExpediee(): void
    {
        $this->statut = self::STATUT_EN_COURS;
        $this->date_expedition = now();
        $this->save();
    }

    public function marquerLivree(): void
    {
        $this->statut = self::STATUT_TERMINE;
        $this->date_livraison = now();
        $this->save();
    }

    public function annuler(): void
    {
        $this->statut = self::STATUT_ANNULE;
        $this->save();
    }
}
