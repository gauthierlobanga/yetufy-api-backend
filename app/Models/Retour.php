<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retour extends Model
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
        'commande_id',
        'reference',
        'motif',
        'motif_autre',
        'statut',
        'action',
        'commentaire',
        'documents',
        'date_demande',
        'date_traitement',
        'date_recuperation',
        'metadata',
    ];

    protected $casts = [
        'documents' => 'array',
        'metadata' => 'array',
        'date_demande' => 'datetime',
        'date_traitement' => 'datetime',
        'date_recuperation' => 'datetime',
    ];

    const MOTIF_DEFECTUEUX = 'defectueux';

    const MOTIF_MAUVAISE_TAILLE = 'mauvaise_taille';

    const MOTIF_NE_CORRESPOND_PAS = 'ne_correspond_pas';

    const MOTIF_ERREUR_COMMANDE = 'erreur_commande';

    const MOTIF_PRODUIT_ABIME = 'produit_abime';

    const MOTIF_LIVRAISON_TARDIVE = 'livraison_tardive';

    const MOTIF_AUTRE = 'autre';

    const STATUT_EN_ATTENTE = 'en_attente';

    const STATUT_ACCEPTE = 'accepte';

    const STATUT_REFUSE = 'refuse';

    const STATUT_EN_COURS = 'en_cours';

    const STATUT_TERMINE = 'termine';

    const ACTION_REMBOURSEMENT = 'remboursement';

    const ACTION_AVOIR = 'avoir';

    const ACTION_ECHANGE = 'echange';

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneRetour::class);
    }

    public function remboursement(): HasOne
    {
        return $this->hasOne(Remboursement::class);
    }

    public function getMotifLabelAttribute(): string
    {
        return match ($this->motif) {
            self::MOTIF_DEFECTUEUX => 'Produit défectueux',
            self::MOTIF_MAUVAISE_TAILLE => 'Mauvaise taille',
            self::MOTIF_NE_CORRESPOND_PAS => 'Ne correspond pas à la description',
            self::MOTIF_ERREUR_COMMANDE => 'Erreur dans la commande',
            self::MOTIF_PRODUIT_ABIME => 'Produit abîmé',
            self::MOTIF_LIVRAISON_TARDIVE => 'Livraison tardive',
            self::MOTIF_AUTRE => $this->motif_autre ?? 'Autre',
            default => $this->motif,
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_ACCEPTE => 'Accepté',
            self::STATUT_REFUSE => 'Refusé',
            self::STATUT_EN_COURS => 'En cours de traitement',
            self::STATUT_TERMINE => 'Terminé',
            default => $this->statut,
        };
    }

    public function getMontantTotalAttribute(): float
    {
        return $this->lignes->sum('montant');
    }

    public function accepter(): void
    {
        $this->statut = self::STATUT_ACCEPTE;
        $this->save();
    }

    public function refuser(string $raison): void
    {
        $this->statut = self::STATUT_REFUSE;
        $this->commentaire = $raison;
        $this->date_traitement = now();
        $this->save();
    }
}
