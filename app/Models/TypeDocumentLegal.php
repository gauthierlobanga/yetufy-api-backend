<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TypeDocumentLegal extends Model
{
    use HasUuids;

    protected $table = 'type_documents_legaux';

    protected $fillable = [
        'code',
        'nom',
        'description',
        'autorite_emettrice',
        'est_obligatoire',
        'forme_juridique',
        'ordre',
    ];

    protected $casts = [
        'est_obligatoire' => 'boolean',
        'ordre' => 'integer',
    ];

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_documents_legaux')
            ->withPivot([
                'numero_document',
                'date_delivrance',
                'date_expiration',
                'lieu_delivrance',
                'autorite_delivrance',
                'metadata',
                'est_verifie',
                'verifie_le',
                'verifie_par',
            ])
            ->withTimestamps();
    }

    /**
     * Retourne le libellé lisible d'une forme juridique.
     */
    public static function getFormeJuridiqueLabel(?string $value): string
    {
        return match ($value) {
            'societe_commerciale' => 'Société commerciale',
            'petit_commercant' => 'Petit commerçant',
            'organisation_sans_but_lucratif' => 'Organisation sans but lucratif',
            'toutes' => 'Toutes formes',
            default => $value ?? '—',
        };
    }

    /**
     * Retourne les options pour un SelectFilter Filament.
     */
    public static function getFormeJuridiqueOptions(): array
    {
        return [
            'societe_commerciale' => 'Société commerciale',
            'petit_commercant' => 'Petit commerçant',
            'organisation_sans_but_lucratif' => 'Organisation sans but lucratif',
            'toutes' => 'Toutes formes',
        ];
    }

    public function scopeObligatoires($query)
    {
        return $query->where('est_obligatoire', true);
    }

    public function scopeOptionnels($query)
    {
        return $query->where('est_obligatoire', false);
    }
}
