<?php

namespace App\Filament\Resources\Vendeurs\Pages;

use App\Filament\Resources\Vendeurs\VendeurResource;
use App\Models\Tenant;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\Model;

class EditVendeur extends EditRecord
{
    protected static string $resource = VendeurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Récupérer le mot de passe s’il a été fourni
        $password = $data['password'] ?? null;
        // Ne pas l’enregistrer dans le tenant si géré séparément
        unset($data['password']);
        // On le garde pour le synchroniser plus bas
        $record->update($data);
        // Synchronisation de l’utilisateur du tenant
        $this->syncTenantUser($record, $password);

        return $record;
    }

    protected function syncTenantUser(Tenant $tenant, ?string $newPassword = null): void
    {
        $tenant->run(function () use ($tenant, $newPassword) {
            $user = User::where('email', $tenant->email)->first();

            if ($user) {
                $userData = [
                    'name' => $tenant->raison_sociale,
                    'email' => $tenant->email,
                ];

                if ($newPassword) {
                    $userData['password'] = $newPassword; // le mutateur du modèle User le hachera
                }

                $user->update($userData);
            }
        });
    }

    // Dans CreateVendeur.php et EditVendeur.php
    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.vendeurs.index');
    }
}
