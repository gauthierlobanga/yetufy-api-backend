<?php

// app/Models/FactureAbonnement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FactureAbonnement extends Model
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

    protected $table = 'facture_abonnements';

    protected $fillable = [
        'abonnement_id',
        'reference',
        'montant',
        'date_echeance',
        'date_paiement',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'date_echeance' => 'datetime',
            'date_paiement' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Constantes
    const STATUT_EN_ATTENTE = 'en_attente';

    const STATUT_PAYEE = 'payee';

    const STATUT_ECHUE = 'echue';

    const STATUT_ANNULEE = 'annulee';

    public static function getStatuts(): array
    {
        return [
            self::STATUT_EN_ATTENTE,
            self::STATUT_PAYEE,
            self::STATUT_ECHUE,
            self::STATUT_ANNULEE,
        ];
    }

    // Relations
    public function abonnement(): BelongsTo
    {
        return $this->belongsTo(Abonnement::class);
    }

    // Accessors
    public function getLibelleStatutAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_PAYEE => 'Payée',
            self::STATUT_ECHUE => 'Échue',
            self::STATUT_ANNULEE => 'Annulée',
            default => $this->statut,
        };
    }

    public function getEstPayeeAttribute(): bool
    {
        return $this->statut === self::STATUT_PAYEE;
    }

    public function getEstEchueAttribute(): bool
    {
        return $this->statut === self::STATUT_ECHUE ||
            ($this->statut === self::STATUT_EN_ATTENTE
                && $this->date_echeance->isPast());
    }

    public function getMontantFormatteAttribute(): string
    {
        return number_format($this->montant, 2).'€';
    }

    // Méthodes utilitaires
    public function marquerPayee(?string $reference = null): void
    {
        $this->statut = self::STATUT_PAYEE;
        $this->date_paiement = now();
        $this->save();

        // Créer un paiement associé
        Paiement::create([
            'commande_id' => null,
            'reference' => $reference ?? 'FACT-'.$this->reference,
            'mode' => Paiement::MODE_VIREMENT,
            'montant' => $this->montant,
            'statut' => Paiement::STATUT_VALIDE,
            'date_paiement' => now(),
        ]);
    }

    public function marquerEchue(): void
    {
        $this->statut = self::STATUT_ECHUE;
        $this->save();

        // Suspendre l'abonnement si nécessaire
        if ($this->abonnement && $this->abonnement->est_actif) {
            $this->abonnement->suspendre("Facture échue: {$this->reference}");
        }
    }

    public function annuler(): void
    {
        $this->statut = self::STATUT_ANNULEE;
        $this->save();
    }
}
