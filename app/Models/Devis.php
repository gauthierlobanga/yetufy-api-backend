<?php

// app/Models/Devis.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Devis extends Model
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

    protected $table = 'devis';

    protected $fillable = [
        'client_id',
        'user_id',
        'adresse_facturation_id',
        'adresse_livraison_id',
        'reference',
        'statut',
        'sous_total',
        'taxe',
        'remise',
        'total',
        'devise_id',
        'taux_change',
        'notes',
        'conditions',
        'date_validite',
        'date_envoi',
        'date_acceptation',
        'date_rejet',
        'metadata',
    ];

    protected $casts = [
        'conditions' => 'array',
        'metadata' => 'array',
        'sous_total' => 'decimal:2',
        'taxe' => 'decimal:2',
        'remise' => 'decimal:2',
        'total' => 'decimal:2',
        'taux_change' => 'decimal:4',
        'date_validite' => 'datetime',
        'date_envoi' => 'datetime',
        'date_acceptation' => 'datetime',
        'date_rejet' => 'datetime',
    ];

    const STATUT_BROUILLON = 'brouillon';

    const STATUT_ENVOYE = 'envoye';

    const STATUT_ACCEPTE = 'accepte';

    const STATUT_REFUSE = 'refuse';

    const STATUT_EXPIRE = 'expire';

    const STATUT_TRANSFORME = 'transforme'; // Transformé en commande

    public static function getStatuts(): array
    {
        return [
            self::STATUT_BROUILLON => 'Brouillon',
            self::STATUT_ENVOYE => 'Envoyé',
            self::STATUT_ACCEPTE => 'Accepté',
            self::STATUT_REFUSE => 'Refusé',
            self::STATUT_EXPIRE => 'Expiré',
            self::STATUT_TRANSFORME => 'Transformé en commande',
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

    public function adresseFacturation(): BelongsTo
    {
        return $this->belongsTo(Adresse::class, 'adresse_facturation_id');
    }

    public function adresseLivraison(): BelongsTo
    {
        return $this->belongsTo(Adresse::class, 'adresse_livraison_id');
    }

    public function devise(): BelongsTo
    {
        return $this->belongsTo(Devise::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneDevis::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    // Accessors
    public function getStatutLabelAttribute(): string
    {
        return self::getStatuts()[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 'gray',
            self::STATUT_ENVOYE => 'primary',
            self::STATUT_ACCEPTE => 'success',
            self::STATUT_REFUSE => 'danger',
            self::STATUT_EXPIRE => 'warning',
            self::STATUT_TRANSFORME => 'info',
            default => 'gray',
        };
    }

    public function getEstExpireAttribute(): bool
    {
        return $this->date_validite && $this->date_validite->isPast() && ! in_array($this->statut, [self::STATUT_ACCEPTE, self::STATUT_REFUSE]);
    }

    // public function getUrlPdfAttribute(): string
    // {
    //     return route('tenant.devis.pdf', $this->reference);
    // }

    // Scopes
    public function scopeEnvoyes($query)
    {
        return $query->where('statut', self::STATUT_ENVOYE);
    }

    public function scopeAcceptes($query)
    {
        return $query->where('statut', self::STATUT_ACCEPTE);
    }

    public function scopeValides($query)
    {
        return $query->whereIn('statut', [self::STATUT_ACCEPTE, self::STATUT_TRANSFORME]);
    }

    public function scopeParClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Méthodes métier
    public function envoyer(): void
    {
        $this->statut = self::STATUT_ENVOYE;
        $this->date_envoi = now();
        $this->save();
    }

    public function accepter(): void
    {
        $this->statut = self::STATUT_ACCEPTE;
        $this->date_acceptation = now();
        $this->save();
    }

    public function refuser(?string $raison = null): void
    {
        $this->statut = self::STATUT_REFUSE;
        $this->date_rejet = now();
        if ($raison) {
            $metadata = $this->metadata ?? [];
            $metadata['raison_refus'] = $raison;
            $this->metadata = $metadata;
        }
        $this->save();
    }

    public function transformerEnCommande(): ?Commande
    {
        if ($this->statut !== self::STATUT_ACCEPTE) {
            return null;
        }

        $commande = Commande::create([
            'client_id' => $this->client_id,
            'adresse_facturation_id' => $this->adresse_facturation_id,
            'adresse_livraison_id' => $this->adresse_livraison_id,
            'numero_commande' => 'CMD-'.strtoupper(Str::random(8)),
            'statut' => Commande::STATUT_EN_ATTENTE,
            'sous_total' => $this->sous_total,
            'taxe' => $this->taxe,
            'frais_livraison' => 0,
            'total' => $this->total,
            'notes' => "Commande issue du devis {$this->reference}",
        ]);

        foreach ($this->lignes as $ligne) {
            $commande->lignes()->create([
                'produit_id' => $ligne->produit_id,
                'variante_produit_id' => $ligne->variante_produit_id,
                'quantite' => $ligne->quantite,
                'prix_unitaire' => $ligne->prix_unitaire,
                'prix_total' => $ligne->prix_total,
                'taxe' => $ligne->taxe,
                'remise' => $ligne->remise,
                'options' => $ligne->options,
            ]);
        }

        $this->statut = self::STATUT_TRANSFORME;
        $this->commande_id = $commande->id;
        $this->save();

        return $commande;
    }

    public function recalculerTotaux(): void
    {
        $this->sous_total = $this->lignes->sum('prix_total');
        $this->total = $this->sous_total + $this->taxe - $this->remise;
        $this->save();
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($devis) {
            if (empty($devis->reference)) {
                $devis->reference = 'DEV-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }
        });
    }
}
