<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
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

    protected $table = 'support_tickets';

    protected $fillable = [
        'client_id',
        'user_id',
        'categorie',
        'priorite',
        'sujet',
        'contenu',
        'statut',
        'reference',
        'ip_address',
        'user_agent',
        'closed_at',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'closed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Constantes
    const CATEGORIE_COMMANDE = 'commande';

    const CATEGORIE_LIVRAISON = 'livraison';

    const CATEGORIE_PRODUIT = 'produit';

    const CATEGORIE_PAIEMENT = 'paiement';

    const CATEGORIE_COMPTE = 'compte';

    const CATEGORIE_AUTRE = 'autre';

    const PRIORITE_BASSE = 'basse';

    const PRIORITE_MOYENNE = 'moyenne';

    const PRIORITE_HAUTE = 'haute';

    const PRIORITE_URGENTE = 'urgente';

    const STATUT_OUVERT = 'ouvert';

    const STATUT_EN_COURS = 'en_cours';

    const STATUT_EN_ATTENTE = 'en_attente';

    const STATUT_RESOLU = 'resolu';

    const STATUT_FERME = 'ferme';

    public static function getCategories(): array
    {
        return [
            self::CATEGORIE_COMMANDE => 'Commande',
            self::CATEGORIE_LIVRAISON => 'Livraison',
            self::CATEGORIE_PRODUIT => 'Produit',
            self::CATEGORIE_PAIEMENT => 'Paiement',
            self::CATEGORIE_COMPTE => 'Compte client',
            self::CATEGORIE_AUTRE => 'Autre',
        ];
    }

    public static function getPriorites(): array
    {
        return [
            self::PRIORITE_BASSE => 'Basse',
            self::PRIORITE_MOYENNE => 'Moyenne',
            self::PRIORITE_HAUTE => 'Haute',
            self::PRIORITE_URGENTE => 'Urgente',
        ];
    }

    public static function getStatuts(): array
    {
        return [
            self::STATUT_OUVERT => 'Ouvert',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_RESOLU => 'Résolu',
            self::STATUT_FERME => 'Fermé',
        ];
    }

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    // Accessors
    public function getCategorieLabelAttribute(): string
    {
        return self::getCategories()[$this->categorie] ?? $this->categorie;
    }

    public function getPrioriteLabelAttribute(): string
    {
        return self::getPriorites()[$this->priorite] ?? $this->priorite;
    }

    public function getStatutLabelAttribute(): string
    {
        return self::getStatuts()[$this->statut] ?? $this->statut;
    }

    public function getPrioriteColorAttribute(): string
    {
        return match ($this->priorite) {
            self::PRIORITE_BASSE => 'gray',
            self::PRIORITE_MOYENNE => 'warning',
            self::PRIORITE_HAUTE => 'danger',
            self::PRIORITE_URGENTE => 'danger',
            default => 'gray',
        };
    }

    public function getStatutColorAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_OUVERT => 'danger',
            self::STATUT_EN_COURS => 'warning',
            self::STATUT_EN_ATTENTE => 'info',
            self::STATUT_RESOLU => 'success',
            self::STATUT_FERME => 'gray',
            default => 'gray',
        };
    }

    public function getTempsReponseAttribute(): ?string
    {
        $firstMessage = $this->messages()->whereNotNull('user_id')->first();
        if ($firstMessage && $this->created_at) {
            $diff = $firstMessage->created_at->diffInMinutes($this->created_at);
            if ($diff < 60) {
                return "{$diff} minutes";
            }

            return floor($diff / 60).' heures';
        }

        return null;
    }

    // Scopes
    public function scopeOuverts($query)
    {
        return $query->whereIn('statut', [self::STATUT_OUVERT, self::STATUT_EN_COURS, self::STATUT_EN_ATTENTE]);
    }

    public function scopeParPriorite($query, $priorite)
    {
        return $query->where('priorite', $priorite);
    }

    public function scopeParCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    public function scopeParClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Méthodes métier
    public function ouvrir(): void
    {
        $this->statut = self::STATUT_OUVERT;
        $this->save();
    }

    public function prendreEnCharge(User $user): void
    {
        $this->user_id = $user->id;
        $this->statut = self::STATUT_EN_COURS;
        $this->save();
    }

    public function resoudre(): void
    {
        $this->statut = self::STATUT_RESOLU;
        $this->resolved_at = now();
        $this->save();
    }

    public function fermer(): void
    {
        $this->statut = self::STATUT_FERME;
        $this->closed_at = now();
        $this->save();
    }

    public function ajouterMessage(User $user, string $contenu): TicketMessage
    {
        return $this->messages()->create([
            'user_id' => $user->id,
            'contenu' => $contenu,
        ]);
    }
}
