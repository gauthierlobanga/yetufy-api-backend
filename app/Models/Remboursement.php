<?php

// app/Models/Remboursement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remboursement extends Model
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
        'paiement_id',
        'retour_id',
        'reference',
        'montant',
        'mode',
        'statut',
        'motif',
        'details',
        'date_remboursement',
    ];

    protected $casts = [
        'details' => 'array',
        'date_remboursement' => 'datetime',
        'montant' => 'decimal:2',
    ];

    const STATUT_EN_ATTENTE = 'en_attente';

    const STATUT_VALIDE = 'valide';

    const STATUT_ECHEC = 'echec';

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }

    public function retour(): BelongsTo
    {
        return $this->belongsTo(Retour::class);
    }

    public function valider(): void
    {
        $this->statut = self::STATUT_VALIDE;
        $this->date_remboursement = now();
        $this->save();

        // Vérifier si le paiement est totalement remboursé
        $montantRembourse = $this->paiement->remboursement()->sum('montant');
        if ($montantRembourse >= $this->paiement->montant) {
            $this->paiement->statut = Paiement::STATUT_REMBOURSE;
            $this->paiement->save();
        } else {
            $this->paiement->statut = Paiement::STATUT_PARTIEL;
            $this->paiement->save();
        }
    }
}
