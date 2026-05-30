<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    public function updated(User $user): void
    {
        $dirty = $user->getDirty();
        if (empty($dirty)) {
            return;
        }

        // Ne synchroniser que dans le contexte CENTRAL
        if (function_exists('tenancy') && tenancy()->initialized) {
            return;
        }

        $centralConnection = config('tenancy.database.central_connection', config('database.default'));

        // Récupérer les tenants via la connexion centrale
        $tenantIds = DB::connection($centralConnection)
            ->table('user_tenant')
            ->where('user_id', $user->id)
            ->pluck('tenant_id');

        foreach ($tenantIds as $tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                $tenant->run(function () use ($user, $dirty) {
                    $tenantUser = User::find($user->id);
                    if ($tenantUser) {
                        $needsSave = false;
                        if (isset($dirty['name'])) {
                            $tenantUser->name = $user->name;
                            $needsSave = true;
                        }
                        if (isset($dirty['email'])) {
                            $tenantUser->email = $user->email;
                            $needsSave = true;
                        }
                        if (isset($dirty['password'])) {
                            $tenantUser->password = $user->password;
                            $needsSave = true;
                        }
                        if ($needsSave) {
                            $tenantUser->saveQuietly();
                        }
                    }
                });
            }
        }
    }
}
