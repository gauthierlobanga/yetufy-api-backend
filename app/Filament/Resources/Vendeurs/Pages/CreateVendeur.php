<?php

namespace App\Filament\Resources\Vendeurs\Pages;

use App\Filament\Resources\Vendeurs\VendeurResource;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\VendorRequest;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateVendeur extends CreateRecord
{
    protected static string $resource = VendeurResource::class;

    protected function afterCreate(): void
    {
        $tenant = $this->getRecord();

        // Activer immédiatement le tenant
        $tenant->update([
            'statut' => Tenant::STATUT_ACTIF,
            'is_active' => true,
        ]);

        // Domaine (fallback si champ vide)
        $domain = $this->data['domain'] ?? str_replace('_', '-', $tenant->slug).'.localhost';
        $tenant->domains()->create([
            'id' => (string) Str::orderedUuid(),
            'domain' => $domain,
        ]);

        // Plan gratuit par défaut
        if (! $tenant->plan_id) {
            $freePlan = Plan::free()->first();
            if ($freePlan) {
                $tenant->update(['plan_id' => $freePlan->id]);

                // Définir la période d'essai
                if ($freePlan->trial_days > 0) {
                    $tenant->update([
                        'date_activation' => now(),
                        'date_expiration' => now()->addDays($freePlan->trial_days),
                    ]);
                } else {
                    $tenant->update(['date_activation' => now()]);
                }

                // Créer une demande approuvée pour tracer la création
                VendorRequest::create([
                    'user_id' => Auth::id(),
                    'plan_id' => $freePlan->id,
                    'shop_name' => $tenant->raison_sociale,
                    'shop_slug' => $tenant->slug,
                    'contact_email' => $tenant->email,
                    'status' => VendorRequest::STATUS_APPROVED,
                    'approved_at' => now(),
                    'tenant_id' => $tenant->id,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.vendeurs.index');
    }
}
