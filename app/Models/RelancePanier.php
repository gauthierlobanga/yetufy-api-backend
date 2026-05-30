<?php

// app/Models/RelancePanier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelancePanier extends Model
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

    protected $table = 'relance_paniers';

    protected $fillable = [
        'abandon_panier_id',
        'canal',
        'statut',
        'taux_conversion',
        'contenu',
        'envoye_at',
        'ouvert_at',
        'clique_at',
    ];

    protected $casts = [
        'contenu' => 'array',
        'taux_conversion' => 'decimal:2',
        'envoye_at' => 'datetime',
        'ouvert_at' => 'datetime',
        'clique_at' => 'datetime',
    ];

    // Constantes
    const CANAL_EMAIL = 'email';

    const CANAL_SMS = 'sms';

    const CANAL_PUSH = 'push';

    const CANAL_NOTIFICATION = 'notification';

    const STATUT_ENVOYE = 'envoye';

    const STATUT_OUVERT = 'ouvert';

    const STATUT_CLIQUE = 'clique';

    const STATUT_CONVERTI = 'converti';

    const STATUT_ECHEC = 'echec';

    public static function getCanaux(): array
    {
        return [
            self::CANAL_EMAIL => 'Email',
            self::CANAL_SMS => 'SMS',
            self::CANAL_PUSH => 'Notification push',
            self::CANAL_NOTIFICATION => 'Notification',
        ];
    }

    public static function getStatuts(): array
    {
        return [
            self::STATUT_ENVOYE => 'Envoyé',
            self::STATUT_OUVERT => 'Ouvert',
            self::STATUT_CLIQUE => 'Cliqué',
            self::STATUT_CONVERTI => 'Converti',
            self::STATUT_ECHEC => 'Échec',
        ];
    }

    // Relations
    public function abandonPanier(): BelongsTo
    {
        return $this->belongsTo(AbandonPanier::class);
    }

    // Accessors
    public function getCanalLabelAttribute(): string
    {
        return self::getCanaux()[$this->canal] ?? $this->canal;
    }

    public function getStatutLabelAttribute(): string
    {
        return self::getStatuts()[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_ENVOYE => 'primary',
            self::STATUT_OUVERT => 'warning',
            self::STATUT_CLIQUE => 'info',
            self::STATUT_CONVERTI => 'success',
            self::STATUT_ECHEC => 'danger',
            default => 'gray',
        };
    }

    public function getEstOuvertAttribute(): bool
    {
        return ! is_null($this->ouvert_at);
    }

    public function getEstCliqueAttribute(): bool
    {
        return ! is_null($this->clique_at);
    }

    public function getEstConvertiAttribute(): bool
    {
        return $this->statut === self::STATUT_CONVERTI;
    }

    public function getDelaiOuvertureAttribute(): ?int
    {
        if (! $this->ouvert_at || ! $this->envoye_at) {
            return null;
        }

        return $this->envoye_at->diffInMinutes($this->ouvert_at);
    }

    public function getDelaiClicAttribute(): ?int
    {
        if (! $this->clique_at || ! $this->ouvert_at) {
            return null;
        }

        return $this->ouvert_at->diffInMinutes($this->clique_at);
    }

    // Scopes
    public function scopeEnvoyes($query)
    {
        return $query->whereIn('statut', [
            self::STATUT_ENVOYE,
            self::STATUT_OUVERT,
            self::STATUT_CLIQUE,
            self::STATUT_CONVERTI,
        ]);
    }

    public function scopeOuverts($query)
    {
        return $query->whereNotNull('ouvert_at');
    }

    public function scopeCliques($query)
    {
        return $query->whereNotNull('clique_at');
    }

    public function scopeConvertis($query)
    {
        return $query->where('statut', self::STATUT_CONVERTI);
    }

    public function scopeParCanal($query, $canal)
    {
        return $query->where('canal', $canal);
    }

    public function scopeRecentes($query, $jours = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($jours));
    }

    // Méthodes métier
    public function marquerOuvert(): void
    {
        if (! $this->ouvert_at) {
            $this->ouvert_at = now();
            $this->statut = self::STATUT_OUVERT;
            $this->save();
        }
    }

    public function marquerClique(): void
    {
        if (! $this->clique_at) {
            $this->clique_at = now();
            $this->statut = self::STATUT_CLIQUE;
            $this->save();
        }
    }

    public function marquerConverti(): void
    {
        $this->statut = self::STATUT_CONVERTI;
        $this->save();

        if ($this->abandonPanier) {
            $this->abandonPanier->marquerRecupere();
        }
    }

    public function marquerEchec(?string $raison = null): void
    {
        $this->statut = self::STATUT_ECHEC;
        if ($raison) {
            $this->contenu = array_merge($this->contenu ?? [], ['erreur' => $raison]);
        }
        $this->save();
    }

    public function getContenuTemplate(): ?string
    {
        return $this->contenu['template'] ?? null;
    }

    public function getContenuPersonnalise(): ?array
    {
        return $this->contenu['personnalisation'] ?? [];
    }
}
