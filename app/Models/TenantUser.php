<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Stancl\Tenancy\Contracts\Syncable;
use Stancl\Tenancy\Database\Concerns\ResourceSyncing;

class TenantUser extends Authenticatable implements Syncable
{
    use HasFactory;
    use ResourceSyncing, SoftDeletes;

    protected $table = 'users';

    protected $fillable = ['global_id', 'name', 'email', 'password', 'email_verified_at', 'is_active'];
    // protected $fillable = ['global_id', 'name', 'email', 'password', 'email_verified_at', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function getGlobalIdentifierKey()
    {
        return $this->getAttribute($this->getGlobalIdentifierKeyName());
    }

    public function getGlobalIdentifierKeyName(): string
    {
        return 'global_id';
    }

    public function getCentralModelName(): string
    {
        return User::class;
    }

    public function getSyncedAttributeNames(): array
    {
        return ['name', 'email', 'password'];
    }
}
