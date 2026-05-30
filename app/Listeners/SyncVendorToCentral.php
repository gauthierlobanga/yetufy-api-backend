<?php

namespace App\Listeners;

use App\Events\VendorProfileUpdated;
use Illuminate\Support\Facades\DB;

class SyncVendorToCentral
{
    public function handle(VendorProfileUpdated $event): void
    {
        $user = $event->user;
        $centralConnection = config('tenancy.database.central_connection', config('database.default'));

        $updateData = [];

        if (isset($event->changes['name'])) {
            $updateData['name'] = $user->name;
        }
        if (isset($event->changes['email'])) {
            $updateData['email'] = $user->email;
            $updateData['email_verified_at'] = null;
        }
        if (isset($event->changes['password'])) {
            $updateData['password'] = $user->password; // déjà hashé
        }

        if (! empty($updateData)) {
            DB::connection($centralConnection)
                ->table('users')
                ->where('id', $user->id)
                ->update($updateData);
        }
    }
}
