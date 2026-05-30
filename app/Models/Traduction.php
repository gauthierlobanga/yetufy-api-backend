<?php

// app/Models/Traduction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Traduction extends Model
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
        'langue',
        'entite_type',
        'entite_id',
        'champ',
        'valeur',
    ];

    const LANGUE_FR = 'fr';

    const LANGUE_EN = 'en';

    const LANGUE_ES = 'es';

    const LANGUE_DE = 'de';

    const LANGUE_IT = 'it';

    public static function getLangues(): array
    {
        return [
            self::LANGUE_FR => 'Français',
            self::LANGUE_EN => 'Anglais',
            self::LANGUE_ES => 'Espagnol',
            self::LANGUE_DE => 'Allemand',
            self::LANGUE_IT => 'Italien',
        ];
    }

    /**
     * Méthodes métier
     */
    public function entite()
    {
        return $this->morphTo('entite');
    }

    public static function traduireOuDefaut(Model $entite, string $champ, ?string $langue = null): string
    {
        $langue = $langue ?? app()->getLocale();

        $traduction = self::where('entite_type', get_class($entite))
            ->where('entite_id', $entite->id)
            ->where('champ', $champ)
            ->where('langue', $langue)
            ->first();

        return $traduction?->valeur ?? $entite->$champ ?? '';
    }

    /**
     * Scopes
     */
    public function scopeParLangue($query, $langue)
    {
        return $query->where('langue', $langue);
    }

    public function scopePourEntite($query, Model $entite)
    {
        return $query->where('entite_type', get_class($entite))
            ->where('entite_id', $entite->id);
    }
}
