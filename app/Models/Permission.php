<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;
    use HasUuids;

    /**
     * Indique que la clé primaire est une UUID au lieu d'un entier auto-incrémenté.
     * Cela permet d'utiliser des UUID pour les identifiants de permission, ce qui est plus sécurisé et plus difficile à deviner que les identifiants auto-incrémentés.
     */
    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'guard_name',
    ];
}
