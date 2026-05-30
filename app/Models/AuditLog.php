<?php

// app/Models/AuditLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    use HasUuids, SoftDeletes;

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

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'entite_type',
        'entite_id',
        'anciennes_valeurs',
        'nouvelles_valeurs',
        'ip_adresse',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'anciennes_valeurs' => 'array',
            'nouvelles_valeurs' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Constantes
    const ACTION_CREATE = 'CREATE';

    const ACTION_UPDATE = 'UPDATE';

    const ACTION_DELETE = 'DELETE';

    const ACTION_RESTORE = 'RESTORE';

    const ACTION_LOGIN = 'LOGIN';

    const ACTION_LOGOUT = 'LOGOUT';

    const ACTION_EXPORT = 'EXPORT';

    const ACTION_IMPORT = 'IMPORT';

    public static function getActions(): array
    {
        return [
            self::ACTION_CREATE,
            self::ACTION_UPDATE,
            self::ACTION_DELETE,
            self::ACTION_RESTORE,
            self::ACTION_LOGIN,
            self::ACTION_LOGOUT,
            self::ACTION_EXPORT,
            self::ACTION_IMPORT,
        ];
    }

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getLibelleActionAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATE => 'Création',
            self::ACTION_UPDATE => 'Modification',
            self::ACTION_DELETE => 'Suppression',
            self::ACTION_RESTORE => 'Restauration',
            self::ACTION_LOGIN => 'Connexion',
            self::ACTION_LOGOUT => 'Déconnexion',
            self::ACTION_EXPORT => 'Export',
            self::ACTION_IMPORT => 'Import',
            default => $this->action,
        };
    }

    public function getNomEntiteAttribute(): string
    {
        return class_basename($this->entite_type);
    }

    public function getModificationsAttribute(): array
    {
        if (! $this->anciennes_valeurs || ! $this->nouvelles_valeurs) {
            return [];
        }

        $modifications = [];
        foreach ($this->nouvelles_valeurs as $key => $newValue) {
            $oldValue = $this->anciennes_valeurs[$key] ?? null;
            if ($oldValue != $newValue) {
                $modifications[$key] = [
                    'ancien' => $oldValue,
                    'nouveau' => $newValue,
                ];
            }
        }

        return $modifications;
    }

    // Méthodes utilitaires
    public static function log(
        string $action,
        Model $entite,
        ?array $anciennesValeurs = null,
        ?array $nouvellesValeurs = null,
        ?User $user = null
    ): self {
        return static::create([
            'tenant_id' => $entite->tenant_id ?? ($user?->getCurrentTenant()?->id),
            'user_id' => $user?->id ?? Auth::id(),
            'action' => $action,
            'entite_type' => get_class($entite),
            'entite_id' => $entite->id,
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $nouvellesValeurs,
            'ip_adresse' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // Scopes
    public function scopeParEntite($query, $entite)
    {
        return $query->where('entite_type', get_class($entite))
            ->where('entite_id', $entite->id);
    }

    public function scopeParUtilisateur($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeParAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeEntreDates($query, $debut, $fin)
    {
        return $query->whereBetween('created_at', [$debut, $fin]);
    }
}
