<?php

// app/Models/Paiement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paiement extends Model
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
        'transaction_id',
        'mode',
        'carte_brand',
        'carte_last4',
        'montant',
        'devise',
        'statut',
        'details',
        'date_paiement',
        'date_remboursement',
    ];

    protected $casts = [
        'details' => 'array',
        'date_paiement' => 'datetime',
        'date_remboursement' => 'datetime',
        'montant' => 'decimal:2',
    ];

    const MODE_CARTE = 'carte';

    const MODE_PAYPAL = 'paypal';

    const MODE_VIREMENT = 'virement';

    const MODE_CHEQUE = 'cheque';

    const MODE_ESPECES = 'especes';

    const MODE_CRYPTO = 'crypto';

    const STATUT_EN_ATTENTE = 'en_attente';

    const STATUT_VALIDE = 'valide';

    const STATUT_ECHEC = 'echec';

    const STATUT_REMBOURSE = 'rembourse';

    const STATUT_PARTIEL = 'partiel';

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function remboursement(): HasOne
    {
        return $this->hasOne(Remboursement::class);
    }

    public function getModeLabelAttribute(): string
    {
        return match ($this->mode) {
            self::MODE_CARTE => 'Carte bancaire',
            self::MODE_PAYPAL => 'PayPal',
            self::MODE_VIREMENT => 'Virement bancaire',
            self::MODE_CHEQUE => 'Chèque',
            self::MODE_ESPECES => 'Espèces',
            self::MODE_CRYPTO => 'Cryptomonnaie',
            default => $this->mode,
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_VALIDE => 'Validé',
            self::STATUT_ECHEC => 'Échec',
            self::STATUT_REMBOURSE => 'Remboursé',
            self::STATUT_PARTIEL => 'Remboursement partiel',
            default => $this->statut,
        };
    }

    public function valider(): void
    {
        $this->statut = self::STATUT_VALIDE;
        $this->date_paiement = now();
        $this->save();

        $this->commande->marquerPayee();
    }

    public function echouer(string $raison): void
    {
        $this->statut = self::STATUT_ECHEC;
        $this->details = array_merge($this->details ?? [], ['erreur' => $raison]);
        $this->save();
    }
}
