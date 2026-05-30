<?php

// app/Models/CommandeAchat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommandeAchat extends Model
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
        'fournisseur_id',
        'numero_commande',
        'date_commande',
        'date_livraison_prevue',
        'date_livraison_reelle',
        'statut',
        'sous_total_ht',
        'remise',
        'frais_livraison',
        'taxe',
        'total_ht',
        'total_ttc',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'date_commande' => 'date',
        'date_livraison_prevue' => 'date',
        'date_livraison_reelle' => 'date',
        'sous_total_ht' => 'decimal:2',
        'remise' => 'decimal:2',
        'frais_livraison' => 'decimal:2',
        'taxe' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    const STATUT_BROUILLON = 'brouillon';

    const STATUT_ENVOYEE = 'envoyee';

    const STATUT_CONFIRMEE = 'confirmee';

    const STATUT_EXPEDIEE = 'expediee';

    const STATUT_RECUE_PARTIELLE = 'recue_partielle';

    const STATUT_RECUE_TOTALE = 'recue_totale';

    const STATUT_ANNULEE = 'annulee';

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneCommandeAchat::class);
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 'Brouillon',
            self::STATUT_ENVOYEE => 'Envoyée',
            self::STATUT_CONFIRMEE => 'Confirmée',
            self::STATUT_EXPEDIEE => 'Expédiée',
            self::STATUT_RECUE_PARTIELLE => 'Reçue partiellement',
            self::STATUT_RECUE_TOTALE => 'Reçue totalement',
            self::STATUT_ANNULEE => 'Annulée',
            default => $this->statut,
        };
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->date_livraison_prevue &&
            $this->date_livraison_prevue->isPast() &&
            ! in_array($this->statut, [self::STATUT_RECUE_TOTALE, self::STATUT_ANNULEE]);
    }

    public function recalculerTotaux(): void
    {
        $this->sous_total_ht = $this->lignes->sum('total_ht');
        $this->total_ht = $this->sous_total_ht - $this->remise + $this->frais_livraison;
        $this->total_ttc = $this->total_ht + $this->taxe;
        $this->save();
    }

    public function confirmer(): void
    {
        $this->statut = self::STATUT_CONFIRMEE;
        $this->save();
    }

    public function expedier(): void
    {
        $this->statut = self::STATUT_EXPEDIEE;
        $this->save();
    }

    public function recevoir(): void
    {
        $this->statut = self::STATUT_RECUE_TOTALE;
        $this->date_livraison_reelle = now();
        $this->save();

        foreach ($this->lignes as $ligne) {
            MouvementStock::enregistrerEntree(
                $ligne->produit,
                $ligne->quantite_recue ?? $ligne->quantite,
                "ACHAT-{$this->numero_commande}"
            );
        }
    }
}
