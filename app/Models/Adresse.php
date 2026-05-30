<?php

namespace App\Models;

use App\Contracts\Addressable;
use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;

#[Table('adresses')]
class Adresse extends Model
{
    /** @use HasFactory<AddressFactory> */
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
        'rue',
        'complement',
        'code_postal',
        'ville',
        'pays',
        'region',
        'telephone',
        'instructions',
        'est_defaut',
        'addressable_type',
        'addressable_id',
        'type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'est_defaut' => 'boolean',
            'type' => AddressType::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public static function getTypes(): array
    {
        return [
            AddressType::TYPE_FACTURATION,
            AddressType::TYPE_LIVRAISON,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['rue', 'complement', 'code_postal', 'ville', 'pays', 'region', 'telephone', 'instructions', 'est_defaut']);
    }

    /**
     * Relations
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Accessors
     */
    public function getAdresseCompleteAttribute(): string
    {
        $parts = [
            $this->rue,
            $this->complement,
            $this->code_postal.' '.$this->ville,
            $this->pays,
        ];

        return implode(', ', array_filter($parts));
    }

    public function getAdresseUneLigneAttribute(): string
    {
        return implode(' ', array_filter([
            $this->rue,
            $this->complement,
            $this->code_postal,
            $this->ville,
        ]));
    }

    public function estFacturation(): bool
    {
        return $this->type === AddressType::TYPE_FACTURATION;
    }

    public function estLivraison(): bool
    {
        return $this->type === AddressType::TYPE_LIVRAISON;
    }

    /**
     * Scopes
     */
    public function scopeFacturation($query)
    {
        return $query->where('type', AddressType::TYPE_FACTURATION);
    }

    public function scopeLivraison($query)
    {
        return $query->where('type', AddressType::TYPE_LIVRAISON);
    }

    public function scopeParDefaut($query)
    {
        return $query->where('est_defaut', true);
    }

    /**
     * Méthodes métier
     */
    public function definirCommeDefaut(): void
    {
        static::where('addressable_type', $this->adressable_type)
            ->where('addressable_id', $this->adressable_id)
            ->where('type', $this->type)
            ->update(['est_defaut' => false]);

        $this->est_defaut = true;
        $this->save();
    }

    /**
     * Copier cette adresse vers une autre entité addressable
     *
     * @param  Addressable  $target  L'entité cible vers laquelle copier l'adresse
     * @param  string|null  $type  Le type de l'adresse copiée (ex: facturation, livraison). Si null, le même type que l'original sera utilisé.
     * @return self L'adresse nouvellement créée pour l'entité cible
     */
    public function copierVers(Addressable $target, ?string $type = null): self
    {
        $newAdresse = $this->replicate();
        $newAdresse->addressable_type = get_class($target);
        $newAdresse->addressable_id = $target->id;
        $newAdresse->type = $type ?? $this->type;
        $newAdresse->est_defaut = false;
        $newAdresse->save();

        return $newAdresse;
    }

    // Dans app/Models/Adresse.php, ajoutez ces méthodes :

    // Validation d'adresse
    public function isValid(): bool
    {
        return ! empty($this->rue) && ! empty($this->code_postal) && ! empty($this->ville) && ! empty($this->pays);
    }

    // Formatage pour affichage différent
    public function getAdresseHtmlAttribute(): string
    {
        $parts = [
            $this->rue,
            $this->complement,
            $this->code_postal.' '.$this->ville,
            $this->pays,
            $this->telephone ? 'Tél: '.$this->telephone : null,
        ];

        return '<div class="adresse">'.implode('<br>', array_filter($parts)).'</div>';
    }

    // Vérifier si l'adresse est en France
    public function getEstEnFranceAttribute(): bool
    {
        return in_array($this->pays, ['France', 'FR', 'FRA']);
    }

    // Obtenir le pays en code ISO
    public function getPaysCodeAttribute(): string
    {
        $codes = [
            'France' => 'FR',
            'Belgique' => 'BE',
            'Suisse' => 'CH',
            'Canada' => 'CA',
            'États-Unis' => 'US',
            'Royaume-Uni' => 'GB',
        ];

        return $codes[$this->pays] ?? substr($this->pays, 0, 2);
    }

    // Scope par pays
    public function scopeParPays($query, string $pays)
    {
        return $query->where('pays', $pays);
    }

    public function scopeDansCodePostal($query, $codePostal)
    {
        return $query->where('code_postal', 'like', "{$codePostal}%");
    }
}
