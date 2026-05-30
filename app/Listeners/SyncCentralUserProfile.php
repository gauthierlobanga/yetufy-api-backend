<?php

namespace App\Listeners;

use App\Events\VendorProfileUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncCentralUserProfile
{
    public function handle(VendorProfileUpdated $event): void
    {
        $user = $event->user;
        $changes = $event->changes;

        // Ne s'exécute que dans le contexte tenant
        if (! function_exists('tenancy') || ! tenancy()->initialized) {
            return;
        }

        $centralConnection = config('tenancy.database.central_connection', config('database.default'));
        $updateData = [];

        if (isset($changes['name'])) {
            $updateData['name'] = $user->name;
        }
        if (isset($changes['email'])) {
            $updateData['email'] = $user->email;
            $updateData['email_verified_at'] = null;
            if (Schema::connection($centralConnection)->hasColumn('users', 'email_verifie')) {
                $updateData['email_verifie'] = false;
            }
        }
        if (isset($changes['password'])) {
            $updateData['password'] = $user->password;
        }

        if (! empty($updateData)) {
            DB::connection($centralConnection)
                ->table('users')
                ->where('id', $user->id)
                ->update($updateData);
        }
    }
}
