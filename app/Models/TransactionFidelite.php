<?php

// app/Models/TransactionFidelite.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionFidelite extends Model
{
    use HasFactory;
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

    protected $table = 'transaction_fidelites';

    protected $fillable = [
        'compte_fidelite_id',
        'type',
        'points',
        'raison',
        'metadata',
        'date_transaction',
    ];

    protected $casts = [
        'metadata' => 'array',
        'points' => 'integer',
        'date_transaction' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    // Constantes
    const TYPE_GAIN = 'gain';

    const TYPE_UTILISATION = 'utilisation';

    const TYPE_EXPIRATION = 'expiration';

    const TYPE_AJUSTEMENT = 'ajustement';

    public static function getTypes(): array
    {
        return [
            self::TYPE_GAIN => 'Gain de points',
            self::TYPE_UTILISATION => 'Utilisation de points',
            self::TYPE_EXPIRATION => 'Expiration de points',
            self::TYPE_AJUSTEMENT => 'Ajustement',
        ];
    }

    public static function getTypeColors(): array
    {
        return [
            self::TYPE_GAIN => 'success',
            self::TYPE_UTILISATION => 'warning',
            self::TYPE_EXPIRATION => 'danger',
            self::TYPE_AJUSTEMENT => 'info',
        ];
    }

    public static function getTypeIcons(): array
    {
        return [
            self::TYPE_GAIN => 'heroicon-o-plus-circle',
            self::TYPE_UTILISATION => 'heroicon-o-minus-circle',
            self::TYPE_EXPIRATION => 'heroicon-o-clock',
            self::TYPE_AJUSTEMENT => 'heroicon-o-cog-6-tooth',
        ];
    }

    // Relations
    public function compteFidelite(): BelongsTo
    {
        return $this->belongsTo(CompteFidelite::class, 'compte_fidelite_id');
    }

    // ✅ Correction : Relation pour accéder au client via le compte fidélité
    public function client()
    {
        return $this->compteFidelite->client();
    }

    // ✅ Alternative : Accesseur pour récupérer le client
    public function getClientAttribute()
    {
        return $this->compteFidelite?->client;
    }

    // ✅ Accesseur pour le nom du client
    public function getClientNomAttribute(): string
    {
        return $this->compteFidelite?->client?->full_name ?? '-';
    }

    // ✅ Accesseur pour l'email du client
    public function getClientEmailAttribute(): string
    {
        return $this->compteFidelite?->client?->email ?? '-';
    }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public function getTypeColorAttribute(): string
    {
        return self::getTypeColors()[$this->type] ?? 'gray';
    }

    public function getTypeIconAttribute(): string
    {
        return self::getTypeIcons()[$this->type] ?? 'heroicon-o-question-mark-circle';
    }

    public function getPointsAbsolusAttribute(): int
    {
        return abs($this->points);
    }

    public function getSigneAttribute(): string
    {
        return $this->points > 0 ? '+' : '-';
    }

    public function getPointsFormattedAttribute(): string
    {
        $signe = $this->signe;
        $points = number_format($this->points_absolus, 0, ',', ' ');

        return "{$signe}{$points} pts";
    }

    public function getDateTransactionFormattedAttribute(): string
    {
        return $this->date_transaction?->format('d/m/Y H:i') ?? '-';
    }

    public function getDateTransactionDiffAttribute(): string
    {
        return $this->date_transaction?->diffForHumans() ?? '-';
    }

    public function getEstGainAttribute(): bool
    {
        return $this->type === self::TYPE_GAIN;
    }

    public function getEstUtilisationAttribute(): bool
    {
        return $this->type === self::TYPE_UTILISATION;
    }

    public function getEstExpirationAttribute(): bool
    {
        return $this->type === self::TYPE_EXPIRATION;
    }

    public function getEstAjustementAttribute(): bool
    {
        return $this->type === self::TYPE_AJUSTEMENT;
    }

    public function getValeurEuroAttribute(): ?float
    {
        $taux = $this->compteFidelite?->programme?->regles['taux_conversion'] ?? 100;
        if ($taux == 0) {
            return null;
        }

        return $this->points_absolus / $taux;
    }

    public function getValeurEuroFormattedAttribute(): string
    {
        $valeur = $this->valeur_euro;
        if ($valeur === null) {
            return '-';
        }

        return number_format($valeur, 2).' €';
    }

    // Scopes
    public function scopeGains($query)
    {
        return $query->where('type', self::TYPE_GAIN);
    }

    public function scopeUtilisations($query)
    {
        return $query->where('type', self::TYPE_UTILISATION);
    }

    public function scopeExpirations($query)
    {
        return $query->where('type', self::TYPE_EXPIRATION);
    }

    public function scopeAjustements($query)
    {
        return $query->where('type', self::TYPE_AJUSTEMENT);
    }

    public function scopePositifs($query)
    {
        return $query->where('points', '>', 0);
    }

    public function scopeNegatifs($query)
    {
        return $query->where('points', '<', 0);
    }

    public function scopeParCompte($query, $compteId)
    {
        return $query->where('compte_fidelite_id', $compteId);
    }

    public function scopeParClient($query, $clientId)
    {
        return $query->whereHas('compteFidelite', fn ($q) => $q->where('client_id', $clientId));
    }

    public function scopeEntreDates($query, $debut, $fin)
    {
        return $query->whereBetween('date_transaction', [$debut, $fin]);
    }

    public function scopeRecentes($query, $limit = 50)
    {
        return $query->orderBy('date_transaction', 'desc')->limit($limit);
    }

    // Méthodes métier
    public static function creerGain(CompteFidelite $compte, int $points, string $raison, ?array $metadata = null): self
    {
        return self::create([
            'compte_fidelite_id' => $compte->id,
            'type' => self::TYPE_GAIN,
            'points' => $points,
            'raison' => $raison,
            'metadata' => $metadata,
            'date_transaction' => now(),
        ]);
    }

    public static function creerUtilisation(CompteFidelite $compte, int $points, string $raison, ?array $metadata = null): self
    {
        return self::create([
            'compte_fidelite_id' => $compte->id,
            'type' => self::TYPE_UTILISATION,
            'points' => -$points,
            'raison' => $raison,
            'metadata' => $metadata,
            'date_transaction' => now(),
        ]);
    }

    public static function creerExpiration(CompteFidelite $compte, int $points, string $raison, ?array $metadata = null): self
    {
        return self::create([
            'compte_fidelite_id' => $compte->id,
            'type' => self::TYPE_EXPIRATION,
            'points' => -$points,
            'raison' => $raison,
            'metadata' => $metadata,
            'date_transaction' => now(),
        ]);
    }
}
