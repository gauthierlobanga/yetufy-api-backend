<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Facture extends Model
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

    protected $table = 'factures';

    protected $fillable = [
        'client_id',
        'commande_id',
        'devis_id',
        'reference',
        'statut',
        'sous_total',
        'taxe',
        'remise',
        'total',
        'devise_id',
        'taux_change',
        'notes',
        'date_emission',
        'date_echeance',
        'date_paiement',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sous_total' => 'decimal:2',
        'taxe' => 'decimal:2',
        'remise' => 'decimal:2',
        'total' => 'decimal:2',
        'taux_change' => 'decimal:4',
        'date_emission' => 'datetime',
        'date_echeance' => 'datetime',
        'date_paiement' => 'datetime',
    ];

    const STATUT_EN_ATTENTE = 'en_attente';

    const STATUT_PAYEE = 'payee';

    const STATUT_EN_RETARD = 'en_retard';

    const STATUT_ANNULEE = 'annulee';

    const STATUT_REMBOURSEE = 'remboursee';

    public static function getStatuts(): array
    {
        return [
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_PAYEE => 'Payée',
            self::STATUT_EN_RETARD => 'En retard',
            self::STATUT_ANNULEE => 'Annulée',
            self::STATUT_REMBOURSEE => 'Remboursée',
        ];
    }

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    public function devise(): BelongsTo
    {
        return $this->belongsTo(Devise::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    // Accessors
    public function getStatutLabelAttribute(): string
    {
        return self::getStatuts()[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_EN_ATTENTE => 'warning',
            self::STATUT_PAYEE => 'success',
            self::STATUT_EN_RETARD => 'danger',
            self::STATUT_ANNULEE => 'gray',
            self::STATUT_REMBOURSEE => 'info',
            default => 'gray',
        };
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->statut === self::STATUT_EN_ATTENTE && $this->date_echeance && $this->date_echeance->isPast();
    }

    public function getMontantRestantAttribute(): float
    {
        $paye = $this->paiements()->where('statut', 'valide')->sum('montant');

        return max(0, $this->total - $paye);
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($facture) {
            if (empty($facture->reference)) {
                $facture->reference = 'FACT-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }
        });
    }
}
