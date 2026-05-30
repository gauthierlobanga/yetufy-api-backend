<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RelanceClient extends Model
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

    protected $table = 'relances_clients';

    protected $fillable = [
        'client_id',
        'type',
        'sujet',
        'contenu',
        'statut',
        'date_envoi',
        'date_ouverture',
        'date_clic',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'date_envoi' => 'datetime',
        'date_ouverture' => 'datetime',
        'date_clic' => 'datetime',
    ];

    const TYPE_PANIER_ABANDONNE = 'panier_abandonne';

    const TYPE_RELANCE_COMMANDE = 'relance_commande';

    const TYPE_ANNIVERSAIRE = 'anniversaire';

    const TYPE_PROMOTION = 'promotion';

    const TYPE_AVIS = 'avis';

    const TYPE_FIDELITE = 'fidelite';

    const STATUT_EN_ATTENTE = 'en_attente';

    const STATUT_ENVOYE = 'envoye';

    const STATUT_OUVERT = 'ouvert';

    const STATUT_CLIQUE = 'clique';

    const STATUT_CONVERTI = 'converti';

    const STATUT_ECHEC = 'echec';

    public static function getTypes(): array
    {
        return [
            self::TYPE_PANIER_ABANDONNE => 'Panier abandonné',
            self::TYPE_RELANCE_COMMANDE => 'Relance commande',
            self::TYPE_ANNIVERSAIRE => 'Anniversaire',
            self::TYPE_PROMOTION => 'Promotion',
            self::TYPE_AVIS => 'Avis',
            self::TYPE_FIDELITE => 'Fidélité',
        ];
    }

    public static function getStatuts(): array
    {
        return [
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_ENVOYE => 'Envoyé',
            self::STATUT_OUVERT => 'Ouvert',
            self::STATUT_CLIQUE => 'Cliqué',
            self::STATUT_CONVERTI => 'Converti',
            self::STATUT_ECHEC => 'Échec',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public function getStatutLabelAttribute(): string
    {
        return self::getStatuts()[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_EN_ATTENTE => 'gray',
            self::STATUT_ENVOYE => 'primary',
            self::STATUT_OUVERT => 'warning',
            self::STATUT_CLIQUE => 'info',
            self::STATUT_CONVERTI => 'success',
            self::STATUT_ECHEC => 'danger',
            default => 'gray',
        };
    }

    public function marquerEnvoye(): void
    {
        $this->statut = self::STATUT_ENVOYE;
        $this->date_envoi = now();
        $this->save();
    }

    public function marquerOuvert(): void
    {
        if (! $this->date_ouverture) {
            $this->statut = self::STATUT_OUVERT;
            $this->date_ouverture = now();
            $this->save();
        }
    }

    public function marquerClique(): void
    {
        if (! $this->date_clic) {
            $this->statut = self::STATUT_CLIQUE;
            $this->date_clic = now();
            $this->save();
        }
    }

    public function marquerConverti(): void
    {
        $this->statut = self::STATUT_CONVERTI;
        $this->save();
    }
}
