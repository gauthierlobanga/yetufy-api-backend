<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Models\User;
use Stancl\Tenancy\Exceptions\TenantDatabaseDoesNotExistException;

class TenantObserver
{
    public function updated(Tenant $tenant): void
    {
        // Ne synchroniser que si des champs pertinents ont été modifiés
        if ($tenant->wasChanged(['raison_sociale', 'email'])) {
            $this->syncUser($tenant);
        }
    }

    protected function syncUser(Tenant $tenant): void
    {
        try {
            $tenant->run(function () use ($tenant) {
                // Chercher l’utilisateur correspondant à l’ancien ou au nouvel email
                $user = User::where('email', $tenant->getOriginal('email'))->first()
                    ?? User::where('email', $tenant->email)->first();

                if ($user) {
                    $user->update([
                        'name' => $tenant->raison_sociale,
                        'email' => $tenant->email,
                        // Le mot de passe n’est pas synchronisé ici.
                    ]);
                }
            });
        } catch (TenantDatabaseDoesNotExistException $e) {
            // La base/schéma du tenant n’existe pas encore, on ignore la synchronisation.
            // Elle se fera automatiquement lors d’une future mise à jour.
        }
    }
}
