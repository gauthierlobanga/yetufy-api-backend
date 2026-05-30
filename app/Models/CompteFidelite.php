<?php

// app/Models/CompteFidelite.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class CompteFidelite extends Model
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

    protected $table = 'compte_fidelites';

    protected $fillable = [
        'client_id',
        'programme_fidelite_id',
        'points',
        'points_cumules',
        'niveau',
        'derniere_maj',
    ];

    protected $casts = [
        'points' => 'integer',
        'points_cumules' => 'integer',
        'derniere_maj' => 'datetime',
    ];

    // Seuils des niveaux (peuvent venir du programme)
    private const SEUILS_NIVEAUX = [
        'bronze' => 0,
        'argent' => 500,
        'or' => 2000,
        'platine' => 5000,
        'diamant' => 10000,
    ];

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(ProgrammeFidelite::class, 'programme_fidelite_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionFidelite::class);
    }

    // Accessors
    public function getPointsUtilisablesAttribute(): int
    {
        return $this->points;
    }

    public function getPointsExpiresAttribute(): int
    {
        return $this->transactions()
            ->where('type', 'expiration')
            ->sum('points');
    }

    public function getNiveauLibelleAttribute(): string
    {
        $niveaux = [
            'bronze' => 'Bronze',
            'argent' => 'Argent',
            'or' => 'Or',
            'platine' => 'Platine',
            'diamant' => 'Diamant',
        ];

        return $niveaux[$this->niveau] ?? 'Bronze';
    }

    public function getTauxConversionAttribute(): float
    {
        $taux = $this->programme?->regles['taux_conversion'] ?? 100;

        return $taux; // ex: 100 points = 1€
    }

    public function getValeurPointsAttribute(): float
    {
        if ($this->taux_conversion == 0) {
            return 0;
        }

        return $this->points / $this->taux_conversion;
    }

    public function getProchainSeuilAttribute(): ?array
    {
        $seuils = $this->programme?->regles['seuils'] ?? self::SEUILS_NIVEAUX;
        $niveaux = array_keys($seuils);
        $currentIndex = array_search($this->niveau, $niveaux);

        if ($currentIndex !== false && isset($niveaux[$currentIndex + 1])) {
            $prochainNiveau = $niveaux[$currentIndex + 1];

            return [
                'niveau' => $prochainNiveau,
                'libelle' => $this->getNiveauLibelleByKey($prochainNiveau),
                'points_requis' => $seuils[$prochainNiveau],
                'points_manquants' => max(0, $seuils[$prochainNiveau] - $this->points_cumules),
            ];
        }

        return null;
    }

    private function getNiveauLibelleByKey(string $key): string
    {
        $niveaux = [
            'bronze' => 'Bronze',
            'argent' => 'Argent',
            'or' => 'Or',
            'platine' => 'Platine',
            'diamant' => 'Diamant',
        ];

        return $niveaux[$key] ?? $key;
    }

    // Scopes
    public function scopeAvecPoints($query)
    {
        return $query->where('points', '>', 0);
    }

    public function scopeParNiveau($query, $niveau)
    {
        return $query->where('niveau', $niveau);
    }

    public function scopeParClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Méthodes métier
    public function ajouterPoints(int $points, string $raison, ?Commande $commande = null): TransactionFidelite
    {
        $transaction = $this->transactions()->create([
            'type' => 'gain',
            'points' => $points,
            'raison' => $raison,
            'metadata' => $commande ? ['commande_id' => $commande->id] : null,
        ]);

        $this->points += $points;
        $this->points_cumules += $points;
        $this->derniere_maj = now();
        $this->mettreAJourNiveau();
        $this->save();

        Cache::forget("client_{$this->client_id}_fidelite");

        return $transaction;
    }

    public function utiliserPoints(int $points, string $raison): bool
    {
        if ($this->points < $points) {
            return false;
        }

        $this->transactions()->create([
            'type' => 'utilisation',
            'points' => -$points,
            'raison' => $raison,
        ]);

        $this->points -= $points;
        $this->derniere_maj = now();
        $this->save();

        Cache::forget("client_{$this->client_id}_fidelite");

        return true;
    }

    public function faireExpirerPoints(int $points, string $raison): void
    {
        $pointsExpires = min($points, $this->points);

        if ($pointsExpires <= 0) {
            return;
        }

        $this->transactions()->create([
            'type' => 'expiration',
            'points' => -$pointsExpires,
            'raison' => $raison,
        ]);

        $this->points -= $pointsExpires;
        $this->derniere_maj = now();
        $this->save();

        Cache::forget("client_{$this->client_id}_fidelite");
    }

    private function mettreAJourNiveau(): void
    {
        $seuils = $this->programme?->regles['seuils'] ?? self::SEUILS_NIVEAUX;

        foreach ($seuils as $niveau => $seuil) {
            if ($this->points_cumules >= $seuil) {
                $this->niveau = $niveau;
            }
        }

        if (empty($this->niveau)) {
            $this->niveau = 'bronze';
        }
    }

    public function getRecompenseDisponible(): ?array
    {
        $recompenses = $this->programme?->recompenses ?? [];

        foreach ($recompenses as $recompense) {
            if ($recompense['points_requis'] <= $this->points) {
                return $recompense;
            }
        }

        return null;
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::created(function ($compte) {
            Cache::forget("client_{$compte->client_id}_fidelite");
        });

        static::updated(function ($compte) {
            Cache::forget("client_{$compte->client_id}_fidelite");
        });
    }
}
